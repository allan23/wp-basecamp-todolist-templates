<?php

/**
 * TodoTemplate Class extends the BasecampAPI class.
 */
class TodoTemplate extends BasecampAPI {

    function __construct() {

        add_action("admin_init", array($this, "settings"));
        add_action("init", array($this, "createPostType"));
        add_action("admin_menu", array($this, "pageSetup"));
        add_action("admin_enqueue_scripts", array($this, "adminScripts"));
    }

    function createPostType() {
        register_post_type('bc_todo', array(
            'labels' => array(
                'name' => __('Todo Lists'),
                'singular_name' => __('Todo List'),
                'add_new_item' => __('Add New Todo List'),
            ),
            'public' => false,
            'supports' => array('title'),
            'show_ui' => true,
            'menu_icon' => plugin_dir_url(__FILE__) . "../assets/images/basecampicon_sm.png"
                )
        );
    }

    function settings() {
        $this->user_ID = get_current_user_id();
        register_setting("todosetup", "BC_ClientID");
        register_setting("todosetup", "BC_Secret");
    }

    function pageSetup() {
        $client_id = get_option("BC_ClientID");
        if ($client_id) {
            $user_ID = get_current_user_id();
            $refresh_token = get_user_meta($user_ID, "BC_RT", true);
            $loadPage = "authorizePage";
            if ($refresh_token) {
                $loadPage = "adminPage";
            }
            add_submenu_page("edit.php?post_type=bc_todo", "Assign to Project", "Assign Project", "publish_pages", "todolist", array($this, $loadPage));
        }

        add_submenu_page("edit.php?post_type=bc_todo", "App Setup", "App Setup", "manage_options", "todosetup", array($this, "setupPage"));

        add_menu_page("Basecamp", "Basecamp", "manage_options", "todolist_auth", array($this, "auth"));
        remove_menu_page("todolist_auth");
    }

    function adminScripts() {
        wp_enqueue_style('bct-css', plugin_dir_url(__FILE__) . "../assets/css/todo.css");
    }

    function adminPage() {

        $token = $this->getUserToken();
        $accounts = $this->getAccounts();

        ob_start();
        if (count($accounts == 1)) {
            $projects = $this->getProjects($accounts[0]->id);
            include plugin_dir_path(__FILE__) . "../views/projects.php";
        } else {
            include plugin_dir_path(__FILE__) . "../views/accounts.php";
        }
        echo ob_get_clean();
        /*
          if ($response) {
          echo "<pre>";
          print_r($response);
          echo "</pre>";
          $url = $response[0]->href;

          $response = $this->getProjects($url);
          echo "<pre>";
          print_r($response);
          echo "</pre>";
          } else {
          echo $this->last_error;
          }
         * 
         */
    }

    function setupPage() {
        $client_id = get_option("BC_ClientID");
        $client_secret = get_option("BC_Secret");
        ob_start();
        include plugin_dir_path(__FILE__) . "../views/setup.php";
        echo ob_get_clean();
    }

    function authorizePage() {

        $auth_url = $this->authenticate();
        ob_start();
        include plugin_dir_path(__FILE__) . "../views/authorize.php";
        echo ob_get_clean();
    }

    function auth() {
        if (isset($_GET['code'])) {
            $code = esc_attr($_GET['code']);
            if ($this->getToken($code)) {
                $this->adminPage();
                return;
            }
        }
        wp_die("Nothing to see here.");
    }

}
