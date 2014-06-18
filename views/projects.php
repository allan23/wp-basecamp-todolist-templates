<div class="wrap">
    <?php if ($this->last_error != '') { ?>
        <div id="message" class="error">
            <p><strong><?php _e($this->last_error) ?></strong></p>
        </div>
    <?php } ?>
    <div class="wrap">
        <div class="bct_box">
            <div class="bct_content">
                <h2><img src="<?php echo plugin_dir_url(__FILE__) . "../assets/images/basecampicon_lg.png"; ?>" id="bc_logo" alt="Basecamp"> Your Projects</h2>


                <div class="bct_content bct_left">
                    Select a project: <br><br>
                    <table class="widefat" >
                        <thead>
                            <tr>
                                <th>Project Name</th>

                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                                <tr>
                                    <td><?php echo $project->name; ?><br>
                                        <small><?php echo $project->description; ?></small></td>

                                    <td><a href="<?php menu_page_url("todolist", true); ?>&amp;project=<?php echo $project->id; ?>" class="button-primary">Select</a></td>
                                </tr>
                            <?php endforeach; ?>
                  
                        </tbody>
                    </table>


                </div>

            </div>
        </div>
    </div>


</div>
