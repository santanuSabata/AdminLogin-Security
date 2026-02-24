<?php

if (!defined('ABSPATH')) {
    exit;
}



class GuardWP_Admin {


    public function __construct() {

        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    public function register_settings() {

        register_setting('guardwp_settings_group', 'guardwp_max_attempts');
        register_setting('guardwp_settings_group', 'guardwp_lockout_time');
        register_setting('guardwp_settings_group', 'guardwp_lockout_message');
        register_setting('guardwp_settings_group', 'guardwp_whitelist_ips');

        register_setting('guardwp_settings_group', option_name: 'guardwp_recaptcha_site_key');
        register_setting('guardwp_settings_group', 'guardwp_recaptcha_secret_key');
        
    }

    public function add_admin_menu() {

        add_menu_page(
            'GuardWP Security',
            'GuardWP',
            'manage_options',
            'guardwp-security',
            array($this, 'settings_page'),
            'dashicons-shield-alt',
            80
        );

        add_submenu_page(
            'guardwp-security',
            'Lockout Logs',
            'Lockout Logs',
            'manage_options',
            'guardwp-lockouts',
            array($this, 'lockout_logs_page')
        );
    }

    public function settings_page() {
        require_once GUARDWP_PATH . 'admin/settings-page.php';
    }

    public function lockout_logs_page() {

        global $wpdb;
        $table = $wpdb->prefix . 'guardwp_lockouts';
        $logs = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC");

        echo '<div class="wrap"><h1>Lockout Logs</h1><table class="widefat">';
        echo '<tr><th>IP</th><th>Attempts</th><th>Time</th><th>Action</th></tr>';

        foreach ($logs as $log) {
            echo '<tr>
            <td>'.esc_html($log->ip).'</td>
            <td>'.esc_html($log->attempts).'</td>
            <td>'.esc_html($log->lock_time).'</td>
            <td><button class="button unlock-ip" data-ip="'.esc_attr($log->ip).'">Unlock</button></td>
            </tr>';
        }

        echo '</table></div>';
    }
 
    public function enqueue_scripts() {

        wp_enqueue_script(
            'guardwp-admin-js',
            plugins_url( 'js/admin.js', __FILE__ ),
            array( 'jquery' ), // ONLY script handles here
            '1.0.0',
            true
        );

        wp_localize_script(
            'guardwp-admin-js',
            'guardwp_ajax',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'guardwp_nonce' ),
            )
        );
    }

    public function sanitize_settings( $input ) {

        $sanitized = array();

        // Max login attempts (number)
        if ( isset( $input['max_attempts'] ) ) {
            $sanitized['max_attempts'] = absint( $input['max_attempts'] );
        }

        // Lockout time (number)
        if ( isset( $input['lockout_time'] ) ) {
            $sanitized['lockout_time'] = absint( $input['lockout_time'] );
        }

        // Lockout message (text)
        if ( isset( $input['lockout_message'] ) ) {
            $sanitized['lockout_message'] = sanitize_text_field( $input['lockout_message'] );
        }

        // reCAPTCHA site key
        if ( isset( $input['recaptcha_site_key'] ) ) {
            $sanitized['recaptcha_site_key'] = sanitize_text_field( $input['recaptcha_site_key'] );
        }

        // reCAPTCHA secret key
        if ( isset( $input['recaptcha_secret_key'] ) ) {
            $sanitized['recaptcha_secret_key'] = sanitize_text_field( $input['recaptcha_secret_key'] );
        }

        // Enable reCAPTCHA checkbox
        $sanitized['enable_recaptcha'] = isset( $input['enable_recaptcha'] ) ? 1 : 0;

        return $sanitized;
    }

    public function render_admin_page() {

        $nonce = wp_create_nonce( 'guardwp_nonce' );
        
        ?>
        <button 
            id="guardwp-unlock" 
            data-nonce="<?php echo esc_attr( $nonce ); ?>">
            Unlock
        </button>
        <?php
    }
}