<?php
namespace WpAssetCleanUp;

use WpAssetCleanUp\OptimiseAssets\OptimizeCommon;

/**
 * Class Update
 * @package WpAssetCleanUp
 */
class Update
{
    /**
     *
     */
    const NONCE_ACTION_NAME = 'wpacu_data_update';
    /**
     *
     */
    const NONCE_FIELD_NAME = 'wpacu_data_nonce';

	/**
	 * @var bool
	 */
	public $frontEndUpdateTriggered = false;

	/**
	 * @var array
	 */
	public $frontEndUpdateFor = array(
        'homepage' => false,
        'page'     => false
    );

	/**
	 * @var array
	 */
	public $afterSubmitMsg = array();

	/**
	 * Update constructor.
	 */
	public function __construct()
	{
	    $homePageSettingsUpdatedText      = __('The homepage\'s settings were updated. Please make sure the homepage\'s cache is cleared (if you\'re using a caching plugin or a server-side caching solution) to immediately have the changes applied for every visitor.', 'wp-asset-clean-up');
		$this->afterSubmitMsg['homepage'] = <<<HTML
<span class="dashicons dashicons-yes"></span> {$homePageSettingsUpdatedText}
HTML;

		$pageSettingsUpdatedText      = __('This page\'s settings were updated. Please make sure the page\'s cache is cleared (if you\'re using a caching plugin or a server-side caching solution) to immediately have the changes applied for every visitor.', 'wp-asset-clean-up');
		$this->afterSubmitMsg['page'] = <<<HTML
<span class="dashicons dashicons-yes"></span> {$pageSettingsUpdatedText}
HTML;

		$invalidNonceText = sprintf(
            __('The changes were not saved because the security nonce has expired (it took over 24 hours since you loaded this page) or it was not sent for verification in the first place because the form was partially submitted due to the input fields being stripped.', 'wp-asset-clean-up'),
            ini_get('max_input_vars')
        );

		$maxInputVarsValue = (int)@ini_get('max_input_vars');

		if ($maxInputVarsValue === 1000) {
			$invalidNonceText .= ' '. sprintf(__('The value of <strong>max_input_vars</strong> is <strong>1000</strong> which is the default one in many hosting accounts. Increase it to a higher number, ideally over %d.'), 4000);
        } elseif ($maxInputVarsValue < 1000) {
			$invalidNonceText .= ' '. sprintf(__('The value of <strong>max_input_vars</strong> is <strong>%d</strong>. That\'s below <strong>1000</strong> (the default one in many hosting accounts). Increase it to a higher number, ideally over %d.'), $maxInputVarsValue, 4000);
		} else {
			$invalidNonceText .= ' '. sprintf(__('The value of <strong>max_input_vars</strong> is <strong>%d</strong>. You might need to increase it to a higher number.'), $maxInputVarsValue);
        }

		$invalidNonceText .= ' <a target="_blank" href="https://www.assetcleanup.com/docs/?p=1346">'.__('How to fix it?', 'wp-asset-clean-up').'</a>';

		$this->afterSubmitMsg['invalid_nonce_error'] = <<<HTML
<span style="color: #cc0000;" class="dashicons dashicons-dismiss"></span> {$invalidNonceText}
HTML;
	}

	/**
     *
     */
    public function init()
    {
    	// Triggers on front-end view
        add_action('init', array($this, 'triggersAfterInit'), 11);

        // After post/page is saved - update your styles/scripts lists
        // This triggers ONLY in the Dashboard after "Update" button is clicked (on Edit mode)
        add_action('save_post', array($this, 'savePost'));

        // This is to update the permalink for the post in "Page Options" if the following option was ever used for the post: "Do not load Asset CleanUp Pro on this page (this will disable any functionality of the plugin)"
	    add_action('post_updated', array($this, 'afterPostUpdate'), 10, 3);

        // Clear cache (via AJAX) only if the user is logged-in (with the right privileges)
	    add_action('wp_ajax_' . WPACU_PLUGIN_ID . '_clear_cache', array($this, 'ajaxClearCache'), PHP_INT_MAX);

	    // After an update, preload the page for the guest view (the preload for the admin is done within script.min.js own plugin file)
	    add_action('wp_ajax_' . WPACU_PLUGIN_ID . '_preload', array($this, 'ajaxPreloadGuest'), PHP_INT_MAX);

	    // e.g. when "+" or "-" is used within an asset's row (CSS/JS manager), the state is updated in the background to be remembered
	    add_action( 'wp_ajax_' . WPACU_PLUGIN_ID . '_update_asset_row_state', array($this, 'ajaxUpdateAssetRowState') );
	    add_action( 'wp_ajax_nopriv_' . WPACU_PLUGIN_ID . '_update_asset_row_state', array($this, 'ajaxUpdateAssetRowState') );
    }

	/**
	 *
	 */
	public function triggersAfterInit()
    {
	    if (! is_admin() && Main::instance()->frontendShow()) {
		    if (! empty($_POST)) {
			    $wpacuAction = Misc::isElementorMaintenanceModeOn() ? 'template_redirect' : 'wp';

			    if ($wpacuAction === 'wp') {
				    add_action( 'wp', array( $this, 'frontendUpdate' ), 9 );
			    } else {
				    add_action( 'template_redirect', array( $this, 'frontendUpdate' ), 15 );
                }
		    }

		    add_action('template_redirect', array($this, 'redirectAfterFrontEndUpdate'), 16);
	    }
    }

