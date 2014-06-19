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
        add_action("wp_ajax_project_details", array($this, "projectAjax"));
        add_action("wp_ajax_todo_box", array($this, "addField"));
        add_action("wp_ajax_post_list", array($this, "getPost"));
        add_action("wp_ajax_assign_todo", array($this, "assignTodoToProject"));
        add_action("wp_ajax_expand_todo", array($this, "expandTodo"));
        add_action("add_meta_boxes", array($this, "addBoxes"));
        add_action("save_post", array($this, "saveTodo"));
        add_filter("user_can_richedit", array($this, "disableWysiwyg"));
    }

    function createPostType() {
        register_post_type('bc_todo', array(
            'labels' => array(
                'name' => __('Todo Lists'),
                'singular_name' => __('Todo List'),
                'add_new_item' => __('Add New Todo List'),
                'edit_item' => __('Edit Todo List'),
            ),
            'public' => false,
            'supports' => array('title', 'editor'),
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

    function disableWysiwyg($default) {
        global $post;
        if ("bc_todo" == get_post_type($post))
            return false;
        return $default;
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
        wp_enqueue_script('bct-js', plugin_dir_url(__FILE__) . "../assets/js/todo.js", array('jquery'));
        wp_localize_script('bct-js', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    }

    function adminPage() {

        $token = $this->getUserToken();
        $accounts = $this->getAccounts();
        $todos = get_posts(array("post_type" => "bc_todo"));
        ob_start();
        if (isset($_GET['acct'])) {
            $account_id = esc_attr($_GET['acct']);
            $projects = $this->getProjects($account_id);
            include plugin_dir_path(__FILE__) . "../views/projects.php";
        } elseif (count($accounts) == 1) {
            $account_id = $accounts[0]->id;
            $projects = $this->getProjects($account_id);
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

    function projectAjax() {
        $account_id = esc_attr($_POST['account_id']);
        $project_id = esc_attr($_POST['project_id']);
        $project_url = "https://basecamp.com/" . $account_id . "/api/v1/projects/" . $project_id . ".json";
        $todo_url = "https://basecamp.com/" . $account_id . "/api/v1/projects/" . $project_id . "/todolists.json";
        
        if (isset($_POST['hardRefresh'])){
            delete_transient($this->user_ID . "_" . $project_id);
        }

        $project = get_transient($this->user_ID . "_" . $project_id);
        if ($project === false) {
            $project = $this->getProjectByURL($project_url);
            $project->todos = $this->getTodoListByURL($todo_url);
            set_transient($this->user_ID . "_" . $project_id, $project, 300);
        }
        echo json_encode($project);
        die();
    }

    function addBoxes() {
        add_meta_box("todo_list_items", "Todo List Items", array($this, "metaBox"), "bc_todo");
    }

    function metaBox($post) {
        wp_nonce_field("todo_meta_box", "todo_meta_box_nonce");
        $todo_items = get_post_meta($post->ID, "_todolist", true);
        ob_start();
        include plugin_dir_path(__FILE__) . "../views/metabox.php";
        echo ob_get_clean();
    }

    function addField() {
        ob_start();
        $todo="";
        include plugin_dir_path(__FILE__) . "../views/fields.php";
        echo ob_get_clean();
        die();
    }

    function saveTodo($post_id) {
        if (!isset($_POST['todo_meta_box_nonce'])) {
            return;
        }
        if (!wp_verify_nonce($_POST['todo_meta_box_nonce'], 'todo_meta_box')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (isset($_POST['post_type']) && 'bc_todo' == $_POST['post_type']) {

            if (!current_user_can('edit_page', $post_id)) {
                return;
            }
        } else {

            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
        }
        $todo_list = array();
        foreach ($_POST['_todolist'] as $todo) {
            $todo_list[] = sanitize_text_field($todo);
        }
        update_post_meta($post_id, "_todolist", $todo_list);
    }

    function getPost() {
        $post_id = (int) esc_attr($_POST['post_id']);
        $post = get_post($post_id);
        $post->post_content=strip_tags($post->post_content);
        $post->todolist = get_post_meta($post->ID, "_todolist", true);
        echo json_encode($post);

        die();
    }

    function assignTodoToProject(){
        $todo=new stdClass();
        $todo->account_id = esc_attr($_POST['account_id']);
        $todo->project_id = esc_attr($_POST['project_id']);
        $todo->name=esc_attr($_POST['todo_name']);
        $todo->description=esc_attr($_POST['todo_description']);
        $post_id=(int) esc_attr($_POST['post_id']);
        # Get the Tasks
        $todo->todos=get_post_meta($post_id, "_todolist", true);
        
        $results=$this->createTodo($todo);
        delete_transient($this->user_ID . "_" . $todo->project_id);
        echo "<pre>";
        print_r($results);
        echo "</pre>";
        die();
    }
    
    function expandTodo(){
        $url=esc_attr($_POST['url']);
        echo $this->getTodoItemsByURL($url);
        die();
    }
}
