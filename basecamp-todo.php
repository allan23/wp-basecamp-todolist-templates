<?php

/*
  Plugin Name: Basecamp Todolist Templates
  Plugin URI:
  Description: This plugin will allow users to create todolist templates and integrate them into Basecamp.
  Author: Allan Collins
  Version: 0.1
  Author URI:
 */

if (is_admin()) {
    include "lib/BasecampAPI.php";
    include "lib/TodoTemplate.php";
    $BC = new TodoTemplate();
}