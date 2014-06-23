<?php

/*
  Plugin Name: Basecamp Todo List Templates
  Plugin URI:
  Description: This plugin will allow users to create todolist templates and integrate them into Basecamp.
  Author: Allan Collins
  Version: 0.1
  Author URI:
 */

/*
  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */

if ( is_admin() ) {
	include "lib/class-basecamp-api.php";
	include "lib/class-todo-template.php";
	$BC = new Todo_Template();
}