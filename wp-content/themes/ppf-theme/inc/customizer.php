<?php
/**
 * Put People First! PA Theme Customizer.
 *
 * @package Put_People_First!_PA
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function put_people_first_pa_customize_register( $wp_customize ) {
	// We don't need these sections for this site
	$wp_customize->remove_section( 'colors' );
	$wp_customize->remove_section( 'header_image' );
	$wp_customize->remove_section( 'background_image' );

	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	$wp_customize->add_setting( 'site-logo', array(
				'type' => 'theme_mod',
			) );
	$wp_customize->add_control( new WP_Customize_Image_Control(
		$wp_customize,
		'site-logo-control',
		array(
			'label' => esc_html__( 'Upload an image for the site\'s logo', 'ppf-theme' ),
			'section' => 'logo-section',
			'settings' => 'site-logo'
		)
	) );
	$wp_customize->add_section( 'logo-section', array(
		'title' => esc_html__( 'Logo', 'logic-department' ),
	) );
}
add_action( 'customize_register', 'put_people_first_pa_customize_register' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function put_people_first_pa_customize_preview_js() {
	wp_enqueue_script( 'put_people_first_pa_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20151215', true );
}
add_action( 'customize_preview_init', 'put_people_first_pa_customize_preview_js' );
