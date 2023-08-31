<?php
define( 'WP_CACHE', true /* Modified by NitroPack */ ); // Added by WP Rocket
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
define('DB_NAME', 'voixofb146');
/** MySQL database username */
define('DB_USER', 'voixofb146');
/** MySQL database password */
define('DB_PASSWORD', 'Chipsie69650');
/** MySQL hostname */
define('DB_HOST', 'voixofb146.mysql.db:3306');
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
define('AUTH_KEY',         'yV@%^hd%0)49OZ:^Mlas$X=KF<sx.&}>V]Ai,<+?xXg3[a2]b1m97C>GW$r*),JM');
define('SECURE_AUTH_KEY',  'DYZ![`D{_$[h!KAwBu4gGM58pIpna?tu.8pAB$v(K+h4*WjZq}d*F46.V4weGzO@');
define('LOGGED_IN_KEY',    'x(~y$lxY3_7};ai^Q>Qg[7D.t;)ucP~/%K:FrADp-Hjc U+N:,Or+jgY:#lYd#q,');
define('NONCE_KEY',        'i*v5 TJ?P(&<Ju:)z?Gtg%xlG{wy|FFV@}MpaXpMC{frP<Hw7YNFz5Onu<GHQ{=%');
define('AUTH_SALT',        'K2<Nz@Af#5rPzto_!v%Yp0}5j*eLi#6FXY~MGY$cCo>MB#;)t-~4,}s^ :`T>[mx');
define('SECURE_AUTH_SALT', '#W 4H%:TAp6h?9wC.3ch6)-m#mFyum-G Kriv5kxKgEZ?GRnGQ;:`jk~VlW;|>Hk');
define('LOGGED_IN_SALT',   'X{p $R46@FR~g;VUd3sh:=fJXx?&DA<FIfGPXam%UW`M`RCrx(=7yk/B/ ,4!:gV');
define('NONCE_SALT',       '|!Qa04SCB81`az7#,B!ad|QVfMw6gzj~/3Ac;0n-xf{CW^03dxE^`5~D]TQ?4g;m');
/**#@-*/
/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wor3033_';
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
define('WP_DEBUG', false );




/* That's all, stop editing! Happy blogging. */
/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
/* Fixes "Add media button not working", see http://www.carnfieldwebdesign.co.uk/blog/wordpress-fix-add-media-button-not-working/ */
define('CONCATENATE_SCRIPTS', false );
/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
