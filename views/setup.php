<div class="wrap">

    <div class="welcome-panel" id="bct_page" >

		<div class="welcome-panel-content" >
			<div class="postbox">
				<div class="inside">
					<h2 ><img src="<?php echo plugin_dir_url( __FILE__ ) . "../assets/images/basecampicon_lg.png"; ?>" id="bc_logo" alt="Basecamp"> Basecamp Todo Template Setup</h2>
					<?php if ( isset( $_GET['settings-updated'] ) ) { ?>
						<div id="message" class="updated">
							<p><strong><?php _e( 'Settings saved.' ) ?></strong></p>
						</div>
					<?php } ?>
					<div class="welcome-panel-column-container">
						<div class="welcome-panel-column">
							<div class="wrap">
								<p>In order for your users to use this plugin, you will need to <a href="https://integrate.37signals.com/" target="_blank">register it with 37Signals</a> to acquire a Client ID and Client Secret Key.</p>
								<p><strong>Enter the following redirect URL:</strong><br><br>
									<em><?php menu_page_url( "todolist_auth", true ); ?></em></p>

							</div>

						</div>
						<div class="welcome-panel-column">

							<div class="wrap bct_center">
								<p>Enter your Client ID and Client Secret Key:</p>
								<form method="post" action="options.php">
									<?php settings_fields( 'todosetup' ); ?>
									<label>Client ID:</label><br>
									<input type="text" name="BC_ClientID" value="<?php echo esc_attr( $client_id ); ?>"><br>
									<label>Client Secret:</label><br>
									<input type="text" name="BC_Secret" value="<?php echo esc_attr( $client_secret ); ?>"><br><br>
									<input type="submit" class="button-primary" value="Save Settings">
								</form>
							</div>
							<br><br>
						</div>
					</div>
				</div>
			</div>
		</div>

    </div>
</div>