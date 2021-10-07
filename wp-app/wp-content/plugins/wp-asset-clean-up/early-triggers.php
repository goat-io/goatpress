<?php
// Exit if accessed directly
if (! defined('ABSPATH')) {
	exit;
}

if ( isset($_GET['wpacu_clean_load']) ) {
	// Autoptimize
	$_GET['ao_noptimize'] = $_REQUEST['ao_noptimize'] = '1';

	// LiteSpeed Cache
	if ( ! defined( 'LITESPEED_DISABLE_ALL' ) ) {
		define('LITESPEED_DISABLE_ALL', true);
	}

	add_action( 'litespeed_disable_all', static function($reason) {
		do_action( 'litespeed_debug', '[API] Disabled_all due to: A clean load of the page was requested via '. WPACU_PLUGIN_TITLE );
	} );

	// No "WP-Optimize – Clean, Compress, Cache." minify
	add_filter('pre_option_wpo_minify_config', function() { return array(); });
}

if (! function_exists('assetCleanUpClearAutoptimizeCache')) {
	/*
	 * By default Autoptimize Cache is cleared after certain Asset CleanUp actions
	 *
	 * To be set in wp-config.php if necessary to deactivate this behaviour
	 * define('WPACU_DO_NOT_CLEAR_AUTOPTIMIZE_CACHE', true);
	 *
	 * @return bool
	 */
	function assetCleanUpClearAutoptimizeCache()
	{
		return ! ( defined( 'WPACU_DO_NOT_ALSO_CLEAR_AUTOPTIMIZE_CACHE' ) && WPACU_DO_NOT_ALSO_CLEAR_AUTOPTIMIZE_CACHE );
	}
}

if (! function_exists('assetCleanUpRequestUriHasAnyPublicVar')) {
	/**
	 * @param $targetUri
	 *
	 * @return bool
	 */
	function assetCleanUpRequestUriHasAnyPublicVar($targetUri)
	{
		$urlQuery = parse_url($targetUri, PHP_URL_QUERY);

		if ( ! $urlQuery ) {
			return false;
		}

		$publicQueryVars = array(
			'attachment',
			'attachment_id',
			'author',
			'author_name',
			'cat',
			'calendar',
			'category_name',
			'comments_popup',
			'cpage',
			'day',
			'error',
			'exact',
			'feed',
			'hour',
			'm',
			'minute',
			'monthnum',
			'more',
			'name',
			'order',
			'orderby',
			'p',
			'page_id',
			'page',
			'paged',
			'pagename',
			'pb',
			'post_type',
			'posts',
			'preview',
			'robots',
			's',
			'search',
			'second',
			'sentence',
			'static',
			'subpost',
			'subpost_id',
			'taxonomy',
			'tag',
			'tag_id',
			'tb',
			'term',
			'w',
			'withcomments',
			'withoutcomments',
			'year'
		);

		foreach ($publicQueryVars as $queryVar) {
			if (strpos('?'.$urlQuery, '&'.$queryVar.'=') !== false || strpos('?'.$urlQuery, '?'.$queryVar.'=') !== false) {
				return true;
			}
		}

		return false;
	}
}

