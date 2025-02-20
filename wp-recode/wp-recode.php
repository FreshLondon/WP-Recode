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
			
			register_activation_hook(__FILE__, [$this, 'activate']);
			register_deactivation_hook(__FILE__, [$this, 'deactivate']);
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
	}
	
	// Initialize the plugin.
	new WPRecode();