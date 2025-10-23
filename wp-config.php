<?php
define( 'WP_CACHE', true );

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'dichvup1_wp_jcx49' );

/** Database username */
define( 'DB_USER', 'dichvup1_wp_jremr' );

/** Database password */
define( 'DB_PASSWORD', 'bTVIfv%73?2fOGHe' );

/** Database hostname */
define( 'DB_HOST', 'localhost:3306' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 't#]6rC|M_3FFo2h5540m;KT9QU[3qG0xMlp#Pu+j~2_8S7j3t9Lknht|m%gQ)01n');
define('SECURE_AUTH_KEY', '1Z8CtEPJgxQ5-]@~%idN/x&Ey2N/fHUsCeEM~8UX6-bUi+*k6#3#b!-+3rF0;1T9');
define('LOGGED_IN_KEY', 'bmJD/8A*gyXV]TFZY7d8@53_7;bR~vls#mtQM;Ve#9g8ow-H!YQX67n~!a2jH4:6');
define('NONCE_KEY', '09qE[hnBtfm18Gv8p*pd65P+/~:M5763WZLR2C03z1g93;L)iD8Q@-K/_4&StO5|');
define('AUTH_SALT', 'hsVi+M_~05]AJ3T)RU/G2e4KKkbChfO%U2sDOw+d8Jcn31H6)9e%[1|E0v686EFO');
define('SECURE_AUTH_SALT', '86Ba0e5]1B5@47Uo5PnoZbb2o]-tO1aU5ujG8/KBlx!~E2R/ml8h+c/@9r1412V5');
define('LOGGED_IN_SALT', '675If~|7lSD1lg[]//f;943wmH7tT4w5TR9V#o#KH+R3Ze@fW10@;@73;Z9*HS3i');
define('NONCE_SALT', 'l||G3-yg~91;&4N8LlIy8dh+-c_HD3;hG6Rade1ys_AmK/dpe9f;IB)rq6~G37l(');


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'OmhTlY_';


/* Add any custom values between this line and the "stop editing" line. */

define('WP_ALLOW_MULTISITE', true);
/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
