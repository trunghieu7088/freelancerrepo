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
/**
 * Database connection information is automatically provided.
 * There is no need to set or change the following database configuration
 * values:
 *   DB_HOST
 *   DB_NAME
 *   DB_USER
 *   DB_PASSWORD
 *   DB_CHARSET
 *   DB_COLLATE
 */
/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '66xQ1_[H?MeZLf6gZP=+(t{X=#lYPtG@g9?L6a_y4^ECNi|vxG+VV21>%Px~rBi.');
define('SECURE_AUTH_KEY',  'i>v8#G)~Y&F$rQKnSv?R)n[4DUY.lh#x7hB|BTl(v/ )/2-Eo&DD{+ _ADD(]*XD');
define('LOGGED_IN_KEY',    '-@H-Cy#+Yb;9l^)e,Qyl=f[{M~PR)XUTn#(FI^a9S(e::F_U_065q]v#!2|nNg,P');
define('NONCE_KEY',        'LH?pA3}%yL9_6pkRtXw6Cq2fc%{7m#18Y):-*<TW})NLrz#M+EY,-m?rV!Z1-+<}');
define('AUTH_SALT',        './%+UbEZz+QVJ9lqJQK.vM*U6I;d9K@;AUapoNRG|.V/D=AQb8bbng|Ve2_kBUGT');
define('SECURE_AUTH_SALT', ')|Rte#^0*,1HH5 ^q|)K$:Xi$2?5bU4vPO};IwMR({g:((WdTNq0N`O1n<r$gGJ]');
define('LOGGED_IN_SALT',   '9Vp7g_`-%m$cnA,V)NYAP4J):`W+{%$AgRHtuwd12T1~zw!:J|5XG<<{scR;qi_5');
define('NONCE_SALT',       'S!e2RlK^K&Xz5m e4}0YgmCL/@zmAg~UF`ka:0$2Mk=8Ibh#c8D{C==lQ+0@1mD_');
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

	define('WP_DEBUG', true);

define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );
@ini_set( 'display_errors', 1 );

/* That's all, stop editing! Happy blogging. */
/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
  define('ABSPATH', dirname(__FILE__) . '/');
/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');