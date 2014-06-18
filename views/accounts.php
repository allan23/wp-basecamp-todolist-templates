<div class="wrap">
     <?php if ($this->last_error != '') { ?>
                    <div id="message" class="error">
                        <p><strong><?php _e($this->last_error) ?></strong></p>
                    </div>
                <?php } ?>
    <div class="wrap">
        <div class="bct_box">
            <div class="bct_content">
                <h2><img src="<?php echo plugin_dir_url(__FILE__) . "../assets/images/basecampicon_lg.png"; ?>" id="bc_logo" alt="Basecamp"> Your Accounts</h2>


                <div class="bct_content bct_left">
                    Select an account: <br><br>
                    <table class="widefat" >
                        <thead>
                            <tr>
                                <th>Account ID</th>
                                <th>Account Name</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (count($accounts) > 0){
                            foreach ($accounts as $account): 
                                ?>
                            <tr>
                                <td><?php echo $account->id; ?></td>
                                <td><?php echo $account->name; ?></td>
                                <td><a href="<?php menu_page_url("todolist", true); ?>&amp;acct=<?php echo $account->id; ?>" class="button-primary">Select</a></td>
                            </tr>
                            <?php 
                            endforeach; 
                            }else{
                                ?>
                            <tr>
                                <td colspan="3">You do not have any Basecamp accounts to access.</td>
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>


                </div>

            </div>
        </div>
    </div>


</div>
