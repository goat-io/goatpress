<?php

/**
 * Add notification when lite version activated
 * Recurrence: 30 Days
 *
 * @since 7.12.3
 */
final class ExactMetrics_Notification_Upgrade_For_Email_Summaries extends ExactMetrics_Notification_Event {

	public $notification_id = 'exactmetrics_notification_upgrade_for_email_summaries';
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
		$notification['title'] = __( 'Upgrade to ExactMetrics Pro to get weekly email reports', 'google-analytics-dashboard-for-wp' );
		// Translators: upgrade for email summaries notification content
		$notification['content'] = sprintf( __( 'Wouldn’t it be easy if you could get your website’s performance report in your email inbox every week? With our new feature, Email Summaries, you can now view all your important stats in a simple report that’s delivered straight to your inbox. <br><br>You get an overview of your site\'s performance without logging in to WordPress or going through different Analytics reports. %sUpgrade to ExactMetrics Pro%s to enable the Email Summaries feature.', 'google-analytics-dashboard-for-wp' ), '<a href="' . $this->get_upgrade_url() . '" target="_blank">', '</a>' );
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
new ExactMetrics_Notification_Upgrade_For_Email_Summaries();
