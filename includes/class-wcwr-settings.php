<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCWR_Settings {
    public static function register() {
        add_filter( 'woocommerce_settings_tabs_array', array( __CLASS__, 'add_tab' ), 50 );
        add_action( 'woocommerce_settings_tabs_wcwr', array( __CLASS__, 'render_settings' ) );
        add_action( 'woocommerce_update_options_wcwr', array( __CLASS__, 'save' ) );
    }

    public static function add_tab( $tabs ) {
        $tabs['wcwr'] = __( 'کیف پول و امتیاز', 'wc-wallet-rewards' );

        return $tabs;
    }

    public static function render_settings() {
        woocommerce_admin_fields( self::fields() );
    }

    public static function save() {
        woocommerce_update_options( self::fields() );
    }

    private static function fields() {
        return array(
            'section_title'              => array(
                'name' => __( 'تنظیمات کیف پول و امتیاز', 'wc-wallet-rewards' ),
                'type' => 'title',
                'desc' => __( 'میزان شارژ و امتیازدهی را مدیریت کنید.', 'wc-wallet-rewards' ),
                'id'   => 'wcwr_section_title',
            ),
            'topup_minimum'              => array(
                'name'     => __( 'حداقل شارژ کیف پول', 'wc-wallet-rewards' ),
                'type'     => 'number',
                'id'       => WCWR_Plugin::OPTION_KEY . '[topup_minimum]',
                'css'      => 'min-width:300px;',
                'desc_tip' => __( 'حداقل مبلغ قابل شارژ برای هر تراکنش.', 'wc-wallet-rewards' ),
                'default'  => 50000,
            ),
            'reward_rate'                => array(
                'name'     => __( 'درصد امتیاز از مبلغ پرداختی', 'wc-wallet-rewards' ),
                'type'     => 'number',
                'id'       => WCWR_Plugin::OPTION_KEY . '[reward_rate]',
                'css'      => 'min-width:300px;',
                'desc_tip' => __( 'مثلا 5 به معنی 5٪ مبلغ سفارش به عنوان امتیاز است.', 'wc-wallet-rewards' ),
                'default'  => 5,
            ),
            'point_value'               => array(
                'name'     => __( 'ارزش هر امتیاز (تومان)', 'wc-wallet-rewards' ),
                'type'     => 'number',
                'id'       => WCWR_Plugin::OPTION_KEY . '[point_value]',
                'css'      => 'min-width:300px;',
                'desc_tip' => __( 'به ازای هر امتیاز چه مقدار به کیف پول قابل تبدیل است.', 'wc-wallet-rewards' ),
                'default'  => 1000,
            ),
            'wallet_gateway_enabled'     => array(
                'name'     => __( 'فعالسازی پرداخت با کیف پول', 'wc-wallet-rewards' ),
                'type'     => 'checkbox',
                'id'       => WCWR_Plugin::OPTION_KEY . '[wallet_gateway_enabled]',
                'css'      => 'min-width:300px;',
                'desc'     => __( 'اجازه پرداخت کامل سفارش با موجودی کیف پول', 'wc-wallet-rewards' ),
                'default'  => 'yes',
            ),
            'section_end'                => array(
                'type' => 'sectionend',
                'id'   => 'wcwr_section_end',
            ),
        );
    }
}
