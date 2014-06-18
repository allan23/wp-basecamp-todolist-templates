<div class="wrap">
    <?php if ($this->last_error != '') { ?>
        <div id="message" class="error">
            <p><strong><?php _e($this->last_error) ?></strong></p>
        </div>
    <?php } ?>
    <div class="wrap">
        <div class="bct_box" style="float:left;">
            <div class="bct_content">
                <h2><img src="<?php echo plugin_dir_url(__FILE__) . "../assets/images/basecampicon_lg.png"; ?>" id="bc_logo" alt="Basecamp"> Assign Todo List to Project</h2>


                <div class="bct_content bct_left">
                    Select a project: <select id="projectList">
                        <option value="">--- Select a Project ---</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo esc_attr($project->id); ?>"><?php echo esc_attr($project->name); ?></option>

                        <?php endforeach; ?>
                    </select>
                    <br><br>
                    Select Todo List: <select>
                        <option value="">--- Select a Todo List ---</option>
                        <?php foreach ($todos as $todo): ?>
                            <option value="<?php echo $todo->ID;?>"><?php echo $todo->post_title; ?></option>

                        <?php endforeach; ?>
                    </select>

                </div>

            </div>
        </div>
        
             <div class="bct_box" style="float:left; display:none;" id="projectDetails">
            <div class="bct_content" >
                <h2><img src="<?php echo plugin_dir_url(__FILE__) . "../assets/images/basecampicon_lg.png"; ?>" id="bc_logo" alt="Basecamp"> <span id="projectName"></span></h2>
                   
                      
                        <div id="projectCreator"></div>
                        <p id="projectDesc"></p>
                        <h3>Todo Lists</h3>
                        <ul id="projectTodo">
                            
                        </ul>
                </div>

            </div>
        </div>
        
        
    </div>


</div>
<script>
    var bc_account = '<?php echo $account_id; ?>';
</script>