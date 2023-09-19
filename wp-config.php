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
define( 'DB_NAME', 'syq' );

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
define( 'AUTH_KEY',         '++-6nA)OW`|I6[,ow#HyE)=x1j:0P>=cdPP{-[6F6EMiIPL;5PHWK3CvS/A[aRID' );
define( 'SECURE_AUTH_KEY',  'eHoT{(A!foFA^u+(Dx<pI{LU,i|($f!grv7#NF~E<qpJ>SifSDSEeS`]NsV({G@D' );
define( 'LOGGED_IN_KEY',    'w._zPI=gIXqO|n:!6w0{|Rs8oG5#fsO}8kTeiM*>&y23:Vpq(G*|gMlIu%nzv7K;' );
define( 'NONCE_KEY',        '0|iApDuqT{E}h<LpeZkuI$,sX0?cI3LcW`P7M,_^l9;jq2:8Hl}_RhQ7UHh#mf3l' );
define( 'AUTH_SALT',        '9^K$OiO*V.m,Xl42?HdBaox9M(4gAKYGu&OLb.cyaEfTC`&Q8jrj;S.b+JU0TRkM' );
define( 'SECURE_AUTH_SALT', 'F:IAp}y)qG4]4L-LH6Rv]`U4Qjzb~PV+A9a%cOa[98Z>R{/2A*3;t{p}8p</Sk={' );
define( 'LOGGED_IN_SALT',   '~Z[,M/_v9r(xg{]^yA3w),9^_Pw!#z!=N)E[?Pj+gKv%l1>wq| s!UAQ8+dp!3.&' );
define( 'NONCE_SALT',       'RB}w](x,$1!UR:v}oq;Rd7cg6#w&UVCMIv8*^T]QCy$9SL>r?8!mij(VYQ]H!<I0' );

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
