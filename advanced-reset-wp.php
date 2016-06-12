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



class ZYZIK_AdvancedResetWP
{
	private $IN_SUB_MENU;

	public function __construct()
	{
		add_action('init', array($this, 'arwp_output_buffer'));
		add_action('wp_footer', array($this, 'arwp_end_buffer'));
		add_action('admin_menu', array($this, 'arwp_register_menu'));
		add_action('admin_enqueue_scripts', array($this, 'arwp_load_css_and_js'));
	}

	public function arwp_output_buffer()
	{
		ob_start();
	}

	public function arwp_end_buffer()
	{
		ob_end_flush();
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
			array($this, 'arwp_render_page')
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
			require_once 'include/head.php';

			// post processing
			if (isset($_POST['arwp_button']) && !empty($_POST['arwp_input'])) {
				if (!check_admin_referer('arwp_nonce')) return;

				$this->arwp_processing_data($_POST);
			}

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
		if (!is_array($post)) return false;
		$type = sanitize_text_field($post['arwp_type']);
		$post_type = isset($post['arwp_post_type']) ? $post['arwp_post_type'] : null;

		switch ($type) {
			case 're-install': $this->arwp_re_install(); break;
			case 'post-clear': $this->arwp_post_clear($post_type); break;
			case 'delete-theme': $this->arwp_delete_theme(); break;
			case 'delete-plugin': $this->arwp_delete_plugin(); break;
			case 'deep-cleaning': $this->arwp_deep_cleaning(); break;
			default: break;
		}

		return true;
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
		if (!is_super_admin($user->ID)) return false;

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

		// install WordPress
		wp_install($blog_title, $user->user_login, $user->user_email, $blog_public, '', $user->user_pass, $blog_language);

        // Set user password
        $query = $wpdb->prepare("UPDATE $wpdb->users SET user_pass = %s WHERE ID = %d", $user->user_pass, $user->ID);
        $wpdb->query($query);

		// activate this plugin
		$activate_plugin = activate_plugin(ARWP_PLUGIN_BASENAME);
		if (is_wp_error($activate_plugin)) {
			echo $activate_plugin->get_error_message();
			return false;
		}

		// Clear all cookies and add new
		wp_logout();
		wp_clear_auth_cookie();
		wp_set_auth_cookie($user->ID);

		// Redirect user to admin panel
		wp_safe_redirect(admin_url('tools.php?page=advanced-reset-wp&reset=re-install'));
		exit;
	}

	private function arwp_post_clear($type)
	{
		// check need access
		if (empty($type) || !is_array($type)) return false;
		if (!current_user_can('delete_posts') || !current_user_can('delete_pages')) return false;

		if (in_array('all', $type)) {
			$this->arwp_delete_in_db('all');
		} else {
			foreach ($type as $item) {
				$this->arwp_delete_in_db(sanitize_post($item, 'db'));
			}
		}

		return true;
	}

	private function arwp_delete_in_db($type)
	{
		$count = null;

		if ($type == 'all') {
			global $wpdb;

			$all = $wpdb->get_results("SELECT ID FROM $wpdb->posts");
			$count = count($all);

			foreach ($all as $item) {
				wp_delete_post($item->ID, true);
			}

			$wpdb->query("TRUNCATE TABLE $wpdb->posts");
		} else {
			switch ($type) {
				case 'post':
					$posts = get_posts();
					$count = count($posts);

					foreach ($posts as $post) {
						wp_delete_post($post->ID, true);
					}
					break;
				case 'page':
					$pages = get_pages();
					$count = count($pages);

					foreach ($pages as $page) {
						wp_delete_post($page->ID, true);
					}
					break;
				case 'revision':
					global $wpdb;

					$revision = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_type = 'revision'");
					$count = count($revision);

					foreach ($revision as $item) {
						wp_delete_post($item->ID, true);
					}
					break;
				case 'attachment';
					$attachments = get_posts(array('post_type' => 'attachment'));
					$count = count($attachments);

					foreach ($attachments as $attachment) {
						wp_delete_post($attachment->ID, true);
					}
					break;
				default:
					break;
			}
		}

		echo "delete $count item from $type<br>";
	}

	private function arwp_delete_theme()
	{
        // check need access
        if (!current_user_can('delete_themes')) return false;

        // get need themes
        $lists = wp_get_themes();
        $active = wp_get_theme();

        foreach ($lists as $theme) {
            if ($theme->Template != $active['Template']) {
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

        wp_safe_redirect(admin_url('tools.php?page=advanced-reset-wp&reset=theme'));
        exit;
    }

	private function arwp_delete_plugin()
	{
        // check need access
        if (!current_user_can('delete_plugins')) return false;

		// plugin list
		$active = array();
		$not_active = array();
		$plugins = get_plugins();
		$default = 'advanced-reset-wp/advanced-reset-wp.php';

		// leave our plugin
		if (array_key_exists($default, $plugins)) {
			unset($plugins[$default]);
		}

		// detect active/inactive plugin
		foreach ($plugins as $plugin_file => $plugin_data) {
			if (is_plugin_active($plugin_file)) {
				$active[] = $plugin_file;
			} else {
				$not_active[] = $plugin_file;
			}
		}

		// deactivate plugins
		deactivate_plugins($active);

		// delete plugins
		$plugin_list = array_merge($not_active, $active);
		$delete_plugin = delete_plugins($plugin_list);
		if (is_wp_error($delete_plugin)) {
			echo $delete_plugin->get_error_message();
			return false;
		}

		wp_safe_redirect(admin_url('tools.php?page=advanced-reset-wp&reset=plugin'));
		exit;
    }

	private function arwp_deep_cleaning()
	{}
}

new ZYZIK_AdvancedResetWP();
