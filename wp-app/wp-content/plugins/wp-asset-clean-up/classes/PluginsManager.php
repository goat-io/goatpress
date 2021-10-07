<?php
namespace WpAssetCleanUp;

/**
 * Class PluginsManager
 * @package WpAssetCleanUp
 */
class PluginsManager
{
    /**
     * @var array
     */
    public $data = array();

	/**
	 *
	 */
	public function page()
    {
    	// Get active plugins and their basic information
	    $this->data['active_plugins'] = self::getActivePlugins();
	    $this->data['plugins_icons']  = Misc::getAllActivePluginsIcons();

	    Main::instance()->parseTemplate('admin-page-plugins-manager', $this->data, true);
    }

	/**
	 * @return array
	 */
	public static function getActivePlugins()
	{
		$activePluginsFinal = array();

		// Get active plugins and their basic information
		$activePlugins = wp_get_active_and_valid_plugins();

		// Also check any network activated plugins in case we're dealing with a MultiSite setup
		if ( is_multisite() ) {
			$activeNetworkPlugins = wp_get_active_network_plugins();

			if ( ! empty( $activeNetworkPlugins ) ) {
				foreach ( $activeNetworkPlugins as $activeNetworkPlugin ) {
					$activePlugins[] = $activeNetworkPlugin;
				}
			}
		}

		$activePlugins = array_unique($activePlugins);

		foreach ($activePlugins as $pluginPath) {
			// Skip Asset CleanUp as it's obviously needed for the functionality
			if (strpos($pluginPath, 'wp-asset-clean-up') !== false) {
				continue;
			}

			$networkActivated = isset($activeNetworkPlugins) && in_array($pluginPath, $activeNetworkPlugins);

			$pluginRelPath = trim(str_replace(WP_PLUGIN_DIR, '', $pluginPath), '/');

			$pluginData = get_plugin_data($pluginPath);

			$activePluginsFinal[] = array(
                'title'             => $pluginData['Name'],
                'path'              => $pluginRelPath,
                'network_activated' => $networkActivated
			);
		}

		usort($activePluginsFinal, static function($a, $b)
		{
			return strcmp($a['title'], $b['title']);
		});

		return $activePluginsFinal;
	}
}
