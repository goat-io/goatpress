<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://themehigh.com
 *
 * @package    woo-checkout-field-editor-pro
 * @subpackage woo-checkout-field-editor-pro/admin
 */

if(!defined('WPINC')){	die; }

if(!class_exists('THWCFD_Admin')):
 
class THWCFD_Admin {
	private $plugin_name;
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.9.0
	 * @param    string    $plugin_name       The name of this plugin.
	 * @param    string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}
	
	public function enqueue_styles_and_scripts($hook) {
		if(strpos($hook, 'page_checkout_form_designer') !== false) {
			$debug_mode = apply_filters('thwcfd_debug_mode', false);
			$suffix = $debug_mode ? '' : '.min';
			
			$this->enqueue_styles($suffix);
			$this->enqueue_scripts($suffix);
		}
	}
	
	private function enqueue_styles($suffix) {
		wp_enqueue_style('woocommerce_admin_styles');
		wp_enqueue_style('thwcfd-admin-style', THWCFD_ASSETS_URL_ADMIN . 'css/thwcfd-admin'. $suffix .'.css', $this->version);
	}

	private function enqueue_scripts($suffix) {
		$deps = array('jquery', 'jquery-ui-dialog', 'jquery-ui-sortable', 'jquery-tiptip', 'woocommerce_admin', 'selectWoo', 'wp-color-picker');
			
		wp_enqueue_script('thwcfd-admin-script', THWCFD_ASSETS_URL_ADMIN . 'js/thwcfd-admin'. $suffix .'.js', $deps, $this->version, false);
	}

	public function wcfd_capability() {
		$allowed = array('manage_woocommerce', 'manage_options');
		$capability = apply_filters('thwcfd_required_capability', 'manage_woocommerce');

		if(!in_array($capability, $allowed)){
			$capability = 'manage_woocommerce';
		}
		return $capability;
	}
	
	public function admin_menu() {
		$capability = $this->wcfd_capability();
		$this->screen_id = add_submenu_page('woocommerce', __('WooCommerce Checkout Field Editor', 'woo-checkout-field-editor-pro'), __('Checkout Form', 'woo-checkout-field-editor-pro'), $capability, 'checkout_form_designer', array($this, 'output_settings'));
	}
	
	public function add_screen_id($ids){
		$ids[] = 'woocommerce_page_checkout_form_designer';
		$ids[] = strtolower(__('WooCommerce', 'woo-checkout-field-editor-pro')) .'_page_checkout_form_designer';

		return $ids;
	}

	public function plugin_action_links($links) {
		$settings_link = '<a href="'.admin_url('admin.php?page=checkout_form_designer').'">'. __('Settings', 'woo-checkout-field-editor-pro') .'</a>';
		array_unshift($links, $settings_link);
		$pro_link = '<a style="color:green; font-weight:bold" target="_blank" href="https://www.themehigh.com/product/woocommerce-checkout-field-editor-pro/?utm_source=free&utm_medium=plugin_action_link&utm_campaign=wcfe_upgrade_link">'. __('Get Pro', 'woo-checkout-field-editor-pro') .'</a>';
		array_push($links,$pro_link);
		return $links;
	}

	private function output_review_request_link(){
		$is_dismissed = get_transient('thwcfd_review_request_notice_dismissed');
		if($is_dismissed){
			return;
		}

		$is_skipped = get_transient('thwcfd_skip_review_request_notice');
		if($is_skipped){
			return;
		}

		$thwcfd_since = get_option('thwcfd_since');
		if(!$thwcfd_since){
			$now = time();
			update_option('thwcfd_since', $now, 'no' );
		}else{
			$now = time();
			$diff_seconds = $now - $thwcfd_since;

			if($diff_seconds > apply_filters('thwcfd_show_review_request_notice_after', 10 * DAY_IN_SECONDS)){
				$this->render_review_request_notice();
			}
		}
		//If you find this plugin useful please show your support and rate it ★★★★★ on WordPress.org - much appreciated! :)
	}

	private function render_review_request_notice(){
		?>
		<div id="thwcfd_review_request_notice" class="notice notice-info is-dismissible  thpladmin-notice" data-nonce="<?php echo wp_create_nonce( 'thwcfd_review_request_notice'); ?>" data-action="dismiss_thwcfd_review_request_notice" style="display:none">
			<h3>
				Just wanted to say thank you for using Checkout Field Editor plugin in your store.
			</h3>
			<p>We hope you had a great experience. Please leave us with your feedback to serve best to you and others. Cheers!</p>
			<p class="action-row">
		        <button type="button" class="button button-primary" onclick="window.open('https://wordpress.org/support/plugin/woo-checkout-field-editor-pro/reviews?rate=5#new-post', '_blank')">Review Now</button>
		        <button type="button" class="button" onclick="thwcfdHideReviewRequestNotice(this)">Remind Me Later</button>
            	<span class="logo"><a target="_blank" href="https://www.themehigh.com">
                	<img src="<?php echo THWCFD_ASSETS_URL_ADMIN ?>css/logo.svg" />
                </a></span>

			</p>
		</div>
		<?php
	}

	public function get_current_tab(){
		return isset( $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : 'fields';
	}
	
	public function output_settings(){
		echo '<div class="wrap">';
		echo '<h2></h2>';
		$this->output_review_request_link();

		$tab = $this->get_current_tab();

		echo '<div class="thwcfd-wrap">';
		if($tab === 'advanced_settings'){			
			$advanced_settings = THWCFD_Admin_Settings_Advanced::instance();	
			$advanced_settings->render_page();
		}elseif($tab === 'pro'){
			$pro_details = THWCFD_Admin_Settings_Pro::instance();	
			$pro_details->render_page();
		}else{
			$general_settings = THWCFD_Admin_Settings_General::instance();	
			$general_settings->init();
		}
		echo '</div>';
		echo '</div>';
	}

	public function dismiss_thwcfd_review_request_notice(){
		if(! check_ajax_referer( 'thwcfd_review_request_notice', 'security' )){
			die();
		}
		set_transient('thwcfd_review_request_notice_dismissed', true, apply_filters('thwcfd_dismissed_review_request_notice_lifespan', 1 * YEAR_IN_SECONDS));
	}

	public function skip_thwcfd_review_request_notice(){
		if(! check_ajax_referer( 'thwcfd_review_request_notice', 'security' )){
			die();
		}
		set_transient('thwcfd_skip_review_request_notice', true, apply_filters('thwcfd_skip_review_request_notice_lifespan', 1 * DAY_IN_SECONDS));
	}

}

endif;