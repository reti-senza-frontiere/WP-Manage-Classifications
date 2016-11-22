<?php
class RSF_Graduatorie {
	// class instance
	static $instance;

	// WP_List_Table object
	public $classifications_obj;

	// class constructor
	public function __construct() {
		add_filter("set-screen-option", [__CLASS__, "set_screen"], 10, 3);
		add_action("admin_menu", [$this, "plugin_menu"]);
		add_action("admin_enqueue_scripts", [$this, "admin_styles"]);
		add_action("admin_enqueue_scripts", [$this, "admin_scripts"]);
	}


	public static function set_screen($status, $option, $value) {
		return $value;
	}

    /**
     * Load custom CSS
     */
    function admin_styles(){
		$graduatorie_pages = array(
			"graduatorie",
			"new-graduatoria"
		);
		$is_graduatorie_page = ((isset($_GET["page"]) && in_array($_GET["page"], $graduatorie_pages) === true) ? true : false);
		print $is_graduatorie_page;
		if(is_admin() && $is_graduatorie_page) {
			//google fonts
			$query_args = array(
				"family" => "Roboto:400,700,900"
			);
			$google_fonts = wp_enqueue_style("google-fonts", add_query_arg($query_args, "//fonts.googleapis.com/css"), array(), null);
			add_action("load-" . $google_fonts, [$this, "screen_option"]);
			$font_awesome = wp_enqueue_style("font-awesome", get_template_directory_uri_packs() . "/font-awesome/css/font-awesome.min.css", array(), null);
	        add_action("load-" . $font_awesome, [$this, "screen_option"]);
	        if(isset($_GET["page"]) && in_array($_GET["page"], array("graduatorie", "new-graduatoria"))) {
	            $materialize = wp_enqueue_style("materialize", get_template_directory_uri_packs() . "/materialize/dist/css/materialize.min.css", array(), null);
	            add_action("load-" . $materialize, [$this, "screen_option"]);
	        } else {
	            $bootstrap = wp_enqueue_style("bootstrap", get_template_directory_uri_packs() . "/bootstrap/dist/css/bootstrap.min.css", array(), null);
	            add_action("load-" . $bootstrap, [$this, "screen_option"]);
	        }
			$main = wp_enqueue_style("rsf_styles", PLUGIN_DIR_URL . "/assets/css/main.css");
	        add_action("load-" . $main, [$this, "screen_option"]);
		}
    }

    /**
     * Load custom Javascript
     */
    function admin_scripts() {
		$graduatorie_pages = array(
			"graduatorie",
			"new-graduatoria"
		);
		$is_graduatorie_page = ((isset($_GET["page"]) && in_array($_GET["page"], $graduatorie_pages) === true) ? true : false);
		print $is_graduatorie_page;
		if(is_admin() && $is_graduatorie_page) {
	        if(isset($_GET["page"]) && in_array($_GET["page"], array("graduatorie", "new-graduatoria"))) {
	            $materialize = wp_enqueue_script("materialize", get_template_directory_uri_packs() . "/materialize/dist/js/materialize.min.js", array(), null, true);
	            add_action("load-" . $materialize, [$this, "screen_option"]);
	        } else {
	            $bootstrap = wp_enqueue_script("bootstrap", get_template_directory_uri_packs() . "/bootstrap/dist/js/bootstrap.min.js", array(), null, true);
	            add_action("load-" . $bootstrap, [$this, "screen_option"]);
	        }
			$main = wp_enqueue_script("main", PLUGIN_DIR_URL . "/assets/js/main.js", array(), null, true);
	        add_action("load-" . $main, [$this, "screen_option"]);
		}
    }

	/**
	 * Plugin menu
	 */
	public function plugin_menu() {
		$db_config = parse_ini_file(ABSPATH . "wp-content/themes/RetiSenzaFrontiere/configs/database.ini");
		$pdo = new PDO("mysql:host=" . $db_config["host"] . ";dbname=rsf_data", $db_config["user"], $db_config["pass"]);
		$sql = "select * from `rsf_members` where `requires_to_be_part_of_the_net` = 1 and `evaluated` = 0";
		$query = $pdo->query($sql);
		$requests = count($query->fetchAll(PDO::FETCH_ASSOC));
		$has_requests = ($requests > 0) ? true : false;
		$badge = ($has_requests) ? ' <span class="update-plugins count-' . $requests . '"><span class="plugin-count">' . $requests . '</span></span>' : "";
        $hook = add_menu_page(
			__("Graduatorie", "rsf"),
            __("Graduatorie", "rsf") . $badge,
			"manage_options",
			"graduatorie",
			[$this, "plugin_home_page"],
            "dashicons-chart-bar",
            20
		);
        $subhook = add_submenu_page(
            "graduatorie",
            __("Nuova graduatoria", "rsf"),
            __("Aggiungi nuova", "rsf"),
            "manage_options",
            "new-graduatoria",
            [$this, "plugin_add_new_page"]
        );

		add_action("load-" . $hook, [$this, "screen_option"]);
		add_action("load-" . $subhook, [$this, "screen_option"]);
	}


	/**
	 * Plugin home page
	 */
	public function plugin_home_page() {
		require_once(PLUGIN_DIR_PATH . "/templates/home.php");
	}

	/**
	 * Plugin home page
	 */
	public function plugin_add_new_page() {
		require_once(PLUGIN_DIR_PATH . "/templates/evaluate.php");
	}

	/**
	 * Screen options
	 */
	public function screen_option() {
		$option = "per_page";
		$args   = [
			"label"   => "Graduatorie",
			"default" => 10,
			"option"  => "classifications_per_page"
		];
		add_screen_option($option, $args);

		$this->classifications_obj = new Classification_List();
	}

	/**
	 * Singleton instance
	 */
	public static function get_instance() {
		if(!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
