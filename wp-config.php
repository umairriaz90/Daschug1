<?php
/**
 * In dieser Datei werden die Grundeinstellungen für WordPress vorgenommen.
 *
 * Zu diesen Einstellungen gehören: MySQL-Zugangsdaten, Tabellenpräfix,
 * Secret-Keys, Sprache und ABSPATH. Mehr Informationen zur wp-config.php gibt es auf der {@link http://codex.wordpress.org/Editing_wp-config.php
 * wp-config.php editieren} Seite im Codex. Die Informationen für die MySQL-Datenbank bekommst du von deinem Webhoster.
 *
 * Diese Datei wird von der wp-config.php-Erzeugungsroutine verwendet. Sie wird ausgeführt, wenn noch keine wp-config.php (aber eine wp-config-sample.php) vorhanden ist,
 * und die Installationsroutine (/wp-admin/install.php) aufgerufen wird.
 * Man kann aber auch direkt in dieser Datei alle Eingaben vornehmen und sie von wp-config-sample.php in wp-config.php umbenennen und die Installation starten.
 *
 * @package WordPress
 */

/**  MySQL Einstellungen - diese Angaben bekommst du von deinem Webhoster. */
/**  Ersetze database_name_here mit dem Namen der Datenbank, die du verwenden möchtest. */
define('DB_NAME', 'wp_elearning');

/** Ersetze username_here mit deinem MySQL-Datenbank-Benutzernamen */
define('DB_USER', 'wp_elearning');

/** Ersetze password_here mit deinem MySQL-Passwort */
define('DB_PASSWORD', '8zq3fd3hwf!');

/** Ersetze localhost mit der MySQL-Serveradresse */
define('DB_HOST', 'localhost');

/** Der Datenbankzeichensatz der beim Erstellen der Datenbanktabellen verwendet werden soll */
define('DB_CHARSET', 'utf8');

/** Der collate type sollte nicht geändert werden */
define('DB_COLLATE', '');


/** WordPress kann selber und unter bestimmten Voraussetzungen einen Datenbankfehler reparieren.
define(‘WP_ALLOW_REPAIR’, true);



// define('RELOCATE', true);

/**#@+
 * Sicherheitsschlüssel
 *
 * Ändere jeden KEY in eine beliebige, möglichst einzigartige Phrase. 
 * Auf der Seite {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service} kannst du dir alle KEYS generieren lassen.
 * Bitte trage für jeden KEY eine eigene Phrase ein. Du kannst die Schlüssel jederzeit wieder ändern, alle angemeldeten Benutzer müssen sich danach erneut anmelden.
 *
 * @seit 2.6.0
 */
define('AUTH_KEY',         '|J2.FMxzTxkM<YCJf;[|c6XQi|#/)K@4{;/Z03^eakE|ukt(-:DyoMmH79hO5`Lr');
define('SECURE_AUTH_KEY',  '7Om8|B=gu_)#-l=;kIn*+<G}dh-D^<Q@D9`pv!P}E!-g~b;z_n%IFAJP^Vw|f%Rh');
define('LOGGED_IN_KEY',    'ev=13rR+IFvDJjtL]FG/q2o5>0NXSsxhpqJ8i-gQ4-XW4;rK51=O2/|TadCTo*C.');
define('NONCE_KEY',        '=?l,L/^nn1)R3Ou#W3|5OHU9#Y}#w6K2j;A}m;QZkoy@MTfajHVGp.2YU[Mnm4|j');
define('AUTH_SALT',        'q*tv`[%uX)Dk%<?alk`9aGx?,Tt}svl,Y<QpXZI /|Z.q|6s9.9VJUdacD|r <cO');
define('SECURE_AUTH_SALT', ']Y4UPUa=&c<T?jKOTb-YX(@y(s&ml[4-?gDQQ;7bpSGSWOHV8[*ECNZ9.-2:rKgK');
define('LOGGED_IN_SALT',   '|;GC=OH?+6<l1Rp`WaD4-e<cbP*B<#?wX&hY@Z?3(,(>akeG| g|)ea7`_>0&)G[');
define('NONCE_SALT',       '$R)]YrZyupEf_sj%d;qWkl2Q/;6{]08;/+7x+Y)2ARz@09+^?2zuT-6RfqNR,[7a');

/**#@-*/

/**
 * WordPress Datenbanktabellen-Präfix
 *
 *  Wenn du verschiedene Präfixe benutzt, kannst du innerhalb einer Datenbank
 *  verschiedene WordPress-Installationen betreiben. Nur Zahlen, Buchstaben und Unterstriche bitte!
 */
$table_prefix  = 'wp13_';

/**
 * WordPress Sprachdatei
 *
 * Hier kannst du einstellen, welche Sprachdatei benutzt werden soll. Die entsprechende
 * Sprachdatei muss im Ordner wp-content/languages vorhanden sein, beispielsweise de_DE.mo
 * Wenn du nichts einträgst, wird Englisch genommen.
 */
define('WPLANG', 'de_DE');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
 	
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

/** Ohne FTP Updates */
define('FS_METHOD','direct');

