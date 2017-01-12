<?php
	/**
	 * Scripts and styles to include
	**/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FSS_Scripts' ) ) {
	class FSS_Scripts {
		protected static $instance = null;

		function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		}

		static function get_instance() {
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function enqueue_assets() {
			wp_enqueue_script( 'fss-stripe-js', FSS_DIR_URL . 'assets/js/fss-stripe-js.js', array( 'jquery' ) );

			$api_key = '';
			if ( get_option( 'fss_is_live' ) === 'on' ) {
				$api_key = get_option( 'fss_live_pub_key' );
			} else {
				$api_key = get_option( 'fss_test_pub_key' );
			}

			wp_localize_script( 'fss-stripe-js', 'ajax_object',
				array( 
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'api_key' => $api_key,
					'title' => get_option( 'fss_form_title' ),
					'desc' => get_option( 'fss_form_desc' ),
					'org' => get_option( 'fss_form_org' ),
					'support_email' => get_option( 'fss_form_support_email' ),
				)
			);
			wp_enqueue_script( 'mask-money', FSS_DIR_URL . 'assets/js/jquery.maskMoney.js', array( 'jquery') );
			wp_enqueue_script( 'fss-inputs-js', FSS_DIR_URL . 'assets/js/fss-inputs-js.js', array( 'mask-money' ) );
			wp_enqueue_style( 'fss-css', FSS_DIR_URL . 'assets/css/fss-css.css' );
		}
	}
}