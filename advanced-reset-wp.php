<?php
/*
Plugin Name: Advanced Reset WP
Plugin URI: https://github.com/3y3ik/advanced-reset-wp
Description: Re-install WordPress, delete themes, plugins and posts, pages, attachments
Author: 3y3ik
Author URI: http://3y3ik.name/
Version: 1.2.3
Text Domain: arwp
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * Check minimal need privilege
 */
if ( ! defined( 'ABSPATH' ) ) { return; }
if ( ! is_admin() ) { return; }


/**
 * Define common constants
 */
if ( ! defined( 'ARWP_PLUGIN_VERSION' ) ) define( 'ARWP_PLUGIN_VERSION', '1.2.3' );
if ( ! defined( 'ARWP_PLUGIN_DIR_PATH' ) ) define( 'ARWP_PLUGIN_DIR_PATH', plugins_url( '', __FILE__ ) );
if ( ! defined( 'ARWP_PLUGIN_BASENAME' ) ) define( 'ARWP_PLUGIN_BASENAME', plugin_basename( __FILE__) );


/**
 * Class ZYZIK_AdvancedResetWP
 * Basic Class For functioning plugin
 */
class i3y3ik_AdvancedResetWP {

	private $IN_SUB_MENU;
	private $uploads_dir;
	private $active_theme;

	/**
	 * ZYZIK_AdvancedResetWP constructor
	 * Register need actions
	 */
	public function __construct() {
		$this->uploads_dir  = wp_get_upload_dir();
		$this->active_theme = wp_get_theme();

		add_action( 'admin_menu', array( $this, 'arwp_register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'arwp_load_css_and_js' ) );
		load_plugin_textdomain( 'arwp', false, basename( dirname( __FILE__ ) ) . '/languages' );

		add_action( 'wp_ajax_arwp_ajax', array( $this, 'arwp_ajax_callback' ) );
	}

	/**
	 * Add sub menu in tools
	 * @return void
	 */
	public function arwp_register_menu() {
		$this->IN_SUB_MENU = add_submenu_page(
			'tools.php',
			__( 'Advanced Reset WP', 'arwp' ),
			__( 'Advanced Reset WP', 'arwp' ),
			'manage_options',
			'advanced-reset-wp',
			array( $this, 'arwp_render_page' )
		);
	}

	/**
	 * Include CSS/JS
	 * @param $hook
	 * @return bool
	 */
	public function arwp_load_css_and_js( $hook ) {
		if ( $hook != $this->IN_SUB_MENU ) { return false; }

		wp_enqueue_style( 'arwp-admin-style-css', ARWP_PLUGIN_DIR_PATH . '/css/admin.css' );
		wp_enqueue_script( 'donorbox', '//donorbox.org/install-popup-button.js', array(), null, true );
		wp_enqueue_script( 'arwp-all-js', ARWP_PLUGIN_DIR_PATH . '/js/all.js', array( 'jquery' ), null, true );
		wp_localize_script( 'arwp-all-js', 'arwp_ajax', array(
			'nonce' => wp_create_nonce( 'arwp-ajax-nonce' ),
		) );

		return true;
	}

	/**
	 * Ajax action
	 * @return void
	 */
	public function arwp_ajax_callback() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'arwp-ajax-nonce' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action!', 'arwp' ) );
		}

		parse_str( $_POST['my_form'], $form );

		if ( $form['arwp_input'] != 'reset' ) {
			echo wpautop( esc_html__( 'Invalid code!', 'arwp' ) );
			wp_die();
		}

		$this->arwp_processing_data( $form );
		wp_die();
	}

	/**
	 * Render admin page
	 * @return void
	 */
	public function arwp_render_page() {
		if ( current_user_can( 'manage_options' ) ) {
			require_once 'include/view.php';
		}
	}

