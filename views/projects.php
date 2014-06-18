<div class="wrap">
    <?php if ($this->last_error != '') { ?>
        <div id="message" class="error">
            <p><strong><?php _e($this->last_error) ?></strong></p>
        </div>
    <?php } ?>
    <div class="wrap">
        <div class="bct_box">
            <div class="bct_content">
                <h2><img src="<?php echo plugin_dir_url(__FILE__) . "../assets/images/basecampicon_lg.png"; ?>" id="bc_logo" alt="Basecamp"> Assign Todo List to Project</h2>


                <div class="bct_content bct_left">
                    Select a project: <select>
                        <option>--- Select a Project ---</option>
                            <?php foreach ($projects as $project): ?>
                        <option value="<?php echo esc_attr($project->id);?>"><?php echo esc_attr($project->name); ?></option>
                              
                            <?php endforeach; ?>
                    </select>
                        


                </div>

            </div>
        </div>
    </div>


</div>
