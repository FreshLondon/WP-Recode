<?php
/**
 * Plugin Name:       WP Recode
 * Plugin URI:        https://wprecode.com
 * Description:       Control various settings and options for WordPress.
 * Version:           0.1
 * Requires at least: 6.0
 * Requires PHP:      8.3
 * Author:            Dirty Mike and the Boys
 * Author URI:        https://dirtymikeandtheboys.com
 * License:           GPL v2 or later
 * Text Domain:       wp-recode
 *
 * @package WP_Recode
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Main WP Recode class.
 *
 * Handles the core functionality of the WP Recode plugin, including
 * plugin activation/deactivation, admin menu creation, and symlink management.
 *
 * @since 0.1.0
 * @package WP_Recode
 */
class WPRecode {
	/**
	 * Path to the must-use plugin file.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	private string $mu_plugin;

	/**
	 * Path to the must-use plugins directory.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	private string $mu_folder;

	/**
	 * Constructor: Initializes plugin paths and hooks.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->mu_plugin = plugin_dir_path( __FILE__ ) . 'wp-recode-mu.php';
		$this->mu_folder = WP_CONTENT_DIR . '/mu-plugins';

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_settings_link' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
	}

	/**
	 * Handles plugin activation: Creates the symlink if needed.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function activate(): void {
		error_log( 'Activating...' );

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		if ( ! $wp_filesystem->exists( $this->mu_folder ) ) {
			$wp_filesystem->mkdir( $this->mu_folder, 0755 );
		}

		$destination = $this->mu_folder . '/' . basename( $this->mu_plugin );

		if ( is_link( $destination ) ) {
			error_log( 'Symlink already exists.' );
			$this->admin_notice( 'WP Recode: Symlink already exists, skipping!', 'warning' );
			return;
		}

		if ( ! symlink( $this->mu_plugin, $destination ) ) {
			error_log( 'Failed to create symlink.' );
			$this->admin_notice( 'WP Recode: Failed to create symlink!', 'error' );
			return;
		}

		error_log( 'Symlink created.' );
		$this->admin_notice( 'WP Recode: Symlink created!', 'success' );
	}

	/**
	 * Handles plugin deactivation: Removes the symlink if it exists.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function deactivate(): void {
		error_log( 'Deactivating...' );

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$destination = $this->mu_folder . '/' . basename( $this->mu_plugin );

		if ( ! $wp_filesystem->exists( $this->mu_folder ) ) {
			error_log( 'MU plugins directory does not exist.' );
			return;
		}

		if ( ! is_link( $destination ) ) {
			error_log( 'No symlink found at: ' . $destination );
			return;
		}

		if ( ! wp_is_writable( dirname( $destination ) ) ) {
			error_log( 'No permission to delete symlink.' );
			$this->admin_notice( 'WP Recode: No permission to remove symlink. Please check file permissions.', 'error' );
			return;
		}

		if ( ! wp_delete_file( $destination ) ) {
			error_log( 'Failed to remove symlink. wp_delete_file() returned false.' );
			$this->admin_notice( 'WP Recode: Failed to remove symlink. Please check file permissions.', 'error' );
			return;
		}

		error_log( 'Symlink removed successfully.' );
		$this->admin_notice( 'WP Recode: Symlink removed!', 'success' );
	}

	/**
	 * Displays admin notices.
	 *
	 * @since 0.1.0
	 * @param string $message The message to display.
	 * @param string $type    The notice type (success, warning, error).
	 * @return void
	 */
	private function admin_notice( string $message, string $type = 'success' ): void {
		add_action(
			'admin_notices',
			function () use ( $message, $type ) {
				printf(
					'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
					esc_attr( $type ),
					esc_html( $message )
				);
			}
		);
	}

	/**
	 * Adds a settings link to the plugin's action links.
	 *
	 * @since 0.1.0
	 * @param array $links The existing plugin action links.
	 * @return array Modified plugin action links.
	 */
	public function add_plugin_settings_link( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=wp-recode-debug' ) ),
			esc_html__( 'Settings', 'wp-recode' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Adds the admin menu and submenu pages.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function add_admin_menu(): void {
		add_menu_page(
			'WP Recode',
			'WP Recode',
			'manage_options',
			'wp-recode-debug',
			'',
			'data:image/svg+xml;base64,' . base64_encode( file_get_contents( plugin_dir_path( __FILE__ ) . 'assets/img/bug.svg' ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			90
		);

		add_submenu_page(
			'wp-recode-debug',
			'Debug Control',
			'Debug Control',
			'manage_options',
			'wp-recode-debug',
			array( $this, 'render_debug_control_page' )
		);

		add_submenu_page(
			'wp-recode-debug',
			'Config Control',
			'Config Control',
			'manage_options',
			'wp-recode-config',
			array( $this, 'render_config_control_page' )
		);

		add_submenu_page(
			'wp-recode-debug',
			'.htaccess Control',
			'.htaccess Control',
			'manage_options',
			'wp-recode-htaccess',
			array( $this, 'render_htaccess_control_page' )
		);
	}

	/**
	 * Renders the Debug Control page.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function render_debug_control_page(): void {
		echo '<h1>' . esc_html__( 'Debug Control', 'wp-recode' ) . '</h1>';
	}

	/**
	 * Renders the Config Control page.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function render_config_control_page(): void {
		echo '<h1>' . esc_html__( 'Config Control', 'wp-recode' ) . '</h1>';
	}

	/**
	 * Renders the htaccess Control page.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function render_htaccess_control_page(): void {
		echo '<h1>' . esc_html__( '.htaccess Control', 'wp-recode' ) . '</h1>';
	}
}

// Initialize the plugin.
new WPRecode();
