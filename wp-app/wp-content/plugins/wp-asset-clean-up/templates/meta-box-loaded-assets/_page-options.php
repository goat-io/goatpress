<?php
if (! isset($data)) {
	exit; // no direct access
}

if ( ! (isset($data['show_page_options']) && $data['show_page_options']) ) {
    return;
}

// This one shows when there are matches for the targeted page in "Settings" -> "Plugin Usage Preferences" -> "Do not load the plugin on certain pages"
// or via "Do not load Asset CleanUp Pro on this page (this will disable any functionality of the plugin)"
if (isset($data['page_options_with_assets_manager_no_load']) && $data['page_options_with_assets_manager_no_load']) {
?>
        <style>
        .wpacu-page-options a.wpacu-assets-collapsible {
            cursor: default;
        }
        .wpacu-page-options a.wpacu-assets-collapsible.wpacu-assets-collapsible-active:after {
            content: '';
        }
        </style>
    <div class="wpacu-assets-collapsible-wrap wpacu-wrap-area wpacu-page-options">
        <a class="wpacu-assets-collapsible wpacu-assets-collapsible-active" href="#wpacu-assets-collapsible-content-page-options">
            <span class="dashicons dashicons-admin-generic"></span>&nbsp; Page Options
        </a>
        <div style="max-height: inherit;" class="wpacu-assets-collapsible-content wpacu-open">
			<?php
			include_once __DIR__.'/_page-options-inner.php';
			?>
            <img style="display: none;"
                 class="wpacu-ajax-loader"
                 src="<?php echo WPACU_PLUGIN_URL; ?>/assets/icons/icon-ajax-loading-spinner.svg" alt="<?php echo __('Loading'); ?>..." />
        </div>
    </div>
<?php
// This shows along with the CSS & JS manager and it's expandable
} elseif (isset($listAreaStatus)) {
?>
<div class="wpacu-assets-collapsible-wrap wpacu-wrap-area wpacu-page-options">
	<a class="wpacu-assets-collapsible <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-assets-collapsible-active<?php } ?>" href="#wpacu-assets-collapsible-content-page-options">
		<span class="dashicons dashicons-admin-generic"></span>&nbsp; Page Options
	</a>
	<div style="max-height: inherit;" class="wpacu-assets-collapsible-content <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-open<?php } ?>">
        <?php
        include_once __DIR__.'/_page-options-inner.php';
        ?>
        <img style="display: none;"
             class="wpacu-ajax-loader"
             src="<?php echo WPACU_PLUGIN_URL; ?>/assets/icons/icon-ajax-loading-spinner.svg" alt="<?php echo __('Loading'); ?>..." />
    </div>
</div>
<?php
}
?>