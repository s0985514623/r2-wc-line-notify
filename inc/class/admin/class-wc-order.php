<?php
/**
 * WC Order Class
 */

declare(strict_types=1);

namespace R2\WcLineNotify\WcOrder;

/**
 * WcOrder Entry
 */
final class WcOrder {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'woocommerce_order_status_completed', array( $this, 'send_line_notify' ), 10, 1 );
	}

	/**
	 * Send_line_notify function
	 *
	 * @param int $order_id The ID of the order.
	 * @return void
	 */
	public function send_line_notify( $order_id ) {
		// 定義請求的URL
		$url = 'https://notify-api.line.me/api/notify';

		// 定義請求頭
		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer mV3KqwOGwETn2qsY1dyUD0p5l68cdPyGlQe4F26mDTo',
			),
			'body'    => array(
				'message' => $order_id,
			),
		);

		// 發送POST請求
		$response = wp_remote_post( $url, $args );

		// 處理響應
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			error_log( "Something went wrong: $error_message" );
		} else {

			$body = wp_remote_retrieve_body( $response );

			error_log( "Something went wrong: $body" );
		}
	}
}

WcOrder::instance();
