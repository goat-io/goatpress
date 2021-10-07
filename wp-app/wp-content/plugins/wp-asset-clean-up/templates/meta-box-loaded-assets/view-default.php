<?php
// no direct access
if (! isset($data)) {
	exit;
}

$listAreaStatus = $data['plugin_settings']['assets_list_layout_areas_status'];

require_once __DIR__.'/_assets-top-area.php';

/*
 * ---------------------
 * [START] STYLES LIST
 * ---------------------
 */
?>
<div class="wpacu-assets-collapsible-wrap wpacu-wrap-area">
    <a class="wpacu-assets-collapsible <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-assets-collapsible-active<?php } ?>" href="#wpacu-assets-styles-collapsible-content">
        <span class="dashicons dashicons-admin-appearance"></span> &nbsp; <?php _e('Styles (.css files)', 'wp-asset-clean-up'); ?> &#10141; Total enqueued (+ core files): <?php echo $data['total_styles']; ?>
    </a>

    <div id="wpacu-assets-styles-collapsible-content"
         class="wpacu-assets-collapsible-content <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-open<?php } ?>">
        <div>
            <?php
            if (! empty($data['all']['styles'])) {
                ?>
                <table class="wpacu_list_table wpacu_widefat wpacu_striped">
                    <tbody>
                    <?php
                    require_once __DIR__.'/_asset-style-rows.php';
                    ?>
                    </tbody>
                </table>
                <?php
            } else {
                echo __('It looks like there are no public .css files loaded or the ones visible do not follow <a href="https://codex.wordpress.org/Function_Reference/wp_enqueue_style">the WordPress way of enqueuing styles</a>.', 'wp-asset-clean-up');
            }
            ?>
        </div>
    </div>
</div>
<?php
/*
* -------------------
* [END] STYLES LIST
* -------------------
*/

/*
 * ---------------------
 * [START] SCRIPTS LIST
 * ---------------------
 */
?>
<div class="wpacu-assets-collapsible-wrap wpacu-wrap-area">
    <a class="wpacu-assets-collapsible <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-assets-collapsible-active<?php } ?>" href="#wpacu-assets-scripts-collapsible-content">
        <span class="dashicons dashicons-media-code"></span> &nbsp; <?php _e('Scripts (.js files)', 'wp-asset-clean-up'); ?> &#10141; Total enqueued (+ core files): <?php echo $data['total_scripts']; ?>
    </a>

    <div id="wpacu-assets-scripts-collapsible-content"
         class="wpacu-assets-collapsible-content <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-open<?php } ?>">
        <div>
        <?php
        if (! empty($data['all']['scripts'])) {
            ?>
            <table class="wpacu_list_table wpacu_widefat wpacu_striped">
                <tbody>
                <?php
                require_once __DIR__.'/_asset-script-rows.php';
                ?>
                </tbody>
            </table>
            <?php
        } else {
            echo __('It looks like there are no public .js files loaded or the ones visible do not follow <a href="https://codex.wordpress.org/Function_Reference/wp_enqueue_script">the WordPress way of enqueuing scripts</a>.', 'wp-asset-clean-up');
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

include_once __DIR__ . '/_page-options.php';

include '_inline_js.php';
/*
 * -------------------
 * [END] SCRIPTS LIST
 * -------------------
 */
