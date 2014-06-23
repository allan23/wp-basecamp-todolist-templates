<div class="wrap">

    <div class="wrap">
        <div class="bct_box">
            <div class="bct_content bct_center">
                <h2 class="bct_left"><img src="<?php echo plugin_dir_url( __FILE__ ) . "../assets/images/basecampicon_lg.png"; ?>" id="bc_logo" alt="Basecamp"> Basecamp Todo Template Setup</h2>
				<?php if ( isset( $_GET['settings-updated'] ) ) { ?>
					<div id="message" class="updated">
						<p><strong><?php _e( 'Settings saved.' ) ?></strong></p>
					</div>
				<?php } ?>
                <div class="bct_innerbox">
                    <div class="bct_content">
                        <p class="bct_left">In order for your users to use this plugin, you will need to <a href="https://integrate.37signals.com/" target="_blank">register it with 37Signals</a> to acquire a Client ID and Client Secret Key.</p>
                        <p><strong>Enter the following redirect URL:</strong><br><br>
                            <em><?php menu_page_url( "todolist_auth", true ); ?></em></p>


                    </div>
                </div>
                <div class="bct_innerbox">
                    <div class="bct_content">
                        <p class="bct_left">Enter your Client ID and Client Secret Key:</p>
                        <form method="post" action="options.php">
							<?php settings_fields( 'todosetup' ); ?>
                            <label>Client ID:</label><br>
                            <input type="text" name="BC_ClientID" value="<?php echo esc_attr( $client_id ); ?>"><br><br>
                            <label>Client Secret:</label><br>
                            <input type="text" name="BC_Secret" value="<?php echo esc_attr( $client_secret ); ?>"><br><br>
                            <input type="submit" class="button-primary" value="Save Settings">
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>