if (! function_exists('assetCleanUpHasNoLoadMatches')) {
	/**
	 * Any matches from "Settings" -> "Plugin Usage Preferences" -> "Do not load the plugin on certain pages"?
	 *
	 * @param string $targetUri
	 * @param bool $forceCheck
	 *
	 * @return bool
	 */
	function assetCleanUpHasNoLoadMatches($targetUri = '', $forceCheck = false)
	{
		if ( ! $forceCheck && isset( $_GET['wpacu_ignore_no_load_option'] ) ) {
			return false;
		}

		if ($targetUri === '') {
			// When called from the Dashboard, it should never be empty
			if (is_admin()) {
				return false;
			}

			$targetUri = isset($_SERVER['REQUEST_URI']) ? rawurldecode($_SERVER['REQUEST_URI']) : ''; // Invalid request
		} else {
			// Passed from the Dashboard as an URL; Strip the prefix and hostname to keep only the URI
			$parseUrl = parse_url(rawurldecode($targetUri));
			$targetUri = isset($parseUrl['path']) ? $parseUrl['path'] : '';
		}

		if ($targetUri === '') {
			return false; // Invalid request
		}

		// Already detected? Avoid duplicate queries
		if (isset($GLOBALS['wpacu_no_load_matches'][$targetUri])) {
			return $GLOBALS['wpacu_no_load_matches'][$targetUri];
		}

		$doNotLoadRegExps = array();

		$wpacuPluginSettingsJson = get_option( WPACU_PLUGIN_ID . '_settings' );
		$wpacuPluginSettings     = @json_decode( $wpacuPluginSettingsJson, ARRAY_A );
		$doNotLoadPatterns       = isset( $wpacuPluginSettings['do_not_load_plugin_patterns'] ) ? $wpacuPluginSettings['do_not_load_plugin_patterns'] : '';

		if ( $doNotLoadPatterns !== '' ) {
			$doNotLoadPatterns = trim( $doNotLoadPatterns );

			if ( strpos( $doNotLoadPatterns, "\n" ) ) {
				// Multiple values (one per line)
				foreach ( explode( "\n", $doNotLoadPatterns ) as $doNotLoadPattern ) {
					$doNotLoadPattern = trim( $doNotLoadPattern );
					if ( $doNotLoadPattern ) {
						$doNotLoadRegExps[] = '#' . $doNotLoadPattern . '#';
					}
				}
			} elseif ( $doNotLoadPatterns ) {
				// Only one value?
				$doNotLoadRegExps[] = '#' . $doNotLoadPatterns . '#';
			}
		}

		if ( ! empty( $doNotLoadRegExps ) ) {
			foreach ( $doNotLoadRegExps as $doNotLoadRegExp ) {
				if ( @preg_match( $doNotLoadRegExp, $targetUri ) || (strpos($targetUri, $doNotLoadRegExp) !== false) ) {
					// There's a match
					$GLOBALS['wpacu_no_load_matches'][$targetUri] = 'is_set_in_settings';
					return $GLOBALS['wpacu_no_load_matches'][$targetUri];
				}
			}
		}

		/*
		 * Page Options -> The following option might be checked "Do not load Asset CleanUp Pro on this page (this will disable any functionality of the plugin)"
		 * For homepage (e.g. latest posts) or a page, post or custom post type
		 */
		$parseUrl = parse_url(get_site_url());
		$rootUrl = $parseUrl['scheme'].'://'.$parseUrl['host'];
		$homepageUri = isset($parseUrl['path']) ? $parseUrl['path'] : '/';

		$cleanTargetUri = $targetUri;

		if (strpos($targetUri, '?') !== false) {
			list($cleanTargetUri) = explode('?', $cleanTargetUri);
		}

		/*
		 * First verification: If it's a homepage and it's not a "page" homepage but a different one such as latest posts
		 */
		$isHomePageUri = trim($homepageUri, '/') === trim($cleanTargetUri, '/') && ! assetCleanUpRequestUriHasAnyPublicVar($targetUri);
		$isSinglePageSetAsHomePage = ( get_option('show_on_front') === 'page' && get_option('page_on_front') > 0 );

		if ( $isHomePageUri && ! $isSinglePageSetAsHomePage ) {
			// Anything different then a page set as the homepage
			$globalPageOptions = get_option(WPACU_PLUGIN_ID . '_global_data');

			if ($globalPageOptions) {
				$globalPageOptionsList = @json_decode($globalPageOptions, true);

				if (isset($globalPageOptionsList['page_options']['homepage']['no_wpacu_load'])
				    && $globalPageOptionsList['page_options']['homepage']['no_wpacu_load'] == 1) {
					$GLOBALS['wpacu_no_load_matches'][$targetUri] = 'is_set_in_page';
					return $GLOBALS['wpacu_no_load_matches'][$targetUri];
				}
			}
		}

		/*
		 * Second verification: For any post, page, custom post type including any page set as the homepage in "Reading" -> "Your homepage displays" -> "A static page (select below)"
		 */
		if ($isHomePageUri && $isSinglePageSetAsHomePage) {
			$pageId = get_option('page_on_front');
			$pageOptionsJson = get_post_meta($pageId, '_' . WPACU_PLUGIN_ID . '_page_options', true);
			$pageOptions = @json_decode( $pageOptionsJson, ARRAY_A );

			if (isset($pageOptions['no_wpacu_load']) && $pageOptions['no_wpacu_load'] == 1) {
				$GLOBALS['wpacu_no_load_matches'][$targetUri] = 'is_set_in_page';
				return $GLOBALS['wpacu_no_load_matches'][$targetUri];
			}
		} else {
			// Visiting a post, page or custom post type but not the homepage
			global $wpdb;
			$anyPagesWithSpecialOptions = $wpdb->get_col( 'SELECT meta_value FROM `' . $wpdb->prefix . 'postmeta` WHERE meta_key=\'_wpassetcleanup_page_options\' && meta_value LIKE \'%no_wpacu_load%\'' );

			if ( ! empty( $anyPagesWithSpecialOptions ) ) {
				foreach ( $anyPagesWithSpecialOptions as $metaValue ) {
					$postPageOptions = @json_decode($metaValue, ARRAY_A);

					if ( ! isset($postPageOptions['no_wpacu_load'], $postPageOptions['_page_uri']) ) {
						continue;
					}

					$dbPageUrl = $postPageOptions['_page_uri'];
					$dbPageUri = str_replace( $rootUrl, '', $dbPageUrl );

					if ( ( $dbPageUri === $targetUri ) || ( strpos( $targetUri, $dbPageUri ) === 0 ) ) {
						$GLOBALS['wpacu_no_load_matches'][$targetUri] = 'is_set_in_page';
						return $GLOBALS['wpacu_no_load_matches'][$targetUri];
					}
				}
			}
		}

		$GLOBALS['wpacu_no_load_matches'][$targetUri] = false;

		return false;
	}
}

