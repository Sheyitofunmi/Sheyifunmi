<?php

if ( function_exists( 'get_permalink' ) ) {
	die( 'WordPress template not found' );
}


$cms    = 'WordPress'; // One of 'drupal' or 'WordPress'.
$ignore = array( '/', '/templates' );
require_once __DIR__ . '/honeycom3-fe/vendor/autoload.php';

// Set directories to use.
$overrides = __DIR__ . '/overrides/';
$honeycomb = __DIR__ . '/honeycom3-fe/templates';
$assets    = '/assets';

// Declare directories for templates and route matching.
$loader = new \Twig\Loader\FilesystemLoader( array( $overrides, $honeycomb ) );

// Declare @hc namespace for loading partials and keeping things consistent.
$loader->addPath( $overrides, 'hc' );
$loader->addPath( $honeycomb, 'hc' );

// Declare @default namespace to allow overriding without looping.
$loader->addPath( $honeycomb, 'default' );

$function = new Twig_SimpleFunction(
	'function',
	function ( $param1, $param2 = null ) {
		return call_user_func( $param1, $param2 );
	}
);

$twig = new \Twig\Environment(
	$loader,
	array(
		'debug' => true,
	)
);

$twig->addFunction( $function );

// Declare global var for assets_dir.
$twig->addGlobal( 'assets_dir', $assets );

$routes = array(
	'/' => 'home',
	'/home-alt' => 'home-alt',
	'/404' => '404',
	'/page-builder' => 'page-builder',
	'/page-builder-sec' => 'page-builder-sec',
	'/page-builder-impact' => 'page-builder-impact',
	'/page-builder-inline' => 'page-builder-inline',
	'/page-builder-inline-sec' => 'page-builder-inline-sec',
	'/page-builder-inline-noside' => 'page-builder-inline-noside',
	'/page-builder-adv' => 'page-builder-adv',
	'/templates' => 'templates',
	'/forms' => 'forms',
	'/news-feed' => 'news/listing-feed',
	'/news-listing' => 'news/listing',
	'/news-single' => 'news/single',
	'/news-single' => 'news/single',
	'/author-profile' => 'author/single',
	'/events-listing' => 'events/listing',
	'/events-single' => 'events/single',
	'/resource-listing' => 'resources/listing',
	'/resource-single' => 'resources/single',
	'/search-listing' => 'search',
	'/donate' => 'donate',
	'/standards' => 'standards',
);

$path = $_SERVER['REQUEST_URI'];
$view = $routes[ $path ];

if ( is_array( $view ) ) {
	list($view, $data) = $view;
} else {
	$data = array();
}

$data['path'] = $path;


echo $twig->render( $view . '.twig', $data );
