<?php
/**
 * Woo Checkout Field Editor Public
 *
 * @link       https://themehigh.com
 * @since      1.3.6
 *
 * @package    woo-checkout-field-editor-pro
 * @subpackage woo-checkout-field-editor-pro/public
 */

defined( 'ABSPATH' ) || exit;

if(!class_exists('THWCFD_Public_Checkout')) :

class THWCFD_Public_Checkout {
	public function __construct() {
		
	}

	public function enqueue_styles_and_scripts() {
		if(is_checkout()){
			$in_footer = apply_filters( 'thwcfd_enqueue_script_in_footer', true );
			$deps = array('jquery', 'selectWoo');
			
			$debug_mode = apply_filters('thwcfd_debug_mode', false);
			$suffix = $debug_mode ? '' : '.min';
			wp_register_script('thwcfd-checkout-script', THWCFD_ASSETS_URL_PUBLIC.'js/thwcfd-public' . $suffix . '.js', $deps, THWCFD_VERSION, $in_footer);
			wp_enqueue_script('thwcfd-checkout-script');
			wp_enqueue_style('thwcfd-checkout-style', THWCFD_ASSETS_URL_PUBLIC . 'css/thwcfd-public' . $suffix . '.css', THWCFD_VERSION);

			$wcfd_var = array(
				'is_override_required' => $this->is_override_required_prop(),
			);
			wp_localize_script('thwcfd-checkout-script', 'thwcfd_public_var', $wcfd_var);
		}
	}

	public function define_public_hooks(){
		$hp_billing_fields  = apply_filters('thwcfd_billing_fields_priority', 1000);
		$hp_shipping_fields = apply_filters('thwcfd_shipping_fields_priority', 1000);
		$hp_checkout_fields = apply_filters('thwcfd_checkout_fields_priority', 1000);

		add_filter('woocommerce_enable_order_notes_field', array($this, 'enable_order_notes_field'), 1000);

		add_filter('woocommerce_get_country_locale_default', array($this, 'prepare_country_locale'));
		add_filter('woocommerce_get_country_locale_base', array($this, 'prepare_country_locale'));
		add_filter('woocommerce_get_country_locale', array($this, 'get_country_locale'));

		add_filter('woocommerce_billing_fields', array($this, 'billing_fields'), $hp_billing_fields, 2);
		add_filter('woocommerce_shipping_fields', array($this, 'shipping_fields'), $hp_shipping_fields, 2);
		add_filter('woocommerce_checkout_fields', array($this, 'checkout_fields'), $hp_checkout_fields);

		add_action('woocommerce_after_checkout_validation', array($this, 'checkout_fields_validation'), 10, 2);
		add_action('woocommerce_checkout_update_order_meta', array($this, 'checkout_update_order_meta'), 10, 2);

		add_filter('woocommerce_email_order_meta_fields', array($this, 'display_custom_fields_in_emails'), 10, 3);
		add_action('woocommerce_order_details_after_order_table', array($this, 'order_details_after_customer_details'), 20, 1);
	}

	/**
	 * Hide Additional Fields title if no fields available.
	 */
	public function enable_order_notes_field() {
		$additional_fields = get_option('wc_fields_additional');
		if(is_array($additional_fields)){
			$enabled = 0;
			foreach($additional_fields as $field){
				if($field['enabled']){
					$enabled++;
				}
			}
			return $enabled > 0 ? true : false;
		}
		return true;
	}

	private  function get_locale_override_value($key, $settings=false, $default=false){
		$value = '';

		if($settings){
			$value = THWCFD_Utils::get_setting_value($settings, $key);
		}else{
			$value = THWCFD_Utils::get_settings($key);
		}

		return $value === 'undefined' ? $default : $value;
	}

	public function is_override_label($settings=false){
		$override_label = $this->get_locale_override_value('enable_label_override', $settings, true);
		$override_label = $override_label ? true : false;
		return apply_filters('thwcfd_address_field_override_label', $override_label);
	}

	public function is_override_placeholder($settings=false){
		$override_ph = $this->get_locale_override_value('enable_placeholder_override', $settings, true);
		$override_ph = $override_ph ? true : false;
		return apply_filters('thwcfd_address_field_override_placeholder', $override_ph);
	}

	public function is_override_class($settings=false){
		$override_class = $this->get_locale_override_value('enable_class_override', $settings, false);
		$override_class = $override_class ? true : false;
		return apply_filters('thwcfd_address_field_override_class', $override_class);
	}

