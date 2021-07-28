<?php

/**
 * Add notification after 1 week of lite version installation
 * Recurrence: 40 Days
 *
 * @since 7.12.3
 */
final class ExactMetrics_Notification_Upgrade_To_Pro extends ExactMetrics_Notification_Event {

	public $notification_id = 'exactmetrics_notification_upgrade_to_pro';
	public $notification_interval = 40; // in days
	public $notification_first_run_time = '+7 day';
	public $notification_type = array( 'lite' );
	public $notification_icon = 'star';

	/**
	 * Build Notification
	 *
	 * @return array $notification notification is ready to add
	 *
	 * @since 7.12.3
	 */
	public function prepare_notification_data( $notification ) {
		$notification['title'] = __( 'Upgrade to ExactMetrics Pro and unlock advanced tracking and reports', 'google-analytics-dashboard-for-wp' );
		// Translators: upgrade to pro notification content
		$notification['content'] = __( 'By upgrading to ExactMetrics Pro you get access to additional reports right in your WordPress dashboard and advanced tracking features like eCommerce, Custom Dimensions, Forms tracking and more!', 'google-analytics-dashboard-for-wp' );
		$notification['btns']    = array(
			"upgrade_to_pro" => array(
				'url'           => $this->get_upgrade_url(),
				'text'          => __( 'Upgrade to Pro', 'google-analytics-dashboard-for-wp' ),
				'is_external'   => true,
			),
		);

		return $notification;
	}

}

// initialize the class
new ExactMetrics_Notification_Upgrade_To_Pro();
