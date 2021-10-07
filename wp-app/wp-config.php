<?php

// Autoload the  Dotenv file
// require_once(__DIR__ . '/vendor/autoload.php');
// (new \Dotenv\Dotenv(__DIR__.'/'))->load();
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', getenv('WORDPRESS_DB_NAME'));

/** MySQL database username */
define('DB_USER', getenv('WORDPRESS_DB_USER'));

/** MySQL database password */
define('DB_PASSWORD', getenv('WORDPRESS_DB_PASSWORD'));
	
/** MySQL hostname */
define('DB_HOST', getenv('WORDPRESS_DB_HOST'));

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '_N4l0Xuu5`pv#|DH{ztv*~.lBRr-?EGdk3@i+|m&+@c9]|^9*f7lAlvRN,WB|nvC');
define('SECURE_AUTH_KEY',  '-&GjU=-:O4]mlw8`?[$txDKv3IM-}&.jFn%32$v:QY8.UjwkfS+{E7@T+dh{<$7|');
define('LOGGED_IN_KEY',    'ks-`63|D-DmR3gWONu6wF`zk5U7MF.>T}iE<{EfSaCcs xt6H6YK87+}8e-g9as*');
define('NONCE_KEY',        '<Wc_Dl9~y[d||5W|+?OtBE*=kTAb/+*><{87.<C5)n~kE9tepX.@i,II&sq3E<gp');
define('AUTH_SALT',        'eN$d/`St]OMECiz|N}uRrUd@rg=rRMjrS6k1|;Qf4-lju:oj$0ZHO?}G7{v6,=xP');
define('SECURE_AUTH_SALT', 'jP%zb!Jo8e,i<Hzf7Q:GDI$zW,YS4nH|O~P9/[M;0FsG}mfr2N5r!.~&$qJc),5c');
define('LOGGED_IN_SALT',   '|%h|G)Rg2^|q6nYWSfC;^Zv{A]=kUusV]Rch}+v@jkw=<WUmng/lkY},Cgy+,`}a');
define('NONCE_SALT',       'MX]R(^^iT,z|S{<.hO|DUS#sef$b,%pZ-~(oBg=zm$)O)4&2k^UHE2Wg+-PwlMG+');
/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */


// If we're behind a proxy server and using HTTPS, we need to alert Wordpress of that fact
// see also http://codex.wordpress.org/Administration_Over_SSL#Using_a_Reverse_Proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
	$_SERVER['HTTPS'] = 'on';
}

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') ) {
	define('ABSPATH', dirname(__FILE__) . '/');
}

#define( 'FORCE_SSL_LOGIN', true );
#define( 'FORCE_SSL_ADMIN', true );
define( 'FS_METHOD', 'direct' );
define('WP_POST_REVISIONS', 5);
define('AUTOSAVE_INTERVAL', 300);
define('WP_MEMORY_LIMIT', getenv('WORDPRESS_WP_MEMORY_LIMIT'));

define('WP_HOME',getenv('WORDPRESS_WP_SITEURL'));
define('WP_SITEURL',getenv('WORDPRESS_WP_SITEURL'));

# REDIS CACHE CONFIGURATION
if(getenv('WP_REDIS_HOST')) {
    define( 'WP_REDIS_HOST', getenv('WP_REDIS_HOST') );
    define( 'WP_REDIS_PORT', getenv('WP_REDIS_PORT') );

    if(getenv('WP_REDIS_PASSWORD')) {
        define( 'WP_REDIS_PASSWORD', getenv('WP_REDIS_PASSWORD') );
    }
}

if(getenv('WP_REDIS_DISABLED')) {
    define( 'WP_REDIS_DISABLED', getenv('WP_REDIS_DISABLED') );
}

define( 'AS3CF_SETTINGS', serialize( array(
    'provider' => 'gcp',
    'key-file-path' => ABSPATH . 'service-account.json',
) ) );

define( 'WP_DEBUG_DISPLAY', false );
define('WP_DEBUG', true);
/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
