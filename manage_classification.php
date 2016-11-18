<?php
/*
Plugin Name: Gestione graduatorie
Plugin URI:
Description: Gestisce le graduatorie per la partecipazione alla Rete dell"Associazione
Version: 1.0.0
Author: Alessandro Gubitosi
Author URI: http://iod.io
*/
/**
 * Admin menu
 */
function graduatorie__custom_post() {
    register_post_type("graduatorie",
        array(
            "labels" => array(
                "name"                  => "Graduatorie",
                "singular_name"         => "Graduatoria",
                "all_items"             => "Tutte le graduatorie",
                "add_new"               => "Aggiungi nuova",
                "add_new_item"          => "Aggiungi nuova graduatoria",
                "edit_item"             => "Modifica graduatoria",
                "new_item"              => "Nuova graduatoria",
                "view_item"             => "Visualizza graduatorie",
                "search_items"          => "Cerca graduatoria",
                "not_found"             => "Nessuna graduatoria trovata",
                "not_found_in_trash"    => "Nessuna graduatoria trovata nel cestino",
                "parent_item_colon"     => ""
            ),
            "description" => "Graduatorie di partecipazione alla Rete dell'Associazione",
            "public" => true,
            "publicly_queryable" => true,
            "exclude_from_search" => true,
            "show_ui" => true,
            "query_var" => true,
            "menu_position" => 20,
            "menu_icon" => "dashicons-chart-bar", // https://developer.wordpress.org/resource/dashicons/
            "rewrite"   => array("slug" => "graduatoria", "with_front" => false),
            "has_archive" => "false",
            "capability_type" => "post",
            "hierarchical" => false,
            "supports" => array("excerpt", "editor", "author", "thumbnail", "excerpt", "trackbacks", "custom-fields", "comments", "revisions", "sticky")
        )
    );
    flush_rewrite_rules();
}
add_action("init", "graduatorie__custom_post" );


?>