	/**
	 * Processing post data
	 * @param $post array
	 * @return bool
	 */
	private function arwp_processing_data( $post ) {
		if ( ! is_array( $post ) ) { return false; }
		$type = sanitize_text_field( $post['arwp_type'] );
		$post_type = isset( $post['arwp_post_type'] ) ? $post['arwp_post_type'] : null;

		switch ( $type ) {
			case 're-install':
				$this->arwp_re_install();
				break;
			case 're-install-uploads':
				$this->arwp_processing_clear_uploads();
				$this->arwp_re_install();
				break;
			case 'post-clear':
				$this->arwp_post_clear( $post_type );
				break;
			case 'delete-theme':
				$this->arwp_delete_theme();
				break;
			case 'delete-plugin':
				$this->arwp_delete_plugin();
				break;
			case 'clear-uploads':
				$this->arwp_processing_clear_uploads();
				break;
			case 'deep-cleaning':
				$this->arwp_deep_cleaning();
				break;
			default:
				break;
		}

		return true;
	}

	/**
	 * Re-install WordPress
	 * @return bool
	 */
	private function arwp_re_install() {
		echo wpautop( esc_html__( 'Starting re-install WordPress...', 'arwp' ) );

		global $current_user;

		// check admin info
		if ( ! is_super_admin( $current_user->ID ) ) return false;

		// get site options
		$blog_title = get_option( 'blogname' );
		$blog_public = get_option( 'blog_public' );
		$blog_language = get_option( 'WPLANG' );

		// include need api
		global $wpdb;
		require_once ABSPATH .'/wp-admin/includes/upgrade.php';

		// get and drop old table
		$tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}%'" );
		foreach ( $tables as $table ) {
			$wpdb->query( "DROP TABLE {$table}" );
		}

		// install WordPress
		$install = wp_install( $blog_title, $current_user->user_login, $current_user->user_email, $blog_public, '', $current_user->user_pass, $blog_language) ;

		// set user password
		$query = $wpdb->prepare( "UPDATE $wpdb->users SET user_pass = %s WHERE ID = %d", $current_user->user_pass, $install['user_id'] );
		$wpdb->query( $query );

		// activate this plugin
		$activate_plugin = activate_plugin( ARWP_PLUGIN_BASENAME );
		if ( is_wp_error( $activate_plugin ) ) {
			wp_die( $activate_plugin->get_error_message() );
		}

		// switch to active theme
		switch_theme( $this->active_theme->get_stylesheet() );

		// clear all cookies and add new
		wp_logout();

		echo wpautop( esc_html__( 'WordPress successfully re-install!', 'arwp' ) );
		echo '<a href="'. admin_url() .'">'. esc_html__( 'Please refresh this page', 'arwp' ) .'</a>';