    /**
     *
     */
    public function frontendUpdate()
    {
        $postId = 0;

        if (Main::instance()->currentPostId > 0) {
            $postId = Main::instance()->currentPostId;
        }

        // Check nonce
        $nonceName = self::NONCE_FIELD_NAME;
        $nonceAction = self::NONCE_ACTION_NAME;

        $updateAction = Misc::getVar('post', 'wpacu_update_asset_frontend');

        if ($updateAction != 1 || ! Main::instance()->frontendShow()) {
            return;
        }

        // only for admins
        if (! Menu::userCanManageAssets()) {
            return;
        }

        if ( ! wp_verify_nonce($_POST[$nonceName], $nonceAction) ) {
            wp_die(
	            $this->afterSubmitMsg['invalid_nonce_error'],
                __('Nonce is missing or has expired', 'wp-asset-clean-up')
            );
        }

        $this->frontEndUpdateTriggered = true;

        // Was the Assets List Layout changed?
        self::updateAssetListLayoutSettings();

        // Form submitted from the homepage
	    // e.g. from a page such as latest blog posts, not a static page that was selected as home page)
        if (Misc::isHomePage() && Misc::getShowOnFront() !== 'page') {
	        $wpacuNoLoadAssets = Misc::getVar('post', WPACU_PLUGIN_ID, array());

            $this->updateFrontPage($wpacuNoLoadAssets);
            return;
        }

	    // Form submitted from a Singular Page
	    // e.g. post, page, custom post type such as 'product' page from WooCommerce, home page (static page selected as front page)

        // Sometimes, there's a singular page set as 404 page (e.g. via "404page – your smart custom 404 error page" plugin)
        if (is_404() && Misc::getVar('post', 'wpacu_is_singular_page')) {
            $postId = (int)$_POST['wpacu_is_singular_page'];
        }

        if ($postId > 0) {
            $post = get_post($postId);
            $this->savePost($post->ID, $post);
            return;
        }

        // Any preloads
	    Preloads::updatePreloads();

        // Any handle notes?
	    self::updateHandleNotes();

	    // Any always load it if user is logged in?
        self::saveGlobalLoadExceptions();

	    // Any ignore deps
	    self::updateIgnoreChild();

	    self::clearTransients();
    }

	/**
	 *
	 */
	public static function updateAssetListLayoutSettings()
    {
	    // Was the Assets List Layout changed?
	    if ($assetsListLayout = Misc::getVar('post', 'wpacu_assets_list_layout')) {
		    $settingsClass = new Settings();
		    $settingsClass->updateOption('assets_list_layout', $assetsListLayout);
	    }
    }

	/**
	 *
	 */
	public function redirectAfterFrontEndUpdate()
    {
    	// It triggers ONLY on front-end view, when a valid POST request is made
    	if (! $this->frontEndUpdateTriggered || is_admin() || ! Misc::getVar('post', 'wpacu_unload_assets_area_loaded')) {
    		return;
	    }

	    $parseUrl = parse_url($_SERVER['REQUEST_URI']);

	    $location = $parseUrl['path'];

	    $paramsToAdd = array(
		    'wpacu_time'    => time(),
		    'nocache'       => 'true',
            'wpacu_updated' => 'true',
            'wpacu_ignore_no_load_option' => 1
	    );

	    $extraParamsSign = '?';

	    if (isset($parseUrl['query']) && $parseUrl['query']) {
		    parse_str($parseUrl['query'], $existingQueryParams);

		    foreach (array_keys($paramsToAdd) as $paramKey) {
			    if ( isset( $existingQueryParams[$paramKey] ) ) {
				    unset( $existingQueryParams[$paramKey] );
			    }
		    }

		    if (! empty($existingQueryParams)) {
			    $location .= '?'.http_build_query($existingQueryParams);
			    $extraParamsSign = '&';
		    }
	    }

	    $location .= $extraParamsSign . http_build_query($paramsToAdd) . '#wpacu_wrap_assets';

	    set_transient('wpacu_page_just_updated', 1, 30);

	    wp_safe_redirect($location);
	    exit();
    }

    /**
     * Save post metadata when a post is saved (not for the "Latest Blog Posts" home page type)
     * Only for post type
     *
     * Dashboard view: triggered via hook
     * Front-end view: triggered by direct call
     *
     * @param $postId
     * @param mixed $post
     */
    public function savePost($postId, $post = '')
    {
	    if (empty($post) || $post === '') {
		    global $post;
	    }

	    if (! isset($post->ID) || ! isset($post->post_type)) {
		    return;
	    }

	    // Any page options set? From the Side Meta Box "Asset CleanUp: Options"
	    // Could be just these fields available in the form (e.g. unavailable CSS/JS manager due to the page set to not load the plugin at all)
	    $this->updatePageOptions($post->ID);

	    // This is triggered only if the "Asset CleanUp" meta box was loaded with the list of assets (either in edit post/page or in "CSS & JS Manager" -> "Manage CSS/JS")
	    // Otherwise, $_POST[WPACU_PLUGIN_ID] will be taken as empty which might be not if there are values in the database
    	if (! Misc::getVar('post', 'wpacu_unload_assets_area_loaded')) {
    	    return;
	    }

        // Has to be a public post type
        $obj = get_post_type_object($post->post_type);

        if ($obj->public < 1) {
            return;
        }

        // only for admins
        if (! Menu::userCanManageAssets()) {
            return;
        }

        $wpacuNoLoadAssets = Misc::getVar('post', WPACU_PLUGIN_ID, array());

        if (is_array($wpacuNoLoadAssets)) {
            global $wpdb;

            $noUpdate = false;

            // Is the list empty?
            if (empty($wpacuNoLoadAssets)) {
                // Remove any row with no results
                $wpdb->delete(
                    $wpdb->postmeta,
                    array('post_id' => $postId, 'meta_key' => '_' . WPACU_PLUGIN_ID . '_no_load')
                );
                $noUpdate = true;
            }

            if (! $noUpdate) {
                $jsonNoAssetsLoadList = json_encode($wpacuNoLoadAssets);

                if (! add_post_meta($postId, '_' . WPACU_PLUGIN_ID . '_no_load', $jsonNoAssetsLoadList, true)) {
                    update_post_meta($postId, '_' . WPACU_PLUGIN_ID . '_no_load', $jsonNoAssetsLoadList);
                }
            }
        }

        // Was the Assets List Layout changed?
	    self::updateAssetListLayoutSettings();

        // If globally disabled, make an exception to load for submitted assets
        $this->saveLoadExceptions('post', $postId);
        $this->saveLoadExceptionsPostType();

	    // Add / Remove Site-wide Unloads
	    $this->updateEverywhereUnloads();

        // Any bulk unloads or removed? (e.g. all pages of a certain post type)
	    $this->saveToBulkUnloads($post);
	    $this->removeBulkUnloads($post->post_type);

        // Any preloads
	    Preloads::updatePreloads();

	    // Any handle notes
	    self::updateHandleNotes();

	    // Any always load it if user is logged in?
	    self::saveGlobalLoadExceptions();

	    // Any ignore deps
	    self::updateIgnoreChild();

	    add_action('wpacu_admin_notices', array($this, 'pageUpdated'));

	    self::clearTransients();

	    // In case Combine CSS/JS was enabled and there are traces of JSON files in the caching directory
        // Clear them if the caching timing expired as they are not relevant anymore and reduce the disk's space
	    OptimizeCommon::clearJsonStorageForPost($postId, true);

	    // Note: Cache is cleared (except the JSON files related to CSS/JS combine option) after the post/page is updated via a separate AJAX call
	    // To avoid the usage of too much memory (good for shared environments) and avoid any memory related errors showing up to the user which could be confusing
    }

