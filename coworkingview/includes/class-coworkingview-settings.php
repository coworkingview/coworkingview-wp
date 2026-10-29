<?php
/**
 * Settings → CoworkingView.
 *
 * @package CoworkingView
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin settings: default language, cache lifetime and the optional credit.
 */
class CoworkingView_Settings {

	const OPTION = 'coworkingview_settings';

	/**
	 * Saved settings merged over the defaults.
	 *
	 * @return array
	 */
	public static function all() {
		$saved = get_option( self::OPTION, array() );
		return wp_parse_args(
			is_array( $saved ) ? $saved : array(),
			array(
				'locale'      => 'en',
				'cache_hours' => 12,
				'show_credit' => 0,
			)
		);
	}

	/**
	 * Default language.
	 *
	 * @return string
	 */
	public static function locale() {
		$locale = self::all()['locale'];
		return in_array( $locale, CoworkingView_API::LOCALES, true ) ? $locale : 'en';
	}

	/**
	 * Cache lifetime, never under one hour.
	 *
	 * @return int
	 */
	public static function cache_seconds() {
		return max( 1, (int) self::all()['cache_hours'] ) * HOUR_IN_SECONDS;
	}

	/**
	 * Whether the site owner opted in to the "Data: CoworkingView" credit.
	 *
	 * @return bool
	 */
	public static function show_credit() {
		return (bool) self::all()['show_credit'];
	}

	/**
	 * Hook the settings page.
	 *
	 * @return void
	 */
	public static function register() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'fields' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( COWORKINGVIEW_DIR . 'coworkingview.php' ), array( __CLASS__, 'action_link' ) );
	}

	/**
	 * "Settings" link on the Plugins screen.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public static function action_link( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=coworkingview' ) ) . '">' . esc_html__( 'Settings', 'coworkingview' ) . '</a>' );
		return $links;
	}

	/**
	 * Add the page under Settings.
	 *
	 * @return void
	 */
	public static function menu() {
		add_options_page( 'CoworkingView', 'CoworkingView', 'manage_options', 'coworkingview', array( __CLASS__, 'page' ) );
	}

	/**
	 * Register the option and its sanitizer.
	 *
	 * @return void
	 */
	public static function fields() {
		register_setting(
			'coworkingview',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Sanitize the submitted settings and clear the cache so a change shows at once.
	 *
	 * @param mixed $input Submitted values.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$input = is_array( $input ) ? $input : array();
		$clean = array(
			'locale'      => isset( $input['locale'] ) && in_array( $input['locale'], CoworkingView_API::LOCALES, true ) ? $input['locale'] : 'en',
			'cache_hours' => isset( $input['cache_hours'] ) ? max( 1, min( 168, absint( $input['cache_hours'] ) ) ) : 12,
			'show_credit' => empty( $input['show_credit'] ) ? 0 : 1,
		);
		CoworkingView_API::flush_cache();
		return $clean;
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s    = self::all();
		$name = self::OPTION;
		?>
		<div class="wrap">
			<h1>CoworkingView</h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'coworkingview' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="coworkingview-locale"><?php esc_html_e( 'Language', 'coworkingview' ); ?></label></th>
						<td>
							<select id="coworkingview-locale" name="<?php echo esc_attr( $name ); ?>[locale]">
								<option value="en" <?php selected( $s['locale'], 'en' ); ?>>English</option>
								<option value="de" <?php selected( $s['locale'], 'de' ); ?>>Deutsch</option>
								<option value="es" <?php selected( $s['locale'], 'es' ); ?>>Español</option>
							</select>
							<p class="description"><?php esc_html_e( 'Used when a shortcode has no locale="…" attribute.', 'coworkingview' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="coworkingview-cache"><?php esc_html_e( 'Cache (hours)', 'coworkingview' ); ?></label></th>
						<td>
							<input id="coworkingview-cache" type="number" min="1" max="168" name="<?php echo esc_attr( $name ); ?>[cache_hours]" value="<?php echo esc_attr( (string) $s['cache_hours'] ); ?>" class="small-text">
							<p class="description"><?php esc_html_e( 'How long results are kept before asking the API again. Saving this page clears the cache.', 'coworkingview' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Credit', 'coworkingview' ); ?></th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[show_credit]" value="1" <?php checked( $s['show_credit'], 1 ); ?>> <?php esc_html_e( 'Show "Data: CoworkingView" under lists and tables', 'coworkingview' ); ?></label>
							<p class="description"><?php esc_html_e( 'Market rates are licensed CC BY 4.0, which asks for a credit and a link to the source when you republish them.', 'coworkingview' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<h2><?php esc_html_e( 'Shortcodes', 'coworkingview' ); ?></h2>
			<p><code>[coworkingview_spaces city="berlin" type="private_office" limit="5"]</code></p>
			<p><code>[coworkingview_rates city="madrid"]</code></p>
		</div>
		<?php
	}
}
