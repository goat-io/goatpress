<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
	exit;
}

include_once '_top-area.php';
?>
<div class="wrap wpacu-overview-wrap">
    <div style="padding: 0 0 10px; line-height: 22px;"><strong>Note:</strong> This overview contains all the changes of any kind (unload rules, load exceptions, preloads, notes, async/defer SCRIPT attributes, changed positions, etc.) made via Asset CleanUp to any of the loaded (enqueued) CSS/JS files. To make any changes to the values below, please use the "CSS &amp; JavaScript Load Manager" or "Bulk Changes" tabs.</div>
    <hr />

    <div style="padding: 0 10px 0 0;">
        <h3><span class="dashicons dashicons-admin-appearance"></span> <?php _e('Stylesheets (.css)', 'wp-asset-clean-up'); ?>
        <?php
        if (isset($data['handles']['styles']) && count($data['handles']['styles']) > 0) {
            echo ' &#10230; Total handles with rules: '.count($data['handles']['styles']);
        }
        ?></h3>
        <?php
        if (isset($data['handles']['styles']) && ! empty($data['handles']['styles'])) {
            ?>
            <table class="wp-list-table wpacu-overview-list-table widefat fixed striped">
                <thead>
                    <tr class="wpacu-top">
                        <td><strong>Handle</strong></td>
                        <td><strong>Unload &amp; Load Exception Rules</strong></td>
                    </tr>
                </thead>
                <?php
                foreach ($data['handles']['styles'] as $handle => $handleData) {
	                // [wpacu_lite]
	                // Traces from the PRO version in case a downgrade to LITE was made
	                $hasProTraces = (strpos($handle, 'wpacu_hardcoded_') !== false);
	                $trStyle = $hasProTraces ? 'opacity: 0.5;' : '';
	                // [/wpacu_lite]
	                ?>
                    <tr class="wpacu_global_rule_row wpacu_bulk_change_row" style="<?php echo $trStyle; ?>">
                        <td>
                            <?php
                            \WpAssetCleanUp\Overview::renderHandleTd($handle, 'styles', $data);

                            if ($hasProTraces) {
	                            echo ' &#10230; Inactive rule left from the PRO version';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $handleData['handle'] = $handle;
                            $handleData['asset_type'] = 'styles';
                            $handleChangesOutput = \WpAssetCleanUp\Overview::renderHandleChangesOutput($handleData);

                            if (! empty($handleChangesOutput)) {
	                            echo '<ul style="margin: 0;">' . "\n";

	                            foreach ( $handleChangesOutput as $handleChangesOutputPart ) {
		                            echo '<li>' . $handleChangesOutputPart . '</li>' . "\n";
	                            }

	                            echo '</ul>';
                            } else {
                                echo '<em style="color: #6d6d6d;">'.__('No unload/load exception rules of any kind are set for this stylesheet file', 'wp-asset-clean-up').'</em>.';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php
        } else {
            ?>
            <p><?php _e('There is no data added to (e.g. unload, load exceptions, notes, changing of location, preloading, etc.) to any stylesheet.', 'wp-asset-clean-up'); ?></p>
            <?php
        }
        ?>

        <hr style="margin: 15px 0;"/>

            <h3><span class="dashicons dashicons-media-code"></span> <?php _e('Scripts (.js)', 'wp-asset-clean-up'); ?>
	        <?php
	        if (isset($data['handles']['scripts']) && count($data['handles']['scripts']) > 0) {
		        echo ' &#10230; Total handles with rules: '.count($data['handles']['scripts']);
	        }
	        ?></h3>
	    <?php
	    if (isset($data['handles']['scripts']) && ! empty($data['handles']['scripts'])) {
		    ?>
            <table class="wp-list-table wpacu-overview-list-table widefat fixed striped">
                <thead>
                    <tr class="wpacu-top">
                        <td><strong>Handle</strong></td>
                        <td><strong>Unload &amp; Load Exception Rules</strong></td>
                    </tr>
                </thead>
			    <?php
			    foreach ($data['handles']['scripts'] as $handle => $handleData) {
				    // [wpacu_lite]
				    // Traces from the PRO version in case a downgrade to LITE was made
                    $hasProTraces = (strpos($handle, 'wpacu_hardcoded_') !== false);
				    $trStyle = $hasProTraces ? 'opacity: 0.5;' : '';
				    // [/wpacu_lite]
				    ?>
                    <tr class="wpacu_global_rule_row wpacu_bulk_change_row" style="<?php echo $trStyle; ?>">
                        <td>
						    <?php
                            \WpAssetCleanUp\Overview::renderHandleTd($handle, 'scripts', $data);

                            if ($hasProTraces) {
                                echo ' &#10230; Inactive rule left from the PRO version';
                            }
						    ?>
                        </td>
                        <td>
	                        <?php
                            $handleData['handle'] = $handle;
	                        $handleData['asset_type'] = 'scripts';
	                        $handleChangesOutput = \WpAssetCleanUp\Overview::renderHandleChangesOutput($handleData);

	                        if (! empty($handleChangesOutput)) {
		                        echo '<ul style="margin: 0;">' . "\n";

		                        foreach ( $handleChangesOutput as $handleChangesOutputPart ) {
			                        echo '<li>' . $handleChangesOutputPart . '</li>' . "\n";
		                        }

		                        echo '</ul>';
	                        } else {
		                        echo '<em style="color: #6d6d6d;">'.__('No unload/load exception rules of any kind are set for this JavaScript file', 'wp-asset-clean-up').'</em>.';
	                        }
	                        ?>
                        </td>
                    </tr>
				    <?php
			    }
			    ?>
            </table>
		    <?php
	    } else {
		    ?>
            <p><?php _e('There is no data added to (e.g. unload, load exceptions, notes, async/defer attributes, changing of location, preloading, etc.) to any SCRIPT tag.', 'wp-asset-clean-up'); ?></p>
		    <?php
	    }
	    ?>

        <hr style="margin: 15px 0;"/>

        <?php
        $hasPostsWithOptions = isset($data['page_options_results']['posts']) && ! empty($data['page_options_results']['posts']);
        $hasHomepageWithOptions = isset( $data['page_options_results']['homepage']['options'] ) && ! empty( $data['page_options_results']['homepage']['options'] );
        $hasAtLeastOneRecord = $hasPostsWithOptions || $hasHomepageWithOptions;
        ?>
        <div id="wpacu-page-options-wrap">
            <h3><span class="dashicons dashicons-admin-generic"></span> <?php _e('Page Options', 'wp-asset-clean-up'); ?></h3>
            <div style="padding: 10px; background: white; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                <?php
                if ($hasAtLeastOneRecord) {
                ?>
                    <p>On the pages listed below, there are special options set. <a target="_blank" style="text-decoration: none;" href="https://www.assetcleanup.com/docs/?p=1318"><span class="dashicons dashicons-info"></span> Read more</a></p>
                    <table class="wp-list-table wpacu-list-table widefat plugins striped" style="margin: 10px 0 0; width: 100%;">
                <?php
                }

                if ( $hasHomepageWithOptions ) {
                    $optionsForCurrentPage = array();

                    foreach ($data['page_options_results']['homepage']['options'] as $optionKey => $optionValue) {
	                    if (isset($data['page_options_to_text'][$optionKey]) && $optionValue) {
		                    $optionsForCurrentPage[] = $data['page_options_to_text'][$optionKey];
	                    }
                    }
                    ?>
                    <tr>
                        <td><span class="dashicons dashicons-admin-home"></span> Homepage (e.g. latest posts)<br /><small><a target="_blank" href="<?php echo get_site_url(); ?>"><?php echo get_site_url(); ?></a></small></td>
                        <td><?php echo implode (', ', $optionsForCurrentPage); ?></td>
                    </tr>
                    <?php
                }

                if ( $hasPostsWithOptions ) {
                    foreach ($data['page_options_results']['posts'] as $results) {
                        $postStatus = $postStatusText = get_post_status($results['post_id']);

	                    $rowStyle = '';

                        if ( ! in_array($postStatus, array('publish', 'private')) ) {
                            $rowStyle = 'style="opacity: 0.6;"';
                            $postStatusText = '<span style="color: #cc0000;">'.$postStatus.'</span>';
                        }
                        ?>
                        <tr <?php echo $rowStyle; ?>>
                            <td><?php echo get_the_title($results['post_id']); ?> / ID: <?php echo $results['post_id']; ?>, Status: <?php echo $postStatusText; ?><br /><small><a target="_blank" href="<?php echo get_permalink($results['post_id']); ?>"><?php echo get_permalink($results['post_id']); ?></a></small></td>
                            <td>
                                <?php
                                $optionsForCurrentPage = array();

                                foreach ($results['options'] as $optionKey => $optionValue) {
                                    if ($optionKey === '_page_uri') {
                                        // Hidden and irrelevant
                                        continue;
                                    }

                                    if (isset($data['page_options_to_text'][$optionKey]) && $optionValue) {
                                        $optionsForCurrentPage[] = $data['page_options_to_text'][$optionKey];
                                    }
                                }

                                echo implode (', ', $optionsForCurrentPage);
                                ?>
                            </td>
                        </tr>
                    <?php
                    }
                }

                if ($hasAtLeastOneRecord) {
                ?>
                    </table>
                <?php
                }
                ?>

                <?php if ( ! $hasAtLeastOneRecord ) { ?>
                    There are no special options set for any page. <a style="text-decoration: none;" target="_blank" href="https://www.assetcleanup.com/docs/?p=1318"><span class="dashicons dashicons-info"></span> Read more</a>
                <?php } ?>
            </div>
        </div>
    </div>
</div>