<?php
/**
 * TodoTemplate Class extends the BasecampAPI class.
 */
class TodoTemplate extends BasecampAPI {

    function __construct() {

        add_action("admin_menu", array($this, "pageSetup"));
    }

    function pageSetup() {
        add_menu_page("Basecamp", "Basecamp", "manage_options", "todolist", array($this, "adminPage"));
    }

    function adminPage() {
        
        $token = $this->getUserToken();
        $response = $this->getAccounts($token);
        if ($response) {
            echo "<pre>";
            print_r($response);
            echo "</pre>";
            $url = $response[0]->href;

            $response = $this->getProjects($url, $token);
            echo "<pre>";
            print_r($response);
            echo "</pre>";
        } else {
            echo $this->last_error;
        }
    }

}
