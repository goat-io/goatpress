<?php
// no direct access
if (! isset($data)) {
	exit;
}

$listAreaStatus = $data['plugin_settings']['assets_list_layout_areas_status'];

/*
* ------------------------------
* [START] STYLES & SCRIPTS LIST
* ------------------------------
*/

require_once __DIR__.'/_assets-top-area.php';
?>
<div class="wpacu-assets-collapsible-wrap wpacu-wrap-all">
    <a style="padding: 15px;" class="wpacu-assets-collapsible <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-assets-collapsible-active<?php } ?>" href="#wpacu-assets-collapsible-content">
        <?php _e('Styles (.css files) &amp; Scripts (.js files)', 'wp-asset-clean-up'); ?> &#10141; Total enqueued (+ core files): <?php echo (int)$data['total_styles'] + (int)$data['total_scripts']; ?> (Styles: <?php echo $data['total_styles']; ?>, Scripts: <?php echo $data['total_scripts']; ?>)
    </a>

    <div id="wpacu-assets-collapsible-content"
         class="wpacu-assets-collapsible-content <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-open<?php } ?>">
        <div>
            <?php
            if (! empty($data['all']['styles']) || ! empty($data['all']['scripts'])) {
                ?>
                <table class="wpacu_list_table wpacu_widefat wpacu_striped">
                    <tbody>
                    <?php
                    $data['view_all'] = true;
                    $data['rows_build_array'] = true;
                    $data['rows_assets'] = array();

                    require_once __DIR__.'/_asset-style-rows.php';
                    require_once __DIR__.'/_asset-script-rows.php';

                    if (! empty($data['rows_assets'])) {
                        ksort($data['rows_assets']);

                        foreach ($data['rows_assets'] as $assetRow) {
                            echo $assetRow."\n";
                        }
                    }
                    ?>
                    </tbody>
                </table>
                <?php
            }
            ?>
        </div>
    </div>
</div>
<?php
if ( isset( $data['all']['hardcoded'] ) && ! empty( $data['all']['hardcoded'] ) ) {
	$data['print_outer_html'] = true; // AJAX call from the Dashboard
	include_once __DIR__ . '/_assets-hardcoded-list.php';
} elseif (isset($hardcodedManageAreaHtml, $data['is_frontend_view']) && $data['is_frontend_view']) {
	echo $hardcodedManageAreaHtml; // AJAX call from the front-end view
}

/*
 * -----------------------------
 * [END] STYLES & SCRIPTS LIST
 * -----------------------------
 */

include_once __DIR__ . '/_page-options.php';

include '_inline_js.php';
