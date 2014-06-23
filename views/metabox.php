<ul id="todo_list">

	<?php
	if ( is_array( $todo_items ) ) {
		if ( count( $todo_items ) == 0 ) {
			$todo = "";
			include plugin_dir_path( __FILE__ ) . "../views/fields.php";
		} else {
			foreach ( $todo_items as $todo ):
				include plugin_dir_path( __FILE__ ) . "../views/fields.php";
			endforeach;
		}
	} else {
		$todo = "";
		include plugin_dir_path( __FILE__ ) . "../views/fields.php";
	}
	?>

</ul>
<a href="#" id="add_bc_todo" class="button-secondary">Add Item</a>