<?php
class NFC_Handler
{
    private $wpdb;
    private $slugs;
    private $passedvalues;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->slugs = array(1 => "1 Hour", 6 => "6 Hours", 12 => "12 Hours", 24 => "1 Day", 168 => "1 Week", 730 => "1 Month");
    }

    /**
     * @return array
     */
    public function fetchIntervals()
    {
        //[1, 6, 12, 24, 168, 730];

        $retrieveintervalsquery = $this->wpdb->get_results("SELECT * FROM ".$this->wpdb->prefix."nfc_runintervals");

        $returnarr = array();

        foreach ($retrieveintervalsquery as $key => $value)
        {
            array_push($returnarr, array(
                "id" => $value->ID,
                "value" => $value->intervalHours,
                "slug" => (key_exists($value->intervalHours, $this->slugs) ? $this->slugs[$value->intervalHours] : $value->intervalHours)
            ));
        }

        return $returnarr;
    }

    /**
     * @param $handlername
     * @param $handlerdescription
     * @param $handlerinterval
     * @param $selectedfields
     * @return bool
     */
    public function createHandler($handlername, $handlerdescription, $handlerinterval, $selectedfields)
    {
        $nextAI = $this->getNextIncrement("nfc_handlers");
        $fieldtablename = $this->wpdb->prefix."nfc_handlerfields";
        $handlertablename = $this->wpdb->prefix."nfc_handlers";
        $now = new DateTime();
        try
        {
            foreach ($selectedfields as $val)
            {
                $this->wpdb->insert($fieldtablename, array(
                    'HandlerID' => $nextAI,
                    'FieldID' => $val
                ));
            }

            $this->wpdb->insert($handlertablename, array(
                'HandlerName' => $handlername,
                'HandlerDesc' => $handlerdescription,
                'DateCreated' => $now->format('Y-m-d H:i:s'),
                'HandlerRunInterval' => $handlerinterval
            ));
            return true;
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    public function getSubmission($fieldid)
    {
        if(isset($fieldid))
        {
            $metakey = '_field_'.$fieldid;
            return $this->wpdb->get_results("SELECT * FROM ".$this->wpdb->prefix."postmeta WHERE `meta_key` = '$metakey'");

        }
    }


    /**
     * @param null $handler
     * @return array
     */
    public function sortDuplicates($handler = null)
    {
        $dupes = $this->getDuplicates($handler);
        $struct = array();
        $finalreturnarr = array();

        foreach ($dupes as $dupe)
        {
            $handlerid = $dupe['handler'];
            $unsortedlist = $dupe['dupes'];

            $templistval = array();
            foreach ($unsortedlist as $item)
            {
                if(!in_array($item['meta_value'], $templistval))
                {
                    //array_push($templistval, array($item['post_id'] => $item['meta_value']));
                    $templistval[$item['post_id']] = $item['meta_value'];
                    array_push($struct, array("og" => $item['post_id'], "duplicates" => array()));
                }
                else
                {
                    //get key of value, this key represents the ORIGINAL submission.
                    $ogkey = array_search($item['meta_value'], $templistval);

                    $structkey = array_flip(array_keys($templistval))[$ogkey];
                    if($item['post_id'] != $ogkey&&!in_array($item['post_id'], $struct[$structkey]['duplicates']))
                    {
                        array_push($struct[$structkey]['duplicates'], $item['post_id']);
                    }
                }
            }

            //private function for this too.
            $tempogcheck = [];
            foreach ($struct as $structkey => $structcheck)
            {
                if(count($structcheck['duplicates']) == 0)
                {
                    unset($struct[$structkey]);
                }

                if(!in_array($structcheck['og'], $tempogcheck))
                {
                    array_push($tempogcheck, $structcheck['og']);
                }
                else
                {
                    unset($struct[$structkey]);
                }
            }
            array_push($finalreturnarr, array("handler" => $handlerid, "result" => $struct));
        }
        return $finalreturnarr;
    }


    /**
     * @param null $handler
     * @return array
     */
    public function getDuplicates($handler = null)
    {
        $pairs = $this->getHandlerPairs($handler);
        $returnarr = array();

        //first result in list is always the original submission. Look at the post_id!
        foreach ($pairs as $key => $pair)
        {
            $pairstr = "'_field_".implode("', '_field_", $pair)."'";

            $query = "SELECT * 
                    FROM ".$this->wpdb->prefix."postmeta
                    WHERE `meta_value` IN (
                    SELECT `meta_value`  
                    FROM ".$this->wpdb->prefix."postmeta  
                    GROUP BY `meta_value`  
                    HAVING count(*) > 1 
                    ) AND `meta_key` IN ({$pairstr}) ORDER BY `meta_id` ASC";

            $result = $this->wpdb->get_results($query);
            $result = json_decode(json_encode($result), true);
            if(count($result) > 1)
            {
                if($handler != null)
                {
                    array_push($returnarr, array("handler" => $handler, "dupes" => $result));
                }
                else
                {
                    array_push($returnarr, array("handler" => $key, "dupes" => $result));
                }
            }

        }

        return $returnarr;



    }

    /**
     * @param null $handler
     * @return array
     */
    public function getHandlerPairs($handler = null)
    {
        $handlerresult = $this->wpdb->get_results("SELECT * FROM ".$this->wpdb->prefix."nfc_handlerfields");

        //array defines what are pairs. Meaning that these need to only check if its a duplicate when all fields are the same!!!

        $handlerarray = [];

        foreach($handlerresult as $handlerkeys)
        {
            $handlerarray[$handlerkeys->HandlerID] = array();
        }

        foreach($handlerresult as $handlerpairs)
        {
            array_push($handlerarray[$handlerpairs->HandlerID], $handlerpairs->FieldID);
        }

        if($handler == null)
        {
            return $handlerarray;
        }
        else
        {
            return array($handlerarray[$handler]);
        }
    }

    public function getPostInfo($postid = null)
    {
        if($postid != null)
        {
            $returnarr = array();
            $returnarr['fields'] = array();
            $results = $this->wpdb->get_results("SELECT * FROM ".$this->wpdb->prefix."postmeta WHERE `post_id` = '$postid'");
            $getgmt = $this->wpdb->get_results("SELECT * FROM ".$this->wpdb->prefix."posts WHERE `post_name` = '{$postid}' AND `post_type` = 'nf_sub'");


            foreach ($results as $result)
            {
                $fieldid = "";
                $returnarr['postdate'] = $getgmt[0]->post_date;
                if($result->meta_key == "_form_id")
                {
                    $returnarr['parent_form'] = $result->meta_value;
                }

                if(strstr($result->meta_key, '_field_'))
                {
                    $fieldid = str_replace('_field_', '', $result->meta_key);
                    $pusharr = array(
                        "field_id" => $fieldid,
                        "meta_id" => $result->meta_id,
                        "value" => $result->meta_value,
                        "post_id" => $result->post_id
                        //array($this->getFieldInfo(5))
                    );
                    $pusharr = array_merge($pusharr, $this->getFieldInfo($fieldid));

                    array_push($returnarr['fields'], $pusharr);
                }



                //echo $fieldid;
                //$this->getFieldInfo(4);
            }
            return $returnarr;
        }
        else
        {
            return false;
        }
    }

    /**
     * @param null $fieldid
     * @return array|bool
     */
    public function getFieldInfo($fieldid = null)
    {
        if($fieldid != null)
        {
            $retrievefieldsquery = $this->wpdb->get_results("SELECT * FROM ".$this->wpdb->prefix."nf".NFVERSION."_fields WHERE `id` = $fieldid");

            return json_decode(json_encode($retrievefieldsquery), true)[0];

        }
        else
        {
            return false;
        }
    }


    public function fieldToTable($array = null)
    {
        $returnarr = array();

        $returnarr['postid'] = "";

        foreach ($array['fields'] as $key => $field)
        {
            $returnarr[$field['label']] = $field['value'];

            if(empty($returnarr['postid']))
            {
                $returnarr['postid'] = $field['post_id'];
            }

        }
        $returnarr['postdate'] = $array['postdate'];


        return $returnarr;
    }

    /**
     * @param $handlerid
     * @return bool
     */
    public function checkHandlePop($handlerid)
    {
        $dupes = $this->sortDuplicates();

        $checkarr = [];

        foreach ($dupes as $dupe)
        {
            if(!in_array($dupe['handler'], $checkarr))
            {
                array_push($checkarr, $dupe['handler']);
            }
        }

        return in_array($handlerid, $checkarr);
    }

    /**
     * @param bool $json
     * @return array|mixed|string|void
     */

    public function getHandlers($json = false, $id = null)
    {
        $returnarr = array();

        if($id == null)
        {
            $handlerresult = $this->wpdb->get_results("SELECT * FROM ".$this->wpdb->prefix."nfc_handlers ORDER BY `ID` DESC");
        }
        else
        {
            $handlerresult = $this->wpdb->get_results($this->wpdb->prepare("SELECT * FROM ".$this->wpdb->prefix."nfc_handlers WHERE `ID` = %s", $id));
        }

        foreach ($handlerresult as $handler)
        {
            $handlerID = $handler->ID;
            $handlerfieldarray = array();

            $handlerfieldresult = $this->wpdb->get_results("SELECT * FROM ".$this->wpdb->prefix."nfc_handlerfields WHERE `HandlerID` = $handlerID");

            foreach($handlerfieldresult as $field)
            {
                $retrievefieldsquery = $this->wpdb->get_results("SELECT * FROM ".$this->wpdb->prefix."nf".NFVERSION."_fields WHERE `id` = '{$field->FieldID}'");

                array_push($handlerfieldarray, array(
                    "parent_form" => $retrievefieldsquery[0]->parent_id,
                    "fieldtype" => $retrievefieldsquery[0]->type,
                    "fieldkey" => $retrievefieldsquery[0]->key,
                    "fieldlabel" => wp_strip_all_tags($retrievefieldsquery[0]->label),
                    "fieldid" => $retrievefieldsquery[0]->id
                ));



            }

            array_push($returnarr, array(
                "HandlerID" => $handler->ID,
                "HandlerName" => $handler->HandlerName,
                "HandlerDescription" => $handler->HandlerDesc,
                "DateCreated" => $handler->DateCreated,
                "HandlerIntervalID" => $handler->HandlerRunInterval,
                "HandlerIntervalSlug" => (key_exists($handler->HandlerRunInterval, $this->slugs) ? $this->slugs[$handler->HandlerRunInterval] : $handler->HandlerRunInterval." Hours"),
                "Fields" => $handlerfieldarray
            ));
        }
        if($id == null)
        {
            return ($json == true ? json_encode($returnarr) : $returnarr);
        }
        else
        {
            return ($json == true ? json_encode($returnarr[0]) : $returnarr[0]);
        }



    }

    public function deleteHandler($id)
    {
        //deletes handler and all fields assigned to it.

        $fieldremovalquery = $this->wpdb->prepare("DELETE FROM ".$this->wpdb->prefix."nfc_handlerfields WHERE `HandlerID` = %s", $id);
        $handlerremovalquery = $this->wpdb->prepare("DELETE FROM ".$this->wpdb->prefix."nfc_handlers WHERE `ID` = %s", $id);
        $this->wpdb->query($fieldremovalquery);
        $this->wpdb->query($handlerremovalquery);
        return true;

    }


    public function checkHandlerExistence($id)
    {
        $result = $this->wpdb->get_results($this->wpdb->prepare("SELECT * FROM ".$this->wpdb->prefix."nfc_handlers WHERE ID = %s", $id));

        return (count($result) >= 1 ? true : false);
    }

    /**
     * @param $handlername
     * @return bool
     */
    public function checkHandlerNameExistence($handlername)
    {
        $result = $this->wpdb->get_results($this->wpdb->prepare("SELECT * FROM ".$this->wpdb->prefix."nfc_handlers WHERE HandlerName = %s", $handlername));
        $numrows = count($result);

        if($numrows >= 1)
        {
            //already one entry in database
            return true;
        }
        else
        {
            //not existent.
            return false;
        }
    }

    /**
     * @param $table
     * @return mixed
     */
    private function getNextIncrement($table)
    {
        $result = $this->wpdb->get_results("SHOW TABLE STATUS LIKE '".$this->wpdb->prefix."$table'");

        return $result[0]->Auto_increment;
    }



    public function checkDuplicates($handler = null)
    {
        if($handler != null)
        {
            $pairs = $this->getHandlerPairs($handler);

            $lowercase = true;

            foreach ($pairs as $pair)
            {
                $originals = array();
                $duplicates = array();
                $temp = array();

                $posts = array();

                $finalarr = array();

                //only putting it in a foreach loop if chosen parameter is null.
                $pairstr = "'_field_".implode("', '_field_", $pair)."'";

                $results = $this->wpdb->get_results("SELECT * FROM ".$this->wpdb->prefix."postmeta WHERE meta_key IN (".$pairstr.") ORDER BY post_id ASC");

                /*
                 * Create collection of pairs
                 */

                foreach ($results as $key => $makematch)
                {
                    if(!in_array($makematch->post_id, $posts))
                    {
                        if(!array_key_exists($makematch->post_id, $posts))
                        {
                            $posts[$makematch->post_id] = array();
                            array_push($posts[$makematch->post_id], $makematch->meta_id);
                        }
                        else
                        {
                            array_push($posts[$makematch->post_id], $makematch->meta_id);
                        }

                        //array_push($posts, $makematch->post_id);
                    }
                }

                foreach ($posts as $postid => $metaidarr)
                {
                    $tempstr = "";

                    foreach ($metaidarr as $metaid)
                    {
                        $result = $this->wpdb->get_results("SELECT * FROM ".$this->wpdb->prefix."postmeta WHERE meta_id = {$metaid} ");
                        $tempstr .= ($lowercase == true ? strtolower($result[0]->meta_value) : $result[0]->meta_value);
                    }

                    //next post
                    array_push($temp, array("str" => $tempstr, "postid" => $postid));
                }

                foreach ($temp as $temps)
                {
                    //f(!in_array($temps['*']))
                    if(!in_array($temps['str'], $originals))
                    {
                        $originals[$temps['postid']] = $temps['str'];
                        array_push($finalarr, array("og" => $temps['postid'], "duplicates" => array()));
                    }
                    else
                    {
                        $original = array_search($temps['str'], $originals);
                        $duplicates[$temps['postid']] = $temps['str'];

                        foreach ($finalarr as $key => $checkarr)
                        {
                            if($checkarr['og'] == $original)
                            {
                                array_push($finalarr[$key]['duplicates'], $temps['postid']);
                            }
                        }
                    }

                }

                //final cleanup

                foreach ($finalarr as $key => $cleanval)
                {
                    if(count($cleanval['duplicates']) == 0)
                    {
                        unset($finalarr[$key]);
                    }
                }

            }
            return $finalarr;
        }
        else
        {
            return false;
        }
    }

}
