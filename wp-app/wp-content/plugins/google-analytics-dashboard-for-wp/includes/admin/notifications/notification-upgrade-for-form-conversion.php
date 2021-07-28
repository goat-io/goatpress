<?php

/**
 * Add notification when lite version activated
 * Recurrence: 20 Days
 *
 * @since 7.12.3
 */
final class ExactMetrics_Notification_Upgrade_For_Form_Conversion extends ExactMetrics_Notification_Event {

	public $notification_id = 'exactmetrics_notification_upgrade_for_form_conversion';
	public $notification_interval = 20; // in days
	public $notification_type = array( 'basic', 'lite', 'plus' );
	public $notification_icon = 'warning';

	/**
	 * Build Notification
	 *
	 * @return array $notification notification is ready to add
	 *
	 * @since 7.12.3
	 */
	public function prepare_notification_data( $notification ) {
		$notification['title'] = __( 'Upgrade to ExactMetrics Pro to Track Form Conversion', 'google-analytics-dashboard-for-wp' );
		// Translators: upgrade for form conversion notification content
		$notification['content'] = sprintf( __( 'Forms are one of the most important points of interaction on your website. When a visitor fills out a form on your site, they’re taking the next step in their customer journey. That’s why it’s so crucial that your WordPress forms are optimized for conversions. Upgrade to %sExactMetrics Pro%s to track %sform conversions in Google Analytics.%s', 'google-analytics-dashboard-for-wp' ), '<a href="' . $this->get_upgrade_url() . '" target="_blank">', '</a>', '<a href="' . $this->build_external_link( 'https://www.exactmetrics.com/addon/forms/' ) . '" target="_blank">', '</a>' );
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
new ExactMetrics_Notification_Upgrade_For_Form_Conversion();
