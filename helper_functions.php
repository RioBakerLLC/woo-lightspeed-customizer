<?php
/**
 *
 */
function wclsi_scope_ls_inventory_by_shop_id($ls_product, $shop_id, $inventory ) {

    foreach( $ls_product['ItemShops']['ItemShop'] as $key => $itemShop ){
        if( isset( $itemShop['shopID'] ) ) {
            if( $itemShop['shopID'] != $shop_id ) {
                unset( $ls_product['ItemShops']['ItemShop'][$key] );
            }
        }
    }

    return $ls_product;
}

/**
 * Helper function to find a Lightspeed product's inventory filtered by $shop_id.
 * If no matching $shop_id is found, then the function will return null.
 *
 * @param $ls_product
 * @param $shop_id
 * @return int|null
 */
function wclsi_get_inventory_by_shop_id( $ls_product, $shop_id ){

    $scoped_item_shop = null;
    $item_shops = $ls_product->ItemShops->ItemShop;

    if( isset( $item_shops ) ) {
        if( is_array( $item_shops ) ) {
            foreach( $item_shops as $key => $item_shop ) {
                if( !empty( $item_shop->shopID ) && $item_shop->shopID == $shop_id ) {
                    $scoped_item_shop = $item_shop;
                    break;
                }
            }
        } elseif ( is_object( $item_shops ) ) {
            if( !empty( $item_shops->shopID ) && ($item_shops->shopID == $shop_id) ) {
                $scoped_item_shop = $item_shops;
            }
        }
    }

    if( !is_null( $scoped_item_shop ) && isset( $scoped_item_shop->qoh ) ) {
        return $scoped_item_shop->qoh;
    } else {
        return null;
    }
}