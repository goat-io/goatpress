<?php

add_action( 'itsec_initialized', function () {
	$modules = array_merge( ITSEC_Modules::get_active_modules(), ITSEC_Modules::get_always_active_modules() );

	foreach ( $modules as $module ) {
		if ( ! $config = ITSEC_Modules::get_config( $module ) ) {
			continue;
		}

		foreach ( $config->get_feature_flags() as $flag => $config ) {
			ITSEC_Lib_Feature_Flags::register_flag( $flag, $config );
		}
	}
} );