	/**
     * This takes action when the CSS/JS manager from edit post/page is used
     *
	 * @param $postId
	 * @param $after
	 */
	public function afterPostUpdate($postId, $afterPostObj, $beforePostObj)
    {
        global $wpdb;

        // The post might have the following page option: "Do not load Asset CleanUp Pro on this page (this will disable any functionality of the plugin)"
        // If the admin changed the slug, we need to update the page URI as well that is used very early in the triggering of the plugin
        // when get_permalink() is not available (e.g. outside any action hook or in the MU plugin)
	    $pageOptionJson = $wpdb->get_var( 'SELECT meta_value FROM `' . $wpdb->prefix . 'postmeta` WHERE post_id=\''.$postId.'\' && meta_key=\'_'.WPACU_PLUGIN_ID.'_page_options\' && meta_value LIKE \'%no_wpacu_load%\'' );

	    $postPageOptions = @json_decode($pageOptionJson, ARRAY_A);

	    if ( ! isset($postPageOptions['no_wpacu_load']) ) {
	        return;
        }

	    if ($afterPostObj->post_name !== $beforePostObj->post_name) {
		    $postPageOptions['_page_uri'] = Misc::getPageUri($postId);
		    update_post_meta($postId, '_' . WPACU_PLUGIN_ID . '_page_options', json_encode(Misc::filterList($postPageOptions)));
        }
    }

    /**
     * @param $wpacuNoLoadAssets
     */
    public function updateFrontPage($wpacuNoLoadAssets)
    {
	    // Any page options set? From the Side Meta Box "Asset CleanUp: Options"
	    // Could be just these fields available in the form (e.g. unavailable CSS/JS manager due to the page set to not load the plugin at all)
	    $this->updatePageOptions(0, 'front_page');

	    // Needed in case the user clicks "Update" on a page without assets retrieved
	    // Avoid resetting the existing values
	    if (! Misc::getVar('post', 'wpacu_unload_assets_area_loaded')) {
		    return;
	    }

	    if (! is_array($wpacuNoLoadAssets)) {
            return; // only arrays (empty or not) should be used
        }

	    // Was the Assets List Layout changed?
	    self::updateAssetListLayoutSettings();

        $jsonNoAssetsLoadList = json_encode( $wpacuNoLoadAssets );
        Misc::addUpdateOption( WPACU_PLUGIN_ID . '_front_page_no_load', $jsonNoAssetsLoadList );

        // If globally disabled, make an exception to load for submitted assets
        $this->saveLoadExceptions('front_page');

        // Add / Remove Site-wide Unloads
		$this->updateEverywhereUnloads();

	    // Any preloads
	    Preloads::updatePreloads();

		// Any handle notes
        self::updateHandleNotes();

	    // Any always load it if user is logged in?
	    self::saveGlobalLoadExceptions();

        // Any ignore deps
        self::updateIgnoreChild();

	    add_action('wpacu_admin_notices', array($this, 'homePageUpdated'));

        $this->frontEndUpdateFor['homepage'] = true;

	    self::clearTransients();

	    // Clear all cache
	    // Note: The cache is cleared after the page is saved
    }

	/**
	 *
	 */
	public function homePageUpdated()
    {
	?>
	    <div class="updated notice wpacu-notice is-dismissible">
		    <p><?php echo $this->afterSubmitMsg['homepage']; ?></p>
	    </div>
	<?php
    }

	/**
	 *
	 */
	public function pageUpdated()
	{
		?>
        <div class="updated notice wpacu-notice is-dismissible">
            <p><?php echo $this->afterSubmitMsg['page']; ?></p>
        </div>
		<?php
	}

	/**
	 *
	 */
	public function changesNotMadeInvalidNonce()
    {
        ?>
        <div class="error notice wpacu-error is-dismissible">
            <p><?php echo $this->afterSubmitMsg['invalid_nonce_error']; ?></p>
        </div>
        <?php
    }

	/**
	 * Lite: For Singular Page (Post, Page, Custom Post Type), Front Page (Home Page), On All Pages of a specific post type (post, page or custom)
	 * Pro: 'for_pro' would trigger the actions from the premium extension (if available)
     * UPDATE: Since v1.2.9.5, no fallback for both the lite and pro version activated at the same time would work anymore
     * Users need to only keep the PRO version since it's standalone since v1.0.3
	 *
	 * This is the function that clears and updates the load exceptions for any of the requested pages
	 *
	 * This method SHOULD NOT be triggered within an AJAX call
	 *
	 * @param string $type
	 * @param string $postId
	 */
	public function saveLoadExceptions($type = 'post', $postId = '')
    {
        if ( $type === 'post' && ! $postId ) {
            // $postId needs to have a value if $type is a 'post' type
            return;
        }

        $loadExceptionsStyles = $loadExceptionsScripts = array();

        // [Start] Clear existing list first
        if ($type === 'post') {
            delete_post_meta($postId, '_' . WPACU_PLUGIN_ID . '_load_exceptions');
        } elseif ($type === 'front_page') {
            delete_option( WPACU_PLUGIN_ID . '_front_page_load_exceptions');
        }
	    // [End] Clear existing list first

        // Load Exception
        // On this page or page type such as 404, search, etc.
        if (isset($_POST['wpacu_styles_load_it']) && ! empty($_POST['wpacu_styles_load_it'])) {
            foreach ($_POST['wpacu_styles_load_it'] as $wpacuHandle) {
                // Do not append it if the global unload is removed
                if (isset($_POST['wpacu_options_styles'][$wpacuHandle])
                    && $_POST['wpacu_options_styles'][$wpacuHandle] === 'remove') {
                    continue;
                }
                $loadExceptionsStyles[] = $wpacuHandle;
            }
        }

        if (! empty($_POST['wpacu_scripts_load_it'])) {
            foreach ($_POST['wpacu_scripts_load_it'] as $wpacuHandle) {
                // Do not append it if the global unload is removed
                if (isset($_POST['wpacu_options_scripts'][$wpacuHandle])
                    && $_POST['wpacu_options_scripts'][$wpacuHandle] === 'remove') {
                    continue;
                }
                $loadExceptionsScripts[] = $wpacuHandle;
            }
        }

        if (! empty($loadExceptionsStyles) || ! empty($loadExceptionsScripts)) {
            // Default
            $list =  array('styles' => array(), 'scripts' => array());

            // Build list
            if (! empty($loadExceptionsStyles)) {
                foreach ($loadExceptionsStyles as $postHandle) {
                    $list['styles'][] = $postHandle;
                }
            }

            if (! empty($loadExceptionsScripts)) {
                foreach ($loadExceptionsScripts as $postHandle) {
                    $list['scripts'][] = $postHandle;
                }
            }

            if (is_array($list['styles'])) {
                $list['styles'] = array_unique($list['styles']);
            }

            if (is_array($list['scripts'])) {
                $list['scripts'] = array_unique($list['scripts']);
            }

            $jsonLoadExceptions = json_encode(Misc::filterList($list));

            if ( $type === 'post' && (! add_post_meta($postId, '_' . WPACU_PLUGIN_ID . '_load_exceptions', $jsonLoadExceptions, true)) ) {
                update_post_meta( $postId, '_' . WPACU_PLUGIN_ID . '_load_exceptions', $jsonLoadExceptions );
            } elseif ($type === 'front_page') {
	            Misc::addUpdateOption( WPACU_PLUGIN_ID . '_front_page_load_exceptions', $jsonLoadExceptions );
            }
        }
    }

