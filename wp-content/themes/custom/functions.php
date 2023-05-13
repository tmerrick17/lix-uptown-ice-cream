<?php

function add_scripts_styles()
{
  // Enqueue styles and scripts
  wp_enqueue_style('main-style', get_template_directory_uri() . '/public/css/main.min.css');
  wp_enqueue_script('main', get_template_directory_uri() . '/public/js/main.js', array('jquery'), false, true);
  // wp_register_script('bootstrap-script', get_template_directory_uri() . '/public/js/bootstrap.js', array(), false, true);
  wp_enqueue_script('fascript', 'https://kit.fontawesome.com/ac8a13df38.js');
}

add_action('wp_enqueue_scripts', 'add_scripts_styles');

// Theme options
add_theme_support('menus');

// Menus
register_nav_menus(
  array(
    'top-menu' => 'Top Menu',
    'mobile-menu' => 'Mobile Menu'
  )
);



