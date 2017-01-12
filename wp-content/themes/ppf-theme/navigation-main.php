<?php
	$menu_items = wp_get_nav_menu_items( 'main-nav' );
	$queried_object_id = $wp_query->queried_object_id;
?>
	<ul class="navigation-list">
<?php
	if ( $menu_items ) {
		ppf__logo_nav_item();
		foreach( $menu_items as $item ) {
			ppf__main_navigation_item( $item );
		}
	}
?>
	</ul>