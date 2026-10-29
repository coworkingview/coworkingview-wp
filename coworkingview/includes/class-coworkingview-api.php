<?php
/**
 * Read-only client for the CoworkingView /v1 API, with transient caching so a
 * busy page never turns into one API call per visitor.
 *
 * @package CoworkingView
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoworkingView API client.
 */
class CoworkingView_API {

	const BASE_URL = 'https://api.coworkingview.com';

	const LOCALES = array( 'en', 'de', 'es' );

	const PRICING_OPTION_TYPES = array(
		'private_office',
		'exclusive_bereich',
		'fix_desk',
		'flex_desk',
		'day_pass',
		'meeting_room',
		'virtual_office',
		'event_space',
		'membership',
	);

	/**
	 * Whether a string is a slug the API accepts.
	 *
	 * @param string $value Candidate slug.
	 * @return bool
	 */
	public static function is_slug( $value ) {
		return is_string( $value ) && 1 === preg_match( '/^[a-z0-9-]{1,200}$/', $value );
	}

	/**
	 * Spaces in a city.
	 *
	 * @param array $args city, type, limit (1-50), locale.
	 * @return array|WP_Error List of spaces.
	 */
	public static function spaces( array $args ) {
		$query = array(
			'locale'   => $args['locale'],
			'city'     => $args['city'],
			'pageSize' => $args['limit'],
		);
		if ( ! empty( $args['type'] ) ) {
			$query['pricingOptionType'] = $args['type'];
		}
		if ( ! empty( $args['zone'] ) ) {
			$query['zone'] = $args['zone'];
		}
		if ( ! empty( $args['operator'] ) ) {
			$query['operator'] = $args['operator'];
		}
		$body = self::get( '/v1/properties', $query );
		if ( is_wp_error( $body ) ) {
			return $body;
		}
		return isset( $body['data'] ) && is_array( $body['data'] ) ? $body['data'] : array();
	}

	/**
	 * Market rates for one city.
	 *
	 * @param array $args city, currency, locale.
	 * @return array|WP_Error The city's figures, or an empty array when unpublished.
	 */
	public static function rates( array $args ) {
		$query = array(
			'locale' => $args['locale'],
			'city'   => $args['city'],
		);
		if ( ! empty( $args['currency'] ) ) {
			$query['currency'] = $args['currency'];
		}
		$body = self::get( '/v1/market/rates', $query );
		if ( is_wp_error( $body ) ) {
			return $body;
		}
		$cities = isset( $body['data']['cities'] ) && is_array( $body['data']['cities'] ) ? $body['data']['cities'] : array();
		return empty( $cities ) ? array() : $cities[0];
	}

	/**
	 * GET a path and decode the JSON envelope, cached in a transient.
	 *
	 * @param string $path  API path.
	 * @param array  $query Query parameters.
	 * @return array|WP_Error Decoded body.
	 */
	public static function get( $path, array $query ) {
		$url = add_query_arg( array_map( 'rawurlencode', array_map( 'strval', $query ) ), self::BASE_URL . $path );
		$key = 'coworkingview_' . md5( $url );

		$cached = get_transient( $key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 10,
				'headers'    => array( 'Accept' => 'application/json' ),
				'user-agent' => 'coworkingview-wordpress/' . COWORKINGVIEW_VERSION . '; ' . home_url( '/' ),
			)
		);
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( $code < 200 || $code > 299 || ! is_array( $body ) ) {
			$detail = is_array( $body ) && isset( $body['detail'] ) ? $body['detail'] : '';
			return new WP_Error( 'coworkingview_http_' . $code, trim( 'CoworkingView API ' . $code . ' ' . $detail ) );
		}

		set_transient( $key, $body, CoworkingView_Settings::cache_seconds() );
		return $body;
	}

	/**
	 * Delete every cached response.
	 *
	 * @return void
	 */
	public static function flush_cache() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Transients have no API to delete by prefix.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				$wpdb->esc_like( '_transient_coworkingview_' ) . '%',
				$wpdb->esc_like( '_transient_timeout_coworkingview_' ) . '%'
			)
		);
	}
}
