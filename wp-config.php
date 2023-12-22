<?php
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
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'teja' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'jo1&1(xDjMsV/]9n`Ewhcf~FTCD7WDXA;H4+L<D%xc:P>Y9(>L.kB@f}&Az&#$HV' );
define( 'SECURE_AUTH_KEY',  'F.H0RjrU;QxF4XHg7[rD}U+% G?7_kQ|N:IaK,0/tGl_R&)>,oPX@$,*:SLw(4Oz' );
define( 'LOGGED_IN_KEY',    'pQqkq3:nw/C]g44Z><wShulL[8[!CF::83zRsMbUewA@L-!Ywq^?}1%69r pG2g<' );
define( 'NONCE_KEY',        'C6?vQ1-pJh]9bt3yU baTP:7vL3nvgbChVMWN5lu9,^}uYxv[Xdq5Q:vCGds@_,v' );
define( 'AUTH_SALT',        'uTZ~VqX{uZgMh3zT{9.XAsD*:-Xr,.pW*(b& =.[6[Z7cT;Ni)u2^`=Y0+r<S!I-' );
define( 'SECURE_AUTH_SALT', '5*TNFZ3/w}ntezZLVN3~F K4n)H#?06UZ6+2k)OVx,vOq/r)<:.PF[6ZpnA`(u<o' );
define( 'LOGGED_IN_SALT',   '?Aa]_9km=gC^4J(6~PBE^wY3/L<)lN ,~BiDuur7rMP#L=n-M#E`3S@4,PU!?zH(' );
define( 'NONCE_SALT',       'oC1A=ZwU,}jSF=t&K~&>)M]lz^2VEi#zz<k!Cj d?@`nr*ZZ!vmx ~xA}Cqg[N]y' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