	/**
	 *
	 */
	public function saveLoadExceptionsPostType()
    {
	    // On all pages belonging to a (custom) post type (e.g. WooCommerce product page)
	    if (isset($_POST['wpacu_styles_load_it_post_type']) && ! empty($_POST['wpacu_styles_load_it_post_type'])) {
		    $wpacuPostType = key($_POST['wpacu_styles_load_it_post_type']);
		    $loadExceptionsStyles = $_POST['wpacu_styles_load_it_post_type'][$wpacuPostType];
		    }

	    if (isset($_POST['wpacu_scripts_load_it_post_type']) && ! empty($_POST['wpacu_scripts_load_it_post_type'])) {
		    $wpacuPostType = key($_POST['wpacu_scripts_load_it_post_type']);
		    $loadExceptionsScripts = $_POST['wpacu_scripts_load_it_post_type'][$wpacuPostType];
	    }

	    if ((! empty($loadExceptionsStyles) || ! empty($loadExceptionsScripts)) && (isset($wpacuPostType) && $wpacuPostType)) {
		    // Default
		    $listToSave = array( 'styles' => array(), 'scripts' => array() );

		    // Build list
		    if (! empty($loadExceptionsStyles)) {
		        $listToSave['styles'] = $loadExceptionsStyles;
		    }

		    if (! empty($loadExceptionsScripts)) {
		        $listToSave['scripts'] = $loadExceptionsScripts;
		    }

		    $jsonLoadExceptionsToAdd = json_encode(array($wpacuPostType => $listToSave));

		    $optionToUpdate = WPACU_PLUGIN_ID . '_post_type_load_exceptions';

		    $existingListEmpty = array( $wpacuPostType => array( 'styles' => array(), 'scripts' => array() ) );
		    $existingListJson = get_option($optionToUpdate);

		    $existingListData = Main::instance()->existingList($existingListJson, $existingListEmpty);
		    $existingList = $existingListData['list'];

		    if ( $existingListJson && is_array($existingList) && ! empty($existingList) ) {
                if (isset($existingList[$wpacuPostType])) {
                    foreach ($listToSave as $assetType => $assetValues) {
                        foreach ($assetValues as $assetHandle => $assetValue) {
                            $existingList[ $wpacuPostType ][ $assetType ][ $assetHandle ] = $assetValue;
                        }
                    }
                } else {
                    $existingList[$wpacuPostType] = $listToSave;
                }

                // Clear empty (redundant) values
			    foreach ($existingList as $wpacuPostType => $assetTypes) {
				    foreach ($assetTypes as $assetType => $assetValues) {
				        if (empty($assetValues)) {
				            unset($existingList[$wpacuPostType][$assetType]);
				        }

				        foreach ($assetValues as $assetHandle => $assetValue) {
					        if ( $assetValue === '' ) {
						        unset( $existingList[ $wpacuPostType ][ $assetType ][ $assetHandle ] );
					        }

					        if (empty($existingList[ $wpacuPostType ][ $assetType ])) {
						        unset( $existingList[$wpacuPostType][$assetType] );
					        }

					        if (empty($existingList[$wpacuPostType])) {
						        unset($existingList[$wpacuPostType]);
					        }
				        }
				    }
			    }

			    Misc::addUpdateOption( $optionToUpdate, json_encode($existingList) );
		    } else {
			    Misc::addUpdateOption( $optionToUpdate, $jsonLoadExceptionsToAdd );
		    }
	    }
    }

	/**
	 * e.g. Always load the handle (if unloaded by any rule) if the user is logged-in (applies site-wide)
	 */
	public static function saveGlobalLoadExceptions()
    {
	    $optionToUpdate  = WPACU_PLUGIN_ID . '_global_data';
	    $formTargetKey   = 'wpacu_load_it_logged_in';
	    $targetGlobalKey = 'load_it_logged_in';

	    $referenceKey = WPACU_FORM_ASSETS_POST_KEY;

	    if (! Misc::isValidRequest('post', $referenceKey)) {
		    return;
	    }

	    if (! isset($_POST[$referenceKey]['styles']) && ! isset($_POST[$referenceKey]['scripts'])) {
		    return;
	    }

	    $existingListEmpty = array('styles' => array($targetGlobalKey => array()), 'scripts' => array($targetGlobalKey => array()));
	    $existingListJson = get_option($optionToUpdate);

	    $existingListData = Main::instance()->existingList($existingListJson, $existingListEmpty);
	    $existingList = $existingListData['list'];

	    foreach (array('styles', 'scripts') as $assetType) {
		    if ( isset( $_POST[ $referenceKey ][$assetType] ) && ! empty( $_POST[ $referenceKey ][$assetType] ) ) {
			    foreach ( array_keys( $_POST[ $referenceKey ][$assetType] ) as $styleHandle ) {
			        // The checkbox was ticked (it's not empty)
				    $isSelected = isset( $_POST[$formTargetKey][$assetType][ $styleHandle ] ) && $_POST[$formTargetKey][$assetType][ $styleHandle ];

				    if ( $isSelected ) {
					    $existingList[$assetType][ $targetGlobalKey ][ $styleHandle ] = 1;
				    } else {
					    unset( $existingList[$assetType][ $targetGlobalKey ][ $styleHandle ] );

					    // Are there no values left? Remove the empty array (free space)
					    if (empty($existingList[$assetType][ $targetGlobalKey ])) {
					    	unset($existingList[$assetType][ $targetGlobalKey ]);
					    }
				    }
			    }
		    }
	    }

	    Misc::addUpdateOption($optionToUpdate, json_encode(Misc::filterList($existingList)));
    }

