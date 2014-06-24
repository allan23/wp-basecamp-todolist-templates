<div class="wrap">
	<?php if ( $this->last_error != '' ) { ?>
		<div id="message" class="error">
			<p><strong><?php _e( $this->last_error ) ?></strong></p>
		</div>
	<?php } ?>
    <div class="wrap">
        <div class="bct_box" style="float:left;">
            <div class="bct_content">
                <h2><img src="<?php echo plugin_dir_url( __FILE__ ) . "../assets/images/basecampicon_lg.png"; ?>" id="bc_logo" alt="Basecamp"> Create Todo List</h2>


                <div class="bct_content bct_left">
                    Select a project: <select id="projectList">
                        <option value="">--- Select a Project ---</option>
						<?php foreach ( $projects as $project ): ?>
							<option value="<?php echo esc_attr( $project->id ); ?>"><?php echo esc_attr( $project->name ); ?></option>

						<?php endforeach; ?>
                    </select>
                    <br><br>
                    <div id="todoInfo">
                        Select Todo List: <select id="postList">
                            <option value="">--- Select a Todo List ---</option>
							<?php foreach ( $todos as $todo ): ?>
								<option value="<?php echo esc_attr( $todo->ID ); ?>"><?php echo esc_attr( $todo->post_title ); ?></option>

							<?php endforeach; ?>
                        </select>
                        <br><br>
                        <div id="todoSelected">
                            Todo List Name: <input type="text" id="todoName">
                            <br><br>
                            Description:
                            <textarea id="todoDesc"></textarea>
                            <br><br>
                            <strong>Todo Items</strong>

                            <ol id="theTodoList">

                            </ol>
                            <br><br>
                            <div class="bct_center">
                                <a href="#" class="button-primary" id="performAssign">Assign To Project</a>
                            </div>
                        </div>
                    </div>
                    <div id="todoResults"></div>
                </div>

            </div>
        </div>

        <div class="bct_box" style="float:left; display:none;" id="projectDetails">
            <div class="bct_content" >
                <h2><img src="<?php echo plugin_dir_url( __FILE__ ) . "../assets/images/basecampicon_lg.png"; ?>" id="bc_logo" alt="Basecamp"> <span id="projectName"></span></h2>


                <div id="projectCreator"></div>
                <p id="projectDesc"></p>
                <h3>Todo Lists</h3>
                <ul id="projectTodo">

                </ul>
                <br><br>
                <div class="bct_center">
					<a href="#" id="refreshProject" class="button-secondary">Refresh This Project</a>
                </div>
            </div>

        </div>
		<div class="bct_box" style="float:left;display:none;" id="ajaxLoading">
            <div class="bct_content" >
                <h2><img src="<?php echo plugin_dir_url( __FILE__ ) . "../assets/images/ajax-loader.gif"; ?>" id="bc_logo" alt="Loading"> Loading...</h2>



                <div class="bct_center loadercontent">
					One moment please.
                </div>
            </div>

        </div>
        <br style="clear:both;">
    </div>


</div>

<br style="clear:both;">

<script>
	var bc_account = '<?php echo esc_attr( $account_id ); ?>';
</script>