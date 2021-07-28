<?php
/**
 * The admin settings page functionality of the plugin.
 *
 * @link       https://themehigh.com
 *
 * @package    woo-checkout-field-editor-pro
 * @subpackage woo-checkout-field-editor-pro/admin
 */

if(!defined('WPINC')){	die; }

if(!class_exists('THWCFD_Admin_Form_Field')):

class THWCFD_Admin_Form_Field extends THWCFD_Admin_Form{
	private $field_props = array();

	public function __construct() {
		$this->init_constants();
	}

	private function init_constants(){
		$this->field_props = $this->get_field_form_props();
		//$this->field_props_display = $this->get_field_form_props_display();
	}

	// private function get_field_types(){
	// 	return array(
	// 		'text' => 'Text', 'hidden' => 'Hidden', 'password' => 'Password', 
	// 		'tel' => 'Telephone', 'email' => 'Email', 'number' => 'Number',  
	// 		'textarea' => 'Textarea', 'select' => 'Select', 'multiselect' => 'Multiselect', 
	// 		'radio' => 'Radio', 'checkbox' => 'Checkbox', 'checkboxgroup' => 'Checkbox Group', 
	// 		'datepicker' => 'Date Picker', 'timepicker' => 'Time Picker', 
	// 		'file' => 'File Upload', 
	// 		'heading' => 'Heading', 'label' => 'Label'
	// 	);
	// }
	public function get_field_types(){
		return array(
			'text'   => __('Text', 'woo-checkout-field-editor-pro'),
			'password' => __('Password', 'woo-checkout-field-editor-pro'),
			'email' => __('Email', 'woo-checkout-field-editor-pro'),
			'tel' => __('Phone', 'woo-checkout-field-editor-pro'),
			'select' => __('Select', 'woo-checkout-field-editor-pro'),
			'textarea' => __('Textarea', 'woo-checkout-field-editor-pro'),
			'radio' => __('Radio', 'woo-checkout-field-editor-pro'),
		);
	}

	public function get_field_form_props(){
		$field_types = $this->get_field_types();
		
		$validations = array(
			'email' => 'Email',
			'phone' => 'Phone',
			'postcode' => 'Postcode',
			'state' => 'State',
			'number' => 'Number',
		);

		$display_style = array(
			'full' => 'Full width',
			'half_left' => 'Half width left',
			'ha;lf_right' => 'Half width right',
		);
				
		return array(
			'type' 		  => array('type'=>'select', 'name'=>'type', 'label'=>'Type', 'required'=>1, 'options'=>$field_types, 
								'onchange'=>'thwcfdFieldTypeChangeListner(this)'),
			'name' 		  => array('type'=>'text', 'name'=>'name', 'label'=>'Name', 'required'=>1),
			'label'       => array('type'=>'text', 'name'=>'label', 'label'=>'Label'),
			'default'     => array('type'=>'text', 'name'=>'default', 'label'=>'Default Value'),
			'placeholder' => array('type'=>'text', 'name'=>'placeholder', 'label'=>'Placeholder'),
			//'options'     => array('type'=>'text', 'name'=>'options', 'label'=>'Options', 'placeholder'=>'Seperate options with pipe(|)'),
			'class'       => array('type'=>'text', 'name'=>'class', 'label'=>'Class', 'placeholder'=>'Separate classes with comma'),
			'validate'    => array('type'=>'multiselect', 'name'=>'validate', 'label'=>'Validation', 'placeholder'=>'Select validations', 'options'=>$validations, 'multiple'=>1),
			'disp_style' => array('type'=>'select', 'name'=>'disp_style', 'label'=>'Field Display', 'options'=>$display_style),
						
			'required' => array('type'=>'checkbox', 'name'=>'required', 'label'=>'Required', 'value'=>'1', 'checked'=>1),
			//'clear'    => array('type'=>'checkbox', 'name'=>'clear', 'label'=>'Clear Row', 'value'=>'1', 'checked'=>1),
			'enabled'  => array('type'=>'checkbox', 'name'=>'enabled', 'label'=>'Enabled', 'value'=>'1', 'checked'=>1),
			
			'show_in_email' => array('type'=>'checkbox', 'name'=>'show_in_email', 'label'=>'Display in Emails', 'value'=>'1', 'checked'=>1),
			'show_in_order' => array('type'=>'checkbox', 'name'=>'show_in_order', 'label'=>'Display in Order Detail Pages', 'value'=>'1', 'checked'=>1),
		);
	}

