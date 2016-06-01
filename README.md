# woocommerce-lightspeed-hooks
This is a tutorial on how to utilize filter and action hooks for the WooCommerce Lightspeed POS integration plugin.

Lightspeed POS has been extended to support filters that you can hook into. This allows for better customization, e.g., You want the Update action to only update the inventory field and not override other fields such as title and content.

See the functions.php file for examples on how to utilize the below filters and action hooks.
  
**Filters for matrix/variable products:**

For the import action:

- wclsi_import_post_fields_matrix_prod - filters title, long description and short description for import action for matrix item
- wclsi_import_post_meta_matrix_item - filters post_meta fields for matrix item
- wclsi_import_variations_matrix_item - filters variation products while importing a matrix item
- wclsi_import_attributes_matrix_item - filters item attributes for matrix item

For the update action:

- wclsi_update_post_fields_matrix_prod - filters title, content for update action
- wclsi_update_post_meta_matrix_item - filters post_meta fields for matrix item on update action
- wclsi_update_post_imgs_matrix_item - filters matrix images on update action
- wclsi_update_variations_matrix_item - filters variations of a matrix/variable product on update action

Syncing from WooCommerce to Lightspeed (Matrix/Variable products):

- wclsi_sync_to_ls_matrix_prod - filters a matrix prod array to be synced with Lightspeed
- wclsi_sync_to_ls_variation_prod - filters a variation prod array to be synced with Lightspeed
  Filters for simple products:

**Filters for single/simple products:**
  
For the update action (single/simple items):

- wclsi_update_post_fields_single_item - filters title and content fields
- wclsi_update_post_tags_single_item - filters product tags for update action
- wclsi_update_prod_meta_fields_single_item - filters post meta fields for update action
- wclsi_update_post_imgs_single_item - filters images for update actions

Syncing from WooCommerce to Lightspeed:

- wclsi_sync_to_ls_simple_prod - filters a simple prod array to be synced with Lightspeed

Action hook for syncing from Lightspeed to WooCommerce:

  wclsi_update_wc_stock - gets triggered on inventory sync from Lightspeed to WooCommerce; see "Inventory Syncing" under How does WooCommerce Lightspeed POS sync products?