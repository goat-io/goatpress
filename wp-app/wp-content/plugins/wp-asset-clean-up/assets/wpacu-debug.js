/* Dashboard */
function wpacuChangeDebugAdminArea()
{
    var $adminMenuWrap = jQuery('#adminmenuwrap'),
        $wpacuDebugAdminArea = jQuery('#wpacu-debug-admin-area');

    if ($adminMenuWrap.length > 0 && $adminMenuWrap.is(':visible')) {
        $wpacuDebugAdminArea.css('margin-left', $adminMenuWrap.width() + 'px');
    } else {
        $wpacuDebugAdminArea.css('margin-left', '0');
    }
}

if (jQuery('body.wp-admin').length > 0) {
    window.addEventListener('resize', function () {
        wpacuChangeDebugAdminArea();
    });

    jQuery(document).ready(function ($) {
        wpacuChangeDebugAdminArea();
    });
}

/* Front-end */
jQuery(document).ready(function($) {
    var wpacuCheckboxDebugInput = '.wpacu_plugin_unload_debug_checkbox';

    if ($(wpacuCheckboxDebugInput).length < 1) {
        return;
    }

    $(wpacuCheckboxDebugInput).change(function() {
        if ($(this).prop('checked')) {
            $(this).parents('tr.wpacu_plugin_row_debug_unload').addClass('wpacu_plugin_row_debug_unload_marked');
        } else {
            $(this).parents('tr.wpacu_plugin_row_debug_unload').removeClass('wpacu_plugin_row_debug_unload_marked');
        }
    });
});
