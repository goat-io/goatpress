<?php
// no direct access
if (! isset($data)) {
	exit;
}

$listAreaStatus = $data['plugin_settings']['assets_list_layout_areas_status'];

/*
* -------------------------
* [START] BY EACH POSITION
* -------------------------
*/
if (! empty($data['all']['styles']) || ! empty($data['all']['scripts'])) {
	require_once __DIR__.'/_assets-top-area.php';

    $data['view_by_position'] =
    $data['rows_build_array'] =
    $data['rows_by_position'] = true;

    $data['rows_assets'] = array();

    require_once __DIR__.'/_asset-style-rows.php';
    require_once __DIR__.'/_asset-script-rows.php';

    $positionsText = array(
        'head' => '<span class="dashicons dashicons-editor-code"></span>&nbsp; HEAD tag (.css &amp; .js)',
        'body' => '<span class="dashicons dashicons-editor-code"></span>&nbsp; BODY tag (.css &amp; .js)'
    );

    if (! empty($data['rows_assets'])) {
        // Sorting: head and body
        $rowsAssets = array('head' => array(), 'body' => array());

        foreach ($data['rows_assets'] as $positionMain => $values) {
            $rowsAssets[$positionMain] = $values;
        }

        foreach ($rowsAssets as $positionMain => $values) {
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
            <div class="wpacu-assets-collapsible-wrap wpacu-by-position wpacu-wrap-area wpacu-<?php echo $positionMain; ?>">
                <a class="wpacu-assets-collapsible <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-assets-collapsible-active<?php } ?>" href="#wpacu-assets-collapsible-content-<?php echo $positionMain; ?>">
                    <?php echo $positionsText[$positionMain]; ?> &#10141; Total files: <?php echo $totalFiles; ?>
                </a>

                <div class="wpacu-assets-collapsible-content <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-open<?php } ?>">
                    <?php if ($positionMain === 'head') { ?>
                        <p class="wpacu-assets-note">The files below (if any) are loaded within <em>&lt;head&gt;</em> and <em>&lt;/head&gt;</em> tags. The output is done through <em>wp_head()</em> WordPress function which should be located before the closing <em>&lt;/head&gt;</em> tag of your theme.</p>
                    <?php } elseif ($positionMain === 'body') { ?>
                        <p class="wpacu-assets-note">The files below (if any)  are loaded within <em>&lt;body&gt;</em> and <em>&lt;/body&gt;</em> tags. The output is done through <em>wp_footer()</em> WordPress function which should be located before the closing <em>&lt;/body&gt;</em> tag of your theme.</p>
                    <?php } ?>

                    <?php if (count($values) > 0) { ?>
                        <table class="wpacu_list_table wpacu_list_by_position wpacu_widefat wpacu_striped">
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
* -----------------------
* [END] BY EACH POSITION
* -----------------------
*/

include_once __DIR__ . '/_page-options.php';

include '_inline_js.php';
