<?php
  /**
   * License: MIT License, https://opensource.org/licenses/MIT
   *
   * Note: These are purely examples, and have not necessarily been tested and hardened for production environments.
   * I highly recommend to test this code on your own QA/staging environments before applying it directly
   * to your production environment. I am not liable for any damage/issues this code may cause.
   * See more detail here: https://opensource.org/licenses/MIT.
   *
   * I am assuming you are placing this code in your theme's functions.php file.
   */

  /**
   * Scopes simple product updates to just the inventory value.
   * This means that whenever a simple product gets updated -- either via a manual
   * update action, or an automated scheduled update, then the only product property
   * that will get updated is the '_stock', or inventory value.
   *
   * Note that you'll need also need to add filters to the simple product images and
   * post_fields in order to limit the update even more.
   */
  function filter_lightspeed_single_prod_post_meta_updates( $meta_fields, $post_id){
    $filtered_meta_fields = array();
    $filtered_meta_fields['_stock'] = $meta_fields['_stock'];
    return $filtered_meta_fields;
  }
  add_filter('wclsi_update_prod_meta_fields_single_item', 'filter_lightspeed_single_prod_post_meta_updates', 10, 2);

  /**
   * Removes title and post content for Lightspeed simple product updates.
   * Note: will apply on a manual update as well as a scheduled update.
   */
  function filter_lightspeed_single_prod_field_updates( $post_fields, $post_id ) {
    $filtered_post_fields       = array();
    $filtered_post_fields['ID'] = $post_fields['ID'];
    return $filtered_post_fields;
  }
  add_filter( 'wclsi_update_post_fields_single_item', 'filter_lightspeed_single_prod_field_updates', 10, 2 );

  /**
   * Overwrites the images array so that no images are updated on an update action
   * for a simple product update.
   */
  function filter_lightspeed_single_prod_imgs_updates( $imgs, $post_id){
    return array();
  }
  add_filter('wclsi_update_prod_imgs_single_item', 'filter_lightspeed_single_prod_imgs_updates', 10, 2);

  /**
   * Polylang <> WooCommerce Lightspeed POS integration:
   * Updates Polylang product inventory on Lightspeed
   * inventory updates.
   */
  function update_polylang_inventory( $post_id, $inventory ) {
    if ( class_exists( "\\Hyyan\\WPI\\Utilities" ) && function_exists( 'pll_get_post_language' ) ) {
      $wc_prod = wc_get_product( $post_id );

      $translations = \Hyyan\WPI\Utilities::getProductTranslationsArrayByObject( $wc_prod );
      $productLang  = pll_get_post_language( $new_ls_prod->wc_prod_id );

      /* Remove the current product from translation array */
      if ( ! empty( $translations ) ) {
        unset( $translations[ $productLang ] );
        foreach ( $translations as $translation_prod_id ) {
          update_post_meta( $translation_prod_id, '_stock', $inventory );
        }
      }
    }
  }

  add_action( 'wclsi_update_wc_stock', 'update_polylang_inventory', 10, 2 );