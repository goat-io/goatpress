<?php
/**
 * Handles the woocommerce product box structure
 *
 * @author  UX Themes
 * @package Flatsome/WooCommerce
 */

if ( ! function_exists( 'flatsome_sale_flash' ) ) {
	/**
	 * Add Extra Sale Flash Bubbles
	 *
	 * @param $text
	 * @param $post
	 * @param $_product
	 * @param $badge_style
	 *
	 * @return string
	 */
	function flatsome_sale_flash( $text, $post, $_product, $badge_style ) {
		global $wc_cpdf;

		if ( ! is_a( $wc_cpdf, 'WC_Product_Data_Fields' ) ) {
			return $text;
		}

		if ( $wc_cpdf->get_value( get_the_ID(), '_bubble_new' ) ) {

			$bubble_text = $wc_cpdf->get_value( get_the_ID(), '_bubble_text' ) ? $wc_cpdf->get_value( get_the_ID(), '_bubble_text' ) : __( 'New', 'flatsome' );

			// Extra Product bubbles.
			$text .= '<div class="badge callout badge-' . $badge_style . '"><div class="badge-inner callout-new-bg is-small new-bubble">' . $bubble_text . '</div></div>';
		}

		return $text;
	}
}
add_filter( 'flatsome_product_labels', 'flatsome_sale_flash', 10, 4 );


if ( ! function_exists( 'flatsome_woocommerce_get_alt_product_thumbnail' ) ) {
	/**
	 * Get Hover image for WooCommerce Grid
	 */
	function flatsome_woocommerce_get_alt_product_thumbnail() {
		$hover_style = get_theme_mod( 'product_hover', 'fade_in_back' );

		if ( $hover_style !== 'fade_in_back' && $hover_style !== 'zoom_in' ) {
			return;
		}

		global $product;
		$attachment_ids = fl_woocommerce_version_check( '3.0.0' ) ? $product->get_gallery_image_ids() : $product->get_gallery_attachment_ids();
		$class          = 'show-on-hover absolute fill hide-for-small back-image';
		if ( $hover_style == 'zoom_in' ) {
			$class .= $class . ' hover-zoom';
		}

		if ( $attachment_ids ) {
			$loop = 0;
			foreach ( $attachment_ids as $attachment_id ) {
				$image_link = wp_get_attachment_url( $attachment_id );
				if ( ! $image_link ) {
					continue;
				}
				$loop ++;
				echo apply_filters( 'flatsome_woocommerce_get_alt_product_thumbnail',
					wp_get_attachment_image( $attachment_id, 'woocommerce_thumbnail', false, array( 'class' => $class ) ) );
				if ( $loop == 1 ) {
					break;
				}
			}
		}
	}
}
add_action( 'flatsome_woocommerce_shop_loop_images', 'flatsome_woocommerce_get_alt_product_thumbnail', 11 );

if ( ! function_exists( 'woocommerce_template_loop_product_title' ) ) {
	/**
	 * Fix WooCommerce Loop Title
	 */
	function woocommerce_template_loop_product_title() {
		echo '<p class="name product-title"><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></p>';
	}
}

if ( ! function_exists( 'flatsome_woocommerce_shop_loop_category' ) ) {
	/**
	 * Add and/or Remove Categories
	 */
	function flatsome_woocommerce_shop_loop_category() {
		if ( ! flatsome_option( 'product_box_category' ) ) {
			return;
		} ?>
		<p class="category uppercase is-smaller no-text-overflow product-cat op-7">
			<?php
			global $product;
			$product_cats = function_exists( 'wc_get_product_category_list' ) ? wc_get_product_category_list( get_the_ID(), '\n', '', '' ) : $product->get_categories( '\n', '', '' );
			$product_cats = strip_tags( $product_cats );

			if ( $product_cats ) {
				list( $first_part ) = explode( '\n', $product_cats );
				echo esc_html( apply_filters( 'flatsome_woocommerce_shop_loop_category', $first_part, $product ) );
			}
			?>
		</p>
	<?php
	}
}
add_action( 'woocommerce_shop_loop_item_title', 'flatsome_woocommerce_shop_loop_category', 0 );

if ( ! function_exists( 'flatsome_woocommerce_shop_loop_ratings' ) ) {
	/**
	 * Add and/or Remove Ratings
	 */
	function flatsome_woocommerce_shop_loop_ratings() {
		// Switch ratings position when grid 3 is chosen.
		if ( 'grid3' === flatsome_option( 'grid_style' ) && ! get_theme_mod( 'disable_reviews' ) ) {
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
			add_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_rating' );
		}
		// Remove ratings.
		if ( flatsome_option( 'product_box_rating' ) ) {
			return;
		}
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
		remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_rating' );
	}
}
add_action( 'woocommerce_shop_loop_item_title', 'flatsome_woocommerce_shop_loop_ratings', 0 );


/* Remove and add Product image */
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
add_action( 'flatsome_woocommerce_shop_loop_images', 'woocommerce_template_loop_product_thumbnail', 10 );

/* Remove Default Add To cart button from Grid */
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