	/**
     * This method should ONLY be triggered when the "Asset CleanUp: Options" area is visible
     *
	 * @param $postId
	 * @param string $type
	 */
	public function updatePageOptions($postId, $type = 'post')
	{
		// Is the "Asset CleanUp: Page Options" meta box not loaded?
		// Then do not perform any update below
		if ( ! Misc::getVar( 'post', 'wpacu_page_options_area_loaded', false ) ) {
			return;
		}

		$pageOptions = Misc::getVar( 'post', WPACU_PLUGIN_ID . '_page_options', array() );

	    if ($type === 'post' || $postId > 0) {
		    /*
			 * For posts, pages, custom post types
			 */
		    // No page options? Delete any entry from the database to free up space
		    // instead of updating it as an empty entry
		    if ( empty( $pageOptions ) ) {
			    delete_post_meta( $postId, '_' . WPACU_PLUGIN_ID . '_page_options' );

			    return;
		    }

		    // Save the page URI as it's needed instead of get_permalink() that can't be called too early (e.g. outside an action hook or in a MU plugin)
		    $pageOptions['_page_uri'] = Misc::getPageUri($postId);

		    $pageOptionsJson = json_encode( Misc::filterList($pageOptions) );

		    if ( ! add_post_meta( $postId, '_' . WPACU_PLUGIN_ID . '_page_options', $pageOptionsJson, true ) ) {
			    update_post_meta( $postId, '_' . WPACU_PLUGIN_ID . '_page_options', $pageOptionsJson );
		    }
	    } elseif ($type === 'front_page') {
            /*
             * For the homepage (e.g. latest posts), but not a page set as homepage
             */
		    $existingListJson = get_option(WPACU_PLUGIN_ID . '_global_data');
		    $existingListData = Main::instance()->existingList($existingListJson, array());
		    $existingList = $existingListData['list'];

		    $existingList['page_options']['homepage'] = $pageOptions;

		    Misc::addUpdateOption( WPACU_PLUGIN_ID . '_global_data', json_encode(Misc::filterList($existingList)));
	    }
	}

	/**
	 * Triggers either "saveToEverywhereUnloads" or "removeEverywhereUnloads" methods
	 */
	public function updateEverywhereUnloads()
    {
	    /*
	     * Any global (all pages / everywhere) UNLOADS?
	     * Coming from a POST request
	     */
	    $reqStyles  = Misc::getVar('post', 'wpacu_global_unload_styles', array());
	    $reqScripts = Misc::getVar('post', 'wpacu_global_unload_scripts', array());

	    $this->saveToEverywhereUnloads($reqStyles, $reqScripts);

	    /*
	     * Any global (all pages / everywhere) REMOVED?
	     * Coming from a POST request
	     */
	    $this->removeEverywhereUnloads(array(), array(), 'post');
    }

	/**
	 * @param array $reqStyles
	 * @param array $reqScripts
	 */
	public function saveToEverywhereUnloads($reqStyles = array(), $reqScripts = array())
    {
        // Is there any entry already in JSON format?
        $existingListJson = get_option(WPACU_PLUGIN_ID . '_global_unload');

        // Default list as array
        $existingListEmpty = array('styles' => array(), 'scripts' => array());

	    $existingListData = Main::instance()->existingList($existingListJson, $existingListEmpty);
	    $existingList = $existingListData['list'];

        // Append to the list anything from the POST (if any)
        if (! empty($reqStyles)) {
            foreach ($reqStyles as $reqStyleHandle) {
                $existingList['styles'][] = $reqStyleHandle;
            }
        }

        if (! empty($reqScripts)) {
            foreach ($reqScripts as $reqScriptHandle) {
                $existingList['scripts'][] = $reqScriptHandle;
            }
        }

        // Make sure all entries are unique (no handle duplicates)
        foreach (array('styles', 'scripts') as $assetType) {
	        if ( isset( $existingList[$assetType] ) && is_array( $existingList[$assetType] ) ) {
		        $existingList[$assetType] = array_unique( $existingList[$assetType] );
	        }
        }

        Misc::addUpdateOption( WPACU_PLUGIN_ID . '_global_unload', json_encode(Misc::filterList($existingList)));
    }

	/**
	 * @param array $stylesList
	 * @param array $scriptsList
	 * @param string $checkType
	 *
	 * @return bool
	 */
	public function removeEverywhereUnloads($stylesList = array(), $scriptsList = array(), $checkType = '')
    {
    	if ($checkType === 'post') {
		    $stylesList  = Misc::getVar('post', 'wpacu_options_styles', array());
		    $scriptsList = Misc::getVar('post', 'wpacu_options_scripts', array());
	    }

        $removeStylesList = $removeScriptsList = array();

        $isUpdated = false;

        if (! empty($stylesList)) {
            foreach ($stylesList as $handle => $action) {
                if ($action === 'remove') {
                    $removeStylesList[] = $handle;
                }
            }
        }

        if (! empty($scriptsList)) {
            foreach ($scriptsList as $handle => $action) {
                if ($action === 'remove') {
                    $removeScriptsList[] = $handle;
                }
            }
        }

        $existingListJson = get_option(WPACU_PLUGIN_ID . '_global_unload');

        if (! $existingListJson) {
            return false;
        }

        $existingList = json_decode($existingListJson, true);

        if (Misc::jsonLastError() === JSON_ERROR_NONE) {
            foreach (array('styles', 'scripts') as $assetType) {
                if ($assetType === 'styles') {
                    $list = $removeStylesList;
                } elseif ($assetType === 'scripts') {
                    $list = $removeScriptsList;
                }

                if (empty($list)) {
                    continue;
                }

                foreach ($list as $handle) {
                    $handleKey = isset($existingList[$assetType]) ? array_search($handle, $existingList[$assetType]) : false;

                    if ($handleKey !== false) {
                        unset($existingList[$assetType][$handleKey]);
                        $isUpdated = true;
                    }
                }
            }

            if ($isUpdated) {
                Misc::addUpdateOption(WPACU_PLUGIN_ID . '_global_unload', json_encode(Misc::filterList($existingList)));
            }
        }

        return $isUpdated;
    }

