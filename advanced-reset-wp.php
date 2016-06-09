<?php

/*
Plugin Name: Advanced Reset WP
Plugin URI: http://wordpress.org/plugin/
Description: A brief description of the Plugin
Version: 1.0
Author: 3y3ik
Author URI: http://wordpress.3y3ik.name/
License: A "advanced-reset" license name e.g. GPL2
*/

/********************************************************************
 * Check need privilege
 ********************************************************************/
if (!defined('ABSPATH')) return;
if (!is_admin()) return;


/********************************************************************
 * Define common constants
 ********************************************************************/
if (!defined('ARWP_PLUGIN_VERSION')) define('ARWP_PLUGIN_VERSION', '1.0.0');
if (!defined('ARWP_PLUGIN_DIR_PATH')) define('ARWP_PLUGIN_DIR_PATH', plugins_url('', __FILE__));
if (!defined('ARWP_PLUGIN_BASENAME')) define('ARWP_PLUGIN_BASENAME', plugin_basename(__FILE__));



class AdvancedResetWP
{
	private $IN_SUB_MENU;

	public function __construct()
	{
		add_action('admin_menu', array(&$this, 'arwp_register_menu'));
		add_action('admin_enqueue_scripts', array(&$this, 'arwp_load_css_and_js'));
	}

	/********************************************************************
	 * Add sub menu in tools
	 ********************************************************************/
	public function arwp_register_menu()
	{
		$this->IN_SUB_MENU = add_submenu_page(
			'tools.php',
			'Advanced Reset WP',
			'Advanced Reset WP',
			'manage_options',
			'advanced-reset-wp',
			array(&$this, 'arwp_render_page')
		);
	}

	/********************************************************************
	 * Load CSS and JS
	 * @param $hook
	 ********************************************************************/
	public function arwp_load_css_and_js($hook)
	{
		if ($hook != $this->IN_SUB_MENU) return;

		wp_enqueue_style('arwp-style-css', ARWP_PLUGIN_DIR_PATH .'/css/style.css');
		wp_enqueue_script('arwp-all-js', ARWP_PLUGIN_DIR_PATH .'/js/all.js', array('jquery'));
	}

	/********************************************************************
	 * The admin page of the plugin
	 ********************************************************************/
	public function arwp_render_page()
	{
		if (current_user_can('manage_options')) {
			if (isset($_POST['arwp_button']) && !empty($_POST['arwp_input'])) {
				if (!check_admin_referer('arwp_nonce')) return;

				$this->arwp_processing_data($_POST);
			}

            require_once 'include/head.php';

			// notice after reset
			if (isset($_GET['reset'])) {
				echo $_GET['reset'];
			}

			require_once 'include/form.php';

            require_once 'include/foot.php';
		}
	}

	private function arwp_processing_data($post)
	{
		if (!is_array($post)) return;
		$type = sanitize_text_field($post['arwp_type']);

		switch ($type) {
			case 're-install': $this->arwp_re_install(); break;
			case 'post-clear': $this->arwp_post_clear(); break;
			case 'delete-theme': $this->arwp_delete_theme(); break;
			case 'delete-plugin': $this->arwp_delete_plugin(); break;
			case 'deep-cleaning': $this->arwp_deep_cleaning(); break;
			default: return; break;
		}
	}

	private function arwp_re_install()
	{
		global $current_user;
		$user = null;

		// get admin info
		if ($current_user->user_login != 'admin') {
			$user = get_user_by('login', 'admin');
		} else {
			$user = $current_user;
		}

		// check admin info
		if ($user->user_level < 10 || !is_super_admin($user->ID)) {
			return false;
		}

		// get site options
		$blog_title = get_option('blogname');
		$blog_public = get_option('blog_public');
		$blog_language = get_option('WPLANG');

		// include need api
		require_once ABSPATH .'/wp-admin/includes/upgrade.php';
		global $wpdb;

		// get and drop old table
		$tables = $wpdb->get_col("SHOW TABLES LIKE '{$wpdb->prefix}%'");
		foreach ($tables as $table) {
			$wpdb->query("DROP TABLE {$table}");
		}

		// install WordPress || $P$B6prmwdXWkVceKPfcVqzz6nmY8PldE.
		wp_install($blog_title, $user->user_login, $user->user_email, $blog_public, '', $user->user_pass, $blog_language);

        // Set user password
        $query = $wpdb->prepare("UPDATE $wpdb->users SET user_pass = %s WHERE ID = %d", $user->user_pass, $user->ID);
        $wpdb->query( $query );

		// activate this plugin
		$activate_plugin = activate_plugin(ARWP_PLUGIN_BASENAME);
		if (is_wp_error($activate_plugin)) {
			echo $activate_plugin->get_error_message();
			return false;
		}

		// Clear all cookies and add new
//		wp_logout();
//		wp_clear_auth_cookie();
//		wp_set_auth_cookie($user->user_id);

		// Redirect user to admin panel
//		wp_redirect(admin_url('tools.php?page=advanced-reset-wp&reset=re-install'));
//		exit;
	}

	private function arwp_post_clear()
	{}

	private function arwp_delete_theme()
	{
        // check need access
        if (!current_user_can('delete_themes')) return false;

        // get need themes
        $lists = wp_get_themes();
        $active = wp_get_theme();
        $default = 'twentysixteen';

        foreach ($lists as $theme) {
            if ($theme->Template != $active['Template'] && $theme->Template != $default) {
                $delete_theme = delete_theme($theme->template);
                if (is_wp_error($delete_theme)) {
                    echo $delete_theme->get_error_message();
                    return false;
                }
                echo 'Theme '. $theme->Name .' successfully removed<br>';
            } else {
                echo 'Theme '. $theme->Name .' <b>IS NOT</b> removed!<br>';
            }
        }

        wp_redirect(admin_url('tools.php?page=advanced-reset-wp&reset=theme'));
        exit;
    }

	private function arwp_delete_plugin()
	{
        // check need access
        if (!current_user_can('delete_plugins')) return false;


        return true;
    }

	private function arwp_deep_cleaning()
	{}
}

new AdvancedResetWP();
