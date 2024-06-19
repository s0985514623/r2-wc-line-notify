<?php
/**
 * WC Order Class
 */

declare(strict_types=1);

namespace R2\WcLineNotify\Admin;

use R2\WcLineNotify\Plugin;
/**
 * WcOrder Entry
 */
final class Action {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Line notify token.
	 *
	 * @var [type]
	 */
	private $token;
	/**
	 * Setting_page Instance
	 *
	 * @var [type]
	 */
	private $setting_page_instance;
	/**
	 * Constructor
	 */
	private function __construct() {
		$this->setting_page_instance = SettingPage::instance();
		$this->token                 = $this->setting_page_instance->get_options_data()['token'] ?? '';
		$state                       = isset( $this->setting_page_instance->get_options_data()['state'] ) ? json_decode( $this->setting_page_instance->get_options_data()['state'], true ) : array();
		// var_dump( $this->setting_page_instance->get_options_data()['state'] );
		// var_dump( ( $state ) );
		$sanitize_state = array_map( array( $this, 'remove_wc_prefix_order_status' ), $state );

		if ( ! empty( $this->token ) ) {
			\add_action( 'woocommerce_new_order', array( $this, 'new_order' ), 10, 2 );
			foreach ( $sanitize_state as $status ) {
				\add_action( 'woocommerce_order_status_' . $status, array( $this, 'woocommerce_order_status_to_action' ), 10, 3 );
			}
		}
	}
	/**
	 * New_order function
	 *
	 * @param int      $order_id The ID of the order.
	 * @param WC_Order $order The order object.
	 */
	public function new_order( $order_id, $order ) {
		// $order          = wc_get_order( $order_id );
		$option_message = $this->setting_page_instance->get_options_data()['message'];

		$product_line = '';
		foreach ( $order->get_items() as $item ) {
			// Get an instance of corresponding the WC_Product object
			$product       = $item->get_product();
			$product_name  = $product->get_name(); // Get the product name
			$item_quantity = $item->get_quantity(); // Get the item quantity
			$item_total    = $item->get_total(); // Get the item line total
			$product_line .= '商品：' . $product_name . ' 數量：' . $item_quantity . '總計：NTD ' . number_format( floatval( $item_total ) ) . "\n";
		}
		$set_order_data = array(
			'order_id'       => $order_id,
			'order_time'     => $order->get_date_created()->date_i18n( 'Y-m-d H:i:s' ),
			'order_total'    => '總計：NTD' . number_format( floatval( $order->get_total() ) ),
			'order_payment'  => $order->get_payment_method_title(),
			'order_customer' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
			'order_phone'    => $order->get_billing_phone(),
			'order_note'     => $order->get_customer_note() ? $order->get_customer_note() : '無',
			'products'       => $product_line,

		);
		$message = $this->replace_placeholders( $option_message, $set_order_data );
		$this->send_line_notify( $message );
	}
	/**
	 * Woocommerce_order_status_to_action function
	 *
	 * @param int      $order_id The order ID.
	 * @param WC_Order $order The order object.
	 * @param array    $status_transition The status transition.
	 * @return void
	 */
	public function woocommerce_order_status_to_action( $order_id, $order, $status_transition ) {
		$message = '訂單編號：' . $order_id . '，狀態已由「' . wc_get_order_status_name( $status_transition['from'] ) . '」變更為「' . wc_get_order_status_name( $status_transition['to'] ) . '」';
		// $order          = new \WC_Order( $order_id );
		// $option_message = $this->setting_page_instance->get_options_data()['message'];

		// $product_line = '';
		// ob_start();
		// print_r( $product_line );
		// error_log( '' . ob_get_clean() );
		// foreach ( $order->get_items() as $item_array ) {
		// $item = new \WC_Order_Item_Product( $item_array );
		// Get an instance of corresponding the WC_Product object
		// $product       = $item->get_product();
		// $product_name  = $product->get_name(); // Get the product name
		// $item_quantity = $item->get_quantity(); // Get the item quantity
		// $item_total    = $item->get_total(); // Get the item line total
		// $product_line .= '商品:' . $product_name . ' 數量:' . $item_quantity . '總計：' . number_format( floatval( $item_total ), 2 ) . "\n";
		// error_log( $product_line );
		// }
		// $set_order_data = array(
		// 'order_id'       => $order_id,
		// 'order_time'     => $order->get_date_created()->date_i18n( 'Y-m-d H:i:s' ),
		// 'order_total'    => $order->get_total(),
		// 'order_payment'  => $order->get_payment_method_title(),
		// 'order_customer' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
		// 'order_phone'    => $order->get_billing_phone(),
		// 'order_note'     => $order->get_customer_note() ? $order->get_customer_note() : '無',
		// 'products'       => $product_line,

		// );
		// $message = $this->replace_placeholders( $option_message, $set_order_data );

		$this->send_line_notify( $message );
	}
	/**
	 * 移除wc_prefix 'wc-' function
	 *
	 * @param string $status The order status.
	 * @return string
	 */
	public function remove_wc_prefix_order_status( $status ) {
		if ( strpos( $status, 'wc-' ) === 0 ) {
			return substr( $status, 3 );
		}
		return $status;
	}
	/**
	 * 動態替換佔位符的函數
	 *
	 * @param string $content The message content.
	 * @param array  $data The order data.
	 * @return string
	 */
	public function replace_placeholders( $content, $data ) {
		foreach ( $data as $key => $value ) {
			// 確保 $value 是字符串類型
			if ( ! is_string( $value ) ) {
				$value = strval( $value );
			}
			$placeholder = '[' . $key . ']';
			$content     = str_replace( $placeholder, $value, $content );
		}
		return $content;
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
