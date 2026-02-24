<?php
/**
 * Plugin Name: GuardWP Security – Login Protection &  reCAPTCHA
 * Plugin URI:  https://iamaze.in/
 * Description: Lightweight WordPress security plugin with login protection, Google reCAPTCHA, lockout logs and security score.
 * Version:     1.0.0
 * Requires at least: 6.0
 * Tested up to: 6.9
 * Requires PHP: 7.4
 * Author:      Santanu Sabata
 * Author URI:  https://iamaze.in/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: guardwp
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define constants
define('GUARDWP_VERSION', '1.0.0');
define('GUARDWP_PATH', plugin_dir_path(__FILE__));
define('GUARDWP_URL', plugin_dir_url(__FILE__));

// Include required files
require_once GUARDWP_PATH . 'includes/class-guardwp-loader.php';

register_activation_hook(__FILE__, 'guardwp_create_log_table');

function guardwp_create_log_table() {
    global $wpdb;

    $table = $wpdb->prefix . 'guardwp_lockouts';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        ip varchar(100) NOT NULL,
        attempts int NOT NULL,
        lock_time datetime NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// Run plugin
function guardwp_run() {
    $plugin = new GuardWP_Loader();
    $plugin->run();
}
guardwp_run();

