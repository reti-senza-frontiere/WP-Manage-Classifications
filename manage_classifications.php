<?php
/*
Plugin Name: Gestione graduatorie
Plugin URI:
Description: Gestisce le graduatorie per la partecipazione alla Rete dell"Associazione
Version: 1.0.0
Author: Alessandro Gubitosi
Author URI: http://iod.io
*/

define("PLUGIN_DIR_PATH", plugin_dir_path(__FILE__));
define("PLUGIN_DIR_URL", plugin_dir_url(__FILE__));

if(!class_exists("WP_List_Table")) {
	require_once(ABSPATH . "wp-admin/includes/class-wp-list-table.php");
}

require_once("lib/classes/Classification_List.php");
require_once("lib/classes/RSF_Graduatorie.php");

add_action(
    "plugins_loaded",
    function () {
        RSF_Graduatorie::get_instance();
    }
);
?>