	public function is_override_priority($settings=false){
		$override_priority = $this->get_locale_override_value('enable_priority_override', $settings, true);
		$override_priority = $override_priority ? true : false;
		return apply_filters('thwcfd_address_field_override_priority', $override_priority);
	}

	public function is_override_required_prop($settings=false){
		$override_required = $this->get_locale_override_value('enable_required_override', $settings, false);
		$override_required = $override_required ? true : false;
		return apply_filters('thwcfd_address_field_override_required', $override_required);
	}
	
	public function prepare_country_locale($fields) {
		if(is_array($fields)){
			$settings = THWCFD_Utils::get_advanced_settings();

			$override_label    = $this->is_override_label($settings);
			$override_ph       = $this->is_override_placeholder($settings);
			$override_class    = $this->is_override_class($settings);
			$override_priority = $this->is_override_priority($settings);

			foreach($fields as $key => $props){
				if($override_label && isset($props['label'])){
					unset($fields[$key]['label']);
				}

				if($override_ph && isset($props['placeholder'])){
					unset($fields[$key]['placeholder']);
				}

				if($override_class && isset($props['class'])){
					unset($fields[$key]['class']);
				}
				
				if($override_priority && isset($props['priority'])){
					unset($fields[$key]['priority']);
				}
			}
		}
		return $fields;
	}

	public function get_country_locale($locale) {
		$countries = array_merge( WC()->countries->get_allowed_countries(), WC()->countries->get_shipping_countries() );
		$countries = array_keys($countries);

		if(is_array($locale) && is_array($countries)){
			foreach($countries as $country){
				if(isset($locale[$country])){
					$locale[$country] = $this->prepare_country_locale($locale[$country]);
				}
			}
		}
		return $locale;
	}
	
	public function billing_fields($fields, $country){
		if(is_wc_endpoint_url('edit-address')){
			return $fields;
		}else{
			return $this->prepare_address_fields(get_option('wc_fields_billing'), $fields, 'billing', $country);
		}
	}

	public function shipping_fields($fields, $country){
		if(is_wc_endpoint_url('edit-address')){
			return $fields;
		}else{
			return $this->prepare_address_fields(get_option('wc_fields_shipping'), $fields, 'shipping', $country);
		}
	}
	
	public function checkout_fields($fields) {
		$additional_fields = get_option('wc_fields_additional');

		if(is_array($additional_fields)){
			if(isset($fields['order']) && is_array($fields['order'])){
				$fields['order'] = $additional_fields + $fields['order'];
			}

			// check if order_comments is enabled/disabled
			if(isset($additional_fields['order_comments']['enabled']) && !$additional_fields['order_comments']['enabled']){
				unset($fields['order']['order_comments']);
			}
		}
				
		if(isset($fields['order']) && is_array($fields['order'])){
			$fields['order'] = $this->prepare_checkout_fields($fields['order'], false);
		}

		if(isset($fields['order']) && !is_array($fields['order'])){
			unset($fields['order']);
		}
		
		return $fields;
	}

	public function prepare_address_fields($fieldset, $original_fieldset = false, $sname = 'billing', $country){
		if(is_array($fieldset) && !empty($fieldset)) {
			$locale = WC()->countries->get_country_locale();

			if(isset($locale[ $country ]) && is_array($locale[ $country ])) {
				$override_required_prop = $this->is_override_required_prop();
				$states = WC()->countries->get_states( $country );

				foreach($locale[ $country ] as $key => $value){
					$fname = $sname.'_'.$key;

					if(is_array($value) && isset($fieldset[$fname])){
						if(!$override_required_prop && isset($value['required'])){
							$fieldset[$fname]['required'] = $value['required'];
						}

						if($key === 'state'){
							if(is_array($states) && empty($states)){
								$fieldset[$fname]['hidden'] = true;
							}
						}else{
							if(isset($value['hidden'])){
								$fieldset[$fname]['hidden'] = $value['hidden'];
							}
						}
					}
				}
			}

			$fieldset = $this->prepare_checkout_fields($fieldset, $original_fieldset);
			return $fieldset;
		}else {
			return $original_fieldset;
		}
	}