	/**
	 * @param string $post
	 */
	public function saveToBulkUnloads($post = '')
    {
        if ($post === '') {
	        global $post;
        }

	    $postType = isset( $post->post_type ) ? $post->post_type : false;

	    // Free Version: It only deals with 'post_type' bulk unloads
	    if ( ! $postType ) {
		    return;
	    }

        $postStyles  = Misc::getVar('post', 'wpacu_bulk_unload_styles', array());
        $postScripts = Misc::getVar('post', 'wpacu_bulk_unload_scripts', array());

        // Is there any entry already in JSON format?
        $existingListJson = get_option( WPACU_PLUGIN_ID . '_bulk_unload');

        // Default list as array
        $existingListEmpty = array(
            'styles'  => array('post_type' => array($postType => array())),
            'scripts' => array('post_type' => array($postType => array()))
        );

	    $existingListData = Main::instance()->existingList($existingListJson, $existingListEmpty);
	    $existingList = $existingListData['list'];

        // Append to the list anything from the POST (if any)
        // Make sure all entries are unique (no handle duplicates)
        $list = array();

        foreach (array('styles', 'scripts') as $assetType) {
            if ($assetType === 'styles') {
                $list = $postStyles;
            } elseif ($assetType === 'scripts') {
                $list = $postScripts;
            }

            if (empty($list)) {
                continue;
            }

            foreach ($list as $bulkType => $values) {
                if (empty($values)) {
                    continue;
                }

                if ($bulkType === 'post_type') {
                    foreach ($values as $postType => $handles) {
                        if (empty($handles)) {
                            continue;
                        }

                    	foreach (array_unique($handles) as $handle) {
		                    $existingList[ $assetType ]['post_type'][ $postType ][] = $handle;
	                    }

	                    $existingList[ $assetType ]['post_type'][ $postType ] = array_unique($existingList[ $assetType ]['post_type'][ $postType ]);
                    }
                }
            }
        }

	    Misc::addUpdateOption( WPACU_PLUGIN_ID . '_bulk_unload', json_encode(Misc::filterList($existingList)));
    }

    /**
     * Lite Version: For post, pages, custom post types
     * @param mixed $postType
     * @return bool
     */
    public function removeBulkUnloads($postType = '')
    {
        if (! $postType) {
            global $post;

            // In the LITE version, post type unload is the only option for bulk unloads
            // $postType could be 'post', 'page' or a custom post type such as 'product' (WooCommerce), 'download' (Easy Digital Downloads), etc.
	        $postType = isset($post->post_type) ? $post->post_type : false;

            if (! $postType) {
            	return false;
            }
        }

	    $bulkType = 'post_type';

	    $stylesList = Misc::getVar('post', 'wpacu_options_'.$bulkType.'_styles', array());
	    $scriptsList = Misc::getVar('post', 'wpacu_options_'.$bulkType.'_scripts', array());

        if (empty($stylesList) && empty($scriptsList)) {
        	return false;
        }

        $removeStylesList = $removeScriptsList = array();

        $isUpdated = false;

        if (! empty($stylesList)) {
            foreach ($stylesList as $handle => $action) {
                if ($action === 'remove') {
                    $removeStylesList[] = $handle;
                }
            }
        }

        if (! empty($scriptsList)) {
            foreach ($scriptsList as $handle => $action) {
                if ($action === 'remove') {
                    $removeScriptsList[] = $handle;
                }
            }
        }

        $existingListJson = get_option( WPACU_PLUGIN_ID . '_bulk_unload');

        if (! $existingListJson) {
            return false;
        }

        $existingList = json_decode($existingListJson, true);

        if (Misc::jsonLastError() === JSON_ERROR_NONE) {
            $list = array();

            foreach (array('styles', 'scripts') as $assetType) {
                if ($assetType === 'styles') {
                    $list = $removeStylesList;
                } elseif ($assetType === 'scripts') {
                    $list = $removeScriptsList;
                }

                if (empty($list)) {
                    continue;
                }

	            foreach ( $existingList[ $assetType ][ $bulkType ][ $postType ] as $handleKey => $handle ) {
		            if ( in_array( $handle, $list ) ) {
			            unset( $existingList[ $assetType ][ $bulkType ][ $postType ][ $handleKey ] );
			            $isUpdated = true;
		            }
	            }
            }

	        Misc::addUpdateOption( WPACU_PLUGIN_ID . '_bulk_unload', json_encode(Misc::filterList($existingList)));
        }

        return $isUpdated;
    }

	/**
	 *
	 */
	public static function updateHandleNotes()
    {
        if (! Misc::isValidRequest('post', 'wpacu_handle_notes')) {
            return;
        }

        if (! isset($_POST['wpacu_handle_notes']['styles']) && ! isset($_POST['wpacu_handle_notes']['scripts'])) {
            return;
        }

	    $optionToUpdate = WPACU_PLUGIN_ID . '_global_data';
	    $globalKey = 'notes';

	    $existingListEmpty = array('styles' => array($globalKey => array()), 'scripts' => array($globalKey => array()));
	    $existingListJson = get_option($optionToUpdate);

	    $existingListData = Main::instance()->existingList($existingListJson, $existingListEmpty);
	    $existingList = $existingListData['list'];

	    if (isset($_POST['wpacu_handle_notes']['styles']) && ! empty($_POST['wpacu_handle_notes']['styles'])) {
            foreach ($_POST['wpacu_handle_notes']['styles'] as $styleHandle => $styleNote) {
                $styleNote = stripslashes($styleNote);

                if ($styleNote === '' && isset($existingList['styles'][$globalKey][$styleHandle])) {
                    unset($existingList['styles'][$globalKey][$styleHandle]);
                } elseif ($styleNote !== '') {
                    $existingList['styles'][$globalKey][$styleHandle] = $styleNote;
                }
            }
	    }

        if (isset($_POST['wpacu_handle_notes']['scripts']) && ! empty($_POST['wpacu_handle_notes']['scripts'])) {
	        foreach ( $_POST['wpacu_handle_notes']['scripts'] as $scriptHandle => $scriptNote ) {
		        $scriptNote = stripslashes( $scriptNote );

		        if ( $scriptNote === '' && isset( $existingList['scripts'][ $globalKey ][ $scriptHandle ] ) ) {
			        unset( $existingList['scripts'][ $globalKey ][ $scriptHandle ] );
		        } elseif ( $scriptNote !== '' ) {
			        $existingList['scripts'][ $globalKey ][ $scriptHandle ] = $scriptNote;
		        }
	        }
        }

	    Misc::addUpdateOption($optionToUpdate, json_encode(Misc::filterList($existingList)));
    }