if (! function_exists('assetCleanUpNoLoad')) {
	/**
	 * There are special cases when triggering "Asset CleanUp" is not relevant
	 * Thus, for maximum compatibility and backend processing speed, it's better to avoid running any of its code
	 *
	 * @return bool
	 */
	function assetCleanUpNoLoad() {
		if ( defined( 'WPACU_NO_LOAD_SET' ) ) {
			return true; // save resources in case the function is called several times
		}

		// Hide top WordPress admin bar on request for debugging purposes and a cleared view of the tested page
		if ( isset($_REQUEST['wpacu_no_admin_bar']) ) {
			add_filter( 'show_admin_bar', '__return_false', PHP_INT_MAX );
		}

		// On request: for debugging purposes - e.g. https://yourwebsite.com/?wpacu_no_load
		// Also make sure it's in the REQUEST URI and $_GET wasn't altered incorrectly before it's checked
		// Technically, it will be like the plugin is not activated: no global settings and unload rules will be applied
		if ( isset($_GET['wpacu_no_load'], $_SERVER['REQUEST_URI']) && strpos( $_SERVER['REQUEST_URI'], 'wpacu_no_load' ) !== false ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// Needs to be called ideally from a MU plugin which always loads before Asset CleanUp
		// or from a different plugin that triggers before Asset CleanUp which is less reliable
		if ( apply_filters( 'wpacu_plugin_no_load', false ) ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// "Elementor" plugin Admin Area: Edit Mode
		if ( isset( $_GET['post'], $_GET['action'] ) && $_GET['post'] && $_GET['action'] === 'elementor' && is_admin() ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// "Elementor" plugin (Preview Mode within Page Builder)
		if ( isset( $_GET['elementor-preview'], $_GET['ver'] ) && (int) $_GET['elementor-preview'] > 0 && $_GET['ver'] ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		$wpacuIsAjaxRequest = ( ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) === 'xmlhttprequest' );

		// If an AJAX call is made to /wp-admin/admin-ajax.php and the action doesn't start with WPACU_PLUGIN_ID.'_
		// then do not trigger Asset CleanUp Pro as it's irrelevant
		$wpacuActionStartsWith = WPACU_PLUGIN_ID . '_';

		if ( $wpacuIsAjaxRequest && // Is AJAX request
		     isset( $_POST['action'] ) && // Has 'action' set as a POST parameter
		     strpos( $_POST['action'], $wpacuActionStartsWith ) !== 0 && // Doesn't start with $wpacuActionStartsWith
		     ( strpos( $_SERVER['REQUEST_URI'],
				     'admin-ajax.php' ) !== false ) && // The request URI contains 'admin-ajax.php'
		     is_admin() ) { // If /wp-admin/admin-ajax.php is called, then it will return true
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// On some hosts .css and .js files are loaded dynamically (e.g. through the WordPress environment)
		if (isset($_SERVER['REQUEST_URI']) && preg_match('#.(css|js)\?ver=#', $_SERVER['REQUEST_URI'])) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// Image Edit via Media Library
		if ( $wpacuIsAjaxRequest && isset( $_POST['action'], $_POST['postid'] ) && $_POST['action'] === 'image-editor' ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// "Elementor" plugin: Do not trigger the plugin on AJAX calls
		if ( $wpacuIsAjaxRequest && isset( $_POST['action'] ) && ( strpos( $_POST['action'], 'elementor_' ) === 0 ) ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// If some users want to have Asset CleanUp loaded on Oxygen Builder's page builder to avoid loading certain plugins (for a faster page editor)
		// they can do that by adding the following constant in wp-config.php
		// define('WPACU_LOAD_ON_OXYGEN_BUILDER_EDIT', true);
		$loadPluginOnOxygenEdit = defined('WPACU_LOAD_ON_OXYGEN_BUILDER_EDIT') && WPACU_LOAD_ON_OXYGEN_BUILDER_EDIT;

		if ( ! $loadPluginOnOxygenEdit ) {
			// "Oxygen" plugin: Edit Mode
			$oxygenBuilderPluginDir = dirname( __DIR__ ) . '/oxygen';
			if ( isset( $_GET['ct_builder'] ) && $_GET['ct_builder'] === 'true' && is_dir( $oxygenBuilderPluginDir ) ) {
				define( 'WPACU_NO_LOAD_SET', true );

				return true;
			}

			// "Oxygen" plugin: Block Edit Mode
			if ( isset( $_GET['oxy_user_library'], $_GET['ct_builder'] ) && $_GET['oxy_user_library'] && $_GET['ct_builder'] ) {
				define( 'WPACU_NO_LOAD_SET', true );

				return true;
			}

			// "Oxygen" plugin (v2.4.1+): Edit Mode (Reusable Template)
			if ( isset( $_GET['ct_builder'], $_GET['ct_template'] ) && $_GET['ct_builder'] && $_GET['ct_template'] ) {
				define( 'WPACU_NO_LOAD_SET', true );

				return true;
			}
		} else {
			// Since the user the constant WPACU_LOAD_ON_OXYGEN_BUILDER_EDIT, we'll check if the Oxygen Editor is ON
			// And if it is set the constant WPACU_ALLOW_ONLY_UNLOAD_RULES to true which will allow only unload rules, but do not trigger any other ones such as preload/defer, etc.
			$isOxygenBuilderLoaded = false;

			// "Oxygen" plugin: Edit Mode
			$oxygenBuilderPluginDir = dirname( __DIR__ ) . '/oxygen';
			if ( isset( $_GET['ct_builder'] ) && $_GET['ct_builder'] === 'true' && is_dir( $oxygenBuilderPluginDir ) ) {
				$isOxygenBuilderLoaded = true;
			}

			// "Oxygen" plugin: Block Edit Mode
			if ( isset( $_GET['oxy_user_library'], $_GET['ct_builder'] ) && $_GET['oxy_user_library'] && $_GET['ct_builder'] ) {
				$isOxygenBuilderLoaded = true;
			}

			// "Oxygen" plugin (v2.4.1+): Edit Mode (Reusable Template)
			if ( isset( $_GET['ct_builder'], $_GET['ct_template'] ) && $_GET['ct_builder'] && $_GET['ct_template'] ) {
				$isOxygenBuilderLoaded = true;
			}

			if ( $isOxygenBuilderLoaded && ! defined('WPACU_ALLOW_ONLY_UNLOAD_RULES') ) {
				define( 'WPACU_ALLOW_ONLY_UNLOAD_RULES', true );
			}
		}

		// If some users want to have Asset CleanUp loaded on Divi Builder to avoid loading certain plugins (for a faster page editor)
		// they can do that by adding the following constant in wp-config.php
		// define('WPACU_LOAD_ON_DIVI_BUILDER_EDIT', true);
		$loadPluginOnDiviBuilderEdit = defined('WPACU_LOAD_ON_DIVI_BUILDER_EDIT') && WPACU_LOAD_ON_DIVI_BUILDER_EDIT;
		$isDiviBuilderLoaded = ( isset( $_GET['et_fb'] ) && $_GET['et_fb'] ) || ( isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], 'et_fb=1' ) !== false );

		if ( ! $loadPluginOnDiviBuilderEdit ) {
			// "Divi" theme builder: Front-end View Edit Mode
			if ( $isDiviBuilderLoaded ) {
				define( 'WPACU_NO_LOAD_SET', true );

				return true;
			}
		} else {
			// Since the user the constant WPACU_LOAD_ON_DIVI_BUILDER_EDIT, we'll check if the Divi Builder is ON
			// And if it is set the constant WPACU_ALLOW_ONLY_UNLOAD_RULES to true which will allow only unload rules, but do not trigger any other ones such as preload/defer, etc.
			if ( $isDiviBuilderLoaded && ! defined('WPACU_ALLOW_ONLY_UNLOAD_RULES') ) {
				define( 'WPACU_ALLOW_ONLY_UNLOAD_RULES', true );
			}
		}

		// "Divi" theme builder: Do not trigger the plugin on AJAX calls
		if ( $wpacuIsAjaxRequest && isset( $_POST['action'] ) && ( strpos( $_POST['action'], 'et_fb_' ) === 0 ) ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// KALLYAS theme: Zion Page Builder
		if ( isset($_GET['zn_pb_edit']) && in_array($_GET['zn_pb_edit'], array(1, 'true')) ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// Beaver Builder
		if ( isset( $_GET['fl_builder'] ) ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// Thrive Architect (Dashboard)
		if ( isset( $_GET['action'], $_GET['tve'] ) && $_GET['action'] === 'architect' && $_GET['tve'] === 'true' && is_admin() ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// Thrive Architect (iFrame)
		$tveFrameFlag = defined( 'TVE_FRAME_FLAG' ) ? TVE_FRAME_FLAG : 'tcbf';

		if ( isset( $_GET['tve'], $_GET[ $tveFrameFlag ] ) && $_GET['tve'] === 'true' ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// Page Builder by SiteOrigin
		if ( isset( $_GET['action'], $_GET['so_live_editor'] ) && $_GET['action'] === 'edit' && $_GET['so_live_editor'] && is_admin() ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// Brizy - Page Builder
		if ( isset( $_GET['brizy-edit'] ) || isset( $_GET['brizy-edit-iframe'] ) ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// Fusion Builder Live: Avada
		if ( ( isset( $_GET['fb-edit'] ) && $_GET['fb-edit'] ) || isset( $_GET['builder'], $_GET['builder_id'] ) ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// WPBakery Page Builder
		if ( isset( $_GET['vc_editable'], $_GET['_vcnonce'] ) || ( is_admin() && isset( $_GET['vc_action'] ) ) ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// Themify Builder (iFrame)
		if ( isset( $_GET['tb-preview'] ) && $_GET['tb-preview'] ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// "Pro" (theme.co) (iFrame)
		if ( isset( $_POST['_cs_nonce'], $_POST['cs_preview_state'] ) && $_POST['_cs_nonce'] && $_POST['cs_preview_state'] ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// "Page Builder: Live Composer" plugin
		if ( defined( 'DS_LIVE_COMPOSER_ACTIVE' ) && DS_LIVE_COMPOSER_ACTIVE ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// "WP Page Builder" plugin (By Themeum.com)
		if ( isset( $_GET['load_for'] ) && $_GET['load_for'] === 'wppb_editor_iframe' ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// "Product Designer for WooCommerce WordPress | Lumise" plugin
		if ( isset( $_GET['product_base'], $_GET['product_cms'] ) && in_array( 'lumise/lumise.php',
				apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// Perfmatters: Script Manager
		if ( isset( $_GET['perfmatters'] ) ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// Gravity Forms (called for uploading files)
		if ( ( ( isset($_GET['gf_page']) && $_GET['gf_page']) || isset($_GET['gf-download'], $_GET['form-id'] ) ) && is_file( WP_PLUGIN_DIR . '/gravityforms/gravityforms.php' ) ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// Custom CSS Pro: Editor
		if ( ( isset( $_GET['page'] ) && $_GET['page'] === 'ccp-editor' )
		     || ( isset( $_GET['ccp-iframe'] ) && $_GET['ccp-iframe'] === 'true' ) ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// TranslatePress Multilingual: Edit translation mode
		if ( isset( $_GET['trp-edit-translation'] ) && $_GET['trp-edit-translation'] === 'preview' ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// WordPress Customise Mode
		if ( ( isset( $_GET['customize_changeset_uuid'], $_GET['customize_theme'] ) && $_GET['customize_changeset_uuid'] && $_GET['customize_theme'] )
		     || ( strpos( $_SERVER['REQUEST_URI'],
					'/wp-admin/customize.php' ) !== false && isset( $_GET['url'] ) && $_GET['url'] ) ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		$cleanRequestUri = trim( $_SERVER['REQUEST_URI'], '?' );
		if ( strpos( $cleanRequestUri, '?' ) !== false ) {
			list ( $cleanRequestUri ) = explode( '?', $cleanRequestUri );
		}

		// REST Request
		if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		     || ( isset($_SERVER['REQUEST_URI']) && strpos( $_SERVER['REQUEST_URI'], '/wp-json/wp/v2/' ) !== false )
		     || ( isset($_SERVER['REQUEST_URI']) && strpos( $cleanRequestUri, '/wp-json/wc/' ) !== false )
		) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		$parseUrl     = parse_url( get_site_url() );
		$parseUrlPath = isset( $parseUrl['path'] ) ? $parseUrl['path'] : '';

		// We want to make sure the RegEx rules will be working fine if certain characters (e.g. Thai ones) are used
		$requestUriAsItIs = rawurldecode($_SERVER['REQUEST_URI']);

		$targetUriAfterSiteUrl = trim( str_replace( array( get_site_url(), $parseUrlPath ), '', $requestUriAsItIs ), '/' )    ;

		if ( strpos( $targetUriAfterSiteUrl, 'wp-json/' ) === 0 ) {
			// WooCommerce, Thrive Ovation
			if (strpos( $targetUriAfterSiteUrl, 'wp-json/wc/' ) === 0 || strpos( $targetUriAfterSiteUrl, 'wp-json/tvo/' ) === 0) {
				define( 'WPACU_NO_LOAD_SET', true );

				return true;
			}

			// Other plugins with a similar pattern
			if ($targetUriAfterSiteUrl === 'wp-json' ||
			    $targetUriAfterSiteUrl === 'wp-json/' ||
			    preg_match('#wp-json/(.*?)/v#', $targetUriAfterSiteUrl) ||
			    preg_match('#wp-json/(|\?)#', $targetUriAfterSiteUrl)) {
				define( 'WPACU_NO_LOAD_SET', true );

				return true;
			}
		}

		// WordPress AJAX Heartbeat
		if ( isset( $_POST['action'] ) && $_POST['action'] === 'heartbeat' ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// EDD Plugin (Listener)
		if ( isset( $_GET['edd-listener'] ) && $_GET['edd-listener'] ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// AJAX Requests from various plugins/themes
		if ( isset( $wpacuIsAjaxRequest ) && $wpacuIsAjaxRequest && isset( $_POST['action'] )
		     && ( strpos( $_POST['action'], 'woocommerce' ) === 0
		          || strpos( $_POST['action'], 'wc_' ) === 0
		          || strpos( $_POST['action'], 'jetpack' ) === 0
		          || strpos( $_POST['action'], 'wpfc_' ) === 0
		          || strpos( $_POST['action'], 'oxygen_' ) === 0
		          || strpos( $_POST['action'], 'oxy_' ) === 0
		          || strpos( $_POST['action'], 'w3tc_' ) === 0
		          || strpos( $_POST['action'], 'wpforms_' ) === 0
		          || strpos( $_POST['action'], 'wdi_' ) === 0
		          || in_array( $_POST['action'], array( 'contactformx' ) )
		     ) ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// e.g. WooCommerce's AJAX call to /?wc-ajax=checkout | no need to trigger Asset CleanUp then, not only avoiding any errors, but also saving resources
		// "wc-ajax" could be one of the following: update_order_review, apply_coupon, checkout, etc.
		if ( isset( $_REQUEST['wc-ajax'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// WooCommerce API call
		if ( (isset($_GET['wc-api']) && $_GET['wc-api']) || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/index.php?wc-api=') !== false) ) {
			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		// Stop triggering Asset CleanUp (completely) on specific front-end pages
		// Do the trigger here and if necessary exit as early as possible to save resources via "registered_taxonomy" action hook)
		$anyPluginNoLoadMatches = assetCleanUpHasNoLoadMatches();

		if ( $anyPluginNoLoadMatches ) {
			// Only use exit() when "wpassetcleanup_load" is used
			if ( isset( $_REQUEST['wpassetcleanup_load'] ) && $_REQUEST['wpassetcleanup_load'] ) {
				add_action( 'registered_taxonomy', function() use ($anyPluginNoLoadMatches) {
					if ( current_user_can( 'administrator' ) ) {
						if ( $anyPluginNoLoadMatches === 'is_set_in_settings' ) {
							$msg = sprintf(
								__( 'This page\'s URL is matched by one of the RegEx rules you have in <em>"Settings"</em> -&gt; <em>"Plugin Usage Preferences"</em> -&gt; <em>"Do not load the plugin on certain pages"</em>, thus %s is not loaded on that page and no CSS/JS are to be managed. If you wish to view the CSS/JS manager, please remove the matching RegEx rule and the list of CSS/JS will be fetched.',
									'wp-asset-clean-up'
								),
								WPACU_PLUGIN_TITLE
							);
						} elseif ( $anyPluginNoLoadMatches === 'is_set_in_page' ) {
							$msg = sprintf(
								__( 'This homepage\'s URI is matched by the rule you have in the "Page Options", thus %s is not loaded on that page and no CSS/JS are to be managed. If you wish to view the CSS/JS manager, please uncheck the option and reload this page.',
									'wp-asset-clean-up'
								), WPACU_PLUGIN_TITLE );
						}

						exit( $msg );
					}
				} );
			}

			define( 'WPACU_NO_LOAD_SET', true );

			return true;
		}

		return false;
	}
}

// In case JSON library is not enabled (rare cases)
if (! defined('JSON_ERROR_NONE')) {
	define('JSON_ERROR_NONE', 0);
}

// Make sure the plugin doesn't load when the editor of either "X" theme or "Pro" website creator (theme.co) is ON
add_action('init', static function() {
	if (is_admin()) {
		return; // Not relevant for the Dashboard view, stop here!
	}

	if (class_exists('\WpAssetCleanUp\Menu') && \WpAssetCleanUp\Menu::userCanManageAssets() && method_exists('Cornerstone_Common', 'get_app_slug') && in_array(get_stylesheet(), array('x', 'pro'))) {
		$customAppSlug = get_stylesheet(); // default one ('x' or 'pro')

		// Is there any custom slug set in "/wp-admin/admin.php?page=cornerstone-settings"?
		// "Settings" -> "Custom Path" (check it out below)
		$cornerStoneSettings = get_option('cornerstone_settings');
		if (isset($cornerStoneSettings['custom_app_slug']) && $cornerStoneSettings['custom_app_slug'] !== '') {
			$customAppSlug = $cornerStoneSettings['custom_app_slug'];
		}

		$lengthToUse = strlen($customAppSlug) + 2; // add the slashes to the count

		if (substr($_SERVER['REQUEST_URI'], -$lengthToUse) === '/'.$customAppSlug.'/') {
			add_filter( 'wpacu_prevent_any_frontend_optimization', '__return_true' );
		}
	}
});
