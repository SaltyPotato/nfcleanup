<div class="mdl-cell mdl-cell--12-col" id="savedhandles">
    <?php
    $displayHandlers = $handler->getHandlers();
    if(count($displayHandlers) != 0)
    {
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
                <!-- Duplicates and originals -->
                <?php
                $duplicatedata = $handler->checkDuplicates($data['HandlerID']);

                foreach ($duplicatedata as $dupedata)
                {
                    echo '<div class="stackedpane">';
                    foreach ($dupedata as $key => $postid)
                    {
                        if($key == "og")
                        {
                            //code for originals.
                            $postinfo = $handler->getPostInfo($postid);
                            $tablearr = $handler->fieldToTable($postinfo);
                            ?>
                            <div class="custom-alert info centertext" style="padding:5px; margin-top: 50px;">
                                <h4 style="text-align: center">Original:</h4>
                            </div>
                            <table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp" style="width:100%;">
                                <thead>
                                <tr>
                                    <?php
                                    foreach ($tablearr as $title => $value)
                                    {
                                        if(!($title == "postid" || $title == "postdate"))
                                        {
                                            echo '<th>'.$title.'</th>';
                                        }
                                    }
                                    echo '<th>Date</th>';
                                    ?>
                                </tr>
                                <tbody>
                                <tr>
                                    <?php
                                    foreach ($tablearr as $title => $value)
                                    {
                                        if($title != "postid")
                                        {
                                            echo '<td>'.$value.'</td>';
                                        }
                                    }
                                    ?>
                                </tr>
                                </tbody>
                            </table>
                            <?php


                        }
                        else if($key == "duplicates")
                        {
                            ?>
                            <div class="custom-alert warning centertext" style="padding:5px; margin-top: 50px;">
                                <h4 style="text-align: center">Duplicates:</h4>
                            </div>
                            <table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp" style="width:100%;">
                                <thead>
                                <tr>
                                    <th>Actions</th>
                                    <?php
                                    $settitlerun = false;
                                    foreach ($postid as $pstid)
                                    {

                                    $postinfo = $handler->getPostInfo($pstid);
                                    $tablearr = $handler->fieldToTable($postinfo);

                                    if($settitlerun == false)
                                    {

                                        foreach ($tablearr as $title => $value)
                                        {
                                            if(!($title == "postid" || $title == "postdate"))
                                            {
                                                echo '<th>'.$title.'</th>';
                                            }
                                        }
                                        echo '<th>Date</th>';
                                    }
                                    $settitlerun = true;
                                    ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                echo '<tr>';
                                $postid = '';
                                foreach ($tablearr as $title => $value) {
                                    if($title == "postid")
                                    {
                                        $postid = $value;
                                        echo '<td><button pid="'.$value.'" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-color-text--red delsubbtn">Delete</button></td>';
                                    }
                                    if($title != "postid")
                                    {
                                        echo '<td pid="'.$postid.'">'.$value.'</td>';
                                    }
                                }
                                echo '</tr>';

                                }
                                ?>
                                </tbody>
                            </table>
                            <?php
                        }
                    }
                    //next duplicate data array after each iteration.
                    echo '</div>';
                }

                ?>

            </div>
            <?php
        }
    }
    else
    {
        echo '<div class="custom-alert info"><b>Info</b> You currently don\'t have any handlers.</div>';
    }
    ?>
</div>