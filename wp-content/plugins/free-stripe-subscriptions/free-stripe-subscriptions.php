<?php
/**
 * Plugin Name: Free Stripe Subscriptions
 * Description: A free plugin for Stripe integration with subscriptions
 * Author: Daniel Jones
 * Version: 0.1
 * Text Domain: free-stripe-subscriptions
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Defining some constants
$free_stripe_subscriptions_constants = array(
	'FSS_MAIN_FILE' => __FILE__,
	'FSS_DIR_PATH' => plugin_dir_path( __FILE__ ),
	'FSS_DIR_URL' => plugin_dir_url( __FILE__ ),
);
foreach( $free_stripe_subscriptions_constants as $key => $value ) {
	if ( ! defined( $key ) ) {
		define( $key, $value );
	}
}

// Load the plugin.
require_once FSS_DIR_PATH . 'classes/class-free-stripe-subscriptions.php';

global $base_class;
$base_class = Free_Stripe_Subscriptions::get_instance();

?>