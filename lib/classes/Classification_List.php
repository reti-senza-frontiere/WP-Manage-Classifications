<?php

class Classification_List extends WP_List_Table {
	/**
	 * Class constructor
	 */
	public function __construct() {
		parent::__construct([
			"singular" => __("Graduatoria", "sp"),
			"plural"   => __("Graduatorie", "sp"),
			"ajax"     => true
		]);
	}

    /**
     * Perform database queries
     * @param  string                           $sql                            The query to execute
     * @return array                                                            The results of the query
     */
    public function query($sql) {
        $db_config = parse_ini_file(ABSPATH . "wp-content/themes/RetiSenzaFrontiere/configs/database.ini");
        $pdo = new PDO("mysql:host=" . $db_config["host"] . ";dbname=rsf_data", $db_config["user"], $db_config["pass"]);
        $query = $pdo->prepare($sql);
        $query->execute();
        return $result = $query->fetchAll(PDO::FETCH_ASSOC);
    }

	/**
	 * Retrieve classifications data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public function get_classifications($per_page = 5, $page_number = 1) {
		$order = (isset($_GET["order"]) ? preg_replace("/[^\w]+/", "", $_GET["order"]) : "");
		$orderby = (isset($_GET["orderby"]) ? preg_replace("/[^\w\_]+/", "", $_GET["orderby"]) : "");
        $sql = <<<SQL
SELECT
	rsf_classifications.id,
	rsf_classifications.member_id,
	rsf_classifications.digital_divide_score,
	rsf_classifications.technical_score,
	rsf_classifications.date,
	rsf_members.date AS 'registration_date'
FROM `rsf_classifications`
JOIN `rsf_members` on `member_id` = rsf_members.id
SQL;
        if(isset($_GET["orderby"]) && !empty($_GET["orderby"])) {
            $sql .= " ORDER BY " . esc_sql($orderby);
            $sql .= ((isset($_GET["order"]) && !empty($_GET["order"])) ? " " . $order : " ASC");
        }
        $sql .= " LIMIT " . $per_page;
        $sql .= " OFFSET " . ($page_number - 1) * $per_page;

        $d = $this->query($sql);
        return $d;
	}


	/**
	 * Delete a classification record.
	 *
	 * @param int $id classification ID
	 */
	public function delete_classification($id) {
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}classifications",
			["id" => $id],
			["%d"]
		);
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public function record_count() {
		$sql = "SELECT COUNT(*) as 'count' FROM `rsf_classifications`";

		return $this->query($sql);
	}


	/** Text displayed when no classification data is available */
	public function no_items() {
		print __("Nessuna graduatoria inserita", "rsf");
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default($item, $column_name) {
		switch($column_name) {
			case "member_id":
                $sql = "select * from `rsf_members` where `id` = " . $item[$column_name];
                $user_data = $this->query($sql)[0];
                return $user_data["name"] . " " . $user_data["last_name"];
                break;
			case "date":
			case "registration_date":
				return date("<\b\i\g>d/m/Y</\b\i\g> <\s\m\a\l\l>H:i:s</\s\m\a\l\l>", strtotime($item[$column_name]));
                break;
            case "digital_divide_score":
            case "technical_score":
                return $item[$column_name];
			default:
				return print_r($item, true);
                break;
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item["id"]
		);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name($item) {
		$delete_nonce = wp_create_nonce("rsf_delete_classification");
		$title = "<strong>" . $item["name"] . "</strong>";
		$actions = array(
			"delete" => sprintf('<a href="?page=%s&action=%s&classification=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST["page"]), "delete", absint($item["ID"]), $delete_nonce)
		);

		return $title . $this->row_actions($actions);
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			"cb"                    => '<input type="checkbox" />',
            "member_id"             => __("Utente"),
            "digital_divide_score"  => __("Svantaggio geografico", "rsf"),
            "technical_score"       => __("Fattibilità tecnica", "rsf"),
            "registration_date"     => __("Data registrazione utente", "rsf"),
            "date"                  => __("Data valutazione", "rsf")
		];
		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = [
            "member_id"             => array("member_id", true),
            "digital_divide_score"  => array("digital_divide_score", true),
            "technical_score"       => array("technical_score", true),
            "registration_date"     => array("registration_date", true),
            "date"                  => array("date", true)
		];
		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	// public function get_bulk_actions() {
	// 	$actions = [
	// 		"bulk-delete" => "Delete"
	// 	];
    //
	// 	return $actions;
	// }


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {
		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		// $this->process_bulk_action();

		$per_page     = $this->get_items_per_page("classifications_per_page", 10);
		$current_page = $this->get_pagenum();
		$total_items  = $this->record_count()[0]["count"];
		$this->set_pagination_args([
			"total_items" => $total_items, //WE have to calculate the total number of items
			"per_page"    => $per_page //WE have to determine how many items to show on a page
		]);

		$this->items = $this->get_classifications($per_page, $current_page);
	}

	// public function process_bulk_action() {
	// 	// Detect when a bulk action is being triggered...
	// 	if("delete" === $this->current_action()) {
	// 		// In our file that handles the request, verify the nonce.
	// 		$nonce = esc_attr($_REQUEST["_wpnonce"]);
    //
	// 		if(!wp_verify_nonce($nonce, "rsf_delete_classification")) {
	// 			die("Go get a life script kiddies");
	// 		}
	// 		else {
	// 			$this->delete_classification(absint($_GET["classification"]));
    //
	// 	                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
	// 	                // add_query_arg() return the current url
	// 	                wp_redirect(esc_url_raw(add_query_arg()));
	// 			exit;
	// 		}
	// 	}
    //
	// 	// if the delete bulk action is triggered
	// 	if((isset($_POST["action"]) && $_POST["action"] == "bulk-delete") || (isset($_POST["action2"]) && $_POST["action2"] == "bulk-delete")) {
	// 		$delete_ids = esc_sql($_POST["bulk-delete"]);
    //
	// 		// loop over the array of record IDs and delete them
	// 		foreach ($delete_ids as $id) {
	// 			$this->delete_classification($id);
    //
	// 		}
    //
	// 		// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
	// 	        // add_query_arg() return the current url
	// 	        wp_redirect(esc_url_raw(add_query_arg()));
	// 		exit;
	// 	}
	// }
}
