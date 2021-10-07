<?php
// no direct access
if (! isset($data)) {
	exit;
}

$listAreaStatus = $data['plugin_settings']['assets_list_layout_areas_status'];

/*
* ----------------------------------------------
* [START] BY EACH HANDLE STATUS (Parent or Not)
* ----------------------------------------------
*/
if (! empty($data['all']['styles']) || ! empty($data['all']['scripts'])) {
    require_once __DIR__.'/_assets-top-area.php';

	$data['view_by_parents'] =
	$data['rows_build_array'] =
	$data['rows_by_parents'] = true;

	$data['rows_assets'] = array();

	require_once __DIR__.'/_asset-style-rows.php';
	require_once __DIR__.'/_asset-script-rows.php';

    $handleStatusesText = array(
        'parent'      => '<span class="dashicons dashicons-groups"></span>&nbsp; \'Parents\' with \'children\' (.css &amp; .js)',
        'child'       => '<span class="dashicons dashicons-admin-users"></span>&nbsp; \'Children\' of \'parents\' (.css &amp; .js)',
        'independent' => '<span class="dashicons dashicons-admin-users"></span>&nbsp; Independent (.css &amp; .js)'
    );


	if (! empty($data['rows_assets'])) {
		// Sorting: parent & non_parent
		$rowsAssets = array('parent' => array(), 'child' => array(), 'independent' => array());

		foreach ($data['rows_assets'] as $handleStatus => $values) {
			$rowsAssets[$handleStatus] = $values;
		}

		foreach ($rowsAssets as $handleStatus => $values) {
			ksort($values);

			$assetRowIndex = 1;

			$assetRowsOutput = '';

			$totalFiles = 0;

			foreach ($values as $assetType => $assetRows) {
				foreach ($assetRows as $assetRow) {
					$assetRowsOutput .= $assetRow . "\n";
					$totalFiles++;
				}
			}
			?>
            <div class="wpacu-assets-collapsible-wrap wpacu-by-parents wpacu-wrap-area wpacu-<?php echo $handleStatus; ?>">
                <a class="wpacu-assets-collapsible <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-assets-collapsible-active<?php } ?>" href="#wpacu-assets-collapsible-content-<?php echo $handleStatus; ?>">
					<?php echo $handleStatusesText[$handleStatus]; ?> &#10141; Total files: <?php echo $totalFiles; ?>
                </a>

                <div class="wpacu-assets-collapsible-content <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-open<?php } ?>">
					<?php if ($handleStatus === 'parent') { ?>
                        <p class="wpacu-assets-note">If you unload any of the files below (if any listed), their 'children' (as listed in green bold font below the handle) will also be unloaded.</p>
					<?php } elseif ($handleStatus === 'child') { ?>
                        <p class="wpacu-assets-note">The following files (if any listed) are 'children' linked to the 'parent' files.</p>
					<?php } elseif ($handleStatus === 'independent') { ?>
                        <p class="wpacu-assets-note">The following files (if any listed) are independent as they are not 'children' or 'parents'.</p>
                    <?php } ?>

                    <?php if (count($values) > 0) { ?>
                        <table class="wpacu_list_table wpacu_list_by_parents wpacu_widefat wpacu_striped">
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
* --------------------------------------------
* [END] BY EACH HANDLE STATUS (Parent or Not)
* --------------------------------------------
*/

include_once __DIR__ . '/_page-options.php';

include '_inline_js.php';
