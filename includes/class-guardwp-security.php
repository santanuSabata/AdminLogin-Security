<?php

if (!defined('ABSPATH')) {
    exit;
}

class GuardWP_Security {

    public function __construct() {

        add_action('wp_login_failed', array($this, 'track_failed_login'));
        add_filter('authenticate', array($this, 'check_login_attempts'), 30, 3);
        add_action('wp_ajax_guardwp_unlock_ip', array($this, 'unlock_ip'));

        add_action('login_form', array($this, 'maybe_add_recaptcha'));
        add_filter('authenticate', array($this, 'verify_recaptcha'), 40, 3);
    }

    private function get_user_ip() {

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
        }

        return sanitize_text_field($_SERVER['REMOTE_ADDR']);
    }

    public function track_failed_login($username) {

        $ip = $this->get_user_ip();
        $key = 'guardwp_failed_' . md5($ip);

        $lockout_time = get_option('guardwp_lockout_time', 15);

        $attempts = get_transient($key);
        $attempts = $attempts ? $attempts + 1 : 1;

        set_transient($key, $attempts, $lockout_time * MINUTE_IN_SECONDS);
    }

    public function check_login_attempts($user, $username, $password) {

        $ip = $this->get_user_ip();
        $key = 'guardwp_failed_' . md5($ip);

        $max_attempts = get_option('guardwp_max_attempts', 5);
        $lockout_time = get_option('guardwp_lockout_time', 15);
        $message = get_option(
            'guardwp_lockout_message',
            'Too many failed attempts. Please try again later.'
        );

        $attempts = get_transient($key);

        if ($attempts && $attempts >= $max_attempts) {

            return new WP_Error(
                'guardwp_locked',
                esc_html($message)
            );
        }

        return $user;
    }

    public function unlock_ip() {

         check_ajax_referer('guardwp_nonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $ip = sanitize_text_field($_POST['ip']);
        $key = 'guardwp_failed_' . md5($ip);

        delete_transient($key);

        wp_send_json_success('IP Unlocked');
    }

    public function maybe_add_recaptcha() {

        $ip = $this->get_user_ip();
        $key = 'guardwp_failed_' . md5($ip);
        $attempts = get_transient($key);

        if ($attempts >= 3) {

            $site_key = get_option('guardwp_recaptcha_site_key');

            if (!empty($site_key)) {
                echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
                echo '<div class="g-recaptcha" data-sitekey="'.esc_attr($site_key).'"></div>';
            }
        }
    
    }

    public function verify_recaptcha($user, $username, $password) {

        $ip = $this->get_user_ip();
        $key = 'guardwp_failed_' . md5($ip);
        $attempts = get_transient($key);

        if ($attempts >= 3) {

            $secret_key = get_option('guardwp_recaptcha_secret_key');

            if (!empty($secret_key)) {

                if (empty($_POST['g-recaptcha-response'])) {
                    return new WP_Error('recaptcha_missing', 'Please complete the reCAPTCHA.');
                }

                $response = wp_remote_post(
                    'https://www.google.com/recaptcha/api/siteverify',
                    array(
                        'body' => array(
                            'secret'   => $secret_key,
                            'response' => sanitize_text_field($_POST['g-recaptcha-response']),
                            'remoteip' => $ip
                        )
                    )
                );

                $result = json_decode(wp_remote_retrieve_body($response), true);

                if (empty($result['success'])) {
                    return new WP_Error('recaptcha_invalid', 'reCAPTCHA verification failed.');
                }
            }
        }

        return $user;
    }
}