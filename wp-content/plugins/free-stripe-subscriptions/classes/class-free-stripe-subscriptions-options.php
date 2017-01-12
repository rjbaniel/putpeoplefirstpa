<?php
	
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FSS_Settings' ) ) {
	class FSS_Settings {
		protected static $instance = null;

		private function __construct() {
			if ( is_admin() ) {
				add_action( 'admin_init', array( $this, 'fss_register_settings') );
				add_action( 'admin_menu', array( $this, 'fss_settings_menu' ) );
			}
		}

		function fss_register_settings() {
			// Add Settings Sections
			add_settings_section( 'fss-api-settings', 'API Settings', array( $this, 'fss_api_settings' ), 'fss-settings' );
			add_settings_section( 'fss-display-settings', 'Display Settings', array( $this, 'fss_display_settings' ), 'fss-settings' );
			
			// Add API Settings Fields
			add_settings_field( 'fss_live_pub_key', "Live Publishable Key", array( $this, 'fss_text_setting' ), 'fss-settings', 'fss-api-settings', array( 'id' => 'fss_live_pub_key' , 'title' => "Live Publishable Key" ) );
			add_settings_field( 'fss_live_sec_key', "Live Secret Key", array( $this, 'fss_text_setting' ), 'fss-settings', 'fss-api-settings', array( 'id' => 'fss_live_sec_key' , 'title' => "Live Secret Key" ) );
			add_settings_field( 'fss_test_pub_key', "Test Publishable Key", array( $this, 'fss_text_setting' ), 'fss-settings', 'fss-api-settings', array( 'id' => 'fss_test_pub_key' , 'title' => "Test Publishable Key" ) );
			add_settings_field( 'fss_test_sec_key', "Test Secret Key", array( $this, 'fss_text_setting' ), 'fss-settings', 'fss-api-settings', array( 'id' => 'fss_test_sec_key' , 'title' => "Test Secret Key" ) );
			add_settings_field( 'fss_is_live', "Use Live Mode?", array( $this, 'fss_is_live' ), 'fss-settings', 'fss-api-settings', array( 'id' => 'fss_is_live' , 'title' => "Use Live Mode?" ) );

			// Add Display Settings Fields
			add_settings_field( 'fss_form_title', "Form Title", array( $this, 'fss_text_setting' ), 'fss-settings', 'fss-display-settings', array( 'id' => 'fss_form_title' , 'title' => "Form Title" ) );
			add_settings_field( 'fss_form_desc', "Form Description", array( $this, 'fss_text_setting' ), 'fss-settings', 'fss-display-settings', array( 'id' => 'fss_form_desc' , 'title' => "Form Description" ) );			
			add_settings_field( 'fss_form_org', "Organization Name", array( $this, 'fss_text_setting' ), 'fss-settings', 'fss-display-settings', array( 'id' => 'fss_form_org' , 'title' => "Organization Name" ) );
			add_settings_field( 'fss_form_support_email', "Support Email", array( $this, 'fss_text_setting' ), 'fss-settings', 'fss-display-settings', array( 'id' => 'fss_form_support_email' , 'title' => "Support Email" ) );

			// Register API Settings Fields as part of 'fss-settings' option group
			register_setting( 'fss-settings', 'fss_live_pub_key' );
			register_setting( 'fss-settings', 'fss_live_sec_key' );
			register_setting( 'fss-settings', 'fss_test_pub_key' );
			register_setting( 'fss-settings', 'fss_test_sec_key' );
			register_setting( 'fss-settings', 'fss_is_live' );

			// Register Display Settings Fields as part of 'fss-settings' option group
			register_setting( 'fss-settings', 'fss_form_title' );
			register_setting( 'fss-settings', 'fss_form_desc' );
			register_setting( 'fss-settings', 'fss_form_org' );
			register_setting( 'fss-settings', 'fss_form_support_email' );
		}

		function fss_api_settings() {
			echo "These are settings related to the Stripe API";
		}

		function fss_display_settings() {
			echo "These are settings related to the display of the sign-up form";
		}

		function fss_text_setting( $args ) {
			echo '<input id="' . $args['id'] . '" name="' . $args['id'] . '" type="text" value="'. ( get_option( $args['id'] ) ? esc_attr( get_option( $args['id'] ) ) : '' ) . '"></input>';
		}

		function fss_is_live( $args ) {
			echo '<input id="' . $args['id'] . '" name="' . $args['id'] . '" type="checkbox"' . ( get_option( $args['id'] ) ? " checked" : '' ) . '></input>'; 
		}

		function fss_settings_menu() {
			add_options_page( 'Free Stripe Subscriptions Settings', 'Free Stripe Subscriptions', 'manage_options', 'fss-settings', array( $this, 'fss_settings_page' ) );
		}

		function fss_settings_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'You do not have sufficiant permissions to access this page' );
			}

			echo '<div class="fss-settings-page_wrap wrap">';
			echo '<h2>Free Stripe Subscriptions Options</h2>';
			echo '<form method="POST" action="options.php">';
			settings_fields( 'fss-settings' );
			do_settings_sections( 'fss-settings' );
			submit_button();
			echo '</form>';
			echo '</div>';
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

?>