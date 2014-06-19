<?php

/**
 * Basecamp API Class
 * This class connects to the 37Signals and Basecamp API.
 * 
 */
class BasecampAPI {

    var $auth_url = "https://launchpad.37signals.com";  # URL for 37Signals API and authentication.
    var $last_error; # Last error message received.
    private $token; # The User's Access Token
    var $user_ID; # The current logged in user's ID.

    /**
     * User Authentication URL
     * This function will return the URL the user needs to visit to authenticate.
     * @return string $url The URL for user authorization.
     */

    function authenticate() {
        $client_id = get_option("BC_ClientID");
        $redirect_url = menu_page_url("todolist_auth", false);
        $url = $this->auth_url . "/authorization/new?type=web_server&client_id=" . $client_id . "&redirect_uri=" . $redirect_url;
        return $url;
    }

    /**
     * Get and set tokens for the user.
     * This function will send a verfication code to the 37Signals API, return access and refresh tokens, and then store them locally for retrieval.
     * @param string $code Verfication code provided by 37Signals OAuth2.
     * @return boolean True/False - Was the call successful?
     */
    function getToken($code) {
        $client_id = get_option("BC_ClientID");
        $client_secret = get_option("BC_Secret");
        $redirect_url = menu_page_url("todolist_auth", false);
        $url = $this->auth_url . "/authorization/token?type=web_server&client_id=" . $client_id . "&redirect_uri=" . $redirect_url . "&client_secret=" . $client_secret . "&code=" . $code;
        $response = wp_remote_post($url);
        $response = json_decode($response['body']);

        if (isset($response->error)) {
            $this->last_error = $response->error;
            return false;
        }

        # Save the tokens for the current user:


        set_transient($this->user_ID . "_BC_AT", $response->access_token, $response->expires_in);  # Save the Access Token as a transient because access tokens expire after two weeks.
        update_user_meta($this->user_ID, "BC_RT", $response->refresh_token); # Save the Refresh Token as user meta so that it can be used to retrieve a new Access Token.
        return true;
    }

    /**
     * API Authorization / List Available Accounts
     * This function will call the API and return a list of available Basecamp accounts to access (otherwise false).
     * @return mixed Returns array of accounts.  Otherwise returns false.
     */
    function getAccounts() {
        $response = get_transient($this->user_ID . "_BC_Acc");
        if ($response === false) {
            $args = array();
            $args['headers']['Authorization'] = 'Bearer "' . $this->token . '"';
            $response = wp_remote_get($this->auth_url . "/authorization.json", $args);
            $response = json_decode($response['body']);
            set_transient($this->user_ID . "_BC_Acc", $response, 300);
        }
        if (isset($response->error)) {
            $this->last_error = $response->error;
            return false;
        }


        $accounts = array();
        foreach ($response->accounts as $account):
            if ($account->product == 'bcx') { # Product is Basecamp.
                $accounts[] = $account;
            }
        endforeach;
        return $accounts;
    }

    /**
     * 
     * @param string $account_id Basecamp Account ID.
     * @return mixed Returns array of projects for account or false if there is an error.
     */
    function getProjects($account_id) {

        $response = get_transient($this->user_ID . "_BC_Pro");
        if ($response === false) {
            $url = "https://basecamp.com/" . $account_id . "/api/v1";
            $args = array();
            $args['headers']['Authorization'] = 'Bearer "' . $this->token . '"';
            $response = wp_remote_get($url . "/projects.json", $args);
            $response = json_decode($response['body']);
            set_transient($this->user_ID . "_BC_Pro", $response, 300);
        }
        if (isset($response->error)) {
            $this->last_error = $response->error;
            return false;
        }
        return $response;
    }

    function getProjectByURL($url) {
        $this->getUserToken();
        $args = array();
        $args['headers']['Authorization'] = 'Bearer "' . $this->token . '"';
        $response = wp_remote_get($url, $args);
        return json_decode($response['body']);
    }

    function getTodoListByURL($url) {
        $this->getUserToken();
        $args = array();
        $args['headers']['Authorization'] = 'Bearer "' . $this->token . '"';
        $response = wp_remote_get($url, $args);
        return json_decode($response['body']);
    }

    /**
     * Retrieves new access token from API based on the user's refresh token.
     * @return mixed Returns access token or false.
     */
    function refreshToken() {
        $client_id = get_option("BC_ClientID");
        $client_secret = get_option("BC_Secret");
        $redirect_url = menu_page_url("todolist_auth", false);

        $refresh_token = get_user_meta($this->user_ID, "BC_RT", true);
        $url = $this->auth_url . "/authorization/token?type=refresh&client_id=" . $client_id . "&redirect_uri=" . $redirect_url . "&client_secret=" . $client_secret . "&refresh_token=" . $refresh_token;
        $response = wp_remote_post($url);
        $response = json_decode($response['body']);
        if (isset($response->error)) {
            $this->last_error = $response->error;
            return false;
        }
        set_transient($this->user_ID . "_BC_AT", $response->access_token, $response->expires_in);  # Save the Access Token as a transient because access tokens expire after two weeks.

        return $response->access_token;
    }

    /**
     * Get User Token
     * Will retrieve token transient, if expired will run the refreshToken() method to retrieve new access token.
     */
    function getUserToken() {
        $token = get_transient($this->user_ID . "_BC_AT");
        if ($token === false) { # access token has expired.
            $token = $this->refreshToken();
        }
        $this->token = $token;
    }

    function createTodo($todo) {
        $this->getUserToken();

        # Create the Todo List first...
        $args = array();
        $args['headers']['Authorization'] = 'Bearer "' . $this->token . '"';
        $args['headers']['Content-Type'] = "application/json";
        $args['body'] = json_encode($todo);
        $url = "https://basecamp.com/" . $todo->account_id . "/api/v1/projects/" . $todo->project_id . "/todolists.json";
        $response = wp_remote_post($url, $args);
        $body = json_decode($response['body']);
        $todoID = $body->id;

        # Add todos to the newly created list:


        $url = "https://basecamp.com/" . $todo->account_id . "/api/v1/projects/" . $todo->project_id . "/todolists/" . $todoID . "/todos.json";
        foreach ($todo->todos as $t):
            $args = array();
            $args['headers']['Authorization'] = 'Bearer "' . $this->token . '"';
            $args['headers']['Content-Type'] = "application/json";
            $args['body'] = json_encode(array("content" => $t));
            $response = wp_remote_post($url, $args);
        endforeach;





        return $response;
    }

}
