<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCWR_Plugin {
    const OPTION_GROUP = 'wcwr_settings';
    const OPTION_KEY   = 'wcwr_options';

    public static function init() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return;
        }

        self::includes();

        add_action( 'init', array( __CLASS__, 'maybe_start_session' ), 1 );
        add_action( 'init', array( 'WCWR_MyAccount', 'register_endpoints' ) );
        add_action( 'init', array( 'WCWR_Wallet', 'register_hooks' ) );
        add_action( 'init', array( 'WCWR_Rewards', 'register_hooks' ) );
        add_action( 'woocommerce_loaded', array( 'WCWR_Gateway_Wallet', 'register_gateway' ) );
        add_filter( 'plugin_action_links_' . plugin_basename( WCWR_PLUGIN_FILE ), array( __CLASS__, 'settings_link' ) );

        WCWR_Settings::register();
        WCWR_MyAccount::hooks();
    }

    private static function includes() {
        require_once WCWR_PLUGIN_DIR . 'includes/class-wcwr-settings.php';
        require_once WCWR_PLUGIN_DIR . 'includes/class-wcwr-wallet.php';
        require_once WCWR_PLUGIN_DIR . 'includes/class-wcwr-rewards.php';
        require_once WCWR_PLUGIN_DIR . 'includes/class-wcwr-gateway-wallet.php';
        require_once WCWR_PLUGIN_DIR . 'includes/class-wcwr-myaccount.php';
    }

    public static function settings_link( $links ) {
        $url     = admin_url( 'admin.php?page=wc-settings&tab=wcwr' );
        $links[] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'تنظیمات', 'wc-wallet-rewards' ) . '</a>';

        return $links;
    }

    public static function maybe_start_session() {
        if ( is_admin() ) {
            return;
        }

        if ( ! session_id() ) {
            wc_maybe_define_constant( 'WOOCOMMERCE_CART', true );
            wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );
            session_start();
        }
    }

    public static function get_option( $key, $default = '' ) {
        $options = get_option( self::OPTION_KEY, array() );

        return isset( $options[ $key ] ) ? $options[ $key ] : $default;
    }
}
