<?php
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
define('DB_NAME', 'wpdemo');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'aZT9WBAK7_@X`X37nf=3l]H}@,ESO_]S4]nn!7/YLh^T^ibk/5qlk4gcDA+;0FJ~');
define('SECURE_AUTH_KEY',  '[lJ6x^/Nw?L[~6p~OHgi$}Jf[u)RN?lT++P<u1b|!lP }e<n[WlF?Bu:I4)=L8h7');
define('LOGGED_IN_KEY',    '9{^p};.0CpIjw>ms@~4jT!acgC][QCJx^ cSy~nSu1$0[l-Kd+wwcQjH2~*4<NnK');
define('NONCE_KEY',        '7j5~zAU&bTWLRzC-@pKkP)o8d~Xu]M6F>^l>:~Wj~xPEN_PBm8D)K&Hga3z>32P4');
define('AUTH_SALT',        '=(;J$8dg6}%u1|Kpk!V#z*KJ4]+GdEJwb9]}~G;*y=B&3JDN*^f):aBf)UwVS:yY');
define('SECURE_AUTH_SALT', '+nnmT Jh2V2Gs;&q?v[DuS,%?VxMxgNz$Y]hU2b(7vlBgYTkud$T8PD:^g65e|vH');
define('LOGGED_IN_SALT',   '@1wL)I._0$,`$;P9 ,nC!%y0a+_^9>ZP{vh/uyby,~ZGd?|yspM$`;PZ,uZ+$x)F');
define('NONCE_SALT',       'M>W3a}z7$_|U-_cX{6*@MU%;]F<HZAp4mM<2`v[p +#yV#GnxDFur7Qpp/=HDd]9');

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
