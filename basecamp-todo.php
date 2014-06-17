<?php

/*
  Plugin Name: Basecamp Todolist Templates
  Plugin URI:
  Description: This plugin will allow users to create todolist templates and integrate them into Basecamp.
  Author: Allan Collins
  Version: 0.1
  Author URI:
 */

include "lib/BasecampAPI.php";

function bc_page() {
    $code = (isset($_GET['code'])) ? $_GET['code'] : false;
    if (!$code) {
        echo "<a href='" . BasecampAPI::authenticate() . "' class='button-primary'>Authenticate</a>";
    } else {
        $response = BasecampAPI::getToken($code);
        echo "<pre>";
        print_r($response);
        echo "</pre>";
    }
}

function bc_page2() {
    $BC = new BasecampAPI();
    $user_ID = get_current_user_id();
    $token = get_transient($user_ID . "_BC_AT");
    $response = $BC->authorize($token);
    if ($response) {
        echo "<pre>";
        print_r($response);
        echo "</pre>";
        $url = $response[0]->href;

        $response = $BC->getProjects($url, $token);
        echo "<pre>";
        print_r($response);
        echo "</pre>";
    } else {
        echo $BC->last_error;
    }
}

function bc_page3() {
    $BC = new BasecampAPI();
    $BC->refreshToken();
}

add_action("admin_menu", "bc_setup");

function bc_setup() {
    add_menu_page("Basecamp", "Basecamp", "manage_options", "todolist", "bc_page2");
}
