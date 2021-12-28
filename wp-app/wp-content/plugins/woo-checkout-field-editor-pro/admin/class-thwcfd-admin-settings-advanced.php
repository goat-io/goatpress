<?php
/**
 * The admin advanced settings page functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.4.4
 *
 * @package    woo-checkout-field-editor-pro
 * @subpackage woo-checkout-field-editor-pro/admin
 */

if(!defined('WPINC')){	die; }

if(!class_exists('THWCFD_Admin_Settings_Advanced')):

class THWCFD_Admin_Settings_Advanced extends THWCFD_Admin_Settings{
	protected static $_instance = null;
	protected $tabs = '';

	private $settings_fields = NULL;
	private $cell_props = array();
	private $cell_props_CB = array();

	public function __construct() {
		parent::__construct();
		
		$this->page_id = 'advanced_settings';
		$this->init_constants();
	}
	
	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function init_constants(){
		$this->cell_props = array( 
			'label_cell_props' => 'class="label"', 
			'input_cell_props' => 'class="field"',
			'input_width' => '260px',
			'label_cell_th' => true
		);

		$this->cell_props_CB = array( 
			'label_props' => 'style="margin-right: 40px;"', 
		);
		
		$this->settings_fields = $this->get_advanced_settings_fields();
	}

	public function get_advanced_settings_fields(){
		return array(
			'enable_label_override' => array(
				'name'=>'enable_label_override', 'label'=>__('Enable label override for address fields.', 'woo-checkout-field-editor-pro'), 'type'=>'checkbox', 'value'=>'1', 'checked'=>1
			),
			'enable_placeholder_override' => array(
				'name'=>'enable_placeholder_override', 'label'=>__('Enable placeholder override for address fields.', 'woo-checkout-field-editor-pro'), 'type'=>'checkbox', 'value'=>'1', 'checked'=>1
			),
			'enable_class_override' => array(
				'name'=>'enable_class_override', 'label'=>__('Enable class override for address fields.', 'woo-checkout-field-editor-pro'), 'type'=>'checkbox', 'value'=>'1', 'checked'=>0
			),
			'enable_priority_override' => array(
				'name'=>'enable_priority_override', 'label'=>__('Enable priority override for address fields.', 'woo-checkout-field-editor-pro'), 'type'=>'checkbox', 'value'=>'1', 'checked'=>1
			),
			'enable_required_override' => array(
				'name'=>'enable_required_override', 'label'=>__('Enable required validation override for address fields.', 'woo-checkout-field-editor-pro'), 'type'=>'checkbox', 'value'=>'1', 'checked'=>0
			),
		);
	}

	public function render_page(){
		$this->render_tabs();
		$this->render_content();
	}
		
	public function save_advanced_settings($settings){
		$result = update_option(THWCFD_Utils::OPTION_KEY_ADVANCED_SETTINGS, $settings, 'no');
		return $result;
	}
	
	private function reset_settings(){
		$nonse = isset($_REQUEST['thwcfd_security_advanced_settings']) ? $_REQUEST['thwcfd_security_advanced_settings'] : false;
		$capability = THWCFD_Utils::wcfd_capability();
		if(!wp_verify_nonce($nonse, 'thwcfd_advanced_settings') || !current_user_can($capability)){
			die();
		}

		delete_option(THWCFD_Utils::OPTION_KEY_ADVANCED_SETTINGS);
		$this->print_notices(__('Settings successfully reset.', 'woo-checkout-field-editor-pro'), 'updated', false);
	}
	