	public function prepare_checkout_fields($fields, $original_fields) {
		if(is_array($fields) && !empty($fields)) {
			$override_required_prop = $this->is_override_required_prop();

			foreach($fields as $name => $field) {
				if(THWCFD_Utils::is_enabled($field)) {
					$new_field = false;
					$allow_override = apply_filters('thwcfd_allow_default_field_override_'.$name, false);
					
					if($original_fields && isset($original_fields[$name]) && !$allow_override){
						$new_field = $original_fields[$name];

						$class     = isset($field['class']) && is_array($field['class']) ? $field['class'] : array();
						$required  = isset($field['required']) ? $field['required'] : 0;
						$is_hidden = isset($field['hidden']) && $field['hidden'] ? true : false;

						if($is_hidden){
							$new_field['hidden'] = $field['hidden'];
							$new_field['required'] = false;
						}else{
							if($override_required_prop){
								$new_field['required'] = $required;
							}
						}

						if($override_required_prop){
							if($required){
								$class[] = 'thwcfd-required';
							}else{
								$class[] = 'thwcfd-optional';
							}
						}
						
						$new_field['label'] = isset($field['label']) ? $field['label'] : '';
						$new_field['default'] = isset($field['default']) ? $field['default'] : '';
						$new_field['placeholder'] = isset($field['placeholder']) ? $field['placeholder'] : '';
						$new_field['class'] = $class;
						$new_field['label_class'] = isset($field['label_class']) && is_array($field['label_class']) ? $field['label_class'] : array();
						$new_field['validate'] = isset($field['validate']) && is_array($field['validate']) ? $field['validate'] : array();
						$new_field['priority'] = isset($field['priority']) ? $field['priority'] : '';
					} else {
						$new_field = $field;
					}

					$type = isset($new_field['type']) ? $new_field['type'] : 'text';

					$new_field['class'][] = 'thwcfd-field-wrapper';
					$new_field['class'][] = 'thwcfd-field-'.$type;
					
					if($type === 'select' || $type === 'radio'){
						if(isset($new_field['options'])){
							$options_arr = THWCFD_Utils::prepare_field_options($new_field['options']);
							$options = array();
							foreach($options_arr as $key => $value) {
								$options[$key] = __($value, 'woo-checkout-field-editor-pro');
							}
							$new_field['options'] = $options;
						}
					}

					if($type === 'select' && apply_filters('thwcfd_enable_select2_for_select_fields', true)){
						$new_field['input_class'][] = 'thwcfd-enhanced-select';
					}
					
					if(isset($new_field['label'])){
						$new_field['label'] = THWCFD_Utils::t($new_field['label']);
					}

					if(isset($new_field['placeholder'])){
						$new_field['placeholder'] = THWCFD_Utils::t($new_field['placeholder']);
					}
					
					$fields[$name] = $new_field;
				}else{
					unset($fields[$name]);
				}
			}								
			return $fields;
		}else {
			return $original_fields;
		}
	}

	/*************************************
	----- Validate & Update - START ------
	*************************************/
	public function checkout_fields_validation($posted, $errors){
		$checkout_fields = WC()->checkout->checkout_fields;
		
		foreach($checkout_fields as $fieldset_key => $fieldset){
			if($this->maybe_skip_fieldset($fieldset_key, $posted)){
				continue;
			}
			
			foreach($fieldset as $key => $field) {
				if(isset($posted[$key]) && !THWCFD_Utils::is_blank($posted[$key])){
					$this->validate_custom_field($field, $posted, $errors);
				}
			}
		}
	}

	public function validate_custom_field($field, $posted, $errors=false, $return=false){
		$err_msgs = array();
		$key = isset($field['name']) ? $field['name'] : false;
		
		if($key){
			$value = isset($posted[$key]) ? $posted[$key] : '';
			$validators = isset($field['validate']) ? $field['validate'] : '';

			if($value && is_array($validators) && !empty($validators)){					
				foreach($validators as $vname){
					$err_msg = '';
					$flabel = isset($field['label']) ? THWCFD_Utils::t($field['label']) : $key;

					if($vname === 'number'){
						if(!is_numeric($value)){
							$err_msg = '<strong>'. $flabel .'</strong> '. THWCFD_Utils::t('is not a valid number.');	
						}
					}

					if($err_msg){
						if($errors || !$return){
							$this->add_validation_error($err_msg, $errors);
						}
						$err_msgs[] = $err_msg;
					}
				}
			}
		}
		return !empty($err_msgs) ? $err_msgs : false;
	}

