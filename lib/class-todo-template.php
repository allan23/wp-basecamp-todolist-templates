<?php

/**
 * TodoTemplate Class extends the BasecampAPI class.
 */
class Todo_Template extends Basecamp_API {
	/*
	 * Fire it up and load our actions and filters.
	 */

	function __construct() {
		add_action( "admin_init", array( $this, "settings" ) );
		add_action( "init", array( $this, "create_post_type" ) );
		add_action( "admin_menu", array( $this, "page_setup" ) );
		add_action( "admin_enqueue_scripts", array( $this, "admin_scripts" ) );
		add_action( "wp_ajax_project_details", array( $this, "project_ajax" ) );
		add_action( "wp_ajax_todo_box", array( $this, "add_field" ) );
		add_action( "wp_ajax_post_list", array( $this, "get_post" ) );
		add_action( "wp_ajax_assign_todo", array( $this, "assign_todo_to_project" ) );
		add_action( "wp_ajax_expand_todo", array( $this, "expand_todo" ) );
		add_action( "add_meta_boxes", array( $this, "add_boxes" ) );
		add_action( "save_post", array( $this, "save_todo" ) );
		add_filter( "user_can_richedit", array( $this, "disable_wysiwyg" ) );
	}

	/**
	 * Creates the custom post type for todo list creation.
	 */
	function create_post_type() {
		register_post_type( 'bc_todo', array(
			'labels'	 => array(
				'name'			 => __( 'Todo Lists' ),
				'singular_name'	 => __( 'Todo List' ),
				'add_new_item'	 => __( 'Add New Todo List' ),
				'edit_item'		 => __( 'Edit Todo List' ),
			),
			'public'	 => false,
			'supports'	 => array( 'title', 'editor' ),
			'show_ui'	 => true,
			'menu_icon'	 => plugin_dir_url( __FILE__ ) . "../assets/images/basecampicon_sm.png"
		)
		);
	}

	/**
	 * Registers our two main options (Client ID and Client Secret).
	 */
	function settings() {
		$this->user_ID = get_current_user_id();
		register_setting( "todosetup", "BC_ClientID" );
		register_setting( "todosetup", "BC_Secret" );
	}

	/**
	 * Prevents the WYSIWYG editor on our custom post type (bc_todo).
	 * @global $post[] $post The post object.
	 * @param $default[] $default The default object/array for the filter.
	 * @return mixed Returns false if our custom post type is being used, otherwise it sends over the $default.
	 */
	function disable_wysiwyg( $default ) {
		global $post;
		if ( "bc_todo" == get_post_type( $post ) )
			return false;
		return $default;
	}

	/**
	 * Sets up our admin menu pages.
	 */
	function page_setup() {
		$client_id = get_option( "BC_ClientID" );
		if ( $client_id ) {
			$user_ID		 = get_current_user_id();
			$refresh_token	 = get_user_meta( $user_ID, "BC_RT", true );
			$loadPage		 = "authorize_page";
			if ( $refresh_token ) {
				$loadPage = "admin_page";
			}
			add_submenu_page( "edit.php?post_type=bc_todo", "Assign to Project", "Assign Project", "publish_pages", "todolist", array( $this, $loadPage ) );
		}

		add_submenu_page( "edit.php?post_type=bc_todo", "App Setup", "App Setup", "manage_options", "todosetup", array( $this, "setup_page" ) );

		add_menu_page( "Basecamp", "Basecamp", "manage_options", "todolist_auth", array( $this, "auth" ) );
		remove_menu_page( "todolist_auth" );
	}

