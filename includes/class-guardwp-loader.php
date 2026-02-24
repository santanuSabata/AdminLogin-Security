<?php

if (!defined('ABSPATH')) {
    exit;
}

class GuardWP_Loader {

    public function __construct() {
        $this->load_dependencies();
    }

    private function load_dependencies() {
        require_once GUARDWP_PATH . 'includes/class-guardwp-admin.php';
        require_once GUARDWP_PATH . 'includes/class-guardwp-security.php';
    }

    public function run() {
        new GuardWP_Admin();
        new GuardWP_Security();
    }
}