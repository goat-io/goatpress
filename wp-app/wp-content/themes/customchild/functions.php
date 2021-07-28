<?php
// Add custom Theme Functions here

add_action( 'rest_api_init', 
  function () {
    register_rest_route( 'goatPlugin/v1', '/provider/(?P<id>\d+)', 
      array(
        'methods' => 'GET',
        'callback' => 'getProviderByID'
      ) 
    );
  } 
);

add_action( 'rest_api_init', 
  function () {
    register_rest_route( 'goatPlugin/v1', '/atumproduct/(?P<id>\d+)', 
      array(
        'methods' => 'GET',
        'callback' => 'getAtumProductById'
      ) 
    );
  } 
);


function wc_ninja_remove_password_strength() {
	if ( wp_script_is( 'wc-password-strength-meter', 'enqueued' ) ) {
		wp_dequeue_script( 'wc-password-strength-meter' );
	}
}
add_action( 'wp_print_scripts', 'wc_ninja_remove_password_strength', 100 );

function getAtumProductById(WP_REST_Request $request) {
  $productId = $request->get_param( 'id' );

  global $wpdb;
  $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}atum_product_data WHERE product_id = {$productId}", OBJECT );

  $response = new WP_REST_Response( $results );

  return $response;
}
        
function getProviderByID( WP_REST_Request $request ) {
        // Or via the helper method:
        $postId = $request->get_param( 'id' );
        
        $args = array(
          'post_type' => 'atum_supplier'
        );

        $meta = get_post_meta($postId);
        // the query
        $query = new WP_Query( $args );

        $provider = ['main' => $query, 'meta' => $meta];
        // Create the response object
        $response = new WP_REST_Response( $provider  );

        return $response;
    }