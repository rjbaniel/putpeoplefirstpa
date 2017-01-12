<?php
	/**
	 *Functions class file
	**/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FSS_Functions' ) ) {
	class FSS_Functions {

		protected static $instance = null;

		private function __construct() {
			$this->load_library();

			$api_key = '';
			if ( get_option( 'fss_is_live' ) === "on" ) {
				$api_key = get_option( 'fss_live_sec_key' );
			} else {
				$api_key = get_option( 'fss_test_sec_key' );
			}

			$this->set_key( $api_key );
			if ( isset( $_POST['token'] ) ) {
				add_action( 'wp_ajax_nopriv_create_subscription', array( $this, 'create_subscription' ) );
				add_action( 'wp_ajax_create_subscription', array( $this, 'create_subscription' ) );
			}
		}

		function load_library() {
			if ( ! class_exists( 'Stripe\Stripe' ) ) {
				require_once( FSS_DIR_PATH . '/libraries/stripe-php/init.php' );
			}
		}

		function set_key( $key ) {
			\Stripe\Stripe::setApiKey( $key );
		}

		function create_subscription() {
			$amount = $_POST['amount'];
			$token = $_POST['token'];
			$email = $token['email'];
			$metadata = $_POST['metadata'];

			// Create the plan
			try {
				$plan = \Stripe\Plan::create( array(
					'amount' => $amount,
					'interval' => 'month',
					'name' => 'PPF-PA membership for ' . $email,
					'currency' => 'usd',
					'id' => 'membership-' . $email,
				) );
			} catch ( Exception $e ) {
				if ( $e->getMessage() == "Plan already exists." ) {
					echo "A membership already exists with that email address.";
				} else {
					 echo $e->getMessage();
				}
				die();
			}

			// Create the customer
			try {
				$customer = \Stripe\Customer::create( array( 
					'source' => $token['id'],
					'plan' => 'membership-' . $email,
					'email' => $email,
					'metadata' => $metadata,
				) );
				echo 'success';
			} catch ( Exception $e ) {
				// if we couldn't create the customer, then delete their plan
				$plan->delete();
				echo $e->getMessage();
				die();
			};
			die();
		}

		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}
}