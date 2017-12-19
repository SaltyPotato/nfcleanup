<?php

/**
 * @link       https://jonahgeluk.com/
 * @since      1.0.0
 *
 * @package    Nf_Cleanup
 * @subpackage Nf_Cleanup/admin/partials/pages
 */
global $wpdb;
$parser = new NFC_Parser();
$handler = new NFC_Handler();

$formquery = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."nf".NFVERSION."_forms");
?>

<div class="wrap">
    <?php
    if(count($formquery) == 0)
    {
        echo '<div class="custom-alert info"><b>Notice!</b> You need to have at least 1 Ninja form!</div>';
        wp_die();
    }
    ?>
    <div class="mdl-grid">
        <div class="mdl-cell mdl-cell--6-col stackedpane">
            <ul class="mdl-list nfcformselector">
                <?php
                foreach ($formquery as $key => $row)
                {
                    ?>
                    <li class="mdl-list__item mdl-list__item--two-line">
                    <span class="mdl-list__item-primary-content">
                      <i class="material-icons mdl-list__item-avatar">format_list_numbered</i>
                      <span><?=$row->title;?></span>
                      <span class="mdl-list__item-sub-title">Form Id: <?=$row->id;?></span>
                    </span
                        <span class="mdl-list__item-secondary-content">
                      <label class="demo-list-radio mdl-radio mdl-js-radio mdl-js-ripple-effect" for="list-option-<?=$row->id;?>">
                        <input type="radio" id="list-option-<?=$row->id;?>" class="mdl-radio__button" name="selectfid" value="<?=$row->id;?>" checked />
                      </label>
                    </span>
                    </li>
                    <?php
                }
                ?>
            </ul>

        </div>


        <div class="mdl-cell mdl-cell--6-col">
            <!--  Spinner -->

            <div class="mdl-spinner mdl-spinner--single-color mdl-js-spinner is-active loadingspinner retrievefieldspinner"></div>
            <?php
            if(count($formquery) > 0)
            {?>
                <div id="selectedformtitle" class="heading stackedpane">
                    <h5>Showing fields of form: <b><?=(count($formquery) >= 1) ? $parser->retrieveFields($formquery[count($formquery)-1]->id)[0]['parent_form'] : 'None';?></b></h5>
                </div>
            <?php
            }
            ?>
            <div id="formfieldresult">
            <?php
            if(count($formquery) >= 1)
            {
                if(count($formquery) == 1)
                {
                    $field = $parser->retrieveFields($formquery[0]->id);
                }
                else
                {
                    $field = $parser->retrieveFields($formquery[count($formquery)-1]->id);
                }

                if(count($field) == 0)
                {
                    echo '<div class="custom-alert info"><b>Notice!</b> You need to have at least 1 field in your Ninja Form!</div>';
                    wp_die();
                }
                ?>
                <?php
                foreach($field as $key => $value)
                {?>
                    <label for="nfc-field-<?=$value['fieldid'];?>" name="<?=$value['fieldlabel'];?>" fid="<?=$value['fieldid'];?>" class="mdl-switch mdl-js-switch fieldhandlecheckbox">
                        <input type="checkbox" id="nfc-field-<?=$value['fieldid'];?>" class="mdl-switch__input">
                        <span class="mdl-switch__label fieldlabel"><?=$value['fieldlabel'];?> - <i><?=$value['fieldtype'];?></i></span>
                    </label>
                    <?php
                }
            }
            ?>
            </div>
        </div>
        <div class="mdl-cell mdl-cell--6-col stackedpane" id="savedhandles">
            <div class="mdl-spinner mdl-spinner--single-color mdl-js-spinner is-active loadingspinner fetchhandlersspinner"></div>
            <?php
            $displayHandlers = $handler->getHandlers();
            if(count($displayHandlers) != 0)
            {
                //print_r($displayHandlers);
                foreach($displayHandlers as $data)
                {
                ?>
                    <div class="stackedpane whitebg handlecard">
                        <h1><?=$data['HandlerName'];?></h1>
                        <p><?=$data['HandlerDescription'];?></p>
                        <button class="mdl-button mdl-js-button mdl-button--raised mdl-color--white" type="button">Current interval: <?=$data['HandlerIntervalSlug'];?></button>
                        <span class="timestamp"><?=$data['DateCreated'];?></span><br><br>
                        <h5>Filtering on fields: </h5><br>
                        <div class="fieldchips">
                        <?php
                            foreach($data['Fields'] as $field)
                            {
                            ?>
                                <span class="mdl-chip">
                                    <span class="mdl-chip__text"><?=$field['fieldlabel'];?></span>
                                </span>
                                <?php
                            }
                            ?>
                        </div>
                        <button value="<?=$data['HandlerID'];?>" class="mdl-button mdl-js-button mdl-button--fab mdl-button--raised mdl-js-ripple-effect danger deletehandlerbtn">
                            <i class="material-icons">delete</i>
                        </button>
                            <?php
                            echo '</div>';
                        }
                    }
                    else
                    {
                        echo '<div class="custom-alert info"><b>Info</b> You currently don\'t have any handlers.</div>';
                    }
                    ?>
        </div>
        <div class="mdl-cell mdl-cell--6-col stackedpane" id="saveoptions">
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%;">
                <input class="mdl-textfield__input" type="text" id="newhandlername">
                <label class="mdl-textfield__label" for="newhandlername">Handle name ex. Handle 1</label>
            </div>
            <div class="mdl-textfield mdl-js-textfield" style="width:100%;">
                <textarea class="mdl-textfield__input" type="text" rows="3" id="newhandlerdescription" ></textarea>
                <label class="mdl-textfield__label" for="newhandlerdescription">Ex. Removes duplicate submissions.</label>
            </div>
            <button id="intervalselector" value="1" class="mdl-button mdl-js-button mdl-button--raised mdl-color--white" type="button">Current interval: 1 hour</button>
            <ul class="mdl-menu mdl-menu--bottom-left mdl-js-menu mdl-js-ripple-effect"
                for="intervalselector">
                <?php
                foreach ($handler->fetchIntervals() as $valuearr)
                {
                    echo '<li value="'.$valuearr['id'].'" class="mdl-menu__item intervalselectoritem">'.$valuearr['slug'].'</li>';
                }

                ?>
            </ul><br><br>

            <div class="stackedpane whitebg">
                <p>Your handler will filter submissions with these duplicate parameters: </p>
                <div id="fieldchips"></div>
            </div>
            <div class="mdl-spinner mdl-spinner--single-color mdl-js-spinner is-active loadingspinner savingspinner"></div>
            <div id="addnewhandlerresponse"></div>
            <button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored mdl-js-ripple-effect" id="savenewhandler">Save</button>
        </div>
    </div>
</div>

<dialog class="mdl-dialog confirmhandledelete">
    <div class="mdl-spinner mdl-spinner--single-color mdl-js-spinner is-active loadingspinner confirmhandledeletespinner"></div>
    <div class="dialogwrapper">
        <h4 class="mdl-dialog__title">Are you sure?</h4>
        <div class="mdl-dialog__content modalcontent">
            <p>
                Are you sure you want to delete: <b>Handler</b>, this action cannot be undone.
            </p>
            <div class="mdl-spinner mdl-spinner--single-color mdl-js-spinner is-active loadingspinner deletehandlerspinner"></div>
        </div>
        <div class="mdl-dialog__actions">
            <button type="button" class="mdl-button close closemodalbtn">Cancel</button>
            <button type="button" class="mdl-button btndeletehandler mdl-color-text--red" value="0">Delete</button>
        </div>
    </div>
</dialog>
