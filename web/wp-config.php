<?php
use Platformsh\ConfigReader\Config;

// Set default scheme and hostname.
$site_scheme = 'http';
$site_host   = 'localhost';

// Update scheme and hostname for the requested page.
if ( isset( $_SERVER['HTTP_HOST'] ) ) {
	$site_host   = $_SERVER['HTTP_HOST'];
	$site_scheme = ! empty( $_SERVER['HTTPS'] ) ? 'https' : 'http';
}

if ( file_exists( __DIR__ . '/../vendor/autoload.php' ) ) {
	require __DIR__ . '/../vendor/autoload.php';
	// Create a new config object to ease reading the Platform.sh environment variables.
	// You can alternatively use getenv() yourself.
	$config = new Config();
	if ( $config->isValidPlatform() ) {
		if ( $config->hasRelationship( 'database' ) ) {
			// This is where we get the relationships of our application dynamically
			// from Platform.sh.

			// Avoid PHP notices on CLI requests.
			if ( php_sapi_name() === 'cli' ) {
				session_save_path( '/tmp' );
			}

			// Get the database credentials.
			$credentials = $config->credentials( 'database' );

			// We are using the first relationship called "database" found in your
			// relationships. Note that you can call this relationship as you wish
			// in your `.platform.app.yaml` file, but 'database' is a good name.
			define( 'DB_NAME', $credentials['path'] );
			define( 'DB_USER', $credentials['username'] );
			define( 'DB_PASSWORD', $credentials['password'] );
			define( 'DB_HOST', $credentials['host'] );
			define( 'DB_CHARSET', 'utf8' );
			define( 'DB_COLLATE', '' );
			define( 'DISABLE_WP_CRON', true );

			// Check whether a route is defined for this application in the Platform.sh
			// routes. Use it as the site hostname if so (it is not ideal to trust HTTP_HOST).
			if ( $config->routes() ) {

				$routes = $config->routes();

				foreach ( $routes as $url => $route ) {
					if ( 'upstream' === $route['type'] && $config->applicationName === $route['upstream'] ) {

						// Pick the first hostname, or the first HTTPS hostname if one exists.
						$host   = parse_url( $url, PHP_URL_HOST );
						$scheme = parse_url( $url, PHP_URL_SCHEME );
						if ( false !== $host && ( ! isset( $site_host ) || ( 'http' === $site_scheme && 'https' === $scheme ) ) ) {
							$site_host = $host;
							if ( $scheme ) {
								$site_scheme = $scheme;
							} else {
								$site_scheme = 'http';
							}
						}
					}
				}
			}

			// Debug mode should be disabled on Platform.sh. Set this constant to true
			// in a wp-config-local.php file to skip this setting on local development.
			if ( ! defined( 'WP_DEBUG' ) ) {
				define( 'WP_DEBUG', false );
				ini_set( 'log_errors', 'On' );
				ini_set( 'display_errors', 'Off' );
				ini_set( 'error_reporting', E_ALL );
				define( 'WP_DEBUG', false );
				define( 'WP_DEBUG_LOG', false );
				define( 'WP_DEBUG_DISPLAY', false );
			}


			// Use to check for if in 'master.
			if ( isset( $config->branch ) ) {
				$platform_env = $config->branch;
			} else {
				$platform_env = false;
			}

			// Set all of the necessary keys to unique values, based on the Platform.sh
			// entropy value.
			if ( $config->projectEntropy ) {
				$keys    = array(
					'AUTH_KEY',
					'SECURE_AUTH_KEY',
					'LOGGED_IN_KEY',
					'NONCE_KEY',
					'AUTH_SALT',
					'SECURE_AUTH_SALT',
					'LOGGED_IN_SALT',
					'NONCE_SALT',
				);
				$entropy = $config->projectEntropy;
				foreach ( $keys as $key ) {
					if ( ! defined( $key ) ) {
						define( $key, $entropy . $key );
					}
				}
			}
		}
	} else {
		if ( file_exists( dirname( __FILE__, 2 ) . '/web/wp-config-local.php' ) ) {
			include dirname( __FILE__, 2 ) . '/web/wp-config-local.php';
		}
	}
} else {
	if ( file_exists( dirname( __FILE__, 2 ) . '/web/wp-config-local.php' ) ) {
		include dirname( __FILE__, 2 ) . '/web/wp-config-local.php';
	}
}


// Do not put a slash "/" at the end.
// https://codex.wordpress.org/Editing_wp-config.php#WP_HOME.
// https://codex.wordpress.org/Editing_wp-config.php#WP_SITEURL.
define( 'WP_HOME', $site_scheme . '://' . $site_host );
define( 'WP_SITEURL', WP_HOME );
define( 'WP_CONTENT_DIR', dirname( __FILE__ ) . '/wp-content' );
define( 'WP_CONTENT_URL', WP_HOME . '/wp-content' );
define( 'WP_POST_REVISIONS', 5 );
define( 'AUTOSAVE_INTERVAL', 120 ); // Seconds.
define( 'WP_AUTO_UPDATE_CORE', false );
define( 'WPML_DOMAIN', 'hc' );

$table_prefix = 'wp_';

// Default PHP settings.
ini_set( 'session.gc_probability', 1 );
ini_set( 'session.gc_divisor', 100 );
ini_set( 'session.gc_maxlifetime', 200000 );
ini_set( 'session.cookie_lifetime', 2000000 );
ini_set( 'pcre.backtrack_limit', 200000 );
ini_set( 'pcre.recursion_limit', 200000 );


// IDs for listing pages
define( 'UPDATES_LISTING_PAGE', 435 );
define( 'LIBRARY_ITEMS_LISTING_PAGE', 800 );
define( 'EVENTS_LISTING_PAGE', 778 );

// Capability for SEO Framework settings.
define( 'THE_SEO_FRAMEWORK_SETTINGS_CAP', 'publish_pages' );


define( 'GMAPS_API_KEY', 'AIzaSyDXWHYq5ef-zeJGnVFJZ-jb9NezOzdkHW8' );
define( 'TIMBER_CACHE_TIME', false );

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
