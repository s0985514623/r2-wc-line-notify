<?php
/**
 * 創建後台設定頁面
 */

declare(strict_types=1);

namespace R2\WcLineNotify\SettingPage;

use R2\WcLineNotify\Plugin;

/**
 * SettingPage Entry
 */
final class SettingPage {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'admin_menu', array( $this, 'r2_line_notify_menu_page' ) );
	}
	/**
	 * R2_line_notify_menu_page function
	 *
	 * @return void
	 */
	public function r2_line_notify_menu_page() {
		add_menu_page(
			'Line Notify設定',       // 頁面標題
			'Line Notify設定',       // 菜單標題
			'manage_options',       // 權限等級
			'r2-wc-line-notify',       // 菜單的slug
			array( $this, 'r2_line_notify_page_content' ), // 回調函數，用於輸出頁面內容
		);
	}
	/**
	 * Show Page function
	 *
	 * @return void
	 */
	public function r2_line_notify_page_content() {
		// 如果用戶點擊了保存按鈕，則更新選項
		if ( isset( $_POST['r2_line_notify_save'] ) && isset( $_POST['r2_line_notify_token'] ) ) {
			// 驗證 nonce
			// phpcs:ignore
			if ( ! isset( $_POST['r2_line_notify_nonce'] ) || ! wp_verify_nonce( $_POST['r2_line_notify_nonce'], 'r2_line_notify_save' ) ) {
				// 如果驗證失敗，顯示錯誤訊息並退出
				wp_die( 'Nonce verification failed' );
			}
			// phpcs:ignore
			$r2_line_notify_token = sanitize_text_field( $_POST['r2_line_notify_token'] ); // 獲取表單提交的值
			update_option( 'r2_line_notify_token', $r2_line_notify_token ); // 更新選項

			echo '<div class="updated"><p>設置已保存。</p></div>';
		}

		?>
	<div class="pageWrap">
		<h1>Line Notify設定</h1>
		<form method="post" action="">
			<div class="pageSection">
		<?php wp_nonce_field( 'r2_line_notify_save', 'r2_line_notify_nonce' ); ?>
				<div><span style="display:block; width: 180px;">Line Notify Token:</span></div>
				<div><input type="text" name="r2_line_notify_token"  value="<?php echo esc_attr( get_option( 'r2_line_notify_token' ) ); ?>"></div>
			</div>
			<div class="pageSection">
				<button type="submit" name="r2_line_notify_save" class="button-primary">保存</button> <!-- 添加保存按钮 -->
			</div>
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
}

SettingPage::instance();
