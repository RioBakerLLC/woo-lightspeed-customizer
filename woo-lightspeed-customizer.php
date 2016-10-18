<?php
/**
 * Plugin Name: Woo Lightspeed Customizer
 * Plugin URI:  https://docs.woothemes.com/document/woocommerce-lightspeed-pos/
 * Author:      Rafi Yagudin
 * Author URI:  http://www.rafilabs.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Description: An example plugin on how to utilize filter and action hooks for the WooCommerce Lightspeed POS integration plugin to further customize it's functionality.
 *
 * Lightspeed POS has been extended to support filters that you can hook into.
 * This allows for better customization, e.g., You want the Update action to only update
 * the inventory field and not override other fields such as title and content.
 *
 * Please make sure you are on the latest version of the WooCommerce Lightspeed POS plugin before
 * attempting to use these customizations.
 *
 *  -- !! Warning !! --
 *
 * These are purely examples, and have not necessarily been tested and hardened for production environments.
 * I highly recommend you to test this code on your own QA/staging environments before applying it directly
 * to your production environment. I am not liable for any damage/issues this code may cause.
 * See more detail here: https://opensource.org/licenses/MIT.
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * An example of how to use the 'wclsi_update_product' and the 'wclsi_import_product'
 * filter hooks to introduce inventory logic. In the example below, we are scoping
 * the inventory down to a specific Lightspeed shop ID.
 *
 * @param $ls_product
 * @return mixed
 */

function wclsi_scope_inventory_by_shop_id( $inventory, $ls_product ) {

    /**
     * You can find the ID of your shop by signing into Lightspeed and
     * navigating to Settings > Shop Setup. Click on the shop you'd like
     * to scope your inventory to.
     *
     * After clicking on the shop, examine the URL. It should look something like this:
     *
     * https://us.merchantos.com/?name=admin.views.shop&form_name=view&id=2&tab=details
     *
     * You'll notice that in the URL, there's a parameter called "id=" followed by a number.
     * The number is your shop's unique ID. In the example above, the shop ID is 2: "id=2".
     */
    $SHOP_ID = 2;

    $item_shops = $ls_product->ItemShops->ItemShop;

    $scoped_item_shop = null;

    if( isset( $item_shops ) && is_array( $item_shops ) ) {
        foreach( $item_shops as $key => $item_shop ) {
            if( !empty( $item_shop->shopID ) && $item_shop->shopID == $SHOP_ID ) {
                $scoped_item_shop = $item_shop;
                break;
            }
        }
    } else {
        return $inventory; // Don't do anything if we can't find shop IDs
    }

    if( !is_null( $scoped_item_shop ) && isset( $scoped_item_shop->qoh ) ) {
        return $scoped_item_shop->qoh;
    } else {
        return $inventory;
    }
}

add_filter('wclsi_get_lightspeed_inventory', 'wclsi_scope_inventory_by_shop_id', 10, 2);

/**
 * A filter hook example of how to filter single product imports from Lightspeed based on a 'webstore' tag.
 *
 * I'd highly recommend to read up on searching Lightspeed's API docs on how to properly
 * format search queries: https://www.lightspeedhq.com/webhelp/retail/en/content/developer-api/api-search.html
 */
function wclsi_filter_single_prods_by_tags( $search_params ) {
    $search_params['tag'] = 'webstore';
    return $search_params;
}
add_filter('wclsi_ls_import_prod_params', 'wclsi_filter_single_prods_by_tags');

/**
 * A filter hook example of how to filter matrix product imports from Lightspeed based on a 'webstore' tag.
 *
 * I'd highly recommend to read up on searching Lightspeed's API docs on how to properly
 * format search queries: https://www.lightspeedhq.com/webhelp/retail/en/content/developer-api/api-search.html
 */
function wclsi_filter_matrix_prods_by_tags( $search_params ) {
    $search_params['tag'] = 'webstore';
    return $search_params;
}
add_filter('wclsi_ls_import_matrix_params', 'wclsi_filter_matrix_prods_by_tags');

/**
 * An example of how to access Lightspeed custom fields.
 * You can utilize WordPress's "update_post_metadata" hook to gain access
 * to a product's Lightspeed data on an import.
 *
 * Make sure that if you're calling "update_post_meta" again within this
 * filter to remove it in your logic to prohibit an infinite loop from
 * occuring.
 */
function wclsi_check_for_custom_fields($mid, $object_id, $meta_key, $_meta_value ) {
  if( '_wclsi_ls_obj' == $meta_key ) {
    error_log("PostID: " . $object_id);
    if( isset( $_meta_value->CustomFieldValues ) ) {
      error_log("Lightspeed custom field values:" . print_r( $_meta_value->CustomFieldValues, true ) );
      // add your custom fields here by referencing $_meta_value->CustomFieldValues
    }
  }
}
add_filter('update_post_metadata', 'wclsi_check_for_custom_fields', 10, 4);

/**
 * Scopes simple product updates to just the inventory value.
 * This means that whenever a simple product gets updated -- either via a manual
 * update action, or an automated scheduled update, then the only product property
 * that will get updated is the '_stock', or inventory value.
 *
 * Note that you'll need also need to add filters to the simple product images and
 * post_fields in order to limit the update even more.
 */
function wclsi_filter_lightspeed_single_prod_post_meta_updates($meta_fields, $post_id){
  $filtered_meta_fields = array();
  $filtered_meta_fields['_stock'] = $meta_fields['_stock'];
  return $filtered_meta_fields;
}
add_filter('wclsi_update_prod_meta_fields_single_item', 'wclsi_filter_lightspeed_single_prod_post_meta_updates', 10, 2);

/**
 * Removes title and post content for Lightspeed simple product updates.
 * Note: will apply on a manual update as well as a scheduled update.
 */
function wclsi_filter_lightspeed_single_prod_field_updates( $post_fields, $post_id ) {
  $filtered_post_fields       = array();
  $filtered_post_fields['ID'] = $post_fields['ID'];
  return $filtered_post_fields;
}
add_filter( 'wclsi_update_post_fields_single_item', 'wclsi_filter_lightspeed_single_prod_field_updates', 10, 2 );

/**
 * Overwrites the images array so that no images are updated on an update action
 * for a simple product update.
 */
function wclsi_filter_lightspeed_single_prod_imgs_updates( $imgs, $post_id){
  return array();
}
add_filter('wclsi_update_prod_imgs_single_item', 'wclsi_filter_lightspeed_single_prod_imgs_updates', 10, 2);

/**
 * Polylang <> WooCommerce Lightspeed POS integration:
 * Updates Polylang product inventory on Lightspeed
 * inventory updates.
 */
function wclsi_update_polylang_inventory( $post_id, $inventory ) {
  if ( class_exists( "\\Hyyan\\WPI\\Utilities" ) && function_exists( 'pll_get_post_language' ) ) {
    $wc_prod = wc_get_product( $post_id );

    $translations = \Hyyan\WPI\Utilities::getProductTranslationsArrayByObject( $wc_prod );
    $productLang  = pll_get_post_language( $wc_prod->get_id() );

    /* Remove the current product from translation array */
    if ( ! empty( $translations ) ) {
      unset( $translations[ $productLang ] );
      foreach ( $translations as $translation_prod_id ) {
        update_post_meta( $translation_prod_id, '_stock', $inventory );
      }
    }
  }
}
add_action( 'wclsi_update_wc_stock', 'wclsi_update_polylang_inventory', 10, 2 );
