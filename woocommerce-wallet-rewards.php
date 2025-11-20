<?php
/**
 * Plugin Name: WooCommerce Wallet & Rewards Pro
 * Description: کیف پول پیشرفته ووکامرس با امکان شارژ و اعطای امتیاز برای خرید.
 * Author: ChatGPT
 * Version: 1.0.0
 * Text Domain: wc-wallet-rewards
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'WCWR_PLUGIN_FILE' ) ) {
    define( 'WCWR_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'WCWR_PLUGIN_DIR' ) ) {
    define( 'WCWR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! class_exists( 'WCWR_Plugin' ) ) {
    require_once WCWR_PLUGIN_DIR . 'includes/class-wcwr-plugin.php';
}

add_action( 'plugins_loaded', array( 'WCWR_Plugin', 'init' ) );