	/*public function get_field_form_props_display(){
		return array(
			'name'  => array('name'=>'name', 'type'=>'text'),
			'type'  => array('name'=>'type', 'type'=>'select'),
			'title' => array('name'=>'title', 'type'=>'text', 'len'=>40),
			'placeholder' => array('name'=>'placeholder', 'type'=>'text', 'len'=>30),
			'validate' => array('name'=>'validate', 'type'=>'text'),
			'required' => array('name'=>'required', 'type'=>'checkbox', 'status'=>1),
			'enabled'  => array('name'=>'enabled', 'type'=>'checkbox', 'status'=>1),
		);
	}*/

	public function output_field_forms(){
		$this->output_field_form_pp();
		$this->output_form_fragments();
	}

	private function output_field_form_pp(){
		?>
        <div id="thwcfd_field_form_pp" class="thpladmin-modal-mask">
        	<?php $this->output_popup_form_fields(); ?>
        </div>
        <?php
	}

	/*****************************************/
	/********** POPUP FORM WIZARD ************/
	/*****************************************/
	private function output_popup_form_fields(){
		?>
		<div class="thpladmin-modal">
			<div class="modal-container">
				<span class="modal-close" onclick="thwcfdCloseModal(this)">×</span>
				<div class="modal-content">
					<div class="modal-body">
						<div class="form-wizard wizard">
							<aside>
								<side-title class="wizard-title">Save Field</side-title>
								<ul class="pp_nav_links">
									<li class="text-primary active first pp-nav-link-basic" data-index="0">
										<i class="dashicons dashicons-admin-generic text-primary"></i>Basic Info
										<i class="i i-chevron-right dashicons dashicons-arrow-right-alt2"></i>
									</li>
									<!-- <li class="text-primary pp-nav-link-styles" data-index="1">
										<i class="dashicons dashicons-art text-primary"></i>Display Styles
										<i class="i i-chevron-right dashicons dashicons-arrow-right-alt2"></i>
									</li> -->
								</ul>
							</aside>
							<main class="form-container main-full">
								<form method="post" id="thwcfd_field_form" action="">
                    				<input type="hidden" name="f_action" value="" />
						          	<input type="hidden" name="i_autocomplete" value="" />
						          	<input type="hidden" name="i_priority" value="" />
						          	<input type="hidden" name="i_custom" value="" />
						          	<input type="hidden" name="i_oname" value="" />
						          	<input type="hidden" name="i_otype" value="" />
						          	<input type="hidden" name="i_options_json" value="" />

									<div class="data-panel data_panel_0">
										<?php $this->render_form_tab_general_info(); ?>
									</div>
									<!-- <div class="data-panel data_panel_1">
										<?php //$this->render_form_tab_display_details(); ?>
									</div> -->
								</form>
							</main>
							<footer>
								<span class="Loader"></span>
								<div class="btn-toolbar">
									<button class="save-btn pull-right btn btn-primary" onclick="thwcfdSaveField(this)">
										<span>Save & Close</span>
									</button>
									<!--<button class="next-btn pull-right btn btn-primary-alt" onclick="thwcfdWizardNext(this)">
										<span>Next</span><i class="i i-plus"></i>
									</button>
									<button class="prev-btn pull-right btn btn-primary-alt" onclick="thwcfdWizardPrevious(this)">
										<span>Back</span><i class="i i-plus"></i>
									</button>-->
								</div>
							</footer>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/*----- TAB - General Info -----*/
	private function render_form_tab_general_info(){
		$this->render_form_tab_main_title('Basic Details');

		?>
		<div style="display: inherit;" class="data-panel-content">
			<?php
			$this->render_form_fragment_general();
			?>
			<table class="thwcfd_field_form_tab_general_placeholder thwcfd_pp_table thwcfd-general-info"></table>
		</div>
		<?php
	}

	/*----- TAB - Display Details -----*/
	private function render_form_tab_display_details(){
		$this->render_form_tab_main_title('Display Settings');

		?>
		<div style="display: inherit;" class="data-panel-content mt-10">
			<table class="thwcfd_pp_table compact thwcfd-display-info">
				<?php
				$this->render_form_elm_row($this->field_props['class']);

				$this->render_form_elm_row_cb($this->field_props['show_in_email']);
		    	$this->render_form_elm_row_cb($this->field_props['show_in_order']);
				?>
			</table>
		</div>
		<?php
	}

	/*-------------------------------*/
	/*------ Form Field Groups ------*/
	/*-------------------------------*/
	private function render_form_fragment_general($input_field = true){
		?>
		<div class="err_msgs"></div>
        <table class="thwcfd_pp_table">
        	<?php
			$this->render_form_elm_row($this->field_props['type']);
			$this->render_form_elm_row($this->field_props['name']);
			?>
        </table>  
        <?php
	}

	private function output_form_fragments(){
		$this->render_form_field_inputtext();
		$this->render_form_field_password();
		$this->render_form_field_tel();
		$this->render_form_field_email();
		$this->render_form_field_textarea();
		$this->render_form_field_select();
		$this->render_form_field_radio();
		$this->render_form_field_default();
	}

	private function render_form_field_inputtext(){
		?>
        <table id="thwcfd_field_form_id_text" class="thwcfd_pp_table" style="display:none;">
        	<?php
			$this->render_form_elm_row($this->field_props['label']);
			$this->render_form_elm_row($this->field_props['placeholder']);
			$this->render_form_elm_row($this->field_props['default']);
			$this->render_form_elm_row($this->field_props['class']);
			$this->render_form_elm_row($this->field_props['validate']);

			$this->render_form_elm_row_cb($this->field_props['required']);
			$this->render_form_elm_row_cb($this->field_props['enabled']);
			$this->render_form_elm_row_cb($this->field_props['show_in_email']);
		    $this->render_form_elm_row_cb($this->field_props['show_in_order']);
			?>
        </table>
        <?php   
	}

	private function render_form_field_password(){
		?>
        <table id="thwcfd_field_form_id_password" class="thwcfd_field_form_table" width="100%" style="display:none;">
            <?php
			$this->render_form_elm_row($this->field_props['label']);
			$this->render_form_elm_row($this->field_props['placeholder']);
			$this->render_form_elm_row($this->field_props['default']);
			$this->render_form_elm_row($this->field_props['class']);
			$this->render_form_elm_row($this->field_props['validate']);

			$this->render_form_elm_row_cb($this->field_props['required']);
			$this->render_form_elm_row_cb($this->field_props['enabled']);
			$this->render_form_elm_row_cb($this->field_props['show_in_email']);
		    $this->render_form_elm_row_cb($this->field_props['show_in_order']);
			?>  
        </table>
        <?php   
	}

	private function render_form_field_tel(){
		?>
        <table id="thwcfd_field_form_id_tel" class="thwcfd_field_form_table" width="100%" style="display:none;">
            <?php
			$this->render_form_elm_row($this->field_props['label']);
			$this->render_form_elm_row($this->field_props['placeholder']);
			$this->render_form_elm_row($this->field_props['default']);
			$this->render_form_elm_row($this->field_props['class']);
			$this->render_form_elm_row($this->field_props['validate']);

			$this->render_form_elm_row_cb($this->field_props['required']);
			$this->render_form_elm_row_cb($this->field_props['enabled']);
			$this->render_form_elm_row_cb($this->field_props['show_in_email']);
		    $this->render_form_elm_row_cb($this->field_props['show_in_order']);
			?>    
        </table>
        <?php   
	}

	private function render_form_field_email(){
		?>
        <table id="thwcfd_field_form_id_email" class="thwcfd_field_form_table" width="100%" style="display:none;">
            <?php
			$this->render_form_elm_row($this->field_props['label']);
			$this->render_form_elm_row($this->field_props['placeholder']);
			$this->render_form_elm_row($this->field_props['default']);
			$this->render_form_elm_row($this->field_props['class']);
			$this->render_form_elm_row($this->field_props['validate']);

			$this->render_form_elm_row_cb($this->field_props['required']);
			$this->render_form_elm_row_cb($this->field_props['enabled']);
			$this->render_form_elm_row_cb($this->field_props['show_in_email']);
		    $this->render_form_elm_row_cb($this->field_props['show_in_order']);
			?>    
        </table>
        <?php   
	}
	
	private function render_form_field_textarea(){
		$value_props = $this->field_props['default'];
		$value_props['type'] = 'textarea';

		?>
        <table id="thwcfd_field_form_id_textarea" class="thwcfd_field_form_table" width="100%" style="display:none;">
            <?php
			$this->render_form_elm_row($this->field_props['label']);
			$this->render_form_elm_row($this->field_props['placeholder']);
			$this->render_form_elm_row($value_props);
			$this->render_form_elm_row($this->field_props['class']);
			$this->render_form_elm_row($this->field_props['validate']);

			$this->render_form_elm_row_cb($this->field_props['required']);
			$this->render_form_elm_row_cb($this->field_props['enabled']);
			$this->render_form_elm_row_cb($this->field_props['show_in_email']);
		    $this->render_form_elm_row_cb($this->field_props['show_in_order']);
			?>     
        </table>
        <?php   
	}
	
	private function render_form_field_select(){
		?>
        <table id="thwcfd_field_form_id_select" class="thwcfd_field_form_table" width="100%" style="display:none;">
            <?php
			$this->render_form_elm_row($this->field_props['label']);
			$this->render_form_elm_row($this->field_props['placeholder']);
			$this->render_form_elm_row($this->field_props['default']);
			$this->render_form_elm_row($this->field_props['class']);
			$this->render_form_elm_row($this->field_props['validate']);

			$this->render_form_elm_row_cb($this->field_props['required']);
			$this->render_form_elm_row_cb($this->field_props['enabled']);
			$this->render_form_elm_row_cb($this->field_props['show_in_email']);
		    $this->render_form_elm_row_cb($this->field_props['show_in_order']);

		    $this->render_form_fragment_h_spacing();
			$this->render_form_fragment_options();
			?>
        </table>
        <?php   
	}
	
	private function render_form_field_radio(){
		?>
        <table id="thwcfd_field_form_id_radio" class="thwcfd_field_form_table" width="100%" style="display:none;">
            <?php
			$this->render_form_elm_row($this->field_props['label']);
			$this->render_form_elm_row($this->field_props['placeholder']);
			$this->render_form_elm_row($this->field_props['default']);
			$this->render_form_elm_row($this->field_props['class']);
			$this->render_form_elm_row($this->field_props['validate']);

			$this->render_form_elm_row_cb($this->field_props['required']);
			$this->render_form_elm_row_cb($this->field_props['enabled']);
			$this->render_form_elm_row_cb($this->field_props['show_in_email']);
		    $this->render_form_elm_row_cb($this->field_props['show_in_order']);

		    $this->render_form_fragment_h_spacing();
			$this->render_form_fragment_options();
			?>
        </table>
        <?php   
	}
	
	private function render_form_field_default(){
		?>
        <table id="thwcfd_field_form_id_default" class="thwcfd_field_form_table" width="100%" style="display:none;">
            <?php
			$this->render_form_elm_row($this->field_props['label']);
			$this->render_form_elm_row($this->field_props['placeholder']);
			$this->render_form_elm_row($this->field_props['default']);
			$this->render_form_elm_row($this->field_props['class']);
			$this->render_form_elm_row($this->field_props['validate']);

			$this->render_form_elm_row_cb($this->field_props['required']);
			$this->render_form_elm_row_cb($this->field_props['enabled']);
			$this->render_form_elm_row_cb($this->field_props['show_in_email']);
		    $this->render_form_elm_row_cb($this->field_props['show_in_order']);
			?>    
        </table>
        <?php   
	}

	private function render_form_fragment_options(){
		?>
		<tr>
			<td class="sub-title"><?php _e('Options', 'woo-checkout-field-editor-pro'); ?></td>
			<?php $this->render_form_fragment_tooltip(); ?>
			<td></td>
		</tr>
		<tr>
			<td colspan="3" class="p-0">
				<table border="0" cellpadding="0" cellspacing="0" class="thwcfd-option-list thpladmin-options-table"><tbody>
					<tr>
						<td class="key"><input type="text" name="i_options_key[]" placeholder="Option Value"></td>
						<td class="value"><input type="text" name="i_options_text[]" placeholder="Option Text"></td>
						<td class="action-cell">
							<a href="javascript:void(0)" onclick="thwcfdAddNewOptionRow(this)" class="btn btn-tiny btn-primary" title="Add new option">+</a><a href="javascript:void(0)" onclick="thwcfdRemoveOptionRow(this)" class="btn btn-tiny btn-danger" title="Remove option">x</a><span class="btn btn-tiny sort ui-sortable-handle"></span>
						</td>
					</tr>
				</tbody></table>            	
			</td>
		</tr>
        <?php
	}
}

endif;