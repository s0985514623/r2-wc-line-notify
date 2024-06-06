<?php
/**
 * Plugin Name:       R2 WC Line 訂單通知
 * Plugin URI:        https://github.com/s0985514623/r2-wc-line-notify
 * Description:       這是一個簡單的WC訂單Line通知，當有新訂單以及訂單狀態改變時發送。
 * Version:           1.0.2
 * Requires at least: 5.7
 * Requires PHP:      8.
 * Author:            R2
 * Author URI:        https://github.com/s0985514623
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       my_plugin
 * Domain Path:       /languages
 * Tags:
 */

declare (strict_types = 1);

namespace R2\WcLineNotify;

if ( ! \class_exists( 'R2\WcLineNotify\Plugin' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';

	/**
		* Class Plugin
		*/
	final class Plugin {
		use \J7\WpUtils\Traits\PluginTrait;
		use \J7\WpUtils\Traits\SingletonTrait;

		/**
		 * Constructor
		 */
		public function __construct() {
			require_once __DIR__ . '/inc/class/class-bootstrap.php';

			// $this->required_plugins = array(
			// array(
			// 'name'     => 'WooCommerce',
			// 'slug'     => 'woocommerce',
			// 'required' => true,
			// 'version'  => '7.6.0',
			// ),
			// array(
			// 'name'     => 'WP Toolkit',
			// 'slug'     => 'wp-toolkit',
			// 'source'   => 'Author URL/wp-toolkit/releases/latest/download/wp-toolkit.zip',
			// 'required' => true,
			// ),
			// );

			$this->init(
				array(
					'app_name'    => 'R2 WC Line Notify',
					'github_repo' => 'https://github.com/s0985514623/r2-wc-line-notify',
					'callback'    => array( '\R2\WcLineNotify\Bootstrap', 'instance' ),
				)
			);
		}
	}

	Plugin::instance();
}
