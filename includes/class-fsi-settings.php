<?php
/**
 * Admin settings page for Floating Social Icons.
 *
 * @package Floating_Social_Icons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FSI_Settings {

	/**
	 * Hook suffix of our settings page, used to conditionally load admin assets.
	 *
	 * @var string
	 */
	private $page_hook = '';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Registers the settings page under Settings.
	 */
	public function add_settings_page() {
		$this->page_hook = add_options_page(
			__( 'Floating Social Icons', 'floating-social-icons' ),
			__( 'Floating Social Icons', 'floating-social-icons' ),
			'manage_options',
			'fsi-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Registers the setting and its sanitize callback with the Settings API.
	 */
	public function register_settings() {
		register_setting(
			'fsi_settings_group',
			FSI_OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => fsi_get_default_settings(),
			)
		);
	}

	/**
	 * Loads the color picker and admin repeater assets only on our settings page.
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( $hook !== $this->page_hook ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'fsi-admin', FSI_PLUGIN_URL . 'assets/css/admin.css', array(), FSI_VERSION );

		wp_enqueue_script(
			'fsi-admin',
			FSI_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery', 'wp-color-picker' ),
			FSI_VERSION,
			true
		);
	}

	/**
	 * Sanitizes and validates submitted settings before they're saved.
	 *
	 * @param array $input Raw submitted settings.
	 * @return array Cleaned settings.
	 */
	public function sanitize_settings( $input ) {
		$defaults = fsi_get_default_settings();
		$input    = is_array( $input ) ? $input : array();
		$output   = array();

		$output['position'] = ( isset( $input['position'] ) && 'left' === $input['position'] ) ? 'left' : 'right';

		$output['top_offset'] = isset( $input['top_offset'] ) ? absint( $input['top_offset'] ) : $defaults['top_offset'];
		$output['icon_size']  = isset( $input['icon_size'] ) ? min( 200, max( 16, absint( $input['icon_size'] ) ) ) : $defaults['icon_size'];

		$allowed_shapes  = array( 'circle', 'square', 'none' );
		$output['shape'] = ( isset( $input['shape'] ) && in_array( $input['shape'], $allowed_shapes, true ) )
			? $input['shape']
			: $defaults['shape'];

		foreach ( array( 'icon_color', 'bg_color', 'hover_color' ) as $color_key ) {
			$sanitized             = isset( $input[ $color_key ] ) ? sanitize_hex_color( wp_unslash( $input[ $color_key ] ) ) : '';
			$output[ $color_key ]  = $sanitized ? $sanitized : $defaults[ $color_key ];
		}

		$output['icon_spacing'] = isset( $input['icon_spacing'] ) ? min( 100, max( 0, absint( $input['icon_spacing'] ) ) ) : $defaults['icon_spacing'];

		$output['hide_on_mobile'] = ! empty( $input['hide_on_mobile'] );

		$output['mobile_breakpoint'] = isset( $input['mobile_breakpoint'] ) ? absint( $input['mobile_breakpoint'] ) : $defaults['mobile_breakpoint'];
		if ( $output['mobile_breakpoint'] < 200 ) {
			$output['mobile_breakpoint'] = $defaults['mobile_breakpoint'];
		}

		$output['icons'] = array();
		$known_networks   = array_keys( FSI_Icons::get_icons() );

		if ( ! empty( $input['icons'] ) && is_array( $input['icons'] ) ) {
			foreach ( $input['icons'] as $icon ) {
				if ( empty( $icon['network'] ) || empty( $icon['url'] ) ) {
					continue;
				}

				$network = sanitize_key( $icon['network'] );
				if ( ! in_array( $network, $known_networks, true ) ) {
					continue;
				}

				$url = esc_url_raw( trim( wp_unslash( $icon['url'] ) ) );
				if ( empty( $url ) ) {
					continue;
				}

				$output['icons'][] = array(
					'network' => $network,
					'url'     => $url,
				);
			}
		}

		return $output;
	}

	/**
	 * Renders the settings page markup.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings      = fsi_get_settings();
		$icons_library = FSI_Icons::get_icons();
		?>
		<div class="wrap fsi-settings-wrap">
			<h1><?php esc_html_e( 'Floating Social Icons', 'floating-social-icons' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'fsi_settings_group' ); ?>

				<h2><?php esc_html_e( 'Appearance', 'floating-social-icons' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Position', 'floating-social-icons' ); ?></th>
						<td>
							<label>
								<input type="radio" name="<?php echo esc_attr( FSI_OPTION_NAME ); ?>[position]" value="left" <?php checked( $settings['position'], 'left' ); ?> />
								<?php esc_html_e( 'Left', 'floating-social-icons' ); ?>
							</label>
							&nbsp;&nbsp;
							<label>
								<input type="radio" name="<?php echo esc_attr( FSI_OPTION_NAME ); ?>[position]" value="right" <?php checked( $settings['position'], 'right' ); ?> />
								<?php esc_html_e( 'Right', 'floating-social-icons' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="fsi-top-offset"><?php esc_html_e( 'Distance from top (px)', 'floating-social-icons' ); ?></label></th>
						<td>
							<input type="number" min="0" step="1" id="fsi-top-offset" class="small-text"
								name="<?php echo esc_attr( FSI_OPTION_NAME ); ?>[top_offset]"
								value="<?php echo esc_attr( $settings['top_offset'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="fsi-icon-size"><?php esc_html_e( 'Icon size (px)', 'floating-social-icons' ); ?></label></th>
						<td>
							<input type="number" min="16" max="200" step="1" id="fsi-icon-size" class="small-text"
								name="<?php echo esc_attr( FSI_OPTION_NAME ); ?>[icon_size]"
								value="<?php echo esc_attr( $settings['icon_size'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="fsi-shape"><?php esc_html_e( 'Icon shape', 'floating-social-icons' ); ?></label></th>
						<td>
							<select id="fsi-shape" name="<?php echo esc_attr( FSI_OPTION_NAME ); ?>[shape]">
								<option value="circle" <?php selected( $settings['shape'], 'circle' ); ?>><?php esc_html_e( 'Circle', 'floating-social-icons' ); ?></option>
								<option value="square" <?php selected( $settings['shape'], 'square' ); ?>><?php esc_html_e( 'Square', 'floating-social-icons' ); ?></option>
								<option value="none" <?php selected( $settings['shape'], 'none' ); ?>><?php esc_html_e( 'None (icon only)', 'floating-social-icons' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="fsi-icon-spacing"><?php esc_html_e( 'Spacing between icons (px)', 'floating-social-icons' ); ?></label></th>
						<td>
							<input type="number" min="0" max="100" step="1" id="fsi-icon-spacing" class="small-text"
								name="<?php echo esc_attr( FSI_OPTION_NAME ); ?>[icon_spacing]"
								value="<?php echo esc_attr( $settings['icon_spacing'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="fsi-icon-color"><?php esc_html_e( 'Icon color', 'floating-social-icons' ); ?></label></th>
						<td>
							<input type="text" id="fsi-icon-color" class="fsi-color-picker"
								name="<?php echo esc_attr( FSI_OPTION_NAME ); ?>[icon_color]"
								value="<?php echo esc_attr( $settings['icon_color'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="fsi-bg-color"><?php esc_html_e( 'Background color', 'floating-social-icons' ); ?></label></th>
						<td>
							<input type="text" id="fsi-bg-color" class="fsi-color-picker"
								name="<?php echo esc_attr( FSI_OPTION_NAME ); ?>[bg_color]"
								value="<?php echo esc_attr( $settings['bg_color'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="fsi-hover-color"><?php esc_html_e( 'Hover color', 'floating-social-icons' ); ?></label></th>
						<td>
							<input type="text" id="fsi-hover-color" class="fsi-color-picker"
								name="<?php echo esc_attr( FSI_OPTION_NAME ); ?>[hover_color]"
								value="<?php echo esc_attr( $settings['hover_color'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Mobile visibility', 'floating-social-icons' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( FSI_OPTION_NAME ); ?>[hide_on_mobile]" value="1" <?php checked( $settings['hide_on_mobile'] ); ?> />
								<?php esc_html_e( 'Hide the floating bar on small screens', 'floating-social-icons' ); ?>
							</label>
							<p class="description">
								<label for="fsi-mobile-breakpoint"><?php esc_html_e( 'Breakpoint (px)', 'floating-social-icons' ); ?></label>
								<input type="number" min="200" step="1" id="fsi-mobile-breakpoint" class="small-text"
									name="<?php echo esc_attr( FSI_OPTION_NAME ); ?>[mobile_breakpoint]"
									value="<?php echo esc_attr( $settings['mobile_breakpoint'] ); ?>" />
							</p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Icons', 'floating-social-icons' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Add one row per social network. Icons are shown in the order listed below.', 'floating-social-icons' ); ?></p>

				<table class="widefat fixed striped fsi-icons-table" id="fsi-icons-table">
					<thead>
						<tr>
							<th style="width:30%"><?php esc_html_e( 'Network', 'floating-social-icons' ); ?></th>
							<th><?php esc_html_e( 'Link URL', 'floating-social-icons' ); ?></th>
							<th style="width:100px"></th>
						</tr>
					</thead>
					<tbody id="fsi-icons-table-body">
						<?php
						if ( ! empty( $settings['icons'] ) ) {
							foreach ( $settings['icons'] as $index => $icon ) {
								$this->render_icon_row( $index, $icon, $icons_library );
							}
						}
						?>
					</tbody>
				</table>
				<p><button type="button" class="button" id="fsi-add-icon-row"><?php esc_html_e( '+ Add Icon', 'floating-social-icons' ); ?></button></p>

				<script type="text/template" id="fsi-icon-row-template"><?php $this->render_icon_row( '__INDEX__', array( 'network' => '', 'url' => '' ), $icons_library ); ?></script>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Renders a single icon row. Also used (with index "__INDEX__") to build the JS row template.
	 *
	 * @param int|string $index         Row index or the "__INDEX__" placeholder.
	 * @param array      $icon          Icon data: network + url.
	 * @param array      $icons_library Bundled icon library.
	 */
	private function render_icon_row( $index, $icon, $icons_library ) {
		$network = isset( $icon['network'] ) ? $icon['network'] : '';
		$url     = isset( $icon['url'] ) ? $icon['url'] : '';
		?>
		<tr class="fsi-icon-row">
			<td>
				<select name="<?php echo esc_attr( FSI_OPTION_NAME ); ?>[icons][<?php echo esc_attr( $index ); ?>][network]">
					<option value=""><?php esc_html_e( '— Select —', 'floating-social-icons' ); ?></option>
					<?php foreach ( $icons_library as $key => $data ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $network, $key ); ?>><?php echo esc_html( $data['label'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
			<td>
				<input type="text" class="regular-text" placeholder="https://..."
					name="<?php echo esc_attr( FSI_OPTION_NAME ); ?>[icons][<?php echo esc_attr( $index ); ?>][url]"
					value="<?php echo esc_attr( $url ); ?>" />
			</td>
			<td>
				<button type="button" class="button fsi-remove-icon-row"><?php esc_html_e( 'Remove', 'floating-social-icons' ); ?></button>
			</td>
		</tr>
		<?php
	}
}
