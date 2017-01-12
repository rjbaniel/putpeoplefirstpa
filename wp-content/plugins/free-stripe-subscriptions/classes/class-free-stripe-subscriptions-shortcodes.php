<?php
	/**
	 * Class file for shortcodes
	**/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FSS_Shortcodes' ) ) {
	class FSS_Shortcodes {

		protected static $instance = null;

		private function __construct() {
			add_shortcode( 'stripe-subscription', array( $this, 'fss_form' ) );
		}

		function fss_form( $attr ) {
			$attr = shortcode_atts( array(), $attr, 'free-stripe-subscriptions' );

			$html = '<script src="https://checkout.stripe.com/checkout.js"></script>';
			// first name
			$html .= '<div class="fss-field">';
			$html .= '<label for="first_name" class="fss-field_label">First name:</label>';
			$html .= '<input name="first_name" type="text" id="first_name"></input>';
			$html .= '</div> <!-- fss-field -->';

			// last name
			$html .= '<div class="fss-field">';
			$html .= '<label for="last_name" class="fss-field_label">Last name:</label>';
			$html .= '<input name="last_name" type="text" id="last_name"></input>';
			$html .= '</div> <!-- fss-field -->';
			
			// zip code
			$html .= '<div class="fss-field">';
			$html .= '<label for="zip_code" class="fss-field_label">ZIP code:</label>';
			$html .= '<input name="zip_code" type="text" id="zip_code"></input>';
			$html .= '</div> <!-- fss-field -->';

			// amount 
			$html .= '<div class="fss-field">';
			$html .= '<label for="amount" class="fss-field_label">Monthly contribution</label>';
			$html .= '<input name="amount" type="text" id="amount"></input>';
			$html .= '</div> <!-- fss-field -->';

			$html .= '<p class="fss-required-notice">* indicates a required field</p>';

			$html .= '<button id="fss-button">Become a member</button>';
			return $html;
		}

		static function get_instance() {
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}
}