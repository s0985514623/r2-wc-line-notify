<?php
/**
 * Bootstrap
 */

namespace R2\WcLineNotify;

// autoload
use R2\WcLineNotify\Admin\SettingPage;
use R2\WcLineNotify\Admin\Action;

use R2\WcLineNotify\Plugin;
/**
 * Class Bootstrap
 */
final class Bootstrap {
	use \J7\WpUtils\Traits\SingletonTrait;


	/**
	 * Constructor
	 */
	private function __construct() {

		\add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_script' ), 99 );
		// \add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue_script' ), 99 );
		SettingPage::instance();
		// Action::instance();
	}

	/**
	 * Admin Enqueue script
	 * You can load the script on demand
	 *
	 * @param string $hook current page hook
	 *
	 * @return void
	 */
	public function admin_enqueue_script( $hook ): void {
		// Only load on plugin page
		$screen = get_current_screen();
		if ( $screen->id == 'toplevel_page_' . Plugin::$kebab ) {
			$this->enqueue_script();
		}
	}


	/**
	 * Front-end Enqueue script
	 * You can load the script on demand
	 *
	 * @return void
	 */
	public function frontend_enqueue_script(): void {
		$this->enqueue_script();
	}

	/**
	 * Enqueue script
	 * You can load the script on demand
	 *
	 * @return void
	 */
	public function enqueue_script(): void {

		\wp_enqueue_script(
			Plugin::$kebab,
			Plugin::$url . '/js/dist/index.js',
			array( 'jquery' ),
			Plugin::$version,
			array(
				'in_footer' => true,
				'strategy'  => 'async',
			)
		);

		\wp_enqueue_style(
			Plugin::$kebab,
			Plugin::$url . '/js/dist/assets/css/index.css',
			array(),
			Plugin::$version
		);
	}
}
