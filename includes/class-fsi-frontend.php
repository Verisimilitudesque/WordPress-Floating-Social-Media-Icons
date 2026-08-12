<?php
/**
 * Front-end rendering for Floating Social Icons.
 *
 * @package Floating_Social_Icons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FSI_Frontend {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_footer', array( $this, 'render_bar' ) );
	}

	/**
	 * Enqueues the front-end stylesheet and injects the configured values as CSS custom properties.
	 */
	public function enqueue_assets() {
		$settings = fsi_get_settings();

		if ( empty( $settings['icons'] ) ) {
			return;
		}

		wp_enqueue_style( 'fsi-frontend', FSI_PLUGIN_URL . 'assets/css/frontend.css', array(), FSI_VERSION );

		$icon_color  = sanitize_hex_color( $settings['icon_color'] );
		$bg_color    = sanitize_hex_color( $settings['bg_color'] );
		$hover_color = sanitize_hex_color( $settings['hover_color'] );

		$inline_css = sprintf(
			':root{--fsi-top-offset:%1$dpx;--fsi-icon-size:%2$dpx;--fsi-icon-color:%3$s;--fsi-bg-color:%4$s;--fsi-hover-color:%5$s;--fsi-icon-spacing:%6$dpx;}',
			absint( $settings['top_offset'] ),
			absint( $settings['icon_size'] ),
			$icon_color ? $icon_color : '#ffffff',
			$bg_color ? $bg_color : '#333333',
			$hover_color ? $hover_color : '#0073aa',
			absint( $settings['icon_spacing'] )
		);

		if ( ! empty( $settings['hide_on_mobile'] ) ) {
			$inline_css .= sprintf(
				'@media (max-width:%1$dpx){#fsi-floating-bar.fsi-hide-on-mobile{display:none;}}',
				absint( $settings['mobile_breakpoint'] )
			);
		}

		wp_add_inline_style( 'fsi-frontend', $inline_css );
	}

	/**
	 * Outputs the floating icon bar in the site footer.
	 */
	public function render_bar() {
		$settings = fsi_get_settings();

		if ( empty( $settings['icons'] ) ) {
			return;
		}

		$classes = array(
			'fsi-floating-bar',
			'left' === $settings['position'] ? 'fsi-position-left' : 'fsi-position-right',
			'fsi-shape-' . $settings['shape'],
		);

		if ( ! empty( $settings['hide_on_mobile'] ) ) {
			$classes[] = 'fsi-hide-on-mobile';
		}
		?>
		<div id="fsi-floating-bar" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<?php foreach ( $settings['icons'] as $icon ) : ?>
				<?php
				$network = isset( $icon['network'] ) ? $icon['network'] : '';
				$url     = isset( $icon['url'] ) ? $icon['url'] : '';
				$svg     = FSI_Icons::get_icon_svg( $network );

				if ( empty( $svg ) || empty( $url ) ) {
					continue;
				}

				$label = FSI_Icons::get_label( $network );
				?>
				<a href="<?php echo esc_url( $url ); ?>"
					class="fsi-icon-link fsi-icon-<?php echo esc_attr( $network ); ?>"
					target="_blank"
					rel="noopener noreferrer"
					aria-label="<?php echo esc_attr( $label ); ?>"
				><?php echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static trusted markup from FSI_Icons, path data already esc_attr()'d there. ?></a>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
