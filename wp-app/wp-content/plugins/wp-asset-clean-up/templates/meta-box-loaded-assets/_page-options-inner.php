<?php
if (! isset($data)) {
	exit; // no direct access
}

$cssUlStyle = (isset($data['is_frontend_view']) && $data['is_frontend_view']) ? 'style="list-style: none; margin-left: 10px;";' : '';
?>
<p>If you have reasons to prevent certain functionality of the plugin on this page, you can use the options below. For instance, you found out that combining CSS files on this page is not working well and you want to disable it. Moreover, if <?php echo WPACU_PLUGIN_TITLE; ?> is not working well for this page (e.g. it's in conflict with another plugin) and you want to avoid loading it, you can choose to do that as well.</p>
<ul id="wpacu-page-options-ul" <?php echo $cssUlStyle; ?>>
	<li>
		<label for="wpacu_page_options_no_css_minify">
			<input type="checkbox"
				<?php if (isset($data['page_options']['no_css_minify']) && $data['page_options']['no_css_minify']) { echo 'checked="checked"'; } ?>
				   id="wpacu_page_options_no_css_minify"
				   name="<?php echo WPACU_PLUGIN_ID; ?>_page_options[no_css_minify]"
				   value="1" />&nbsp;<?php _e('Do not minify CSS files on this page', 'wp-asset-clean-up'); ?>
		</label>
	</li>
	<li>
		<label for="wpacu_page_options_no_css_optimize">
			<input type="checkbox"
				<?php if (isset($data['page_options']['no_css_optimize']) && $data['page_options']['no_css_optimize']) { echo 'checked="checked"'; } ?>
				   id="wpacu_page_options_no_css_optimize"
				   name="<?php echo WPACU_PLUGIN_ID; ?>_page_options[no_css_optimize]"
				   value="1" />&nbsp;<?php _e('Do not combine CSS files on this page', 'wp-asset-clean-up'); ?>
		</label>
	</li>

	<li>
		<label for="wpacu_page_options_no_js_minify">
			<input type="checkbox"
				<?php if (isset($data['page_options']['no_js_minify']) && $data['page_options']['no_js_minify']) { echo 'checked="checked"'; } ?>
				   id="wpacu_page_options_no_js_minify"
				   name="<?php echo WPACU_PLUGIN_ID; ?>_page_options[no_js_minify]"
				   value="1" />&nbsp;<?php _e('Do not minify JS files on this page', 'wp-asset-clean-up'); ?>
		</label>
	</li>
	<li>
		<label for="wpacu_page_options_no_js_optimize">
			<input type="checkbox"
				<?php if (isset($data['page_options']['no_js_optimize']) && $data['page_options']['no_js_optimize']) { echo 'checked="checked"'; } ?>
				   id="wpacu_page_options_no_js_optimize"
				   name="<?php echo WPACU_PLUGIN_ID; ?>_page_options[no_js_optimize]"
				   value="1" />&nbsp;<?php _e('Do not combine JS files on this page', 'wp-asset-clean-up'); ?>
		</label>
	</li>

	<li>
		<label for="wpacu_page_options_no_assets_settings">
			<input type="checkbox"
				<?php if (isset($data['page_options']['no_assets_settings']) && $data['page_options']['no_assets_settings']) { echo 'checked="checked"'; } ?>
				   id="wpacu_page_options_no_assets_settings"
				   name="<?php echo WPACU_PLUGIN_ID; ?>_page_options[no_assets_settings]"
				   value="1" />&nbsp;<?php _e('Do not apply any front-end optimization on this page (this includes any changes related to CSS/JS files)', 'wp-asset-clean-up'); ?>
		</label>
	</li>

	<li>
		<label for="wpacu_page_options_no_wpacu_load">
			<input type="checkbox"
				<?php if (isset($data['page_options']['no_wpacu_load']) && $data['page_options']['no_wpacu_load']) { echo 'checked="checked"'; } ?>
				   id="wpacu_page_options_no_wpacu_load"
				   name="<?php echo WPACU_PLUGIN_ID; ?>_page_options[no_wpacu_load]"
				   value="1" />&nbsp;<?php echo sprintf(__('Do not load %s on this page (this will disable any functionality of the plugin)', 'wp-asset-clean-up'), WPACU_PLUGIN_TITLE); ?>
		</label>
	</li>
</ul>
<hr/>
<p style="margin-top: 10px;">
	<strong><span style="color: #82878c;" class="dashicons dashicons-lightbulb"></span></strong>
	<?php echo sprintf(__('If you are not sure how these options work, you can %sread more about them%s in the documentation.', 'wp-asset-clean-up'), '<a target="_blank" href="https://www.assetcleanup.com/docs/?p=1318">', '</a>'); ?>
</p>
<input type="hidden" name="wpacu_page_options_area_loaded" value="1" />