if ( ! function_exists( 'flatsome_product_box_actions_add_to_cart' ) ) {
	/**
	 * Add 'Add to cart' icon
	 */
	function flatsome_product_box_actions_add_to_cart() {
		// Check if active
		if ( flatsome_option( 'add_to_cart_icon' ) !== "show" ) {
			return;
		}
		global $product;
		echo apply_filters( 'woocommerce_loop_add_to_cart_link',
			sprintf( '<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s %s add-to-cart-grid no-padding" style="width:0;display:block">
            <div class="cart-icon tooltip absolute is-small" title="%s"><strong>+</strong></div></a>',
				esc_url( $product->add_to_cart_url() ),
				esc_attr( isset( $quantity ) ? $quantity : 1 ),
				esc_attr( $product->get_id() ),
				esc_attr( $product->get_sku() ),
				esc_attr( $product->is_type( 'variable' ) || $product->is_type( 'grouped' ) ? '' : 'ajax_add_to_cart' ),
				esc_attr( isset( $class ) ? $class : 'add_to_cart_button' ),
				esc_html( $product->add_to_cart_text() ) ),
			$product );
	}
}
add_action( 'flatsome_product_box_actions', 'flatsome_product_box_actions_add_to_cart', 1 );

if ( ! function_exists( 'flatsome_woocommerce_shop_loop_button' ) ) {
	/**
	 * Add 'Add to Cart' After
	 */
	function flatsome_woocommerce_shop_loop_button() {
		if ( flatsome_option( 'add_to_cart_icon' ) !== "button" ) {
			return;
		}
		global $product;

		echo apply_filters( 'woocommerce_loop_add_to_cart_link',
			sprintf( '<div class="add-to-cart-button"><a href="%s" rel="nofollow" data-product_id="%s" class="%s %s product_type_%s button %s is-%s mb-0 is-%s">%s</a></div>',
				esc_url( $product->add_to_cart_url() ),
				esc_attr( $product->get_id() ),
				esc_attr( $product->is_type( 'variable' ) || $product->is_type( 'grouped' ) ? '' : 'ajax_add_to_cart' ),
				$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
				esc_attr( $product->get_type() ),
				esc_attr( 'primary' ), // Button color
				esc_attr( get_theme_mod( 'add_to_cart_style', 'outline' ) ), // Button style
				esc_attr( 'small' ), // Button size
				esc_html( $product->add_to_cart_text() ) ),
			$product );
	}
}
add_action( 'flatsome_product_box_after', 'flatsome_woocommerce_shop_loop_button', 100 );

if ( ! function_exists( 'flatsome_woocommerce_shop_loop_excerpt' ) ) {
	/**
	 * Add Product Short description
	 */
	function flatsome_woocommerce_shop_loop_excerpt() {
		if ( ! flatsome_option( 'short_description_in_grid' ) ) {
			return;
		}
		?>
		<p class="box-excerpt is-small">
			<?php echo get_the_excerpt(); ?>
		</p>
		<?php
	}
}
add_action( 'flatsome_product_box_after', 'flatsome_woocommerce_shop_loop_excerpt', 20 );

if ( ! function_exists( 'flatsome_product_box_class' ) ) {
	/**
	 * Add Classes to product box
	 *
	 * @return null/string
	 */
	function flatsome_product_box_class() {
		$classes             = array();
		$category_grid_style = get_theme_mod( 'category_grid_style', 'grid');

		if ( $category_grid_style == 'list' ) {
			$classes[] = 'box-vertical';
		}
		if ( ! empty( $classes ) ) {
			return implode( ' ', $classes );
		}
		return null;
	}
}

if ( ! function_exists( 'flatsome_product_box_image_class' ) ) {
	/**
	 * Add Classes to product image box
	 *
	 * @return null/string
	 */
	function flatsome_product_box_image_class() {
		$hover_style = flatsome_option( 'product_hover' );
		if ( $hover_style == 'fade_in_back' && $hover_style == 'zoom_in' ) {
			return null;
		}
		$classes   = array();
		$classes[] = 'image-' . $hover_style;
		if ( ! empty( $classes ) ) {
			return implode( ' ', $classes );
		}
		return null;
	}
}

if ( ! function_exists( 'flatsome_product_box_actions_class' ) ) {
	/**
	 * Add Classes to product actions
	 *
	 * @return string
	 */
	function flatsome_product_box_actions_class() {
		return 'grid-tools text-center hide-for-small bottom hover-slide-in show-on-hover';
	}
}

if ( ! function_exists( 'flatsome_product_box_text_class' ) ) {
	/**
	 * Add classes to product text box
	 *
	 * @return string
	 */
	function flatsome_product_box_text_class() {
		$classes = array( 'box-text-products' );

		$grid_style = flatsome_option( 'grid_style' );

		if ( $grid_style == 'grid2' ) {
			$classes[] = 'text-center grid-style-2';
		}

		if ( $grid_style == 'grid3' ) {
			$classes[] = 'flex-row align-top grid-style-3 flex-wrap';
		}

		return implode( ' ', $classes );
	}
}
