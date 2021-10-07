<?php
// no direct access
if (! isset($data)) {
	exit;
}
?>

<p><?php echo sprintf(
	__('Please select the styles &amp; scripts that are %sNOT NEEDED%s from the list below. Not sure which ones to unload? %s Use "Test Mode" (to make the changes apply only to you), while you are going through the trial &amp; error process.', 'wp-asset-clean-up'),
	'<span style="color: #CC0000;"><strong>',
	'</strong></span>',
	'<img draggable="false" class="wpacu-emoji" style="max-width: 26px; max-height: 26px;" alt="" src="https://s.w.org/images/core/emoji/11.2.0/svg/1f914.svg">'
); ?></p>

<?php
if ($data['plugin_settings']['hide_core_files']) {
	?>
	<div class="wpacu_note"><span class="dashicons dashicons-info"></span> WordPress CSS &amp; JavaScript core files are hidden as requested in the plugin's settings. They are meant to be managed by experienced developers in special situations.</div>
	<div class="wpacu-clearfix" style="margin-top: 10px;"></div>
	<?php
}

if ( ( (isset($data['core_styles_loaded']) && $data['core_styles_loaded']) || (isset($data['core_scripts_loaded']) && $data['core_scripts_loaded']) ) && ! $data['plugin_settings']['hide_core_files']) {
	?>
	<div class="wpacu_note wpacu_warning">
		<em><?php echo sprintf(
				__('Assets that are marked with %s are part of WordPress core files. Be careful if you decide to unload them! If you are not sure what to do, just leave them loaded by default and consult with a developer.', 'wp-asset-clean-up'),
				'<span class="dashicons dashicons-wordpress-alt wordpress-core-file"></span>' );
			?>
		</em>
	</div>
	<?php
}
?>

<div style="margin: 10px 0;">
	<?php echo $data['assets_list_layout_output']; ?>
</div>

<?php
$imgLoadingSpinner = '<img class="wpacu-ajax-loader" align="top" src="'.admin_url('images/spinner.gif').'" alt="" />';
?>
<div style="margin-bottom: 20px;" class="wpacu-contract-expand-area">
	<div class="col-left">
		<strong>&#10141; Total enqueued files (including core files): <?php echo (int)$data['total_styles'] + (int)$data['total_scripts']; ?></strong>
	</div>
	<div id="wpacu-assets-groups-change-state-area" data-wpacu-groups-current-state="<?php echo $data['plugin_settings']['assets_list_layout_areas_status']; ?>" class="col-right">
        <button id="wpacu-assets-contract-all" class="wpacu-wp-button wpacu-wp-button-secondary"><?php echo $imgLoadingSpinner; ?> <span>Contract All Groups</span></button>&nbsp;
        <button id="wpacu-assets-expand-all" class="wpacu-wp-button wpacu-wp-button-secondary"><?php echo $imgLoadingSpinner; ?> <span>Expand All Groups</span></button>
	</div>
	<div class="wpacu-clearfix"></div>
</div>
