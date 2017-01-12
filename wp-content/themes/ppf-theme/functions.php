<?php
/**
 * Put People First! PA functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Put_People_First!_PA
 */

if ( ! function_exists( 'put_people_first_pa_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function put_people_first_pa_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on Put People First! PA, use a find and replace
	 * to change 'put-people-first-pa' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'put-people-first-pa', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary', 'put-people-first-pa' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	// Set up the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'put_people_first_pa_custom_background_args', array(
		'default-color' => 'ffffff',
		'default-image' => '',
	) ) );
}
endif;
add_action( 'after_setup_theme', 'put_people_first_pa_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function put_people_first_pa_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'put_people_first_pa_content_width', 640 );
}
add_action( 'after_setup_theme', 'put_people_first_pa_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function put_people_first_pa_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'put-people-first-pa' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'put-people-first-pa' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'put_people_first_pa_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function put_people_first_pa_scripts() {
	wp_enqueue_style( 'put-people-first-pa-style', get_stylesheet_uri() );

	wp_enqueue_script( 'put-people-first-pa-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20151215', true );

	wp_enqueue_script( 'put-people-first-pa-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20151215', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'put_people_first_pa_scripts' );

function ppf__get_nav_item_classes( $item_is_home, $item_is_current ) {
	$item_classes = array( 'navigation-list__item' );
	if ( $item_is_current ) {
		$item_classes[] = 'navigation-list__item--current';
	}
	if ( $item_is_home ) {
		$item_classes[] = 'navigation-list__item--home';
	}

	return $item_classes;
}

function ppf__main_navigation_item( $nav_item ) {
	global $wp_query;

	$label = $nav_item->title;
	$url = $nav_item->url;
	if (
		empty( $label ) ||
		empty( $url ) ||
		$nav_item->menu_item_parent != '0'
	)
		return;

	$item_is_current = false;
	if ( $nav_item->object_id == $wp_query->queried_object_id ) {
		$item_is_current = true;
	}
	$item_is_home = false;

	if ( $item_is_current ) {
		$url = "#main";
	}
	$item_classes = ppf__get_nav_item_classes( $item_is_home, $item_is_current );
	$item_classes_string = implode( ' ', $item_classes );
	?>
	<li class="<?php echo esc_attr( $item_classes_string ); ?>">
		<a href="<?php echo esc_url( $url ); ?>" class="navigation-list__item-link">
			<?php echo esc_html( $label ); ?>
		</a>
	</li>
	<?php
}

function ppf__logo_nav_item() {
	$item_is_home = true;
	$item_is_current = false;
	$url = '/';
	$label = 'Home';
	if ( is_front_page() ) {
		$item_is_current = true;
		$url = "#main";
	}
	$item_classes = ppf__get_nav_item_classes( $item_is_home, $item_is_current );
	$item_classes_string = implode( ' ', $item_classes );
?>
	<li class="<?php echo esc_attr( $item_classes_string ); ?>">
		<a href="<?php echo esc_url( $url ); ?>" class="navigation-list__item-link">
			<?php
			if ( !empty( get_theme_mod( 'site-logo' ) ) ) : ?>
				<img src="<?php echo esc_url( get_theme_mod( 'site-logo' ) ); ?>" alt="<?php esc_attr( $label ); ?>" class="navigation-list__home-logo">
			<?php
			else :
				echo esc_html( $label );
			endif;
			?>
		</a>
	</li>
<?php
}

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';