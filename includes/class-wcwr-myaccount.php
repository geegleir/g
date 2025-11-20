<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCWR_MyAccount {
    const ENDPOINT = 'wcwr-wallet';

    public static function register_endpoints() {
        add_rewrite_endpoint( self::ENDPOINT, EP_ROOT | EP_PAGES );
    }

    public static function hooks() {
        add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );
        add_filter( 'woocommerce_account_menu_items', array( __CLASS__, 'add_menu_item' ) );
        add_action( 'woocommerce_account_' . self::ENDPOINT . '_endpoint', array( __CLASS__, 'render_page' ) );
        add_action( 'template_redirect', array( __CLASS__, 'handle_forms' ) );
    }

    public static function add_query_vars( $vars ) {
        $vars[] = self::ENDPOINT;

        return $vars;
    }

    public static function add_menu_item( $items ) {
        $items[ self::ENDPOINT ] = __( 'کیف پول من', 'wc-wallet-rewards' );

        return $items;
    }

    public static function handle_forms() {
        if ( ! is_user_logged_in() || ! is_account_page() ) {
            return;
        }

        if ( isset( $_POST['wcwr_topup_amount'] ) && isset( $_POST['_wcwr_nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['_wcwr_nonce'] ), 'wcwr_wallet_action' ) ) {
            $amount      = (float) wc_clean( wp_unslash( $_POST['wcwr_topup_amount'] ) );
            $min_topup   = (float) WCWR_Plugin::get_option( 'topup_minimum', 50000 );
            $amount      = max( 0, $amount );

            if ( $amount < $min_topup ) {
                wc_add_notice( sprintf( __( 'حداقل شارژ کیف پول %s است.', 'wc-wallet-rewards' ), wc_price( $min_topup ) ), 'error' );
                return;
            }

            WCWR_Wallet::set_topup_session( $amount );
            wc_add_notice( __( 'مبلغ شارژ به سبد خرید افزوده شد. مراحل پرداخت را ادامه دهید.', 'wc-wallet-rewards' ) );
        }

        if ( isset( $_POST['wcwr_convert_points'] ) && isset( $_POST['_wcwr_nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['_wcwr_nonce'] ), 'wcwr_wallet_action' ) ) {
            $user_id = get_current_user_id();
            $added   = WCWR_Rewards::convert_points_to_wallet( $user_id );

            if ( $added > 0 ) {
                wc_add_notice( sprintf( __( 'مبلغ %s به کیف پول اضافه شد.', 'wc-wallet-rewards' ), wc_price( $added ) ) );
            } else {
                wc_add_notice( __( 'امتیازی برای تبدیل وجود ندارد.', 'wc-wallet-rewards' ), 'error' );
            }
        }
    }

    public static function render_page() {
        $user_id = get_current_user_id();
        $balance = WCWR_Wallet::get_balance( $user_id );
        $points  = WCWR_Rewards::get_points( $user_id );
        ?>
        <div class="wcwr-wallet-box">
            <h3><?php esc_html_e( 'کیف پول من', 'wc-wallet-rewards' ); ?></h3>
            <p><?php printf( esc_html__( 'موجودی فعلی: %s', 'wc-wallet-rewards' ), wp_kses_post( wc_price( $balance ) ) ); ?></p>
            <p><?php printf( esc_html__( 'امتیازهای وفاداری: %d', 'wc-wallet-rewards' ), absint( $points ) ); ?></p>
        </div>

        <div class="wcwr-topup">
            <h4><?php esc_html_e( 'شارژ کیف پول', 'wc-wallet-rewards' ); ?></h4>
            <form method="post">
                <?php wp_nonce_field( 'wcwr_wallet_action', '_wcwr_nonce' ); ?>
                <p>
                    <label for="wcwr_topup_amount"><?php esc_html_e( 'مبلغ شارژ (تومان)', 'wc-wallet-rewards' ); ?></label>
                    <input type="number" name="wcwr_topup_amount" id="wcwr_topup_amount" min="0" step="1000" />
                </p>
                <button type="submit" class="button"><?php esc_html_e( 'افزودن به سبد خرید', 'wc-wallet-rewards' ); ?></button>
            </form>
        </div>

        <div class="wcwr-points">
            <h4><?php esc_html_e( 'تبدیل امتیاز به کیف پول', 'wc-wallet-rewards' ); ?></h4>
            <form method="post">
                <?php wp_nonce_field( 'wcwr_wallet_action', '_wcwr_nonce' ); ?>
                <p><?php esc_html_e( 'تمام امتیازهای موجود به کیف پول منتقل می‌شوند.', 'wc-wallet-rewards' ); ?></p>
                <input type="hidden" name="wcwr_convert_points" value="1" />
                <button type="submit" class="button button-secondary"><?php esc_html_e( 'تبدیل امتیاز', 'wc-wallet-rewards' ); ?></button>
            </form>
        </div>
        <?php
    }
}
