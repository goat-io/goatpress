<?php
// no direct access
if (! isset($data)) {
	exit;
}

$listAreaStatus = $data['plugin_settings']['assets_list_layout_areas_status'];

/*
* --------------------------------------
* [START] BY (ANY) RULES SET (yes or no)
* --------------------------------------
*/

if (! empty($data['all']['styles']) || ! empty($data['all']['scripts'])) {
    require_once __DIR__.'/_assets-top-area.php';

	$data['view_by_rules'] =
	$data['rows_build_array'] =
	$data['rows_by_rules'] = true;

	$data['rows_assets'] = array();

	require_once __DIR__.'/_asset-style-rows.php';
	require_once __DIR__.'/_asset-script-rows.php';

	$rulesText = array(
        'with_rules'    => '<span class="dashicons dashicons-star-filled"></span>&nbsp; Styles &amp; Scripts with at least one rule',
        'with_no_rules' => '<span class="dashicons dashicons-star-empty"></span>&nbsp; Styles &amp; Scripts without any rules',
    );

	if (! empty($data['rows_assets'])) {
		// Sorting: With (any) rules and without rules (loaded and without alterations to the tags such as async/defer attributes)
		$rowsAssets = array('with_rules' => array(), 'with_no_rules' => array());

		foreach ($data['rows_assets'] as $rulesStatus => $values) {
			$rowsAssets[$rulesStatus] = $values;
		}

		foreach ($rowsAssets as $rulesStatus => $values) {
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
            <div class="wpacu-assets-collapsible-wrap wpacu-by-rules wpacu-wrap-area wpacu-<?php echo $rulesStatus; ?>">
                <a class="wpacu-assets-collapsible <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-assets-collapsible-active<?php } ?>" href="#wpacu-assets-collapsible-content-<?php echo $rulesStatus; ?>">
					<?php echo $rulesText[$rulesStatus]; ?> &#10141; Total enqueued files: <?php echo $totalFiles; ?>
                </a>

                <div class="wpacu-assets-collapsible-content <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-open<?php } ?>">
					<?php if ($rulesStatus === 'with_rules') { ?>
                        <p class="wpacu-assets-note">This is the list of enqueued CSS &amp; JavaScript files that have AT LEAST ONE RULE applied to them on this page. The rule could be one of the following: unloaded, preloaded, async/defer attributes applied &amp; changed location (e.g. from HEAD to BODY or vice-versa).</p>
					    <?php
                        if (count($values) < 1) {
                        ?>
                            <p style="padding: 0 15px 15px;"><strong>No rules were applied to any of the enqueued CSS/JS files from this page.</strong></p>
                        <?php
                        }
                        ?>
                    <?php } elseif ($rulesStatus === 'with_no_rules') { ?>
                        <p class="wpacu-assets-note">This is the list of enqueued CSS &amp; JavaScript files that have NO RULES applied to them on this page. They are loaded by default in their original location (e.g. HEAD or BODY) without any attributes applied to them (e.g. async/defer).</p>
					<?php } ?>

					<?php if (count($values) > 0) { ?>
                        <table class="wpacu_list_table wpacu_list_by_rules wpacu_widefat wpacu_striped">
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
* -------------------------------------
* [END] BY (ANY) RULES SET (yes or no)
* -------------------------------------
*/
include_once __DIR__ . '/_page-options.php';

include '_inline_js.php';
