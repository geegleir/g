<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCWR_Rewards {
    const META_POINTS = '_wcwr_reward_points';

    public static function register_hooks() {
        add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'award_points' ), 20 );
    }

    public static function get_points( $user_id ) {
        return (int) get_user_meta( $user_id, self::META_POINTS, true );
    }

    public static function add_points( $user_id, $points ) {
        $current = self::get_points( $user_id );
        $new_total = max( 0, (int) $current + (int) $points );
        update_user_meta( $user_id, self::META_POINTS, $new_total );
    }

    public static function award_points( $order_id ) {
        $order   = wc_get_order( $order_id );
        $user_id = $order ? $order->get_user_id() : 0;

        if ( ! $user_id || $order->get_meta( '_wcwr_points_awarded' ) ) {
            return;
        }

        $rate       = (float) WCWR_Plugin::get_option( 'reward_rate', 5 );
        $points_raw = ( $order->get_total() * $rate ) / 100;
        $points     = (int) floor( $points_raw );

        if ( $points <= 0 ) {
            return;
        }

        self::add_points( $user_id, $points );
        $order->update_meta_data( '_wcwr_points_awarded', $points );
        $order->add_order_note( sprintf( __( 'اعطای %d امتیاز وفاداری به مشتری.', 'wc-wallet-rewards' ), $points ) );
        $order->save();
    }

    public static function convert_points_to_wallet( $user_id ) {
        $points       = self::get_points( $user_id );
        $point_value  = (float) WCWR_Plugin::get_option( 'point_value', 1000 );
        $wallet_value = $points * $point_value;

        if ( $wallet_value <= 0 ) {
            return 0;
        }

        self::add_points( $user_id, - $points );
        WCWR_Wallet::add_balance( $user_id, $wallet_value, __( 'تبدیل امتیاز به کیف پول', 'wc-wallet-rewards' ) );

        return $wallet_value;
    }
}
