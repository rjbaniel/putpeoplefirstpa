<?php

/**
 * Main class
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Free_Stripe_Subscriptions' ) ) {
	class Free_Stripe_Subscriptions {

		protected static $instance = null;

		private function __construct() {
			$this->includes();
			add_action( 'init', array( $this, 'init' ), 1 );
		}

		public function includes() {;
			include_once( FSS_DIR_PATH . 'classes/class-free-stripe-subscriptions-functions.php' );
			include_once( FSS_DIR_PATH . 'classes/class-free-stripe-subscriptions-shortcodes.php' );
			include_once( FSS_DIR_PATH . 'classes/class-free-stripe-subscriptions-scripts.php' );
			include_once( FSS_DIR_PATH . 'classes/class-free-stripe-subscriptions-options.php' );
		}

		public static function get_instance() {
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		public function init() {
			FSS_Functions::get_instance();
			FSS_Shortcodes::get_instance();
			FSS_Scripts::get_instance();
			FSS_Settings::get_instance();
		}

	}
}