<?php
// no direct access
if (! isset($data)) {
	exit;
}

$listAreaStatus = $data['plugin_settings']['assets_list_layout_areas_status'];

/*
* --------------------------------------
* [START] BY PRELOAD STATUS (yes or no)
* --------------------------------------
*/
if (! empty($data['all']['styles']) || ! empty($data['all']['scripts'])) {
	require_once __DIR__.'/_assets-top-area.php';

	$data['view_by_preload'] =
	$data['rows_build_array'] =
	$data['rows_by_preload'] = true;

	$data['rows_assets'] = array();

	require_once __DIR__.'/_asset-style-rows.php';
	require_once __DIR__.'/_asset-script-rows.php';

	$preloadsText = array(
        'preloaded'     => '<span class="dashicons dashicons-upload"></span>&nbsp; Preloaded assets (.css &amp; .js)',
        'not_preloaded' => '<span class="dashicons dashicons-download"></span>&nbsp; Not-preloaded (default status) assets (.css &amp; .js)',
    );

	if (! empty($data['rows_assets'])) {
		// Sorting: Preloaded and Not Preloaded (standard loading)
		$rowsAssets = array('preloaded' => array(), 'not_preloaded' => array());

		foreach ($data['rows_assets'] as $preloadStatus => $values) {
			$rowsAssets[$preloadStatus] = $values;
		}

		foreach ($rowsAssets as $preloadStatus => $values) {
			ksort($values);

			$assetRowsOutput = '';

			$totalFiles    = 0;
			$assetRowIndex = 1;

			foreach ($values as $assetType => $assetRows) {
				foreach ($assetRows as $assetRow) {
					$assetRowsOutput .= $assetRow . "\n";
					$totalFiles++;
				}
			}
			?>
            <div class="wpacu-assets-collapsible-wrap wpacu-by-preloads wpacu-wrap-area wpacu-<?php echo $preloadStatus; ?>">
                <a class="wpacu-assets-collapsible <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-assets-collapsible-active<?php } ?>" href="#wpacu-assets-collapsible-content-<?php echo $preloadStatus; ?>">
					<?php echo $preloadsText[$preloadStatus]; ?> &#10141; Total files: <?php echo $totalFiles; ?>
                </a>

                <div class="wpacu-assets-collapsible-content <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-open<?php } ?>">
					<?php if ($preloadStatus === 'preloaded') { ?>
                        <p class="wpacu-assets-note">This is the list of assets (if any) that were chosen to be preloaded through the <span style="background: #e8e8e8; padding: 2px;">&lt;link rel="preload"&gt;</span> tag (any valid option from "Preload (if kept loaded)?" drop-down). Note that the preload option is obviously irrelevant if the asset was chosen to be unloaded. The preload option is ONLY relevant for the assets that are loading in the page.</p>
					    <?php
                        if (count($values) < 1) {
                        ?>
                            <p style="padding: 0 15px 15px;"><strong>There are no assets chosen to be preloaded.</strong></p>
                        <?php
                        }
                        ?>
                    <?php } elseif ($preloadStatus === 'not_preloaded') { ?>
                        <p class="wpacu-assets-note">This is the list of assets that do not have any preload option added to them which is the default way of showing up on the page.</p>
					<?php } ?>

					<?php if (count($values) > 0) { ?>
                        <table class="wpacu_list_table wpacu_list_by_preload wpacu_widefat wpacu_striped">
                            <tbody>
							<?php
							echo $assetRowsOutput;
							?>
                            </tbody>
                        </table>
					<?php } ?>
                </div>
            </div>
			<?php
		}
	}
}

if ( isset( $data['all']['hardcoded'] ) && ! empty( $data['all']['hardcoded'] ) ) {
	$data['print_outer_html'] = true; // AJAX call from the Dashboard
	include_once __DIR__ . '/_assets-hardcoded-list.php';
} elseif (isset($hardcodedManageAreaHtml, $data['is_frontend_view']) && $data['is_frontend_view']) {
	echo $hardcodedManageAreaHtml; // AJAX call from the front-end view
}
/*
* ------------------------------------
* [END] BY PRELOAD STATUS (yes or no)
* ------------------------------------
*/

include_once __DIR__ . '/_page-options.php';

include '_inline_js.php';
