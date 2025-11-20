<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCWR_Gateway_Wallet extends WC_Payment_Gateway {
    public function __construct() {
        $this->id                 = 'wcwr_wallet';
        $this->method_title       = __( 'پرداخت با کیف پول', 'wc-wallet-rewards' );
        $this->method_description = __( 'استفاده از موجودی کیف پول برای پرداخت سفارش.', 'wc-wallet-rewards' );
        $this->has_fields         = false;
        $this->supports           = array( 'products' );

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled     = $this->get_option( 'enabled', 'yes' );
        $this->title       = $this->get_option( 'title', __( 'پرداخت از کیف پول', 'wc-wallet-rewards' ) );
        $this->description = $this->get_option( 'description', __( 'پرداخت سریع با کسر از کیف پول شما.', 'wc-wallet-rewards' ) );

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled'     => array(
                'title'   => __( 'فعال/غیرفعال', 'wc-wallet-rewards' ),
                'type'    => 'checkbox',
                'label'   => __( 'فعالسازی پرداخت از کیف پول', 'wc-wallet-rewards' ),
                'default' => 'yes',
            ),
            'title'       => array(
                'title'   => __( 'عنوان', 'wc-wallet-rewards' ),
                'type'    => 'text',
                'default' => __( 'پرداخت از کیف پول', 'wc-wallet-rewards' ),
            ),
            'description' => array(
                'title'   => __( 'توضیحات', 'wc-wallet-rewards' ),
                'type'    => 'textarea',
                'default' => __( 'پرداخت سریع با کسر از کیف پول شما.', 'wc-wallet-rewards' ),
            ),
        );
    }

    public function is_available() {
        if ( 'yes' !== WCWR_Plugin::get_option( 'wallet_gateway_enabled', 'yes' ) ) {
            return false;
        }

        if ( ! is_user_logged_in() ) {
            return false;
        }

        $user_id = get_current_user_id();
        $total   = WC()->cart ? WC()->cart->total : 0;

        return WCWR_Wallet::get_balance( $user_id ) >= $total && parent::is_available();
    }

    public function process_payment( $order_id ) {
        $order   = wc_get_order( $order_id );
        $user_id = $order->get_user_id();

        if ( ! $user_id ) {
            wc_add_notice( __( 'برای استفاده از کیف پول باید وارد شوید.', 'wc-wallet-rewards' ), 'error' );

            return array( 'result' => 'fail' );
        }

        $total = $order->get_total();

        if ( WCWR_Wallet::get_balance( $user_id ) < $total ) {
            wc_add_notice( __( 'موجودی کیف پول کافی نیست.', 'wc-wallet-rewards' ), 'error' );

            return array( 'result' => 'fail' );
        }

        WCWR_Wallet::subtract_balance( $user_id, $total, sprintf( __( 'پرداخت سفارش #%s', 'wc-wallet-rewards' ), $order_id ) );
        $order->payment_complete();
        $order->add_order_note( __( 'سفارش با موجودی کیف پول پرداخت شد.', 'wc-wallet-rewards' ) );

        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url( $order ),
        );
    }

    public static function register_gateway() {
        add_filter( 'woocommerce_payment_gateways', array( __CLASS__, 'add_gateway' ) );
    }

    public static function add_gateway( $gateways ) {
        $gateways[] = __CLASS__;

        return $gateways;
    }
}
