<?php
/**
 * The [coworkingview_spaces] and [coworkingview_rates] shortcodes.
 *
 * @package CoworkingView
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode rendering.
 */
class CoworkingView_Shortcodes {

	/**
	 * Register both shortcodes.
	 *
	 * @return void
	 */
	public static function register() {
		add_shortcode( 'coworkingview_spaces', array( __CLASS__, 'spaces' ) );
		add_shortcode( 'coworkingview_rates', array( __CLASS__, 'rates' ) );
	}

	/**
	 * Human label for a pricing option type.
	 *
	 * @param string $type Pricing option type.
	 * @return string
	 */
	public static function type_label( $type ) {
		$labels = array(
			'private_office'    => __( 'Private office', 'coworkingview' ),
			'exclusive_bereich' => __( 'Exclusive area', 'coworkingview' ),
			'fix_desk'          => __( 'Fixed desk', 'coworkingview' ),
			'flex_desk'         => __( 'Flex desk', 'coworkingview' ),
			'day_pass'          => __( 'Day pass', 'coworkingview' ),
			'meeting_room'      => __( 'Meeting room', 'coworkingview' ),
			'virtual_office'    => __( 'Virtual office', 'coworkingview' ),
			'event_space'       => __( 'Event space', 'coworkingview' ),
			'membership'        => __( 'Membership', 'coworkingview' ),
		);
		return isset( $labels[ $type ] ) ? $labels[ $type ] : $type;
	}

	/**
	 * A space's page on coworkingview.com. The path segment is localized.
	 *
	 * @param string $locale en, de or es.
	 * @param string $slug   Space slug.
	 * @return string
	 */
	public static function space_url( $locale, $slug ) {
		$segments = array(
			'en' => 'workspace',
			'de' => 'buero',
			'es' => 'oficina',
		);
		$segment  = isset( $segments[ $locale ] ) ? $segments[ $locale ] : 'workspace';
		return 'https://coworkingview.com/' . $locale . '/' . $segment . '/' . rawurlencode( $slug );
	}

	/**
	 * Format a money object from the API.
	 *
	 * @param mixed $money Array with amount and currency.
	 * @return string
	 */
	public static function money( $money ) {
		if ( ! is_array( $money ) || ! isset( $money['amount'] ) ) {
			return '';
		}
		$amount   = (float) $money['amount'];
		$decimals = floor( $amount ) === $amount ? 0 : 2;
		$currency = isset( $money['currency'] ) ? (string) $money['currency'] : '';
		return trim( $currency . ' ' . number_format_i18n( $amount, $decimals ) );
	}

	/**
	 * Billing period suffix.
	 *
	 * @param string $period Billing period.
	 * @return string
	 */
	public static function per( $period ) {
		$labels = array(
			'hour'  => __( 'per hour', 'coworkingview' ),
			'day'   => __( 'per day', 'coworkingview' ),
			'week'  => __( 'per week', 'coworkingview' ),
			'month' => __( 'per month', 'coworkingview' ),
			'year'  => __( 'per year', 'coworkingview' ),
		);
		return isset( $labels[ $period ] ) ? $labels[ $period ] : '';
	}

	/**
	 * Shared attribute validation. Returns WP_Error on a bad value so the editor
	 * sees why nothing rendered instead of an empty block.
	 *
	 * @param array $atts Raw attributes.
	 * @return array|WP_Error
	 */
	private static function clean( array $atts ) {
		$city = strtolower( trim( (string) $atts['city'] ) );
		if ( ! CoworkingView_API::is_slug( $city ) ) {
			return new WP_Error( 'coworkingview_city', __( 'Set city="…" to a city slug, e.g. city="berlin".', 'coworkingview' ) );
		}
		$locale = strtolower( trim( (string) $atts['locale'] ) );
		if ( ! in_array( $locale, CoworkingView_API::LOCALES, true ) ) {
			$locale = CoworkingView_Settings::locale();
		}
		$atts['city']   = $city;
		$atts['locale'] = $locale;
		return $atts;
	}