	public function add_validation_error($msg, $errors=false){
		if($errors){
			$errors->add('validation', $msg);
		}else if(THWCFD_Utils::woo_version_check('2.3.0')){
			wc_add_notice($msg, 'error');
		} else {
			WC()->add_error($msg);
		}
	}

	public function checkout_update_order_meta($order_id, $posted){
		$types = array('billing', 'shipping', 'additional');

		foreach($types as $type){
			if($this->maybe_skip_fieldset($type, $posted)){
				continue;
			}

			$fields = THWCFD_Utils::get_fields($type);
			
			foreach($fields as $name => $field){
				if(THWCFD_Utils::is_active_custom_field($field) && isset($posted[$name])){
					$value = wc_clean($posted[$name]);
					if($value){
						update_post_meta($order_id, $name, $value);
					}
				}
			}
		}
	}

	private function maybe_skip_fieldset( $fieldset_key, $data ) {
        $ship_to_different_address = isset($data['ship_to_different_address']) ? $data['ship_to_different_address'] : false;
        $ship_to_destination = get_option( 'woocommerce_ship_to_destination' );

        if ( 'shipping' === $fieldset_key && ( ! $ship_to_different_address || ! WC()->cart->needs_shipping_address() ) ) {
            return  $ship_to_destination != 'billing_only' ? true : false;
        }
        return false;
    }
	
	/****************************************
	----- Display Field Values - START ------
	*****************************************/
	/**
	 * Display custom fields in emails
	 */
	public function display_custom_fields_in_emails($ofields, $sent_to_admin, $order){
		$custom_fields = array();
		$fields = THWCFD_Utils::get_checkout_fields();

		// Loop through all custom fields to see if it should be added
		foreach( $fields as $key => $field ) {
			if(isset($field['show_in_email']) && $field['show_in_email']){
				$order_id = THWCFD_Utils::get_order_id($order);
				$value = get_post_meta( $order_id, $key, true );
				
				if($value){
					$label = isset($field['label']) && $field['label'] ? $field['label'] : $key;
					$label = esc_attr($label);
					$value = THWCFD_Utils::get_option_text($field, $value);
					
					$custom_field = array();
					$custom_field['label'] = THWCFD_Utils::t($label);
					$custom_field['value'] = $value;
					
					$custom_fields[$key] = $custom_field;
				}
			}
		}

		return array_merge($ofields, $custom_fields);
	}	
	
	/**
	 * Display custom checkout fields on view order pages
	 */
	public function order_details_after_customer_details($order){
		$order_id = THWCFD_Utils::get_order_id($order);
		$fields = THWCFD_Utils::get_checkout_fields($order);
		
		if(is_array($fields) && !empty($fields)){
			$fields_html = '';
			// Loop through all custom fields to see if it should be added
			foreach($fields as $key => $field){			
				if(THWCFD_Utils::is_active_custom_field($field) && isset($field['show_in_order']) && $field['show_in_order']){
					$value = get_post_meta( $order_id, $key, true );
					
					if($value){
						$label = isset($field['label']) && $field['label'] ? THWCFD_Utils::t($field['label']) : $key;

						$label = esc_attr($label);
						//$value = wptexturize($value);
						$value = THWCFD_Utils::get_option_text($field, $value);
						
						if(is_account_page()){
							if(apply_filters( 'thwcfd_view_order_customer_details_table_view', true )){
								$fields_html .= '<tr><th>'. $label .':</th><td>'. $value .'</td></tr>';
							}else{
								$fields_html .= '<br/><dt>'. $label .':</dt><dd>'. $value .'</dd>';
							}
						}else{
							if(apply_filters( 'thwcfd_thankyou_customer_details_table_view', true )){
								$fields_html .= '<tr><th>'. $label .':</th><td>'. $value .'</td></tr>';
							}else{
								$fields_html .= '<br/><dt>'. $label .':</dt><dd>'. $value .'</dd>';
							}
						}
					}
				}
			}
			
			if($fields_html){
				do_action( 'thwcfd_order_details_before_custom_fields_table', $order ); 
				?>
				<table class="woocommerce-table woocommerce-table--custom-fields shop_table custom-fields">
					<?php
						echo $fields_html;
					?>
				</table>
				<?php
				do_action( 'thwcfd_order_details_after_custom_fields_table', $order ); 
			}
		}
	}
	/*****************************************
	----- Display Field Values - END --------
	*****************************************/
}

endif;