	/**
	 * Enqueues our script and stylesheet.
	 */
	function admin_scripts() {
		wp_enqueue_style( 'bct-css', plugin_dir_url( __FILE__ ) . "../assets/css/todo.css" );
		wp_enqueue_script( 'bct-js', plugin_dir_url( __FILE__ ) . "../assets/js/todo.js", array( 'jquery' ) );
		wp_localize_script( 'bct-js', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'security' => wp_create_nonce( "bct_security" ) ) );
	}

	/**
	 * The "Assign Project" page.  This will either show a list of accounts if there is more than one account or the todo assignment view.
	 */
	function admin_page() {

		$token		 = $this->get_user_token();
		$accounts	 = $this->get_accounts();
		$todos		 = get_transient( "bct_todos" );
		if ( $todos === false ) {
			$todos = get_posts( array( "post_type" => "bc_todo" ) );
			set_transient( "bct_todos", $todos, 600 );
		}
		ob_start();
		if ( isset( $_GET['acct'] ) ) {
			$account_id	 = esc_attr( $_GET['acct'] );
			$projects	 = $this->get_projects( $account_id );
			include plugin_dir_path( __FILE__ ) . "../views/projects.php";
		} elseif ( count( $accounts ) == 1 ) {
			$account_id	 = $accounts[0]->id;
			$projects	 = $this->get_projects( $account_id );
			include plugin_dir_path( __FILE__ ) . "../views/projects.php";
		} else {
			include plugin_dir_path( __FILE__ ) . "../views/accounts.php";
		}
		echo ob_get_clean();
	}

	/**
	 * This is the "App Setup" page.  Will allow storing of Client ID and Client Secret.
	 */
	function setup_page() {
		$client_id		 = get_option( "BC_ClientID" );
		$client_secret	 = get_option( "BC_Secret" );
		ob_start();
		include plugin_dir_path( __FILE__ ) . "../views/setup.php";
		echo ob_get_clean();
	}

	/**
	 * This is the user authorization page.  It will prompt the user to authorize the app with 37Signals.
	 */
	function authorize_page() {

		$auth_url = $this->authenticate();
		ob_start();
		include plugin_dir_path( __FILE__ ) . "../views/authorize.php";
		echo ob_get_clean();
	}

	/**
	 * This function will get the access token and refresh token based upon the returned code string from the API.
	 * 
	 */
	function auth() {
		if ( isset( $_GET['code'] ) ) {
			$code = esc_attr( $_GET['code'] );
			if ( $this->get_token( $code ) ) {
				$this->admin_page();
				return;
			}
		}
		wp_die( "Nothing to see here." );
	}

	/**
	 * AJAX callback that will retrive project details.
	 */
	function project_ajax() {
		check_ajax_referer( 'bct_security', 'security' );
		$account_id	 = esc_attr( $_POST['account_id'] );
		$project_id	 = esc_attr( $_POST['project_id'] );
		$project_url = "https://basecamp.com/" . $account_id . "/api/v1/projects/" . $project_id . ".json";
		$todo_url	 = "https://basecamp.com/" . $account_id . "/api/v1/projects/" . $project_id . "/todolists.json";

		if ( isset( $_POST['hardRefresh'] ) ) {
			delete_transient( $this->user_ID . "_" . $project_id );
		}

		$project = get_transient( $this->user_ID . "_" . $project_id );
		if ( $project === false ) {
			$project		 = $this->get_project_by_url( $project_url );
			$project->todos	 = $this->get_todo_list_by_url( $todo_url );
			set_transient( $this->user_ID . "_" . $project_id, $project, 300 );
		}
		echo json_encode( $project );
		die();
	}

	/**
	 * Sets up meta boxes on our custom post type.
	 */
	function add_boxes() {
		add_meta_box( "todo_list_items", "Todo List Items", array( $this, "meta_box" ), "bc_todo" );
	}

	/**
	 * This will get our postmeta and display the metabox.php view.
	 * @param $post[] $post The post object.
	 */
	function meta_box( $post ) {
		wp_nonce_field( "todo_meta_box", "todo_meta_box_nonce" );
		$todo_items = get_post_meta( $post->ID, "_todolist", true );
		ob_start();
		include plugin_dir_path( __FILE__ ) . "../views/metabox.php";
		echo ob_get_clean();
	}

	/**
	 * AJAX callback to generate a new todo item field in the meta box.
	 */
	function add_field() {
		check_ajax_referer( 'bct_security', 'security' );
		ob_start();
		$todo = "";
		include plugin_dir_path( __FILE__ ) . "../views/fields.php";
		echo ob_get_clean();
		die();
	}

	/**
	 * This will update the postmeta for the todo list items if the post is our custom post type.
	 * @param int $post_id The saved post's ID.
	 */
	function save_todo( $post_id ) {
		if ( !isset( $_POST['todo_meta_box_nonce'] ) ) {
			return;
		}
		if ( !wp_verify_nonce( $_POST['todo_meta_box_nonce'], 'todo_meta_box' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( isset( $_POST['post_type'] ) && 'bc_todo' == $_POST['post_type'] ) {

			if ( !current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
		} else {

			if ( !current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}
		$todo_list = array();
		foreach ( $_POST['_todolist'] as $todo ) {
			$todo_list[] = sanitize_text_field( $todo );
		}
		update_post_meta( $post_id, "_todolist", $todo_list );

		# Delete transients on save:
		delete_transient( "bct_todos" );
		delete_transient( "bctpost_" . $post_id );
	}

	/**
	 * AJAX callback to retrieve the post object and the postmeta based on post_id.
	 */
	function get_post() {
		check_ajax_referer( 'bct_security', 'security' );
		$post_id = (int) esc_attr( $_POST['post_id'] );
		$post	 = $this->get_the_post( $post_id );
		echo json_encode( $post );

		die();
	}

	/**
	 * Retrieves the post object and post meta based on post_id.
	 * @param int $post_id Post ID
	 * @return $post[] The $post object and the post meta.
	 */
	function get_the_post( $post_id ) {
		$post = get_transient( "bctpost_" . $post_id );
		if ( $post === false ) {
			$post				 = get_post( $post_id );
			$post->post_content	 = strip_tags( $post->post_content );
			$post->todolist		 = get_post_meta( $post->ID, "_todolist", true );
			set_transient( "bctpost_" . $post_id, $post, 600 );
		}
		return $post;
	}

	/**
	 * This will assign the chosen todo list to the chosen project.
	 */
	function assign_todo_to_project() {
		check_ajax_referer( 'bct_security', 'security' );
		$todo				 = new stdClass();
		$todo->account_id	 = esc_attr( $_POST['account_id'] );
		$todo->project_id	 = esc_attr( $_POST['project_id'] );
		$todo->name			 = esc_attr( $_POST['todo_name'] );
		$todo->description	 = esc_attr( $_POST['todo_description'] );
		$post_id			 = (int) esc_attr( $_POST['post_id'] );

		# Get the Tasks
		$todo->todos = get_post_meta( $post_id, "_todolist", true );

		$results = $this->create_todo( $todo );
		delete_transient( $this->user_ID . "_" . $todo->project_id );
		die();
	}

	/**
	 * This will retrieve the todo items for the todo list you wish to expand.
	 */
	function expand_todo() {
		check_ajax_referer( 'bct_security', 'security' );
		$url = esc_attr( $_POST['url'] );
		echo $this->get_todo_items_by_url( $url );
		die();
	}

}