	/**
	 * A visible error for editors only; visitors see nothing.
	 *
	 * @param WP_Error $error The error.
	 * @return string
	 */
	private static function error( WP_Error $error ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return '';
		}
		return '<p class="coworkingview-error">' . esc_html( 'CoworkingView: ' . $error->get_error_message() ) . '</p>';
	}

	/**
	 * Optional "Data: CoworkingView" credit, off unless the site owner enables it.
	 *
	 * @return string
	 */
	private static function credit() {
		if ( ! CoworkingView_Settings::show_credit() ) {
			return '';
		}
		return '<p class="coworkingview-credit">' . sprintf(
			/* translators: %s: link to CoworkingView. */
			esc_html__( 'Data: %s', 'coworkingview' ),
			'<a href="' . esc_url( 'https://coworkingview.com' ) . '">CoworkingView</a>'
		) . '</p>';
	}

	/**
	 * [coworkingview_spaces city="berlin" type="private_office" limit="5"]
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string HTML.
	 */
	public static function spaces( $atts ) {
		$atts = shortcode_atts(
			array(
				'city'     => '',
				'type'     => '',
				'limit'    => 5,
				'locale'   => '',
				'zone'     => '',
				'operator' => '',
			),
			is_array( $atts ) ? $atts : array(),
			'coworkingview_spaces'
		);
		$atts = self::clean( $atts );
		if ( is_wp_error( $atts ) ) {
			return self::error( $atts );
		}
		$atts['limit'] = max( 1, min( 50, absint( $atts['limit'] ) ) );
		$atts['type']  = in_array( $atts['type'], CoworkingView_API::PRICING_OPTION_TYPES, true ) ? $atts['type'] : '';
		foreach ( array( 'zone', 'operator' ) as $key ) {
			$atts[ $key ] = CoworkingView_API::is_slug( $atts[ $key ] ) ? $atts[ $key ] : '';
		}

		$spaces = CoworkingView_API::spaces( $atts );
		if ( is_wp_error( $spaces ) ) {
			return self::error( $spaces );
		}
		if ( empty( $spaces ) ) {
			return '<p class="coworkingview-empty">' . esc_html__( 'No spaces match right now.', 'coworkingview' ) . '</p>';
		}

		wp_enqueue_style( 'coworkingview' );
		$html = '<ul class="coworkingview-spaces">';
		foreach ( $spaces as $space ) {
			if ( empty( $space['slug'] ) || empty( $space['title'] ) ) {
				continue;
			}
			$url   = self::space_url( $atts['locale'], $space['slug'] );
			$where = array();
			if ( ! empty( $space['zone']['name'] ) ) {
				$where[] = $space['zone']['name'];
			}
			if ( ! empty( $space['city']['name'] ) ) {
				$where[] = trim( $space['city']['name'] );
			}
			$price = self::money( isset( $space['fromPrice'] ) ? $space['fromPrice'] : null );

			$html .= '<li class="coworkingview-space">';
			$html .= '<a class="coworkingview-space__title" href="' . esc_url( $url ) . '">' . esc_html( $space['title'] ) . '</a>';
			if ( $where ) {
				$html .= ' <span class="coworkingview-space__where">' . esc_html( implode( ', ', $where ) ) . '</span>';
			}
			if ( '' !== $price ) {
				$per   = self::per( isset( $space['fromBillingPeriod'] ) ? $space['fromBillingPeriod'] : '' );
				$html .= ' <span class="coworkingview-space__price">' . esc_html(
					/* translators: 1: price, 2: billing period such as "per month". */
					trim( sprintf( __( 'from %1$s %2$s', 'coworkingview' ), $price, $per ) )
				) . '</span>';
			}
			$html .= '</li>';
		}
		$html .= '</ul>';
		return $html . self::credit();
	}

	/**
	 * [coworkingview_rates city="madrid" currency="EUR"]
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string HTML.
	 */
	public static function rates( $atts ) {
		$atts = shortcode_atts(
			array(
				'city'     => '',
				'currency' => '',
				'locale'   => '',
			),
			is_array( $atts ) ? $atts : array(),
			'coworkingview_rates'
		);
		$atts = self::clean( $atts );
		if ( is_wp_error( $atts ) ) {
			return self::error( $atts );
		}
		$currency         = strtoupper( trim( (string) $atts['currency'] ) );
		$atts['currency'] = preg_match( '/^[A-Z]{3}$/', $currency ) ? $currency : '';

		$city = CoworkingView_API::rates( $atts );
		if ( is_wp_error( $city ) ) {
			return self::error( $city );
		}
		$figures = isset( $city['figures'] ) && is_array( $city['figures'] ) ? $city['figures'] : array();
		if ( empty( $figures ) ) {
			return '<p class="coworkingview-empty">' . esc_html__( 'Not enough published prices in this city yet.', 'coworkingview' ) . '</p>';
		}

		wp_enqueue_style( 'coworkingview' );
		$html  = '<table class="coworkingview-rates"><thead><tr>';
		$html .= '<th scope="col">' . esc_html__( 'Workspace', 'coworkingview' ) . '</th>';
		$html .= '<th scope="col">' . esc_html__( 'Median per month', 'coworkingview' ) . '</th>';
		$html .= '<th scope="col">' . esc_html__( 'Spaces', 'coworkingview' ) . '</th>';
		$html .= '</tr></thead><tbody>';
		foreach ( $figures as $figure ) {
			if ( empty( $figure['optionType'] ) || empty( $figure['median'] ) ) {
				continue;
			}
			$html .= '<tr>';
			$html .= '<td>' . esc_html( self::type_label( $figure['optionType'] ) ) . '</td>';
			$html .= '<td>' . esc_html( self::money( $figure['median'] ) ) . '</td>';
			$html .= '<td>' . esc_html( number_format_i18n( isset( $figure['propertyCount'] ) ? (int) $figure['propertyCount'] : 0 ) ) . '</td>';
			$html .= '</tr>';
		}
		$html .= '</tbody></table>';
		return $html . self::credit();
	}
}
