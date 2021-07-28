<?php
/**
 * The file that defines the core plugin class.
 *
 * @link       https://themehigh.com
 * @since      1.5.0
 *
 * @package    woo-checkout-field-editor-pro
 * @subpackage woo-checkout-field-editor-pro/includes
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFD')):

class THWCFD {
	protected $plugin_name;
	protected $version;
	const TEXT_DOMAIN = 'woo-checkout-field-editor-pro';

	public function __construct() {
		if(defined( 'THWCFD_VERSION')){
			$this->version = THWCFD_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woo-checkout-field-editor-pro';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		add_action('init', array($this, 'init'));
	}

	private function load_dependencies() {
		if(!function_exists('is_plugin_active')){
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-thwcfd-autoloader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-thwcfd-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-thwcfd-public-checkout.php';

		// require_once THWCFD_PATH . 'classes/class-thwcfd-utils.php';
		// require_once THWCFD_PATH . 'classes/class-thwcfd-settings.php';
		// require_once THWCFD_PATH . 'classes/class-thwcfd-settings-general.php';
		// require_once THWCFD_PATH . 'classes/class-thwcfd-settings-advanced.php';
		// require_once THWCFD_PATH . 'classes/class-thwcfd-checkout.php';
	}

	private function set_locale() {
		add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
	}

	public function load_plugin_textdomain(){
		$locale = apply_filters('plugin_locale', get_locale(), self::TEXT_DOMAIN);
	
		load_textdomain(self::TEXT_DOMAIN, WP_LANG_DIR.'/woo-checkout-field-editor-pro/'.self::TEXT_DOMAIN.'-'.$locale.'.mo');
		load_plugin_textdomain(self::TEXT_DOMAIN, false, dirname(THWCFD_BASE_NAME) . '/languages/');
	}

	private function define_admin_hooks() {
		$plugin_admin = new THWCFD_Admin( $this->get_plugin_name(), $this->get_version() );

		add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles_and_scripts'));
		add_action('admin_menu', array($plugin_admin, 'admin_menu'));
		add_filter('woocommerce_screen_ids', array($plugin_admin, 'add_screen_id'));
		add_filter('plugin_action_links_'.THWCFD_BASE_NAME, array($plugin_admin, 'plugin_action_links'));
		//add_filter('plugin_row_meta', array($plugin_admin, 'plugin_row_meta'), 10, 2);
		add_action('wp_ajax_dismiss_thwcfd_review_request_notice', array($plugin_admin, 'dismiss_thwcfd_review_request_notice'));
		add_action('wp_ajax_skip_thwcfd_review_request_notice', array($plugin_admin, 'skip_thwcfd_review_request_notice'));

		$general_settings = new THWCFD_Admin_Settings_General();
		add_action('after_setup_theme', array($general_settings, 'define_admin_hooks'));
	}

	private function define_public_hooks() {
		//if(!is_admin() || (defined( 'DOING_AJAX' ) && DOING_AJAX)){
			$plugin_checkout = new THWCFD_Public_Checkout( $this->get_plugin_name(), $this->get_version() );
			add_action('wp_enqueue_scripts', array($plugin_checkout, 'enqueue_styles_and_scripts'));
			add_action('after_setup_theme', array($plugin_checkout, 'define_public_hooks'));
		//}
	}

	public function init(){
		$this->define_constants();
	}
	
	private function define_constants(){
		!defined('THWCFD_ASSETS_URL_ADMIN') && define('THWCFD_ASSETS_URL_ADMIN', THWCFD_URL . 'admin/assets/');
		!defined('THWCFD_ASSETS_URL_PUBLIC') && define('THWCFD_ASSETS_URL_PUBLIC', THWCFD_URL . 'public/assets/');
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}

endif;