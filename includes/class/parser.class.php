<?php
class NFC_Parser
{
    private $wpdb;

    private $forbiddenfields;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;

        $this->forbiddenfields = ['submit', 'hr', 'html'];
    }

    /**
     * @param null $formid
     * @return array
     *
     */
    public function retrieveFields($formid = null)
    {
        if(isset($formid))
        {
            $fieldarray = array();

            $retrievefieldsquery = $this->wpdb->get_results("SELECT * FROM ".$this->wpdb->prefix."nf".NFVERSION."_fields WHERE `parent_id` = '{$formid}'");

            foreach($retrievefieldsquery as $key => $row)
            {
                if(!in_array($row->type, $this->forbiddenfields))
                {
                    array_push($fieldarray, array(
                        "parent_form" => $row->parent_id,
                        "fieldtype" => $row->type,
                        "fieldkey" => $row->key,
                        "fieldlabel" => wp_strip_all_tags($row->label),
                        "fieldid" => $row->id
                    ));
                }
            }

            return $fieldarray;
        }
    }
}

?>