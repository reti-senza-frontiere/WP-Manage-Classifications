<?php
// Connect to database
$db_config = parse_ini_file(ABSPATH . "wp-content/themes/RetiSenzaFrontiere/configs/database.ini");
$pdo = new PDO("mysql:host=" . $db_config["host"] . ";dbname=rsf_data", $db_config["user"], $db_config["pass"]);
$sql = "SELECT * FROM `rsf_members` WHERE `requires_to_be_part_of_the_net` = 1 AND `evaluated` = 0";
$query = $pdo->query($sql);
$has_requests = (count($query->fetchAll(PDO::FETCH_ASSOC)) > 0) ? true : false;

if(isset($_POST) && count($_POST) > 0) {
    $user_id = preg_replace("/[^\d]+/", "", $_POST["user"]);
    $digital_divide_score = preg_replace("/[^\d]+/", "", $_POST["digital_divide_score"]);
    $technical_score = preg_replace("/[^\d]+/", "", $_POST["technical_score"]);

    $insert = "INSERT INTO rsf_classifications(member_id, digital_divide_score, technical_score, total_score) VALUES(:member_id, :digital_divide_score, :technical_score, :total_score)";
    $stmt_insert = $pdo->prepare($insert);
    $stmt_insert->execute(array(
        "member_id" => $user_id,
        "digital_divide_score" => $digital_divide_score,
        "technical_score" => $technical_score,
        "total_score" => ($digital_divide_score + $technical_score)
    ));

    $update = "UPDATE `rsf_members` SET `evaluated` = :evaluated WHERE `id` = :id";
    $stmt_update = $pdo->prepare($update);
    $stmt_update->execute(array(
        "evaluated" => "1",
        "id" => $user_id
    ));

    wp_redirect("admin.php?page=graduatorie");
    exit();
}
?>
<div class="wrap">
    <h1>Aggiungi graduatoria</h1>
    <br />
    <?php
    if($has_requests) {
        ?>
        <form id="post" name="post" action="" method="post">
            <div id="poststuff">
                <div class="row">
                    <div class="col l8 m6 s12">
                        <div class="row">
                            <div class="input-field col l6 m8 s12">
                                <label class="active" for="user">Utente</label>
                                <select id="user" name="user">
                                    <option value="" disabled>Seleziona...</option>
                                    <?php
                                    $sql = "select * from `rsf_members` where `requires_to_be_part_of_the_net` = 1 order by `last_name` asc";
                                    $query = $pdo->query($sql);
                                    $members = $query->fetchAll(PDO::FETCH_ASSOC);
                                    foreach($members as $k => $v) {
                                        ?><option value="<?php print $v["id"]; ?>"><?php print $v["name"] . " " . $v["last_name"]; ?></option><?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col l6 m6 s6">
                                <label for="digital_divide_score">Divario Digitale</label>
                                <br />
                                <p class="range-field">
                                    <input type="range" id="digital_divide_score" name="digital_divide_score" min="0" max="10" value="0" />
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col l6 m6 s6">
                                <label for="technical_score">Fattibilità tecnica</label>
                                <br />
                                <p class="range-field">
                                    <input type="range" id="technical_score" name="technical_score" min="0" max="10" value="0" />
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col l6 m8 s12">
                                <a href="admin.php?page=graduatorie" class="waves-effect waves-teal btn-flat left"><span class="fa fa-chevron-left"></span> Annulla</a>
                                <button class="btn waves-effect waves-light deep-orange right">Salva</button>
                            </div>
                        </div>
                    </div>
                    <div class="col l4 m6 s12">
                        <div id="graduatorie_map"></div>
                    </div>
                </div>
            </div>
        </form>
        <?php
    } else {
        ?>
        <p class="flow-text valign-wrapper">
            <span class="fa fa-fw fa-2x fa-clock-o grey-text"></span> <?php print __("Nessun utente ha ancora fatto richiesta di entrare a far parte della Rete."); ?>
        </p>
        <br />
        <br />
        &lsaquo; <a href="admin.php?page=graduatorie" class="">Torna alle graduatorie</a>
        <?php
    }
    ?>
</div>
