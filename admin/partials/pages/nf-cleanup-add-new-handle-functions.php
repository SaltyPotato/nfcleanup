<?php
/**
 * User: Jonah
 * Date: 7/6/2017
 * Time: 04:42 PM
 */

//include_once '../wp-content/plugins/'.__DIR__.'/includes/class/parser.class.php';
//include_once(ABSPATH.'includes/class/parser.class.php');
include_once(realpath(dirname(dirname(dirname(__DIR__)))).'/includes/class/parser.class.php');
include_once(realpath(dirname(dirname(dirname(__DIR__)))).'/includes/class/handler.class.php');

add_action('wp_ajax_nfc_get_fields', 'nfc_fetch_fields');
add_action('wp_ajax_nfc_save_new_handler', 'nfc_save_handler');
add_action('wp_ajax_nfc_fetch_handlers', 'nfc_fetch_handlers');
add_action('wp_ajax_nfc_confirm_handler_deletion', 'nfc_confirm_handle_delete');
add_action('wp_ajax_nfc_final_delete_handler', 'nfc_final_delete_handler');
add_action('wp_ajax_nfc_submission_delete', 'nfc_submission_delete');
add_action('wp_ajax_nfc_load_duplicate_subs', 'nfc_load_duplicate_subs');
add_action('wp_ajax_nfc_bulk_remove_dupes', 'nfc_bulk_remove_dupes');


function nfc_load_duplicate_subs()
{
    $parser = new NFC_Parser();
    $handler = new NFC_Handler();

    ob_start();
    include_once dirname(dirname(__FILE__)).'/parts/runhandlers.php';
    $return = ob_get_clean();

    echo substr($return, 0, -1);
}

function nfc_bulk_remove_dupes()
{
    global $wpdb;
    $handler = new NFC_Handler();
    if(check_ajax_referer('nfc-retrieve-fields', 'security'))
    {
        //nonce matches
        if (is_admin())
        {
            foreach ($handler->getHandlers() as $handlerObj)
            {
                foreach ($handler->checkDuplicates($handlerObj['HandlerID']) as $dupe)
                {
                    $dupes = $dupe['duplicates'];
                    foreach ($dupes as $dupeId)
                    {
                        $sql = "DELETE FROM `" . $wpdb->prefix . "postmeta` WHERE `post_id` = " . $dupeId;
                        $wpdb->query($sql);
                    }
                }
            }
        }
    }

}

function nfc_submission_delete()
{
    global $wpdb;
    $parser = new NFC_Parser();

    if(check_ajax_referer('nfc-retrieve-fields', 'security'))
    {
        //nonce matches
        if (is_admin())
        {
            if (isset($_POST['sid']))
            {
                $sql = "DELETE FROM `".$wpdb->prefix."postmeta` WHERE `post_id` = ".$_POST['sid'];
                $wpdb->query($sql);
                echo wp_send_json_success("true");
            }
            else
            {
                echo wp_send_json_error("An error occurred!");
            }

        }
        else
        {
            echo wp_send_json_error("You do not have permission!");
        }
    }
    else
    {
        echo wp_send_json_error("Invalid nonce!");
    }

    wp_die();

}

function nfc_fetch_fields()
{
    $parser = new NFC_Parser();
    if(check_ajax_referer('nfc-retrieve-fields', 'security'))
    {
        //nonce matches
        if(is_admin())
        {
            if(isset($_POST['fid']))
            {
                $formid = $_POST['fid'];
                echo wp_send_json_success(json_encode($parser->retrieveFields($formid)));
            }
            else
            {
                echo wp_send_json_error("An error occurred!");
            }
        }
        else
        {
            echo wp_send_json_error("You do not have permission!");
        }
    }
    else
    {
        echo wp_send_json_error("Invalid nonce!");
    }

    wp_die();
}

