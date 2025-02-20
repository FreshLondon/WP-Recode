<?php
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	
	class DebugControl {
		private static $instance = null;
		private $options;
		
		public static function getInstance() {
			if ( self::$instance == null ) {
				self::$instance = new self();
			}
			
			return self::$instance;
		}
		
		private function __construct() {
			add_action( 'admin_menu', [ $this, 'addSettingsPage' ] );
			add_action( 'admin_init', [ $this, 'registerSettings' ] );
			add_action( 'plugins_loaded', [ $this, 'setupErrorReporting' ] );
			
			$this->options = get_option( 'debug_control_options', [
				'E_ERROR'           => 1,
				'E_WARNING'         => 1,
				'E_PARSE'           => 1,
				'E_NOTICE'          => 0,
				'E_DEPRECATED'      => 0,
				'E_USER_DEPRECATED' => 0,
			] );
		}
		
		public function addSettingsPage() {
			add_options_page( 'Debug Control Settings', 'Debug Control', 'manage_options', 'debug-control', [ $this, 'renderSettingsPage' ] );
		}
		
		public function registerSettings() {
			register_setting( 'debug_control_options', 'debug_control_options' );
		}
		
		public function renderSettingsPage() {
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
		
		public function setupErrorReporting() {
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
			
			error_reporting( $error_level );
		}
	}
	
	// Initialize debug control
	DebugControl::getInstance();