		return true;
	}

	/**
	 * Preparing for remove post
	 * @param $type array
	 * @return bool
	 */
	private function arwp_post_clear( $type ) {
		// check need access
		if ( empty( $type ) || ! is_array( $type ) ) { return false; }
		if ( ! current_user_can( 'delete_posts' ) || ! current_user_can( 'delete_pages' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action!', 'arwp' ) );
		}

		if ( in_array( 'all', $type ) ) {
			echo wpautop( esc_html__( 'Starting remove all posts...', 'arwp' ) );
			$this->arwp_delete_in_db( 'all' );
		} else {
			foreach ( $type as $item ) {
				echo wpautop( sprintf( esc_html__( 'Starting remove posts type %s...', 'arwp' ), $item ) );
				$this->arwp_delete_in_db( sanitize_post( $item, 'db' ) );
			}
		}

		return true;
	}

	/**
	 * Delete post form db
	 * @param $type string
	 * @return bool
	 */
	private function arwp_delete_in_db( $type ) {
		$count = null;

		switch ( $type ) {
			case 'all':
				global $wpdb;

				$all = $wpdb->get_results( "SELECT ID FROM {$wpdb->posts}" );
				$count = count( $all );

				foreach ( $all as $item ) {
					wp_delete_post( $item->ID, true );
				}

				$wpdb->query( "TRUNCATE TABLE {$wpdb->posts}" );
				break;
			case 'post':
				$posts = get_posts();
				$count = count( $posts );

				foreach ( $posts as $post ) {
					wp_delete_post( $post->ID, true );
				}
				break;
			case 'page':
				$pages = get_pages();
				$count = count( $pages );

				foreach ( $pages as $page ) {
					wp_delete_post( $page->ID, true );
				}
				break;
			case 'revision':
				global $wpdb;

				$revision = $wpdb->get_results( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'revision'" );
				$count = count( $revision );

				foreach ( $revision as $item ) {
					wp_delete_post( $item->ID, true );
				}
				break;
			case 'attachment';
				$attachments = get_posts( array( 'post_type' => 'attachment' ) );
				$count = count( $attachments );

				foreach ( $attachments as $attachment ) {
					wp_delete_post( $attachment->ID, true );
				}
				break;
			default:
				break;
		}

		echo wpautop( sprintf( esc_html__( 'All removed %d posts type %s!', 'arwp' ), $count, $type ) );

		return true;
	}

	/**
	 * Remove themes
	 * @return void
	 */
	private function arwp_delete_theme() {
		// check need access
		if ( ! current_user_can( 'delete_themes' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action!', 'arwp' ) );
		}

		echo wpautop( esc_html__( 'Starting remove themes...', 'arwp' ) );

		// get need themes
		$count = 0;
		$lists = wp_get_themes();

		foreach ( $lists as $theme ) {
			if ( $theme->Template != $this->active_theme['Template'] ) {
				$delete_theme = delete_theme( $theme->template );
				if ( is_wp_error( $delete_theme ) ) {
					wp_die( $delete_theme->get_error_message() );
				}
				$count ++;
				echo wpautop( sprintf( esc_html__( 'Theme %s has been removed!', 'arwp' ), $theme->Name ) );
			}
		}

		echo wpautop( sprintf( esc_html__( 'All removed %d themes!', 'arwp' ), $count ) );
	}

	/**
	 * Remove plugins
	 * @return void
	 */
	private function arwp_delete_plugin() {
		// check need access
		if ( ! current_user_can( 'delete_plugins' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action!', 'arwp' ) );
		}

		echo wpautop( esc_html__( 'Starting remove plugins...', 'arwp' ) );

		// plugin list
		$active = array();
		$not_active = array();
		$plugins = get_plugins();
		$count = count( $plugins ) - 1;

		// leave our plugin
		if ( array_key_exists( ARWP_PLUGIN_BASENAME, $plugins ) ) {
			unset( $plugins[ ARWP_PLUGIN_BASENAME ] );
		}

		// detect active/inactive plugin
		foreach ( $plugins as $plugin_file => $plugin_data ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active[] = $plugin_file;
			} else {
				$not_active[] = $plugin_file;
			}
		}

		// deactivate plugins
		deactivate_plugins( $active );

		// delete plugins
		$plugin_list = array_merge( $not_active, $active );
		$delete_plugin = delete_plugins( $plugin_list );
		if ( is_wp_error( $delete_plugin ) ) {
			wp_die( $delete_plugin->get_error_message() );
		}

		echo wpautop( sprintf( esc_html__( 'All removed %d plugins!', 'arwp' ), $count ) );
	}

	/**
	 * Text status before/after starting
	 * clear "uploads" folder
	 * @return void
	 */
	private function arwp_processing_clear_uploads() {
		echo wpautop( esc_html__( 'Starting clear "uploads" folder...', 'arwp' ) );
		$this->arwp_clear_uploads( $this->uploads_dir['basedir'] );
		echo wpautop( esc_html__( 'Folder "uploads" successful cleared!', 'arwp' ) );
	}

	/**
	 * Clear "uploads" folder
	 * @param string $dir
	 * @return bool
	 */
	private function arwp_clear_uploads( $dir ) {
		$files = array_diff( scandir( $dir ), array( '.', '..' ) );
		foreach ( $files as $file ) {
			( is_dir( "$dir/$file" ) ) ? $this->arwp_clear_uploads( "$dir/$file" ) : unlink( "$dir/$file" );
		}

		return ( $dir != $this->uploads_dir['basedir'] ) ? rmdir( $dir ) : true;
	}

	/**
	 * Deep cleaning
	 * @return void
	 */
	private function arwp_deep_cleaning() {
		$this->arwp_delete_plugin();
		$this->arwp_delete_theme();
		$this->arwp_processing_clear_uploads();
		$this->arwp_re_install();
	}

}

new i3y3ik_AdvancedResetWP();
