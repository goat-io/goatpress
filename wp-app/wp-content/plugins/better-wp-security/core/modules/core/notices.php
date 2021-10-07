<?php

class ITSEC_Admin_Notice_New_Feature_Core implements ITSEC_Admin_Notice {

	public function get_id() {
		return 'release-new-ui-launch';
	}

	public function get_title() {
		return '';
	}

	public function get_message() {
		return sprintf(
			esc_html__( 'iThemes Security %s is here!', 'better-wp-security' ),
			ITSEC_Core::is_pro() ? '7.0' : '8.0'
		);
	}

	public function get_meta() {
		return array();
	}

	public function get_severity() {
		return self::S_INFO;
	}

	public function show_for_context( ITSEC_Admin_Notice_Context $context ) {
		return true;
	}

	public function get_actions() {
		return array(
			'blog' => new ITSEC_Admin_Notice_Action_Link(
				add_query_arg( 'itsec_view_release_post', 'release-new-ui', admin_url( 'index.php' ) ),
				esc_html__( 'See What’s New', 'better-wp-security' ),
				ITSEC_Admin_Notice_Action::S_PRIMARY,
				function () {
					$this->handle_dismiss();
					$url = ITSEC_Core::is_pro() ? 'https://ithemes.com/?p=64448' : 'https://ithemes.com/?p=65086';

					wp_redirect( $url );
					die;
				}
			)
		);
	}

	private function handle_dismiss() {
		$dismissed   = $this->get_storage();
		$dismissed[] = $this->get_id();
		$this->save_storage( $dismissed );

		return null;
	}

	private function get_storage() {
		$dismissed = get_site_option( 'itsec_dismissed_notices', array() );

		if ( ! is_array( $dismissed ) ) {
			$dismissed = array();
		}

		return $dismissed;
	}

	private function save_storage( $storage ) {
		update_site_option( 'itsec_dismissed_notices', $storage );
	}
}

ITSEC_Lib_Admin_Notices::register( new ITSEC_Admin_Notice_Globally_Dismissible( new ITSEC_Admin_Notice_Managers_Only( new ITSEC_Admin_Notice_New_Feature_Core() ) ) );

