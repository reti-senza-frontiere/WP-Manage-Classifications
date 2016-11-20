<?php
// Connect to database
$db_config = parse_ini_file(ABSPATH . "wp-content/themes/RetiSenzaFrontiere/configs/database.ini");
$pdo = new PDO("mysql:host=" . $db_config["host"] . ";dbname=rsf_data", $db_config["user"], $db_config["pass"]);
$sql = "SELECT * FROM `rsf_members` WHERE `requires_to_be_part_of_the_net` = 1 AND `evaluated` = 0";
$query = $pdo->query($sql);
$requests = count($query->fetchAll(PDO::FETCH_ASSOC));
$has_requests = ($requests > 0) ? true : false;
$users = (($requests) == 1) ? "utente" : "utenti";
$users_has = (($requests) == 1) ? "ha" : "hanno";
?>


<div class="wrap">
    <h1>
        <?php print __("Graduatorie", "rsf"); ?>
    </h1>
    <?php
    if($has_requests) {
        ?>
        <div class="card white right">
            <div class="card-content">
                <span class="card-title deep-orange-text">Ci sono richieste di valutazione!</span>
                <p>
                    <?php print '<b>' . $requests . " " . $users . '</b> ' . $users_has; ?> fatto richiesta di accedere alla Rete dell'Associazione.<br />
                    È necessaria una seduta del Consiglio Direttivo per valutarli
                </p>
            </div>
            <div class="card-action">
                <a href="?page=new-graduatoria" class="deep-orange-text right"><?php print __("Valuta ora", "rsf"); ?> <span class="fa fa-chevron-right"></span></a>
            </div>
        </div>
        <?php
    }
    ?>
    <p class="flow-text left"><?php print __("Valutazioni effettuate", "rsf"); ?></p>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <form method="post">
                        <?php $this->classifications_obj->prepare_items(); ?>
                        <?php $this->classifications_obj->display(); ?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
</div>
