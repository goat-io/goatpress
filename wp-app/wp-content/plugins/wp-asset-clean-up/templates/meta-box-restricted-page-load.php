<?php
if (! isset($data)) {
	exit;
}
// This file can be loaded either via edit post/page from the Dashboard when the plugin is set to not load based on the matched URI
// or in the front-end view when "wpacu_ignore_no_load_option" query string is used (whenever "Manage in the front-end" is enabled)
?>
<?php
if ($data['bulk_unloaded_type'] === 'post_type') {
	$isWooPage = $iconShown = false;

	if (
		(function_exists('is_woocommerce') && is_woocommerce()) ||
		(function_exists('is_cart') && is_cart()) ||
		(function_exists('is_product_tag') && is_product_tag()) ||
		(function_exists('is_product_category') && is_product_category()) ||
		(function_exists('is_checkout') && is_checkout())
	) {
		$isWooPage = true;
		$iconShown = WPACU_PLUGIN_URL . '/assets/icons/woocommerce-icon-logo.svg';
	}

	if (! $iconShown) {
		switch ( $data['post_type'] ) {
			case 'post':
				$dashIconPart = 'post';
				break;
			case 'page':
				$dashIconPart = 'page';
				break;
			case 'attachment':
				$dashIconPart = 'media';
				break;
			default:
				$dashIconPart = 'post';
		}
	}
	?>
    <p>
		<?php if ($isWooPage) { ?>
            <img src="<?php echo $iconShown; ?>" alt="" style="height: 40px !important; margin-top: -6px; margin-right: 5px;" align="middle" /> <strong>WooCommerce</strong>
		<?php } ?>
		<?php if (! $iconShown) { ?><span style="color: #0f6cab;" class="dashicons dashicons-admin-<?php echo $dashIconPart; ?>"></span> <?php } ?> <u><?php echo $data['post_type']; ?></u> <?php if ($data['post_type'] !== 'post') {  echo 'post'; } ?> type.
    </p>
	<?php
}

if ($data['status'] === 5) {
	?>
    <p class="wpacu_verified">
        <strong>Page URL:</strong> <a target="_blank" href="<?php echo $data['fetch_url']; ?>"><span><?php echo $data['fetch_url']; ?></span></a>
    </p>
	<?php
	$msg =__('This page\'s URL is matched by one of the RegEx rules you have in <strong>"Settings"</strong> -&gt; <strong>"Plugin Usage Preferences"</strong> -&gt; <strong>"Do not load the plugin on certain pages"</strong>, thus Asset CleanUp Pro is not loaded on that page and no CSS/JS are to be managed. If you wish to view the CSS/JS manager, please remove the matching RegEx rule and reload this page.', 'wp-asset-clean-up');
	?>
    <p class="wpacu-warning"
       style="margin: 15px 0 0; padding: 10px; font-size: inherit; width: 99%;">
            <span style="color: red;"
                  class="dashicons dashicons-info"></span> <?php echo $msg; ?>
    </p>
	<?php
} elseif ($data['status'] === 6) {
	?>
    <p class="wpacu_verified">
        <strong>Page URL:</strong> <a target="_blank" href="<?php echo $data['fetch_url']; ?>"><span><?php echo $data['fetch_url']; ?></span></a>
    </p>
	<?php
	$msg = sprintf(__('This page\'s URI is matched by the rule you have in the "Page Options", thus %s is not loaded on that page and no CSS/JS are to be managed. If you wish to view the CSS/JS manager, please uncheck the following option shown below: <em>"Do not load Asset CleanUp Pro on this page (this will disable any functionality of the plugin"</em>.', 'wp-asset-clean-up'), WPACU_PLUGIN_TITLE);
	?>
    <p class="wpacu-warning"
       style="margin: 15px 0 0; padding: 10px; font-size: inherit; width: 99%;">
                <span style="color: red;"
                      class="dashicons dashicons-info"></span> <?php echo $msg; ?>
    </p>

    <?php
    if ( isset($_GET['wpacu_ignore_no_load_option']) ) {
        ?>
        <p style="color: #cc0000;"><strong>Note:</strong> You have enabled the following option which is meant to prevent the plugin from loading on this page: <em>"Do not load Asset CleanUp Pro on this page (this will disable any functionality of the plugin)"</em>. To help you manage the assets in the front-end view, for your convenience, the query string "<em>&amp;wpacu_ignore_no_load_option</em>" has been added to the URL (after the settings were saved) which bypasses the inactivation of Asset CleanUp Pro.</small></p>
        <?php
    }
    ?>
<?php
}
$data['show_page_options'] = true;

if ($data['post_id'] > 0) {
	$data['page_options'] = \WpAssetCleanUp\MetaBoxes::getPageOptions( $data['post_id'] );
} elseif (isset($data['wpacu_type']) && $data['wpacu_type'] === 'front_page') {
	$data['page_options'] = \WpAssetCleanUp\MetaBoxes::getPageOptions( 0, 'front_page' );
}

$data['page_options_with_assets_manager_no_load'] = true;

include __DIR__.'/meta-box-loaded-assets/_page-options.php';
