<div class="wrap arwp-wrap">
	<h2><?php esc_html_e('Advanced Reset WP', 'arwp'); ?></h2>
	<div class="arwp-block">
		<div class="arwp-left">
			<div class="arwp-form-view">
				<form id="arwp_form" action="" method="post">
					<h3><?php esc_html_e('Reset type:', 'arwp'); ?></h3>
					<p>
						<label><input type="radio" name="arwp_type" class="arwp-type" value="re-install"><?php esc_html_e('Re-install WordPress', 'arwp'); ?></label><br>
						<label><input type="radio" name="arwp_type" class="arwp-type" value="re-install-uploads"><?php esc_html_e('Re-install WordPress and clear "uploads" folder', 'arwp'); ?></label><br>
						<label><input type="radio" name="arwp_type" class="arwp-type" value="post-clear"><?php esc_html_e('Post cleaning', 'arwp'); ?></label><br>
						<label><input type="radio" name="arwp_type" class="arwp-type" value="delete-theme"><?php esc_html_e('Delete themes', 'arwp'); ?></label><br>
						<label><input type="radio" name="arwp_type" class="arwp-type" value="delete-plugin"><?php esc_html_e('Delete plugins', 'arwp'); ?></label><br>
						<label><input type="radio" name="arwp_type" class="arwp-type" value="clear-uploads"><?php esc_html_e('Clear "uploads" folder', 'arwp'); ?></label><br>
						<label><input type="radio" name="arwp_type" class="arwp-type" value="deep-cleaning" required><?php esc_html_e('Deep cleaning', 'arwp'); ?></label>
					</p>
					<div class="post-class">
						<p><strong><?php esc_html_e('Select types that you want to delete?', 'arwp'); ?></strong></p>
						<p>
							<label><input type="checkbox" name="arwp_post_type[]" class="arwp-post-type" value="post"><?php esc_html_e('Posts', 'arwp'); ?></label><br>
							<label><input type="checkbox" name="arwp_post_type[]" class="arwp-post-type" value="page"><?php esc_html_e('Pages', 'arwp'); ?></label><br>
							<label><input type="checkbox" name="arwp_post_type[]" class="arwp-post-type" value="revision"><?php esc_html_e('Revisions', 'arwp'); ?></label><br>
							<label><input type="checkbox" name="arwp_post_type[]" class="arwp-post-type" value="attachment"><?php esc_html_e('Attachments', 'arwp'); ?></label><br>
							<label><input type="checkbox" name="arwp_post_type[]" class="arwp-post-type" value="all"><?php esc_html_e('All types', 'arwp'); ?></label>
						</p>
					</div>
					<p>
						<label>
							<?php esc_html_e('Security key:', 'arwp'); ?> <strong><?php esc_html_e('reset', 'arwp'); ?></strong><br>
							<input id="arwp-input" type="text" name="arwp_input" autocomplete="off" autofocus required />
						</label>
					</p>
					<p><input id="arwp-button" name="arwp_button" type="submit" class="button-primary" value="<?php esc_attr_e('Start cleaning', 'arwp'); ?>" /></p>
				</form>
			</div>
			<div class="arwp-form-info">
				<h3><?php esc_html_e('Information:', 'arwp'); ?></h3>
				<p class="re-install-info"><?php esc_html_e('This option a reset makes a fresh installation of your database. Therefore, ANY data in your database will be lost!', 'arwp'); ?></p>
				<p class="re-install-uploads-info"><?php esc_html_e('This option a reset makes a fresh installation of your database. Therefore, ANY data in your database will be lost. There will also be completely cleared folder "uploads"!', 'arwp'); ?></p>
				<p class="post-clear-info"><?php esc_html_e('This option is to remove posts, pages, revisions, attachments or all items!', 'arwp'); ?></p>
				<p class="delete-theme-info"><?php esc_html_e('This option is to remove all of your theme except active theme!', 'arwp'); ?></p>
				<p class="delete-plugin-info"><?php esc_html_e('This option is to remove all of your plugins!', 'arwp'); ?></p>
				<p class="clear-uploads-info"><?php esc_html_e('This option is to clean the "uploads" folder!', 'arwp'); ?></p>
				<p class="deep-cleaning-info"><?php esc_html_e('This option removes the your plugins and themes, cleared "uploads" folder and then start a re-installation WordPress!', 'arwp'); ?></p>
			</div>
			<div class="overflow">
				<span class="spinner"></span>
			</div>
			<div class="clear"></div>
		</div>

		<div class="arwp-right">
			<div id="result">
				<h3><?php esc_html_e('Operation result:', 'arwp'); ?></h3>
				<div id="loader">
					<div class="rect1"></div>
					<div class="rect2"></div>
					<div class="rect3"></div>
					<div class="rect4"></div>
					<div class="rect5"></div>
				</div>
				<p class="empty"><?php esc_html_e('Operation not started =(', 'arwp'); ?></p>
			</div>
		</div>

		<div class="clear"></div>
		<div class="arwp-center">
			<p class="left">&copy; <?php echo date('Y'); ?></p>
			<p class="right">Development <a href="//3y3ik.name">by 3y3ik</a></p>
			<div class="clear"></div>
		</div>
	</div>
</div>