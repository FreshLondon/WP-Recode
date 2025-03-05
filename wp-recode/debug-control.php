<?php
/**
 * Debug Control functionality.
 *
 * @package WP_Recode
 * @subpackage Debug
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Debug Control class.
 *
 * Controls error reporting and debugging settings for WordPress.
 *
 * @since 1.0.0
 */
class Debug_Control {
	/**
	 * Instance of this class.
	 *
	 * @var Debug_Control
	 */
	private static $instance = null;

	/**
	 * Debug control options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Get instance of this class.
	 *
	 * @since 1.0.0
	 * @return Debug_Control Instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'plugins_loaded', array( $this, 'setup_error_reporting' ) );

		$this->options = get_option(
			'debug_control_options',
			array(
				'E_ERROR'           => 1,
				'E_WARNING'         => 1,
				'E_PARSE'           => 1,
				'E_NOTICE'          => 0,
				'E_DEPRECATED'      => 0,
				'E_USER_DEPRECATED' => 0,
			)
		);
	}

	/**
	 * Add settings page to WordPress admin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_settings_page() {
		add_options_page(
			'Debug Control Settings',
			'Debug Control',
			'manage_options',
			'debug-control',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings for the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_settings() {
		register_setting( 'debug_control_options', 'debug_control_options' );
	}

	/**
	 * Render the settings page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h2>Debug Control Settings</h2>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'debug_control_options' );
				?>
				<table class="form-table">
					<tr>
						<th scope="row">Error Types to Log</th>
						<td>
							<fieldset>
								<label>
									<input type="checkbox" name="debug_control_options[E_ERROR]" value="1" <?php checked( isset( $this->options['E_ERROR'] ) ); ?>>
									E_ERROR (Fatal run-time errors)
								</label>
								<br>
								<label>
									<input type="checkbox" name="debug_control_options[E_WARNING]" value="1" <?php checked( isset( $this->options['E_WARNING'] ) ); ?>>
									E_WARNING (Run-time warnings)
								</label>
								<br>
								<label>
									<input type="checkbox" name="debug_control_options[E_PARSE]" value="1" <?php checked( isset( $this->options['E_PARSE'] ) ); ?>>
									E_PARSE (Compile-time parse errors)
								</label>
								<br>
								<label>
									<input type="checkbox" name="debug_control_options[E_NOTICE]" value="1" <?php checked( isset( $this->options['E_NOTICE'] ) ); ?>>
									E_NOTICE (Run-time notices)
								</label>
								<br>
								<label>
									<input type="checkbox" name="debug_control_options[E_DEPRECATED]" value="1" <?php checked( isset( $this->options['E_DEPRECATED'] ) ); ?>>
									E_DEPRECATED (Run-time notices about deprecated features)
								</label>
								<br>
								<label>
									<input type="checkbox" name="debug_control_options[E_USER_DEPRECATED]" value="1" <?php checked( isset( $this->options['E_USER_DEPRECATED'] ) ); ?>>
									E_USER_DEPRECATED (User-generated deprecation warnings)
								</label>
							</fieldset>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Setup error reporting based on options.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function setup_error_reporting() {
		$error_level = E_ALL;

		if ( ! isset( $this->options['E_WARNING'] ) || ! $this->options['E_WARNING'] ) {
			$error_level &= ~E_WARNING;
		}

		if ( ! isset( $this->options['E_NOTICE'] ) || ! $this->options['E_NOTICE'] ) {
			$error_level &= ~E_NOTICE;
		}

		if ( ! isset( $this->options['E_DEPRECATED'] ) || ! $this->options['E_DEPRECATED'] ) {
			$error_level &= ~E_DEPRECATED;
		}

		if ( ! isset( $this->options['E_USER_DEPRECATED'] ) || ! $this->options['E_USER_DEPRECATED'] ) {
			$error_level &= ~E_USER_DEPRECATED;
		}

		// phpcs:ignore
		error_reporting( $error_level ); 
	}
}

// Initialize debug control.
Debug_Control::get_instance();