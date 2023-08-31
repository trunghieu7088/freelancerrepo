<?php
define('WP_CACHE', true); // WP-Optimize Cache
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en « wp-config.php » et remplir les
 * valeurs.
 *
 * Ce fichier contient les réglages de configuration suivants :
 *
 * Réglages MySQL
 * Préfixe de table
 * Clés secrètes
 * Langue utilisée
 * ABSPATH
 *
 * @link https://fr.wordpress.org/support/article/editing-wp-config-php/.
 *
 * @package WordPress
 */
// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define( 'DB_NAME', '123coaching-image' );
/** Utilisateur de la base de données MySQL. */
define( 'DB_USER', '123coaching-image' );
/** Mot de passe de la base de données MySQL. */
define( 'DB_PASSWORD', '@Xst2w80' );
/** Adresse de l’hébergement MySQL. */
define( 'DB_HOST', 'localhost' );
/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define( 'DB_CHARSET', 'utf8mb4' );
/**
 * Type de collation de la base de données.
 * N’y touchez que si vous savez ce que vous faites.
 */
define( 'DB_COLLATE', '' );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );
define( 'WP_MEMORY_LIMIT', '256M' );

set_time_limit(10000000000);
/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clés secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'pH)TIaC%J4&Zy0V>F5}xt4X2dp -{wo!=i!*(!$XavXJjfE+WNO!<L|p[k($Sw[]' );
define( 'SECURE_AUTH_KEY',  '|(H1[iO],A,e4XQ,DXS`5TTjpJ%4TQ..wp_*Y}_]|p..)&b0Qok,>lZ3.ace}/^?' );
define( 'LOGGED_IN_KEY',    ':j{DIoP,@jS 0lvX*gW2mDn>|e2Pecq$p#4dma9y~ M#Cm^dy5)td2KCvt/D8vF2' );
define( 'NONCE_KEY',        'vgpCkJb6c$Hsn0>28`>unry}}U](W>8xPdUFNXZ8|$Sii>R7!cXvkvF5|hy]/6MW' );
define( 'AUTH_SALT',        'vh*$ky//mDFeA7mRrziLyv/zjfy6]J>TR%|C$ ch_4aa6|1G+RMk-phZ-0]ZU5R;' );
define( 'SECURE_AUTH_SALT', 'KEG+6A<TA_,)*m&`OL`tJ }ZTM%Osp CgG*T>_]L` NWJ&^!&E2F,DcN_d}WqW!o' );
define( 'LOGGED_IN_SALT',   'NMX(6BnOyI|tor5dKjSl?GsOEzuMijogj,(<{>Sb2%?;DO*=c7p9!UdRp:8cUtN{' );
define( 'NONCE_SALT',       'zz++m[_ e089 +G.=%g38K/vxkm*3W5Ej!*3?#I=& NWb3Aa@}9|GFv8hTKcTD,a' );
/**#@-*/
/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'wp_';
/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortement recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://fr.wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );
/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */
/** Chemin absolu vers le dossier de WordPress. */
if ( ! defined( 'ABSPATH' ) )
  define( 'ABSPATH', dirname( __FILE__ ) . '/' );
/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once( ABSPATH . 'wp-settings.php' );