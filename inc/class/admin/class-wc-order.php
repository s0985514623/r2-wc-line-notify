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
	 * Line notify token.
	 *
	 * @var [type]
	 */
	private $token;
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->set_token();
		\add_action( 'woocommerce_order_status_completed', array( $this, 'order_completed' ), 10, 1 );
		\add_action( 'woocommerce_new_order', array( $this, 'new_order' ), 10, 1 );
		\add_action( 'woocommerce_order_status_changed', array( $this, 'order_status_changed' ), 10, 3 );
	}

	/**
	 * Set token function
	 */
	public function set_token() {
		$token       = get_option( 'r2_line_notify_token' );
		$this->token = $token;
	}
	/**
	 * New_order function
	 *
	 * @param int $order_id The ID of the order.
	 */
	public function new_order( $order_id ) {
		// $order = wc_get_order( $order_id );
		$message = '有一筆新訂單編號成立：' . $order_id;
		$this->send_line_notify( $message );
	}
	/**
	 * Order_completed function
	 *
	 * @param int $order_id The ID of the order.
	 */
	public function order_completed( $order_id ) {
		// $order = wc_get_order( $order_id );
		$message = '訂單編號：' . $order_id . '已完成';
		$this->send_line_notify( $message );
	}
	/**
	 * Order status changed
	 *
	 * @param int    $order_id The ID of the order.
	 * @param string $old_status The old status of the order.
	 * @param string $new_status The new status of the order.
	 */
	public function order_status_changed( $order_id, $old_status, $new_status ) {
		$old_status_name = wc_get_order_status_name( $old_status );
		$new_status_name = wc_get_order_status_name( $new_status );
		$message         = '訂單編號：' . $order_id . '，狀態已由「' . $old_status_name . '」變更為「' . $new_status_name . '」';
		$this->send_line_notify( $message );
	}

	/**
	 * Send_line_notify function
	 *
	 * @param string $message The message.
	 * @return void
	 */
	public function send_line_notify( $message ) {
		// 定義請求的URL
		$url = 'https://notify-api.line.me/api/notify';

		// 定義請求頭
		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $this->token,
			),
			'body'    => array(
				'message' => $message,
			),
		);

		// 發送POST請求
		$response = wp_remote_post( $url, $args );

		// 處理響應
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			error_log( "Something went wrong: $error_message" );
		}
	}
}

WcOrder::instance();
