<div class="wrap">
	<?php if ( $this->last_error != '' ) { ?>
		<div id="message" class="error">
			<p><strong><?php _e( $this->last_error ) ?></strong></p>
		</div>
	<?php } ?>
	<div class="welcome-panel" id="bct_page" >
		<div class="welcome-panel-content" >

			<div class="welcome-panel-column-container">
				<div class="welcome-panel-column">
					<div class="wrap">
						<div class="postbox">
							<div class="inside">
								<h2><img src="<?php echo plugin_dir_url( __FILE__ ) . "../assets/images/basecampicon_lg.png"; ?>" id="bc_logo" alt="Basecamp"> Authorization Required</h2>



								<p class="bct_center">This plugin needs permission to access your Basecamp account(s).</p>
								<br>
								<p class="bct_center"><a href="<?php echo esc_attr( $auth_url ); ?>" class="button-primary">Authorize This App</a></p>
							</div>
						</div>
					</div>


				</div>
            </div>
        </div>
    </div>


</div>
