<?php
require_once get_template_directory() . '/classes/class-honeycom3-theme.php';

add_theme_support( 'editor-styles' );
add_editor_style( 'assets/css/style.css' );

$honeycom3 = new Honeycom3();
