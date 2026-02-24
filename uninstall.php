<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('guardwp_max_attempts');
delete_option('guardwp_lockout_time');
delete_option('guardwp_lockout_message');
delete_option('guardwp_recaptcha_site_key');
delete_option('guardwp_recaptcha_secret_key');
delete_option('guardwp_whitelist_ips');

global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}guardwp_lockouts");