	/**
	 * @param array $mainVarToUse
	 */
	public static function updateIgnoreChild($mainVarToUse = array())
	{
		// No $mainVarToUse passed? Then it's a $_POST
		// Check if $_POST is empty via Misc::isValidRequest()
		if (empty($mainVarToUse)) {
		    if ( (isset($_POST[WPACU_FORM_ASSETS_POST_KEY]['styles']) && ! empty($_POST[WPACU_FORM_ASSETS_POST_KEY]['styles']))
                || (isset($_POST[WPACU_FORM_ASSETS_POST_KEY]['scripts']) && ! empty($_POST[WPACU_FORM_ASSETS_POST_KEY]['scripts'])) ) {
			    $mainVarToUse = self::updateIgnoreChildAdapt($_POST[WPACU_FORM_ASSETS_POST_KEY]); // New form fields (starting from v1.1.9.9)
		    } elseif (Misc::isValidRequest('post', 'wpacu_ignore_child')) {
			    $mainVarToUse = $_POST;
			} else {
		        return;
		    }
		}

		if (! isset($mainVarToUse['wpacu_ignore_child']['styles']) && ! isset($mainVarToUse['wpacu_ignore_child']['scripts'])) {
			return;
		}

		$optionToUpdate = WPACU_PLUGIN_ID . '_global_data';
		$globalKey = 'ignore_child';

		$existingListEmpty = array('styles' => array($globalKey => array()), 'scripts' => array($globalKey => array()));
		$existingListJson = get_option($optionToUpdate);

		$existingListData = Main::instance()->existingList($existingListJson, $existingListEmpty);
		$existingList = $existingListData['list'];

		if (isset($mainVarToUse['wpacu_ignore_child']['styles']) && ! empty($mainVarToUse['wpacu_ignore_child']['styles'])) {
			foreach ($mainVarToUse['wpacu_ignore_child']['styles'] as $styleHandle => $styleVal) {
				$styleVal = trim($styleVal);

				if ($styleVal === '' && isset($existingList['styles'][$globalKey][$styleHandle])) {
					unset($existingList['styles'][$globalKey][$styleHandle]);
				} elseif ($styleVal !== '') {
					$existingList['styles'][$globalKey][$styleHandle] = $styleVal;
				}
			}
		}

		if (isset($mainVarToUse['wpacu_ignore_child']['scripts']) && ! empty($mainVarToUse['wpacu_ignore_child']['scripts'])) {
			foreach ($mainVarToUse['wpacu_ignore_child']['scripts'] as $scriptHandle => $scriptVal) {
				$scriptVal = trim($scriptVal); // should be '1' (meaning it's true)

				if ($scriptVal === '' && isset($existingList['scripts'][$globalKey][$scriptHandle])) {
					unset($existingList['scripts'][$globalKey][$scriptHandle]);
				} elseif ($scriptVal !== '') {
					$existingList['scripts'][$globalKey][$scriptHandle] = $scriptVal;
				}
			}
		}

		Misc::addUpdateOption($optionToUpdate, json_encode(Misc::filterList($existingList)));
	}

	/**
	 * @param $mainFormArray
	 *
	 * @return array
	 */
	public static function updateIgnoreChildAdapt($mainFormArray)
    {
	    $wpacuIgnoreChildList = array();

        foreach (array('styles', 'scripts') as $assetKey) {
            if (isset($mainFormArray[$assetKey]) && ! empty($mainFormArray[$assetKey])) {
                foreach ($mainFormArray[$assetKey] as $assetHandle => $assetData) {
	                $wpacuIgnoreChildList['wpacu_ignore_child'][$assetKey][$assetHandle] = ''; // default

                    if (isset($assetData['ignore_child']) && $assetData['ignore_child']) {
	                    $wpacuIgnoreChildList['wpacu_ignore_child'][ $assetKey ][ $assetHandle ] = 1;
                    }
                }
            }
        }

        return $wpacuIgnoreChildList;
	}

	/**
     * This function is called via AJAX whenever "+" or "-" is used on an asset's row
     *
	 * @param $newState
	 * @param $handle
	 * @param $handleFor
	 *
	 * @return array|false
	 */
	public static function updateHandleRowStatus($newState, $handle, $handleFor)
	{
		$optionToUpdate = WPACU_PLUGIN_ID . '_global_data';
		$globalKey = 'handle_row_contracted'; // Contracted or Expanded (default)

		$existingListEmpty = array('styles' => array($globalKey => array()), 'scripts' => array($globalKey => array()));
        $existingListJson = get_option($optionToUpdate);

		$existingListData = Main::instance()->existingList($existingListJson, $existingListEmpty);
		$existingList = $existingListData['list'];

		if ($handleFor === 'style') {
		    $keyList = 'styles';
		} elseif ($handleFor === 'script') {
		    $keyList = 'scripts';
		} else {
		    return false;
        }

        // The database value should be equal with '1' suggesting it's contracted (no value means it's expanded by default)
        if ( $newState === 'expanded' && isset( $existingList[$keyList][ $globalKey ][ $handle ] ) ) {
            unset( $existingList[$keyList][ $globalKey ][ $handle ] ); // "expanded" (default)
        } elseif ( $newState === 'contracted' ) {
            $existingList[$keyList][ $globalKey ][ $handle ] = 1; // "contracted"
        }

		Misc::addUpdateOption($optionToUpdate, json_encode(Misc::filterList($existingList)));

        return $existingList[$keyList][$globalKey];
	}

