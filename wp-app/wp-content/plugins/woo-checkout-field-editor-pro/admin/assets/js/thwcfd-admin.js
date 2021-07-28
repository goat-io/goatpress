var thwcfd_base = (function($, window, document) {
	'use strict';

	function escapeHTML(html) {
	   var fn = function(tag) {
		   var charsToReplace = {
			   '&': '&amp;',
			   '<': '&lt;',
			   '>': '&gt;',
			   '"': '&#34;'
		   };
		   return charsToReplace[tag] || tag;
	   }
	   return html.replace(/[&<>"]/g, fn);
	}

	function decodeHtml(str) {
		if(str && typeof(str) === 'string'){
		   	var map = {
	        	'&amp;': '&',
	        	'&lt;': '<',
	        	'&gt;': '>',
	        	'&quot;': '"',
	        	'&#039;': "'"
	    	};
	    	return str.replace(/&amp;|&lt;|&gt;|&quot;|&#039;/g, function(m) {return map[m];});
	    }
	    return str;
	}

	function isHtmlIdValid(id) {
		//var re = /^[a-z]+[a-z0-9\_]*$/;
		var re = /^[a-z\_]+[a-z0-9\_]*$/;
		// if(wcfe_var.sanitize_names == false){
		// 	re = /^[a-zA-Z\_]+[a-zA-Z0-9\_]*$/;
		// }
		return re.test(id.trim());
	}

	function isValidHexColor(value) {
		if ( preg_match( '/^#[a-f0-9]{6}$/i', value ) ) { // if user insert a HEX color with #
			return true;
		}
		return false;
	}

	function is_option_field(type){
		var result = false;
		if(type == 'select' || type == 'multiselect' || type == 'radio' || type == 'checkboxgroup'){
			result = true;
		}
		return result;
	}

	function setup_tiptip_tooltips(){
		var tiptip_args = {
			'attribute': 'data-tip',
			'fadeIn': 50,
			'fadeOut': 50,
			'delay': 200
		};

		$('.tips').tipTip( tiptip_args );
	}

	function setup_enhanced_multi_select(parent){
		parent.find('select.thwcfd-enhanced-multi-select').each(function(){
			if(!$(this).hasClass('enhanced')){
				$(this).selectWoo({
					//minimumResultsForSearch: 10,
					allowClear : true,
					placeholder: $(this).data('placeholder')
				}).addClass('enhanced');
			}
		});
	}

	function setup_enhanced_multi_select_with_value(parent){
		parent.find('select.thwcfd-enhanced-multi-select').each(function(){
			if(!$(this).hasClass('enhanced')){
				$(this).selectWoo({
					//minimumResultsForSearch: 10,
					allowClear : true,
					placeholder: $(this).data('placeholder')
				}).addClass('enhanced');

				var value = $(this).data('value');
				value = value.split(",");

				$(this).val(value);
				$(this).trigger('change');
			}
		});
	}

	function setup_color_picker(form){
		form.find('.thpladmin-colorpick').iris({
			change: function( event, ui ) {
				$( this ).parent().find( '.thpladmin-colorpickpreview' ).css({ backgroundColor: ui.color.toString() });
			},
			hide: true,
			border: true
		}).click( function() {
			$('.iris-picker').hide();
			$(this ).closest('td').find('.iris-picker').show();
		});

		$('body').click( function() {
			$('.iris-picker').hide();
		});

		$('.thpladmin-colorpick').click( function( event ) {
			event.stopPropagation();
		});
	}

	function setup_color_pick_preview(form){
		form.find('.thpladmin-colorpick').each(function(){
			$(this).parent().find('.thpladmin-colorpickpreview').css({ backgroundColor: this.value });
		});
	}

	function prepare_field_order_indexes(elm) {
		$(elm+" tbody tr").each(function(index, el){
			$('input.f_order', el).val( parseInt( $(el).index(elm+" tbody tr") ) );
		});
	}

	function setup_sortable_table(parent, elm, left){
		parent.find(elm+" tbody").sortable({
			items:'tr',
			cursor:'move',
			axis:'y',
			handle: '.sort',
			scrollSensitivity:40,
			helper:function(e,ui){
				ui.children().each(function(){
					$(this).width($(this).width());
				});
				ui.css('left', left);
				return ui;
			}
		});

		$(elm+" tbody").on("sortstart", function( event, ui ){
			ui.item.css('background-color','#f6f6f6');
		});
		$(elm+" tbody").on("sortstop", function( event, ui ){
			ui.item.removeAttr('style');
			prepare_field_order_indexes(elm);
		});
	}

	function get_property_field_value(form, type, name){
		var value = '';

		switch(type) {
			case 'select':
				value = form.find("select[name=i_"+name+"]").val();
				value = value == null ? '' : value;
				break;

			case 'checkbox':
				value = form.find("input[name=i_"+name+"]").prop('checked');
				value = value ? 1 : 0;
				break;

			case 'textarea':
				value = form.find("textarea[name=i_"+name+"]").val();
				value = value == null ? '' : value;
				break;

			default:
				value = form.find("input[name=i_"+name+"]").val();
				value = value == null ? '' : value;
		}

		return value;
	}

	function set_property_field_value(form, type, name, value, multiple){
		switch(type) {
			case 'select':
				if(multiple == 1){
					value = typeof(value) === 'string' ? value.split(",") : value;
					name = name+"[]";
					form.find('select[name="i_'+name+'"]').val(value).trigger("change");
				}else{
					form.find('select[name="i_'+name+'"]').val(value);
				}
				break;

			case 'checkbox':
				value = value == 1 || value == 'yes' ? true : false;
				form.find("input[name=i_"+name+"]").prop('checked', value);
				break;

			case 'textarea':
				value = value ? decodeHtml(value) : value;
				form.find("textarea[name=i_"+name+"]").val(value);
				break;

			case 'colorpicker':
				var bg_color = value ? { backgroundColor: value } : {};
				form.find("input[name=i_"+name+"]").val(value);
				form.find("."+name+"_preview").css(bg_color);
				break;

			default:
				value = value ? decodeHtml(value) : value;
				form.find("input[name=i_"+name+"]").val(value);
		}
	}

   /*-------------------------------------------
	*---- POPUP WIZARD FUNCTIONS - SATRT -------
	*------------------------------------------*/
	var active_tab = 0;

	function setup_form_wizard(){
		$('.pp_nav_links > li').click(function(){
			var index = $(this).data('index');
			var popup = $(this).closest('.thpladmin-modal-mask');

			open_tab(popup, $(this), index);
			active_tab = index;
		});
	}

	function get_popup(elm){
		return $(elm).closest('.thpladmin-modal-mask');
	}

	function get_active_tab(popup){
		return popup.find('ul.pp_nav_links').find('li.active')
	}

	function get_next_tab_index(elm){
		var popup  = get_popup(elm);
		var active = get_active_tab(popup);

		var link = active.nextAll("li").not(".disabled").first();
		var index = link.length ? link.data('index') : active_tab;
		return index;
	}

	function get_prev_tab_index(elm){
		var popup  = get_popup(elm);
		var active = get_active_tab(popup);

		var link = active.prevAll("li").not(".disabled").first();
		var index = link.length ? link.data('index') : active_tab;
		return index;
	}

	function form_wizard_open(popup){
		active_tab = 0;
		popup.find('ul.pp_nav_links li').first().click();
		popup.css("display", "block");
	}

	function form_wizard_close(elm) {
		var popup = get_popup(elm);
		popup.css("display", "none");
		active_tab = 0;
	}

	function form_wizard_next(elm){
		active_tab = get_next_tab_index(elm);
		move_to(elm, active_tab);
	}

	function form_wizard_previous(elm){
		active_tab = get_prev_tab_index(elm);
		move_to(elm, active_tab);
	}

	function form_wizard_start(elm){
		active_tab = 0;
		move_to(elm, active_tab);
	}

	function move_to(elm, index){
		var popup = get_popup(elm);
		var link = popup.find('*[data-index="'+index+'"]');
		open_tab(popup, link, index);
	}

	function open_tab(popup, link, index){
		var panel = popup.find('.data_panel_'+index);

		close_all_data_panel(popup);
		link.addClass('active');
		panel.css("display", "block");

		enable_disable_btns(popup, link);
	}

	function close_all_data_panel(popup){
		popup.find('.pp_nav_links > li').removeClass('active');
		popup.find('.data-panel').css("display", "none");
	}

	function enable_disable_tab(popup, index, disable){
		var link = popup.find('*[data-index="'+index+'"]');
		var panel = popup.find('.data_panel_'+index);

		if(disable){
			link.addClass('disabled');
			panel.find(":input").attr("disabled", true);
			//panel.css("display", "none");
		}else{
			link.removeClass('disabled');
			panel.find(":input").attr("disabled", false);
			//panel.css("display", "block");
		}
	}

	function form_wizard_enable_tab(popup, index){
		enable_disable_tab(popup, index, 0);
	}
	function form_wizard_disable_tab(popup, index){
		enable_disable_tab(popup, index, 1);
	}
	function form_wizard_enable_all_tabs(popup){
		popup.find('.pp_nav_links > li').removeClass('disabled');
	}

	function enable_disable_btns(popup, link){
		var nextBtn = popup.find('.next-btn');
		var prevBtn = popup.find('.prev-btn');
		var nextBtnTxt = 'Save & Next';

		if(link.hasClass('first')){
			nextBtn.prop( "disabled", false );
			prevBtn.prop( "disabled", true );
		}else if(link.hasClass('last')){
			nextBtn.prop( "disabled", true );
			prevBtn.prop( "disabled", false );
			nextBtnTxt = 'Save & Close';
		}else{
			nextBtn.prop( "disabled", false );
			prevBtn.prop( "disabled", false );
		}
	}

   /*-------------------------------------------
	*---- POPUP WIZARD FUNCTIONS - END ---------
	*------------------------------------------*/

	return {
		escapeHTML : escapeHTML,
		decodeHtml : decodeHtml,
		isHtmlIdValid : isHtmlIdValid,
		isValidHexColor : isValidHexColor,
		is_option_field : is_option_field,
		setup_tiptip_tooltips : setup_tiptip_tooltips,
		setupEnhancedMultiSelect : setup_enhanced_multi_select,
		setupEnhancedMultiSelectWithValue : setup_enhanced_multi_select_with_value,
		setupColorPicker : setup_color_picker,
		setup_color_pick_preview : setup_color_pick_preview,
		setupSortableTable : setup_sortable_table,
		get_property_field_value : get_property_field_value,
		set_property_field_value : set_property_field_value,

		setup_form_wizard : setup_form_wizard,
		form_wizard_open : form_wizard_open,
		form_wizard_close : form_wizard_close,
		form_wizard_next : form_wizard_next,
		form_wizard_previous : form_wizard_previous,
		form_wizard_start : form_wizard_start,
		form_wizard_enable_tab : form_wizard_enable_tab,
		form_wizard_disable_tab : form_wizard_disable_tab,
		form_wizard_enable_all_tabs : form_wizard_enable_all_tabs,
   	};
}(window.jQuery, window, document));

function thwcfdSetupEnhancedMultiSelectWithValue(elm){
	thwcfd_base.setupEnhancedMultiSelectWithValue(elm);
}

function thwcfdSetupSortableTable(parent, elm, left){
	thwcfd_base.setupSortableTable(parent, elm, left);
}

function thwcfdCloseModal(elm){
	thwcfd_base.form_wizard_close(elm);
}
function thwcfdWizardNext(elm){
	thwcfd_base.form_wizard_next(elm);
}
function thwcfdWizardPrevious(elm){
	thwcfd_base.form_wizard_previous(elm);
}

var thwcfd_settings_field = (function($, window, document) {
	'use strict';

	var MSG_INVALID_NAME = 'NAME/ID must begin with a lowercase letter ([a-z]) or underscores ("_") and may be followed by any number of lowercase letters, digits ([0-9]) and underscores ("_")';
	var SPECIAL_FIELD_TYPES = ["country", "state", "city"];

	var FIELD_FORM_PROPS = {
		name  : {name : 'name', type : 'text'},
		type  : {name : 'type', type : 'select'},

		label       : {name : 'label', type : 'text'},
		default     : {name : 'default', type : 'text'},
		placeholder : {name : 'placeholder', type : 'text'},
		class       : {name : 'class', type : 'text'},
		validate    : {name : 'validate', type : 'select', multiple : 1 },

		required : {name : 'required', type : 'checkbox'},
		enabled  : {name : 'enabled', type : 'checkbox'},

		show_in_email : {name : 'show_in_email', type : 'checkbox'},
		show_in_order : {name : 'show_in_order', type : 'checkbox'},
	};

	var FIELDS_TO_HIDE = {
		radio : ['placeholder', 'validate'],
		select : ['validate'],
	};	

	function open_new_field_form(sname){
		open_field_form('new', false, sname);
	}

	function open_edit_field_form(elm, rowId){
		open_field_form('edit', elm, false);
	}

	function open_copy_field_form(elm, rowId){
		open_field_form('copy', elm, false);
	}

	function open_field_form(type, elm, sname){
		var popup = $("#thwcfd_field_form_pp");
		var form  = $("#thwcfd_field_form");

		populate_field_form(popup, form, type, elm, sname);

		thwcfd_base.form_wizard_open(popup);
		//thwcfd_base.setup_color_pick_preview(form);
	}

	function populate_field_form(popup, form, action, elm, sname){
		var title = action === 'edit' ? 'Edit Field' : 'New Field';
		popup.find('.wizard-title').text(title);

		form.find('.err_msgs').html('');
		form.find("input[name=f_action]").val(action);

		if(action === 'new'){
			if(sname == 'billing' || sname == 'shipping' || sname == 'additional'){
				sname = sname+'_';
			}

			clear_field_form_general(form);
			//clear_field_form_display(form);
			form.find("select[name=i_type]").change();

			thwcfd_base.set_property_field_value(form, 'text', 'name', sname, 0);
			thwcfd_base.set_property_field_value(form, 'text', 'class', 'form-row-wide', 0);

		}else{
			var row = $(elm).closest('tr');
			var props_json = row.find(".f_props").val();
			var props = JSON.parse(props_json);

			populate_field_form_general(action, form, props);
			form.find("select[name=i_type]").change();
			populate_field_form_props(form, row, props);
		}
	}

	function clear_field_form_general(form){
		thwcfd_base.set_property_field_value(form, 'hidden', 'autocomplete', '', 0);
		thwcfd_base.set_property_field_value(form, 'hidden', 'priority', '', 0);
		thwcfd_base.set_property_field_value(form, 'hidden', 'custom', '', 0);
		thwcfd_base.set_property_field_value(form, 'hidden', 'oname', '', 0);
		thwcfd_base.set_property_field_value(form, 'hidden', 'otype', '', 0);

		thwcfd_base.set_property_field_value(form, 'select', 'type', 'text', 0);
		thwcfd_base.set_property_field_value(form, 'text', 'name', '', 0);
		/*
		thwcfd_base.set_property_field_value(form, 'text', 'label', '', 0);
		thwcfd_base.set_property_field_value(form, 'text', 'placeholder', '', 0);
		thwcfd_base.set_property_field_value(form, 'text', 'default', '', 0);
		thwcfd_base.set_property_field_value(form, 'text', 'class', '', 0);
		thwcfd_base.set_property_field_value(form, 'select', 'validate', '', 1);

		thwcfd_base.set_property_field_value(form, 'checkbox', 'required', 1, 0);
		thwcfd_base.set_property_field_value(form, 'checkbox', 'enabled', 1, 0);
		thwcfd_base.set_property_field_value(form, 'checkbox', 'show_in_email', 1, 0);
		thwcfd_base.set_property_field_value(form, 'checkbox', 'show_in_order', 1, 0);
		*/
	}

	/*
	function clear_field_form_display(form){
		thwcfd_base.set_property_field_value(form, 'text', 'class', '', 0);
		thwcfd_base.set_property_field_value(form, 'checkbox', 'show_in_email', 1, 0);
		thwcfd_base.set_property_field_value(form, 'checkbox', 'show_in_order', 1, 0);
	}
	*/

	function populate_field_form_general(action, form, props){
		var autocomplete = props['autocomplete'] ? props['autocomplete'] : '';
		var priority = props['priority'] ? props['priority'] : '';
		var custom = props['custom'] ? props['custom'] : '';

		var type = props['type'] ? props['type'] : 'text';
		var name = props['name'] ? props['name'] : '';

		if(action === 'copy'){
			name = '';
		}

		thwcfd_base.set_property_field_value(form, 'hidden', 'autocomplete', autocomplete, 0);
		thwcfd_base.set_property_field_value(form, 'hidden', 'priority', priority, 0);
		thwcfd_base.set_property_field_value(form, 'hidden', 'custom', custom, 0);
		thwcfd_base.set_property_field_value(form, 'hidden', 'oname', name, 0);
		thwcfd_base.set_property_field_value(form, 'hidden', 'otype', type, 0);

		thwcfd_base.set_property_field_value(form, 'select', 'type', type, 0);
		thwcfd_base.set_property_field_value(form, 'text', 'name', name, 0);

		// if(type == "country" || type == "state" || type == "city"){
		// 	form.find("select[name=i_type]").prop('disabled', true);
		// }else{
		// 	form.find("select[name=i_type]").prop('disabled', false);
		// }
	}

	function populate_field_form_props(form, row, props){
		var ftype  = props.type;
		var custom = props['custom'] ? props['custom'] : '';

		$.each( FIELD_FORM_PROPS, function( name, field ) {
			if(name == 'name' || name == 'type') {
				return true;
			}

			var type   = field['type'];
			var value  = props && props[name] ? props[name] : '';

			if(ftype == 'textarea' && name == 'value'){
				type = "textarea";
			}

			thwcfd_base.set_property_field_value(form, type, name, value, field['multiple']);

			if(type == 'select'){
				name = field['multiple'] == 1 ? name+"[]" : name;

				if(field['multiple'] == 1 || field['change'] == 1){
					form.find('select[name="i_'+name+'"]').trigger("change");
				}
			}else if(type == 'checkbox'){
				if(field['change'] == 1){
					form.find('input[name="i_'+name+'"]').trigger("change");
				}
			}
		});

		var optionsJson = row.find(".f_options").val();
		populate_options_list(form, optionsJson);

		if(custom == 1){
			form.find("input[name=i_name]").prop('disabled', false);
			form.find("select[name=i_type]").prop('disabled', false);
			form.find("input[name=i_show_in_email]").prop('disabled', false);
			form.find("input[name=i_show_in_order]").prop('disabled', false);
		}else{
			thwcfd_base.set_property_field_value(form, 'checkbox', 'show_in_email', true, 0);
			thwcfd_base.set_property_field_value(form, 'checkbox', 'show_in_order', true, 0);

			form.find("input[name=i_name]").prop('disabled', true);
			form.find("select[name=i_type]").prop('disabled', true);
			form.find("input[name=i_show_in_email]").prop('disabled', true);
			form.find("input[name=i_show_in_order]").prop('disabled', true);
			form.find("input[name=i_label]").focus();
		}
	}

	function field_type_change_listner(elm){
		var popup = $("#thwcfd_field_form_pp");
		var form = $(elm).closest('form');
		var type = $(elm).val();

		type = type == null ? 'text' : type;
		form.find('.thwcfd_field_form_tab_general_placeholder').html($('#thwcfd_field_form_id_'+type).html());

		enable_all_tabs_and_fields(popup, form);

		if(type in FIELDS_TO_HIDE){
			$.each(FIELDS_TO_HIDE[type], function(index, name) {

				if(FIELD_FORM_PROPS[name]){
					var f_props = FIELD_FORM_PROPS[name];
					disable_hide_field(form, f_props['type'], name);
				}
			});
		}		

		thwcfd_base.setupEnhancedMultiSelect(form);
		thwcfd_base.setupColorPicker(form);
		thwcfd_base.setupSortableTable(form, '.thwcfd-option-list', '100');
	}

	function enable_all_tabs_and_fields(popup, form){
		thwcfd_base.form_wizard_enable_all_tabs(popup);
		form.find(':input').attr("disabled", false);
		form.find('tr').removeClass('disabled hide');
	}

	function enable_disable_field(form, type, name, enabled, hide){
		var elm = null;

		switch(type) {
			case 'select':
				elm = form.find('select[name="i_'+name+'"]');
				if(elm.length == 0){
					elm = form.find('select[name="i_'+name+'[]"]');
				}
				break;

			case 'textarea':
				elm = form.find("textarea[name=i_"+name+"]");
				if(elm.length == 0){
					elm = form.find('textarea[name="i_'+name+'[]"]');
				}			
				break;

			default:
				elm = form.find("input[name=i_"+name+"]");
				if(elm.length == 0){
					elm = form.find('input[name="i_'+name+'[]"]');
				}		
		}

		if(elm && elm.length){
			var rowClass = hide ? 'disabled hide' : 'disabled';

			if(!enabled){
				elm.attr("disabled", true);
				elm.closest('tr.form_field_'+name).addClass(rowClass);
			}else{
				elm.attr("disabled", false);
				elm.closest('tr.form_field_'+name).removeClass('disabled hide');
			}
		}
	}
	function enable_field(form, type, name){
		enable_disable_field(form, type, name, true, false);
	}
	function disable_field(form, type, name){
		enable_disable_field(form, type, name, false, false);
	}
	function disable_hide_field(form, type, name){
		enable_disable_field(form, type, name, false, true);
	}

	function save_field(elm){
		var popup = $("#thwcfd_field_form_pp");
		var form  = $("#thwcfd_field_form");
		var result = validate_field_form(form, popup);

		if(result){
			prepare_field_form(form);
			form.submit();
		}
	}

	function validate_field_form(form, popup){
		var err_msgs = '';

		var fname  = thwcfd_base.get_property_field_value(form, 'text', 'name');
		var ftype  = thwcfd_base.get_property_field_value(form, 'select', 'type');
		var ftitle = thwcfd_base.get_property_field_value(form, 'text', 'label');
		var fotype = thwcfd_base.get_property_field_value(form, 'hidden', 'otype');

		if(ftype == '' && ($.inArray(fotype, SPECIAL_FIELD_TYPES) == -1) ){
			err_msgs = 'Type is required';

		}else if(fname == ''){
			err_msgs = 'Name is required';

		}else if(!thwcfd_base.isHtmlIdValid(fname)){
			err_msgs = MSG_INVALID_NAME;
		}

		if(err_msgs != ''){
			form.find('.err_msgs').html(err_msgs);
			thwcfd_base.form_wizard_start(popup);
			return false;
		}

		return true;
	}

	function prepare_field_form(form){
		var options_json = get_options(form);
		thwcfd_base.set_property_field_value(form, 'hidden', 'options_json', options_json, 0);
	}
   /*------------------------------------
	*---- PRODUCT FIELDS - END ----------
	*------------------------------------*/

   /*------------------------------------
	*---- OPTIONS FUNCTIONS - SATRT -----
	*------------------------------------*/
	function get_options(form){
		var optionsKey  = form.find("input[name='i_options_key[]']").map(function(){ return $(this).val(); }).get();
		var optionsText = form.find("input[name='i_options_text[]']").map(function(){ return $(this).val(); }).get();

		var optionsSize = optionsText.length;
		var optionsArr = [];

		for(var i=0; i<optionsSize; i++){
			var optionDetails = {};
			optionDetails["key"] = optionsKey[i];
			optionDetails["text"] = optionsText[i];

			optionsArr.push(optionDetails);
		}

		var optionsJson = optionsArr.length > 0 ? JSON.stringify(optionsArr) : '';
		optionsJson = encodeURIComponent(optionsJson);
		return optionsJson;
	}

	function populate_options_list(form, optionsJson){
		var optionsHtml = "";

		if(optionsJson){
			try{
				optionsJson = decodeURIComponent(optionsJson);
				var optionsList = $.parseJSON(optionsJson);
				if(optionsList){
					jQuery.each(optionsList, function() {
						optionsHtml += prepare_option_row_html(this);
					});
				}
			}catch(err) {
				console.log(err);
			}
		}

		var optionsTable = form.find(".thwcfd-option-list tbody");
		if(optionsHtml){
			optionsTable.html(optionsHtml);
		}else{
			optionsTable.html(prepare_option_row_html(null));
		}
	}

	function prepare_option_row_html(option){
		var key = '';
		var text = '';

		if(option){
			key = option.key ? option.key : '';
			text = option.text ? option.text : '';
		}

		var html  = '<tr>';
	        html += '<td class="key"><input type="text" name="i_options_key[]" value="'+key+'" placeholder="Option Value"></td>';
			html += '<td class="value"><input type="text" name="i_options_text[]" value="'+text+'" placeholder="Option Text"></td>';
			html += '<td class="action-cell">';
			html += '<a href="javascript:void(0)" onclick="thwcfdAddNewOptionRow(this)" class="btn btn-tiny btn-primary" title="Add new option">+</a>';
			html += '<a href="javascript:void(0)" onclick="thwcfdRemoveOptionRow(this)" class="btn btn-tiny btn-danger" title="Remove option">x</a>';
			html += '<span class="btn btn-tiny sort ui-sortable-handle"></span></td>';
			html += '</tr>';

		return html;
	}

	function add_new_option_row(elm){
		var ptable = $(elm).closest('table');
		var optionsSize = ptable.find('tbody tr').size();

		if(optionsSize > 0){
			ptable.find('tbody tr:last').after(prepare_option_row_html(null));
		}else{
			ptable.find('tbody').append(prepare_option_row_html(null));
		}
	}

	function remove_option_row(elm){
		var ptable = $(elm).closest('table');
		$(elm).closest('tr').remove();
		var optionsSize = ptable.find('tbody tr').size();

		if(optionsSize == 0){
			ptable.find('tbody').append(prepare_option_row_html(null));
		}
	}
   /*------------------------------------
	*---- OPTIONS FUNCTIONS - END -------
	*------------------------------------*/

	return {
		openNewFieldForm : open_new_field_form,
		openEditFieldForm : open_edit_field_form,
		openCopyFieldForm : open_copy_field_form,
		fieldTypeChangeListner : field_type_change_listner,
		addNewOptionRow : add_new_option_row,
		removeOptionRow : remove_option_row,
		save_field : save_field,
   	};
}(window.jQuery, window, document));

function thwcfdOpenNewFieldForm(sectionName){
	thwcfd_settings_field.openNewFieldForm(sectionName);
}

function thwcfdOpenEditFieldForm(elm, rowId){
	thwcfd_settings_field.openEditFieldForm(elm, rowId);
}

function thwcfdOpenCopyFieldForm(elm, rowId){
	thwcfd_settings_field.openCopyFieldForm(elm, rowId);
}

function thwcfdFieldTypeChangeListner(elm){
	thwcfd_settings_field.fieldTypeChangeListner(elm);
}

function thwcfdAddNewOptionRow(elm){
	thwcfd_settings_field.addNewOptionRow(elm);
}
function thwcfdRemoveOptionRow(elm){
	thwcfd_settings_field.removeOptionRow(elm);
}

function thwcfdSaveField(elm){
	thwcfd_settings_field.save_field(elm);
}

var thwcfd_settings = (function($, window, document) {
	'use strict';
		
	$(function() {
		var settings_form = $('#thwcfd_checkout_fields_form');

		thwcfd_base.setupSortableTable(settings_form, '#thwcfd_checkout_fields', '0');
		thwcfd_base.setup_tiptip_tooltips();
		thwcfd_base.setup_form_wizard();
	});


	$( document ).on( 'click', '.thpladmin-notice .notice-dismiss', function() {
		var wrapper = $(this).closest('div.thpladmin-notice');
		var nonce = wrapper.data("nonce");
		var action = wrapper.data("action");
		var data = {
			security: nonce,
			action: action,
		};
		$.post( ajaxurl, data, function() {

		});
	})

	$(document).ready(function(){
	   setTimeout(function(){
	      $("#thwcfd_review_request_notice").fadeIn(500);
	   }, 2000);
	});	
   
	function select_all_fields(elm){
		var checkAll = $(elm).prop('checked');
		$('#thwcfd_checkout_fields tbody input:checkbox[name=select_field]').prop('checked', checkAll);
	}
   	
	function remove_selected_fields(){
		$('#thwcfd_checkout_fields tbody tr').removeClass('strikeout');
		$('#thwcfd_checkout_fields tbody input:checkbox[name=select_field]:checked').each(function () {
			var row = $(this).closest('tr');
			if(!row.hasClass("strikeout")){
				row.addClass("strikeout");
			}
			row.find(".f_deleted").val(1);
			//row.find(".f_edit_btn").prop('disabled', true);
	  	});
	}

	function enable_disable_selected_fields(enabled){
		$('#thwcfd_checkout_fields tbody input:checkbox[name=select_field]:checked').each(function(){
			var row = $(this).closest('tr');
			if(enabled == 0){
				if(!row.hasClass("thpladmin-disabled")){
					row.addClass("thpladmin-disabled");
				}
			}else{
				row.removeClass("thpladmin-disabled");				
			}
			
			//row.find(".f_edit_btn").prop('disabled', enabled == 1 ? false : true);
			row.find(".td_enabled").html(enabled == 1 ? '<span class="dashicons dashicons-yes tips" data-tip="Yes"></span>' : '-');
			row.find(".f_enabled").val(enabled);
	  	});	
	}

	function hide_review_request_notice(elm){
		var wrapper = $(elm).closest('div.thpladmin-notice');
		var nonce = wrapper.data("nonce");
		var data = {
			security: nonce,
			action: 'skip_thwcfd_review_request_notice',
		};
		$.post( ajaxurl, data, function() {

		});
		$(wrapper).hide(50);
	}
	   				
	return {
		selectAllFields : select_all_fields,
		removeSelectedFields : remove_selected_fields,
		enableDisableSelectedFields : enable_disable_selected_fields,
		hideReviewRequestNotice : hide_review_request_notice,
   	};
}(window.jQuery, window, document));	

function thwcfdSelectAllCheckoutFields(elm){
	thwcfd_settings.selectAllFields(elm);
}

function thwcfdRemoveSelectedFields(){
	thwcfd_settings.removeSelectedFields();
}

function thwcfdEnableSelectedFields(){
	thwcfd_settings.enableDisableSelectedFields(1);
}

function thwcfdDisableSelectedFields(){
	thwcfd_settings.enableDisableSelectedFields(0);
}

function thwcfdHideReviewRequestNotice(elm){
	thwcfd_settings.hideReviewRequestNotice(elm);
}
