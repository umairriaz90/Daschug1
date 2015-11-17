<?php
  add_action( 'init', 'register_my_menu' );
function register_my_menu() {
    register_nav_menu( 'secondary-menu', __( 'Secondary Menu' ) );
}


/**
 * Setup My Child Theme's textdomain.
 *
 * Declare textdomain for this child theme.
 * Translations can be filed in the /languages/ directory.
 * Needed for correct translations
 */
function my_child_theme_setup() {
    load_child_theme_textdomain( 'daschug-child-tten', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'my_child_theme_setup' );


?>