function nfc_save_handler()
{
    $handler = new NFC_Handler();

    if(check_ajax_referer('nfc-retrieve-fields', 'security'))
    {
        //nonce matches
        if(is_admin())
        {
            if(isset($_POST['handlername'])&&isset($_POST['handlerdescription'])&&isset($_POST['handlerinterval'])
                &&!empty($_POST['handlername'])&&!empty($_POST['handlerdescription'])&&!empty($_POST['handlerinterval']))
            {
                $handlername = $_POST['handlername'];
                $handlerdescription = $_POST['handlerdescription'];
                $handlerinterval = $_POST['handlerinterval'];
                $checkedfields = $_POST['checkedids']; //Array

                if($handler->checkHandlerNameExistence($handlername))
                {
                    //error cannot create 2 handlers with the same name.
                    wp_send_json_error("There is already a handler with the name: ".$handlername);
                }
                else
                {
                    if($handler->createHandler($handlername,$handlerdescription,$handlerinterval,$checkedfields))
                    {
                        //success
                        echo wp_send_json_success("You successfully added the handler: ".$handlername);
                    }
                    else
                    {
                        //an unknown error occurred
                        echo wp_send_json_error("An unknown error occurred");
                    }
                }
               // echo wp_send_json_success(json_encode($parser->retrieveFields($formid)));
            }
            else
            {
                echo wp_send_json_error("You need to fill in all the fields!");
            }
        }
        else
        {
            echo wp_send_json_error("You do not have permission!");
        }
    }
    else
    {
        echo wp_send_json_error("Invalid nonce!");
    }

    wp_die();
}

function nfc_fetch_handlers()
{
    $handler = new NFC_Handler();

    $displayHandlers = $handler->getHandlers();
    if(check_ajax_referer('nfc-retrieve-fields', 'security'))
    {
        //nonce matches
        if (is_admin())
        {

            if (count($displayHandlers) != 0) {
                //print_r($displayHandlers);
                foreach ($displayHandlers as $data) {
                    echo '<div class="stackedpane whitebg handlecard">';
                    echo '<h1>' . $data['HandlerName'] . '</h1>';
                    echo '<p>' . $data['HandlerDescription'] . '</p>';
                    echo '<button class="mdl-button mdl-js-button mdl-button--raised mdl-color--white" type="button">Current interval: ' . $data['HandlerIntervalSlug'] . '</button>';
                    echo '<span class="timestamp">' . $data['DateCreated'] . '</span><br><br>';
                    echo '<h5>Filtering on fields: </h5><br>';
                    echo '<div class="fieldchips">';
                    foreach ($data['Fields'] as $field) {
                        echo '<span class="mdl-chip">';
                        echo '<span class="mdl-chip__text">' . $field['fieldlabel'] . '</span>';
                        echo '</span>';
                    }
                    echo '</div>';
                    echo '<button value="' . $data['HandlerID'] . '" class="mdl-button mdl-js-button mdl-button--fab mdl-button--raised mdl-js-ripple-effect danger deletehandlerbtn">';
                    echo '<i class="material-icons">delete</i>';
                    echo '</button>';

                    echo '</div>';
                }
            }
            else
            {
                echo '<div class="custom-alert info"><b>Info</b> You currently don\'t have any handlers.</div>';
            }
        }
        else
        {
            echo '<div class="custom-alert danger"><b>Error!</b> You do not have permission!</div>';

        }
    }
    else
    {
        echo '<div class="custom-alert danger"><b>Error!</b> Invalid nonce.</div>';
    }

    wp_die();
}

function nfc_confirm_handle_delete()
{
    if(check_ajax_referer('nfc-retrieve-fields', 'security'))
    {
        //nonce matches
        if (is_admin())
        {
            //all good
            $handler = new NFC_Handler();

            if(isset($_POST['hid'])&&$handler->checkHandlerExistence($_POST['hid']))
            {
                echo wp_send_json_success($handler->getHandlers(true, $_POST['hid']));
            }
            else
            {
                echo wp_send_json_error("An unknown error occurred!");
            }
        }
        else
        {
            echo wp_send_json_error("You do not have permission!");
        }
    }
    else
    {
        echo wp_send_json_error("Invalid nonce!");
    }
    wp_die();
}

function nfc_final_delete_handler()
{
    if(check_ajax_referer('nfc-retrieve-fields', 'security'))
    {
        //nonce matches
        if (is_admin())
        {
            //all good
            $handler = new NFC_Handler();

            if(isset($_POST['hid'])&&$handler->checkHandlerExistence($_POST['hid']))
            {
                $handlerinfo = $handler->getHandlers(true, $_POST['hid']);

                $handler->deleteHandler($_POST['hid']);
                echo wp_send_json_success($handlerinfo);

            }
            else
            {
                echo wp_send_json_error("An unknown error occurred!");
            }
        }
        else
        {
            echo wp_send_json_error("You do not have permission!");
        }
    }
    else
    {
        echo wp_send_json_error("Invalid nonce!");
    }
    wp_die();
}

