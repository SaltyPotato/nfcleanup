<?php
/**
 * Created by PhpStorm.
 * User: Jonah
 * Date: 7/13/2017
 * Time: 02:45 PM
 */
global $nfc_db_version;
global $wpdb;

$nfc_db_version = "1.1";

function nfc_install()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $tablename_handler = $wpdb->prefix."nfc_handlers";
    $tablename_handlerfields = $wpdb->prefix."nfc_handlerfields";
    $tablename_runlog = $wpdb->prefix."nfc_runlog";
    $tablename_runIntervals = $wpdb->prefix."nfc_runintervals";


    $handlerSql = "CREATE TABLE $tablename_handler (
      ID int(11) NOT NULL AUTO_INCREMENT,
      HandlerName varchar(255) DEFAULT 'Undefined' NOT NULL,
      HandlerDesc text NOT NULL,
      DateCreated datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      HandlerRunInterval smallint(4) NOT NULL,
      PRIMARY KEY  (ID)
    ) $charset_collate;";

    $handlerFieldsSql = "CREATE TABLE $tablename_handlerfields (
      ID int(11) NOT NULL AUTO_INCREMENT,
      HandlerID int(11) NOT NULL,
      FieldID int(11) NOT NULL,
      PRIMARY KEY  (ID)
    ) $charset_collate;";


    $runlogSql = "CREATE TABLE $tablename_runlog (
      ID int(11) NOT NULL AUTO_INCREMENT,
      HandlerID int(11) NOT NULL,
      DateRan datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      PRIMARY KEY  (ID)
    ) $charset_collate;";


    $runIntervalsSql = "CREATE TABLE $tablename_runIntervals (
      ID int(11) NOT NULL AUTO_INCREMENT,
      intervalHours int(11) NOT NULL,
      PRIMARY KEY  (ID)
    ) $charset_collate;";


    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    //execute queries

    //dbDelta()
    $wpdb->query($handlerSql);
    $wpdb->query($handlerFieldsSql);
    $wpdb->query($runlogSql);
    $wpdb->query($runIntervalsSql);
}

function nfc_insert_data()
{

    global $wpdb;

    $tablename_runlog = $wpdb->prefix."nfc_runintervals";

    $intervalValues = [1, 6, 12, 24, 168, 730];

    foreach ($intervalValues as $value)
    {
        $wpdb->insert(
            $tablename_runlog,
            array(
                'intervalHours' => $value
            )
        );
    }
}

function plugin_db_version_check()
{
    global $nfc_db_version;

    if(get_site_option('nfc_db_version') != $nfc_db_version)
    {
        nfc_install();
        nfc_insert_data();
        update_option('nfc_db_version', $nfc_db_version);
    }
    else
    {
        add_option('nfc_db_version', $nfc_db_version);
    }
}

