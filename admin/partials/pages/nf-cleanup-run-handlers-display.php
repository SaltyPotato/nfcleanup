<?php
/**
 * User: Jonah
 * Date: 7/27/2017
 * Time: 12:41 PM
 */
global $wpdb;
$parser = new NFC_Parser();
$handler = new NFC_Handler();

?>

<div class="wrap">
    <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-color--red-500 mdl-color-text--white delalldupes" style="margin-bottom: 20px;">
        Delete all duplicates
    </button>
    <div class="mdl-grid stackedpane runhandlers">

        <div class="mdl-spinner mdl-spinner--single-color mdl-js-spinner is-active loadingspinner reloadsubmissiondata"></div>

        <?php
        include_once dirname(dirname(__FILE__)).'/parts/runhandlers.php';
        ?>
    </div>
</div>