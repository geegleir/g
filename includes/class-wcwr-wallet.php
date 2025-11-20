<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCWR_Wallet {
    const META_BALANCE = '_wcwr_wallet_balance';

    public static function register_hooks() {
        add_action( 'woocommerce_cart_calculate_fees', array( __CLASS__, 'maybe_add_topup_fee' ) );
        add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'attach_topup_to_order' ) );
        add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'maybe_credit_topup' ) );
        add_action( 'woocommerce_thankyou', array( __CLASS__, 'clear_topup_session' ) );
    }

    public static function get_balance( $user_id ) {
        $balance = (float) get_user_meta( $user_id, self::META_BALANCE, true );

        return max( 0, $balance );
    }

    public static function add_balance( $user_id, $amount, $note = '' ) {
        $current = self::get_balance( $user_id );
        $new     = $current + (float) $amount;
        update_user_meta( $user_id, self::META_BALANCE, $new );

        if ( $note ) {
            self::log_wallet_note( $user_id, $note );
        }
    }

    public static function subtract_balance( $user_id, $amount, $note = '' ) {
        $current = self::get_balance( $user_id );
        $new     = max( 0, $current - (float) $amount );
        update_user_meta( $user_id, self::META_BALANCE, $new );

        if ( $note ) {
            self::log_wallet_note( $user_id, $note );
        }
    }

    private static function log_wallet_note( $user_id, $message ) {
        $existing = (array) get_user_meta( $user_id, '_wcwr_wallet_log', true );
        $existing[] = current_time( 'mysql' ) . ' - ' . $message;
        update_user_meta( $user_id, '_wcwr_wallet_log', $existing );
    }

    public static function set_topup_session( $amount ) {
        $_SESSION['wcwr_topup_amount'] = $amount;
    }

    public static function get_topup_session() {
        return isset( $_SESSION['wcwr_topup_amount'] ) ? (float) $_SESSION['wcwr_topup_amount'] : 0;
    }

    public static function clear_topup_session() {
        unset( $_SESSION['wcwr_topup_amount'] );
    }

    public static function maybe_add_topup_fee( $cart ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }

        $amount = self::get_topup_session();

        if ( $amount <= 0 ) {
            return;
        }

        $cart->add_fee( __( 'شارژ کیف پول', 'wc-wallet-rewards' ), $amount, false );
    }

    public static function attach_topup_to_order( $order ) {
        $amount = self::get_topup_session();

        if ( $amount > 0 ) {
            $order->update_meta_data( '_wcwr_topup_amount', $amount );
        }
    }

    public static function maybe_credit_topup( $order_id ) {
        $order  = wc_get_order( $order_id );
        $amount = (float) $order->get_meta( '_wcwr_topup_amount', true );

        if ( $amount <= 0 ) {
            return;
        }

        $user_id = $order->get_user_id();
        if ( ! $user_id ) {
            return;
        }

        self::add_balance( $user_id, $amount, sprintf( __( 'شارژ به مبلغ %s از سفارش #%s', 'wc-wallet-rewards' ), wc_price( $amount ), $order_id ) );
    }
}
