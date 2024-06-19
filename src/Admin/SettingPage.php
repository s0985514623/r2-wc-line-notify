<?php
/**
 * 創建後台設定頁面&主要設定功能
 */

namespace R2\WcLineNotify\Admin;

use R2\WcLineNotify\Plugin;

/**
 * LineNotify Entry
 */
final class SettingPage {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Get Base64 svg string
	 *
	 * @var string
	 */
	private $base64_svg;
	/**
	 * Option Data variable
	 *
	 * @var string
	 */
	private $options_data;
	/**
	 * Order Data variable
	 *
	 * @var array
	 */
	private $order_data;

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->get_base64_svg();
		$this->get_options_data();
		$this->get_order_data();
		\add_action( 'admin_init', array( $this, 'r2_register_settings' ) );
		\add_action( 'admin_menu', array( $this, 'r2_add_menu_page' ) );
	}
	/**
	 * Register Settings function
	 */
	public function r2_register_settings() {
		// 註冊設定選項，自動將數據保存到 wp_options 表中key為options_name.
		\register_setting( Plugin::$snake . '_group', Plugin::$snake, array( $this, 'sanitize_data' ) );
		// 新增設定區段.
		add_settings_section(
			Plugin::$snake . '_section', // 區段 ID
			'Line Notify設定', // 區段標題
			array( $this, 'section_text_callback' ), // 顯示區段描述的回呼函數
			Plugin::$kebab // 設定頁面 slug
		);

		// 新增設定欄位=>Token.
		add_settings_field(
			Plugin::$snake . 'token', // 欄位 ID
			'Line Notify 金鑰:', // 欄位標題
			array( $this, 'token_callback' ), // 顯示欄位內容的回呼函數
			Plugin::$kebab, // 設定頁面 slug
			Plugin::$snake . '_section' // 區段 ID
		);

		// 新增設定欄位=>Message.
		add_settings_field(
			Plugin::$snake . '_message', // 欄位 ID
			'新訂單訊息', // 欄位標題
			array( $this, 'message_callback' ), // 顯示欄位內容的回呼函數
			Plugin::$kebab, // 設定頁面 slug
			Plugin::$snake . '_section' // 區段 ID
		);
		// 新增設定欄位=>state.
		add_settings_field(
			Plugin::$snake . '_state', // 欄位 ID
			'訂單狀態通知', // 欄位標題
			array( $this, 'state_callback' ), // 顯示欄位內容的回呼函數
			Plugin::$kebab, // 設定頁面 slug
			Plugin::$snake . '_section' // 區段 ID
		);
	}


	/**
	 * R2_add_menu_page function
	 *
	 * @return void
	 */
	public function r2_add_menu_page() {
		add_menu_page(
			'Line Notify設定',       // 頁面標題
			'Line Notify設定',       // 菜單標題
			'manage_options',       // 權限等級
			Plugin::$kebab,       // 菜單的slug
			array( $this, 'r2_page_content' ), // 回調函數，用於輸出頁面內容
			$this->base64_svg, // 圖標
		);
	}
	/**
	 * Show Page function
	 *
	 * @return void
	 */
	public function r2_page_content() {

		?>
	<div class="wrap">
		<form action="options.php" method="post">
		<?php
		// 輸出安全設置表單欄位
		settings_fields( Plugin::$snake . '_group' );
		// 輸出所有設置區段
		do_settings_sections( Plugin::$kebab );
		// 提交按鈕
		submit_button();
		?>
		</form>
		<div>
			<p>如何取得Line Notify Token？</p>
			<p>1. 登入Line Notify官方網站：<a href="https://notify-bot.line.me/my/">https://notify-bot.line.me/my/</a></p>
			<p>2. 點選「登入」，並使用Line帳號登入</p>
			<p>3. 點選「發行權杖」</p>
			<img src="<?php echo esc_url( Plugin::$url ); ?>\assets\img\line-notify.png" alt="Line Notify Guide" style="max-width:1000px;">
			<p>4. 輸入權杖名稱，並勾選「1對1聊天」，或是任一群組，點選「發行」</p>
			<img src="<?php echo esc_url( Plugin::$url ); ?>\assets\img\line-notify2.png" alt="Line Notify Guide"style="max-width:1000px;">
			<p>5. 複製權杖，並貼上到上方的輸入框中</p>
		</div>
	</div>
		<?php
	}

	/**
	 * Sanitize settings data before save it.
	 *
	 * @param array $input The input data to sanitize.
	 * @return array
	 */
	public function sanitize_data( $input ) {
		$sanitary_values = array();
		if ( isset( $input['token'] ) ) {
			$sanitary_values['token'] = sanitize_text_field( $input['token'] );
		}
		if ( isset( $input['message'] ) ) {
			$sanitary_values['message'] = esc_textarea( $input['message'] );
		}
		if ( isset( $input['state'] ) ) {

			// 檢查 state 是否已經是字符串，如果是則不進行 JSON 編碼
			if ( is_array( $input['state'] ) ) {
				$sanitary_values['state'] = wp_json_encode( $input['state'] );
			} else {
				$sanitary_values['state'] = $input['state'];
			}
		}
		return $sanitary_values;
	}
	/**
	 * Section Text Callback function
	 *
	 * @return void
	 */
	public function section_text_callback() {
		echo '';
	}
	/**
	 * Token Callback function
	 *
	 * @return void
	 */
	public function token_callback() {
		printf(
			'<input type="text" id="%s_token" name="%s[token]" value="%s" /><a href="https://notify-bot.line.me/my/" style="margin-left:4px">創建金鑰</a>',
			esc_attr( Plugin::$snake ),
			esc_attr( Plugin::$snake ),
			isset( $this->options_data['token'] ) ? esc_attr( $this->options_data['token'] ) : ''
		);
	}
	/**
	 * Message Callback function
	 *
	 * @return void
	 */
	public function message_callback() {
		printf(
			'<textarea id="%s_message" name="%s[message]" style="max-width: 580px;height: 200px;" rows="5" class="large-text" maxlength="500"  />%s</textarea>',
			esc_attr( Plugin::$snake ),
			esc_attr( Plugin::$snake ),
			isset( $this->options_data['message'] ) ? esc_attr( $this->options_data['message'] ) : ''
		);
		print( '<p>
		<strong>Shortcode</strong> <br><span class="shortcodeBadge" style="max-width: 500px;display:block;">
				<a href="javascript:void(0)" class="shortcode-code" style="text-decoration: none;">' . join(
			'</a>
				<a href="javascript:void(0)" class="shortcode-code" style="text-decoration: none;">',
			( $this->order_data )// phpcs:ignore
		) . '</a></span>
		</p>' );
	}
	/**
	 * State_callback function
	 *
	 * @return void
	 */
	public function state_callback() {
		$order_status = wc_get_order_statuses();
		$stated       = isset( $this->options_data['state'] ) ? json_decode( $this->options_data['state'] ) : array();
		foreach ( $order_status as $key => $value ) {
			$checked = in_array( $key, $stated, true ) ? 'checked' : '';
			printf(
				'<input type="checkbox" id="%s_state_%s" name="%s[state][]" value="%s" %s>%s<br>',
				esc_attr( Plugin::$snake ),
				esc_attr( $key ),
				esc_attr( Plugin::$snake ),
				esc_attr( $key ),
				esc_attr( $checked ),
				esc_html( $value )
			);
		}
	}
	/**
	 * Get Base64 svg string
	 *
	 * @return string
	 */
	public function get_base64_svg() {
		if ( empty( $this->base64_svg ) ) {
			$svg_file         = Plugin::$dir . '/assets/img/line_icon.svg'; // SVG文件的路径
			$svg_content      = file_get_contents( $svg_file );
			$this->base64_svg = 'data:image/svg+xml;base64,' . base64_encode( $svg_content );
		}
		return $this->base64_svg;
	}
	/**
	 * Get Options Data function
	 *
	 * @return array
	 */
	public function get_options_data() {
		if ( empty( $this->options_data ) ) {
			$this->options_data = get_option( Plugin::$snake );
		}
		return $this->options_data;
	}
	/**
	 * Get Order Data function
	 *
	 * @return array
	 */
	public function get_order_data() {
		if ( empty( $this->order_data ) ) {
			$this->order_data = array(
				'[order_id]',
				'[order_time]',
				'[order_total]',
				'[order_payment]',
				'[order_customer]',
				'[order_phone]',
				'[order_note]',
				'[products]',
			);
		}
		return $this->order_data;
	}
}


