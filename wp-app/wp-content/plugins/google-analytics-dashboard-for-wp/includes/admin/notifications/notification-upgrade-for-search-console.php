<?php

/**
 * Add notification when lite version activated
 * Recurrence: 30 Days
 *
 * @since 7.12.3
 */
final class ExactMetrics_Notification_Upgrade_For_Search_Console extends ExactMetrics_Notification_Event {

	public $notification_id = 'exactmetrics_notification_upgrade_for_search_console_report';
	public $notification_interval = 30; // in days
	public $notification_type = array( 'lite' );
	public $notification_icon = 'warning';

	/**
	 * Build Notification
	 *
	 * @return array $notification notification is ready to add
	 *
	 * @since 7.12.3
	 */
	public function prepare_notification_data( $notification ) {
		$notification['title'] = __( 'Get access to Google Search Keywords data by upgrading to ExactMetrics Pro', 'google-analytics-dashboard-for-wp' );
		// Translators: upgrade for search console notification content
		$notification['content'] = sprintf( __( 'Do you want to find out which search terms from Google bring your site the most visitors? %sUpgrade to ExactMetrics PRO%s today and get access to the %sSearch Console Report%s and more directly in your WordPress admin.', 'google-analytics-dashboard-for-wp' ), '<a href="' . $this->get_upgrade_url() . '" target="_blank">', '</a>', '<a href="' . $this->build_external_link( 'https://www.exactmetrics.com/feature/search-console-report/' ) . '" target="_blank">', '</a>' );
		$notification['btns']    = array(
			"get_exactmetrics_pro" => array(
				'url'           => $this->get_upgrade_url(),
				'text'          => __( 'Get ExactMetrics Pro', 'google-analytics-dashboard-for-wp' ),
				'is_external'   => true,
			),
		);

		return $notification;
	}

}

// initialize the class
new ExactMetrics_Notification_Upgrade_For_Search_Console();