	private function save_settings(){
		$nonse = isset($_REQUEST['thwcfd_security_advanced_settings']) ? $_REQUEST['thwcfd_security_advanced_settings'] : false;
		$capability = THWCFD_Utils::wcfd_capability();
		if(!wp_verify_nonce($nonse, 'thwcfd_advanced_settings') || !current_user_can($capability)){
			die();
		}
		
		$settings = array();
		
		foreach( $this->settings_fields as $name => $field ) {
			$value = '';
			
			if($field['type'] === 'checkbox'){
				$value = !empty( $_POST['i_'.$name] ) ? '1' : '';

			}else if($field['type'] === 'multiselect_grouped'){
				$value = !empty( $_POST['i_'.$name] ) ? $_POST['i_'.$name] : '';
				$value = is_array($value) ? implode(',', wc_clean(wp_unslash($value))) : wc_clean(wp_unslash($value));

			}else if($field['type'] === 'text' || $field['type'] === 'textarea'){
				$value = !empty( $_POST['i_'.$name] ) ? $_POST['i_'.$name] : '';
				$value = !empty($value) ? wc_clean( wp_unslash($value)) : '';

			}else{
				$value = !empty( $_POST['i_'.$name] ) ? $_POST['i_'.$name] : '';
				$value = !empty($value) ? wc_clean( wp_unslash($value)) : '';
			}
			
			$settings[$name] = $value;
		}
				
		$result = $this->save_advanced_settings($settings);
		if ($result == true) {
			$this->print_notices(__('Your changes were saved.', 'woo-checkout-field-editor-pro'), 'updated', false);
		} else {
			$this->print_notices(__('Your changes were not saved due to an error (or you made none!).', 'woo-checkout-field-editor-pro'), 'error', false);
		}	
	}
	
	private function render_content(){
		if(isset($_POST['reset_settings']))
			$this->reset_settings();	
			
		if(isset($_POST['save_settings']))
			$this->save_settings();
			
    	$this->render_plugin_settings();
	}

	private function render_plugin_settings(){
		$settings = THWCFD_Utils::get_advanced_settings();
		?>            
        <div style="padding-left: 30px;">               
		    <form id="advanced_settings_form" method="post" action="">
                <table class="thwcfd-settings-table thpladmin-form-table">
                    <tbody>
                    <?php
                    $this->render_locale_override_settings($settings);
					?>
                    </tbody>
                </table> 
                <p class="submit">
					<input type="submit" name="save_settings" class="btn btn-small btn-primary" value="Save changes">
                    <input type="submit" name="reset_settings" class="btn btn-small" value="Reset to default" 
					onclick="return confirm(<?php _e('Are you sure you want to reset to default settings? all your changes will be deleted.', 'woo-checkout-field-editor-pro'); ?>)">
            	</p>
            	<?php wp_nonce_field( 'thwcfd_advanced_settings', 'thwcfd_security_advanced_settings' ); ?>
            </form>
    	</div>       
    	<?php
	}

	private function render_locale_override_settings($settings){
		$this->render_form_elm_row_title('Locale override settings');
		$this->render_form_elm_row_cb($this->settings_fields['enable_label_override'], $settings, true);
		$this->render_form_elm_row_cb($this->settings_fields['enable_placeholder_override'], $settings, true);
		$this->render_form_elm_row_cb($this->settings_fields['enable_class_override'], $settings, true);
		$this->render_form_elm_row_cb($this->settings_fields['enable_priority_override'], $settings, true);
		$this->render_form_elm_row_cb($this->settings_fields['enable_required_override'], $settings, true);
	}

	public function render_form_elm_row_title($title=''){
		?>
		<tr>
			<td colspan="3" class="section-title" ><?php echo $title; ?></td>
		</tr>
		<?php
	}

	private function render_form_elm_row_cb($field, $settings=false, $merge_cells=false){
		$name = $field['name'];
		if(is_array($settings) && isset($settings[$name])){
			if($field['value'] === $settings[$name]){
				$field['checked'] = 1;
			}else{
				$field['checked'] = 0;
			}
		}

		if($merge_cells){
			?>
			<tr>
				<td colspan="3">
		    		<?php $this->render_form_field_element($field, $this->cell_props_CB, false); ?>
		    	</td>
		    </tr>
			<?php
		}else{
			?>
			<tr>
				<td colspan="2"></td>
				<td class="field">
		    		<?php $this->render_form_field_element($field, $this->cell_props_CB, false); ?>
		    	</td>
		    </tr>
			<?php
		}
	}
}

endif;