<?php
	
	/**
	 * Plugin Name:       WP Recode
	 * Plugin URI:        https://wprecode.com
	 * Description:       Developer mode
	 * Version:           0.1
	 * Requires at least: 6.0
	 * Requires PHP:      8.3
	 * Author:            Dirty Mike and the Boys
	 * License:           GPL v2 or later
	 * Text Domain:       wp-recode
	 */
	
	if (!defined('ABSPATH')) {
		exit; // Exit if accessed directly.
	}
	
	class WPRecode {
		/**
		 * Path to the must-use plugin file.
		 * @var string
		 */
		private $mu_plugin;
		
		/**
		 * Path to the must-use plugins directory.
		 * @var string
		 */
		private $mu_folder;
		
		/**
		 * Constructor: Initializes plugin paths and hooks.
		 */
		public function __construct() {
			$this->mu_plugin = plugin_dir_path(__FILE__) . 'wp-recode-mu.php';
			$this->mu_folder = WP_CONTENT_DIR . '/mu-plugins';
			
			register_activation_hook( __FILE__, [ $this, 'activate' ] );
			register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
			
			add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_settings_link' ) );
			add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );

		}
		
		/**
		 * Handles plugin activation: Creates the symlink if needed.
		 */
		public function activate(): void {
			error_log('Activating...');
			
			if (!file_exists($this->mu_folder)) {
				mkdir($this->mu_folder, 0755, true);
			}
			
			$destination = $this->mu_folder . '/' . basename($this->mu_plugin);
			
			if (is_link($destination)) {
				error_log('Symlink already exists.');
				$this->admin_notice('WP Recode: Symlink already exists, skipping!', 'warning');
			} else {
				if (symlink($this->mu_plugin, $destination)) {
					error_log('Symlink created.');
					$this->admin_notice('WP Recode: Symlink created!', 'success');
				} else {
					error_log('Failed to create symlink.');
					$this->admin_notice('WP Recode: Failed to create symlink!', 'error');
				}
			}
		}
		
		/**
		 * Handles plugin deactivation: Removes the symlink if it exists.
		 */
		public function deactivate(): void {
			error_log('Deactivating...');
			$destination = $this->mu_folder . '/' . basename($this->mu_plugin);
			
			if (!file_exists($this->mu_folder)) {
				return;
			}
			
			if (is_link($destination)) {
				if (unlink($destination)) {
					error_log('Symlink removed.');
					$this->admin_notice('WP Recode: Symlink removed!', 'success');
				} else {
					error_log('Failed to remove symlink.');
					$this->admin_notice('WP Recode: Failed to remove symlink.', 'error');
				}
			} else {
				error_log('No symlink found.');
			}
		}
		
		/**
		 * Displays admin notices.
		 *
		 * @param string $message The message to display.
		 * @param string $type    The notice type (success, warning, error).
		 */
		private function admin_notice( string $message, string $type = 'success'): void {
			add_action('admin_notices', function () use ($message, $type) {
				echo '<div class="notice notice-' . esc_attr($type) . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
			});
		}
		
		public function add_plugin_settings_link( $links ) {
			$settings_link = '<a href="options-general.php?page=debug-control">' . __( 'Settings' ) . '</a>';
			array_unshift( $links, $settings_link );
			return $links;
		}
		
		public function add_admin_menu() {
			$svg_icon = 'data:image/svg+xml;base64,' . base64_encode('<?xml version="1.0" ?><svg width="800" height="800" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16 4L13.7487 6.25129M13.7487 6.25129C13.242 6.09132 12.6621 6 12 6C11.526 6 10.9172 6.08088 10.2886 6.28864M13.7487 6.25129C15.3001 6.741 16.1662 7.87407 16.6073 9M8 4L10.2886 6.28864M10.2886 6.28864C9.13478 6.67002 7.91423 7.47896 7.33838 9M16.6073 9C16.8926 9.72834 17 10.4537 17 11V13M16.6073 9H18C18.6667 9 20 8.6 20 7M17 13V15C17 15.5463 16.8926 16.2717 16.6073 17M17 13H20M16.6073 17C16.0221 18.4937 14.6889 20 12 20C9.31111 20 7.97787 18.4937 7.39275 17M16.6073 17H18C18.6667 17 20 17.4 20 19M7.33838 9C7.12488 9.56394 7 10.2258 7 11V13M7.33838 9H6C5.33333 9 4 8.6 4 7M7 13V15C7 15.5463 7.10744 16.2717 7.39275 17M7 13H4M7.39275 17H6C5.33333 17 4 17.4 4 19" stroke="#edeff5" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><path d="M12 10H12.001" stroke="#edeff5" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><path d="M10 13H10.001" stroke="#edeff5" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><path d="M14 13H14.001" stroke="#edeff5" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>');

			add_menu_page( 
				'WP Recode',
				'WP Recode',
				'manage_options',
				'wp-recode-debug',
				'',
				$svg_icon,
				90
			);
			
			add_submenu_page(
				'wp-recode-debug',
				'Debug Control',
				'Debug Control',
				'manage_options',
				'wp-recode-debug',
				[ $this, 'render_debug_control_page' ]
			);
			
			add_submenu_page(
				'wp-recode-debug',
				'Foobar Control',
				'Foobar Control',
				'manage_options',
				'wp-recode-foobar',
				[ $this, 'render_foobar_control_page' ]
			);
			
			add_submenu_page(
				'wp-recode-debug',
				'Barfoo Control',
				'Barfoo Control',
				'manage_options',
				'wp-recode-barfoo',
				[ $this, 'render_barfoo_control_page' ]
			);
		}
		
		public function render_debug_control_page() {
			echo '<h1>Debug Control</h1>';
		}
		
		public function render_foobar_control_page() {
			echo '<h1>Foobar Control</h1>';
		}
		
		public function render_barfoo_control_page() {
			echo '<h1>Barfoo Control</h1>';
		}
	}
	
	// Initialize the plugin.
	new WPRecode();