	/**
     * This is triggered automatically and sets a transient with the handles info
     * It doesn't require any manual action from the user
     *
	 * @param $assetList
	 */
	public static function updateHandlesInfo($assetList)
	{
		$optionToUpdate = WPACU_PLUGIN_ID . '_global_data';
		$globalKey = 'assets_info';

		$existingListEmpty = array('styles' => array($globalKey => array()), 'scripts' => array($globalKey => array()));
		$existingListJson = get_option($optionToUpdate);

		$existingListData = Main::instance()->existingList($existingListJson, $existingListEmpty);
		$existingList = $existingListData['list'];

		// $assetKey could be 'styles' or 'scripts'
		foreach ($assetList as $assetKey => $assetDataHandleList) {
			if (empty($assetDataHandleList) || ! in_array($assetKey, array('styles', 'scripts'))) {
				continue;
			}

			foreach ($assetDataHandleList as $assetObj) {
				$assetArray = (array)$assetObj;
				$assetHandle = $assetArray['handle'];

				// Strip other unused information including the 'handle' (no need to have it twice as it's already in one of the array's keys)
				unset( $assetArray['handle'], $assetArray['textdomain'], $assetArray['translations_path'] );

				// Some handles don't have an "src" value such as "woocommerce-inline"
				if (isset($assetArray['src']) && $assetArray['src']) {
					$assetArray['src'] = Misc::assetFromHrefToRelativeUri( $assetArray['src'], $assetKey );
				}

				// [wpacu_pro]
				if (isset($assetArray['output'])) { // hardcoded assets have an 'output' value
					// Is there already an entry for the same handle with a value set for 'output' and 'output_min'
					if (isset($existingList[$assetKey][$globalKey][$assetHandle]['output'], $existingList[$assetKey][$globalKey][$assetHandle]['output_min'])) {
						// Save resources: do not update the same values and skip the minification (good to avoid large inline content)
						continue;
					}

					if ( ! isset( $assetArray['output_min'] ) ) {
						$assetArray['output_min'] = '';

						// Reference: $wpacuHardcodedInfoToStoreAfterSubmit from _assets-hardcoded-list.php
						if ( strpos( $assetHandle, 'wpacu_hardcoded_script_' ) === 0 ) {
							$outputMin = \WpAssetCleanUp\OptimiseAssets\MinifyJs::applyMinification( $assetArray['output'] );
							if ( $assetArray['output'] !== $outputMin ) {
								$assetArray['output_min'] = $outputMin;
							}
						} elseif ( ( strpos( $assetHandle, 'wpacu_hardcoded_link_' ) === 0 ) || ( strpos( $assetHandle,
									'wpacu_hardcoded_style_' ) === 0 ) ) {
							$outputMin = \WpAssetCleanUp\OptimiseAssets\MinifyCss::applyMinification( $assetArray['output'] );
							if ( $assetArray['output'] !== $outputMin ) {
								$assetArray['output_min'] = $outputMin;
							}
						}
					}
				}
				// [/wpacu_pro]

				$existingList[$assetKey][$globalKey][$assetHandle] = $assetArray;
			}
		}

		update_option($optionToUpdate, json_encode(Misc::filterList($existingList)));
	}

	/**
	 *
	 */
	public static function clearTransients()
	{
		delete_transient(WPACU_PLUGIN_ID. '_total_unloaded_assets_all');
		delete_transient(WPACU_PLUGIN_ID. '_total_unloaded_assets_per_page');
	}

	/**
	 * This is triggered when /admin/admin-ajax.php is called (default WordPress AJAX handler)
	 */
	public function ajaxClearCache()
	{
	    if (! Menu::userCanManageAssets()) {
	        echo 'Error: Not enough privileges to clear the cache.';
	        exit();
        }

		OptimizeCommon::clearCache();

	    exit();
	}

	/**
	 * This is triggered when /admin/admin-ajax.php is called (default WordPress AJAX handler)
	 */
	public function ajaxPreloadGuest()
    {
        // Check nonce
	    if ( ! isset( $_POST['wpacu_ajax_preload_url_nonce'] ) || ! wp_verify_nonce( $_POST['wpacu_ajax_preload_url_nonce'], 'wpacu_ajax_preload_url_nonce' ) ) {
		    echo 'Error: The security nonce is not valid.';
		    exit();
	    }

        $pageUrl = isset($_POST['page_url']) ? $_POST['page_url'] : false;
	    $pageUrlDomain = parse_url($pageUrl, PHP_URL_HOST);
	    $pageUrlPreload = add_query_arg( array( 'wpacu_preload' => 1 ), $pageUrl );

	    // Check if the URL is valid
	    if (! filter_var($pageUrlPreload, FILTER_VALIDATE_URL)) {
		    echo 'The URL `'.$pageUrlPreload.'` is not valid.';
		    exit();
	    }

	    // Check the domain from "page_url" parameter
	    if (strpos(site_url(), $pageUrlDomain) === false) {
	        echo 'Error: Possible hacking attempt! The host name of the requested URL is not the same as the one of "Site Address (URL)" from "Settings" - "General".';
	        exit();
	    }

	    // Check privileges
	    if (! Menu::userCanManageAssets()) {
		    echo 'Error: Not enough privileges to perform this action.';
		    exit();
	    }

	    $response = wp_remote_get($pageUrlPreload);

	    if (is_wp_error($response)) {
	        // Any error generated during the fetch? Print it
	        echo 'Error: '.$response->get_error_code();
	    } else {
	        // No errors
		    echo 'Status Code: '.wp_remote_retrieve_response_code($response).' /  Page URL (preload): ' . $pageUrlPreload . "\n\n";
		    echo (isset($response['body']) ? $response['body'] : 'No "body" key found from wp_remote_get(), the preload might not have triggered');
	    }

	    exit();
    }

	/**
	 *
	 */
	public function ajaxUpdateAssetRowState()
    {
	    // Option: "On Assets List Layout Load, keep the groups:"
	    if (isset($_POST['wpacu_update_asset_row_state'])) {
		    if ( ! isset( $_POST['action'], $_POST['wpacu_asset_row_state'], $_POST['wpacu_handle'], $_POST['wpacu_handle_for'] )
                 || ! Menu::userCanManageAssets() ) {
			    return;
		    }

		    if ( $_POST['wpacu_update_asset_row_state'] !== 'yes' ) {
			    return;
		    }

		    $assetRowState = $_POST['wpacu_asset_row_state'];

            $newContractedList = self::updateHandleRowStatus($assetRowState, $_POST['wpacu_handle'], $_POST['wpacu_handle_for']);

		    echo "<pre>" . print_r($newContractedList, true);
	    }

	    exit();
    }
}
