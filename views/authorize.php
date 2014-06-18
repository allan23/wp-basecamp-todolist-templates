<div class="wrap">
     <?php if ($this->last_error != '') { ?>
                    <div id="message" class="error">
                        <p><strong><?php _e($this->last_error) ?></strong></p>
                    </div>
                <?php } ?>
    <div class="wrap">
        <div class="bct_box">
            <div class="bct_content">
                <h2><img src="<?php echo plugin_dir_url(__FILE__) . "../assets/images/basecampicon_lg.png"; ?>" id="bc_logo" alt="Basecamp"> Authorization Required</h2>


                <div class="bct_content">
                    <p class="bct_left">This plugin needs permission to access your Basecamp account(s).</p>
                    <br>
                    <p class="bct_center"><a href="<?php echo $auth_url;?>" class="button-primary">Authorize This App</a></p>


                </div>

            </div>
        </div>
    </div>


</div>
