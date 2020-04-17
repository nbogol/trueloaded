<?php
/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\helpers;

class Attributes {

    public static function has_product_attributes($products_id, $simple = false) {
        if ($simple) {
            $attributes_query = tep_db_query("select count(*) as count from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . $products_id . "'");
            $attributes = tep_db_fetch_array($attributes_query);
            if ($attributes['count'] > 0) {
                return true;
            }
            return false;
        }
// {{ Products Bundle Sets
        $bundle_products = array(\common\helpers\Inventory::get_prid($products_id));
        if ($ext = \common\helpers\Acl::checkExtension('ProductBundles', 'getBundles')) {
            $bundle_products = $ext::getBundles($products_id, \common\classes\platform::currentId());
        }
// }}
        $attributes_query = tep_db_query("select count(*) as count from " . TABLE_PRODUCTS_ATTRIBUTES . " where " . (count($bundle_products) > 1 ? " products_id in ('" . implode("','", $bundle_products) . "')" : " products_id = '" . (int) $products_id . "'"));
        $attributes = tep_db_fetch_array($attributes_query);

        if ($attributes['count'] > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function delete_products_attributes($delete_product_id) {
        // delete products attributes
        $products_delete_from_query = tep_db_query("select pa.products_id, pa.products_attributes_id from " . TABLE_PRODUCTS_ATTRIBUTES . " pa  where pa.products_id='" . $delete_product_id . "'");
        while ($products_delete_from = tep_db_fetch_array($products_delete_from_query)) {
            tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " where products_attributes_id = '" . $products_delete_from['products_attributes_id'] . "'");
        }
        tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . $delete_product_id . "'");
    }

    public static function updateAttributesPrices($new_id, $old_id) {
        tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " where products_attributes_id = '" . $new_id . "'");
        $query = tep_db_query("select * from " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " where products_attributes_id = '" . $old_id . "'");
        while ($data = tep_db_fetch_array($query)) {
            tep_db_query("insert into " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " ( products_attributes_id, groups_id, currencies_id, attributes_group_price, attributes_group_discount_price ) values ('" . $new_id . "', '" . $data['groups_id'] . "', '" . $data['currencies_id'] . "', '" . $data['attributes_group_price'] . "', '" . $data['attributes_group_discount_price'] . "')");
        }
    }

    public static function copy_products_attributes($products_id_from, $products_id_to) {
        global $copy_attributes_delete_first, $copy_attributes_duplicates_skipped;

        $products_copy_to_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_id='" . $products_id_to . "'");
        $products_copy_to_check_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_id='" . $products_id_to . "'");
        $products_copy_from_query = tep_db_query("select * from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . $products_id_from . "'");
        $products_copy_from_check_query = tep_db_query("select * from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . $products_id_from . "'");

        // Check for errors in copy request
        if (!$products_copy_from_check = tep_db_fetch_array($products_copy_from_check_query) or ! $products_copy_to_check = tep_db_fetch_array($products_copy_to_check_query) or $products_id_to == $products_id_from) {
            echo '<table width="100%"><tr>';
            if ($products_id_to == $products_id_from) {
                // same products_id
                echo '<td class="messageStackError">' . tep_image(DIR_WS_ICONS . 'warning.gif', ICON_WARNING) . '<b>WARNING: Cannot copy from Product ID #' . $products_id_from . ' to Product ID # ' . $products_id_to . ' ... No copy was made' . '</b>' . '</td>';
            } else {
                if (!$products_copy_from_check) {
                    // no attributes found to copy
                    echo '<td class="messageStackError">' . tep_image(DIR_WS_ICONS . 'warning.gif', ICON_WARNING) . '<b>WARNING: No Attributes to copy from Product ID #' . $products_id_from . ' for: ' . \common\helpers\Product::get_backend_products_name($products_id_from) . ' ... No copy was made' . '</b>' . '</td>';
                } else {
                    // invalid products_id
                    echo '<td class="messageStackError">' . tep_image(DIR_WS_ICONS . 'warning.gif', ICON_WARNING) . '<b>WARNING: There is no Product ID #' . $products_id_to . ' ... No copy was made' . '</b>' . '</td>';
                }
            }
            echo '</tr></table>';
        } else {

            if ($copy_attributes_delete_first == '1') {
                // delete all attributes
                self::delete_products_attributes($products_id_to);
            }

            while ($products_copy_from = tep_db_fetch_array($products_copy_from_query)) {
                $check_attribute_query = tep_db_query("select products_id, products_attributes_id, options_id, options_values_id from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . $products_id_to . "' and options_id='" . $products_copy_from['options_id'] . "' and options_values_id ='" . $products_copy_from['options_values_id'] . "'");
                $check_attribute = tep_db_fetch_array($check_attribute_query);

                // Process Attribute
                $skip_it = false;
                switch (true) {
                    case ($check_attribute and $copy_attributes_duplicates_skipped):
                        // skip duplicate attributes
                        $skip_it = true;
                        break;
                    default:
                        // skip anything when $skip_it
                        if (!$skip_it) {
                            if ($check_attribute['products_id']) {
                                $sql_data_array = array(
                                    'options_id' => tep_db_prepare_input($products_copy_from['options_id']),
                                    'options_values_id' => tep_db_prepare_input($products_copy_from['options_values_id']),
                                    'options_values_price' => tep_db_prepare_input($products_copy_from['options_values_price']),
                                    'price_prefix' => tep_db_prepare_input($products_copy_from['price_prefix']),
                                    'products_options_sort_order' => tep_db_prepare_input($products_copy_from['products_options_sort_order']),
                                    'product_attributes_one_time' => tep_db_prepare_input($products_copy_from['product_attributes_one_time']),
                                    'products_attributes_weight' => tep_db_prepare_input($products_copy_from['products_attributes_weight']),
                                    'products_attributes_weight_prefix' => tep_db_prepare_input($products_copy_from['products_attributes_weight_prefix']),
                                    'products_attributes_units' => tep_db_prepare_input($products_copy_from['products_attributes_units']),
                                    'products_attributes_discount_price' => tep_db_prepare_input($products_copy_from['products_attributes_discount_price']),
                                    'products_attributes_filename' => tep_db_prepare_input($products_copy_from['products_attributes_filename']),
                                    'products_attributes_maxdays' => tep_db_prepare_input($products_copy_from['products_attributes_maxdays']),
                                    'products_attributes_maxcount' => tep_db_prepare_input($products_copy_from['products_attributes_maxcount']),
                                    'products_attributes_units_price' => tep_db_prepare_input($products_copy_from['products_attributes_units_price'])
                                );

                                $cur_attributes_id = $check_attribute['products_attributes_id'];
                                tep_db_perform(TABLE_PRODUCTS_ATTRIBUTES, $sql_data_array, 'update', 'products_id = \'' . tep_db_input($products_id_to) . '\' and products_attributes_id=\'' . tep_db_input($cur_attributes_id) . '\'');
                                self::updateAttributesPrices($cur_attributes_id, $products_copy_from['products_attributes_id']);
                            } else {
                                tep_db_query("insert into " . TABLE_PRODUCTS_ATTRIBUTES . " (products_attributes_id, products_id, options_id, options_values_id, options_values_price, price_prefix, products_options_sort_order, product_attributes_one_time, products_attributes_weight, products_attributes_weight_prefix, products_attributes_units,     products_attributes_units_price, products_attributes_discount_price, products_attributes_filename, products_attributes_maxdays, products_attributes_maxcount) values ('', '" . $products_id_to . "', '" . $products_copy_from['options_id'] . "', '" . $products_copy_from['options_values_id'] . "', '" . $products_copy_from['options_values_price'] . "', '" . $products_copy_from['price_prefix'] . "', '" . $products_copy_from['products_options_sort_order'] . "', '" . $products_copy_from['product_attributes_one_time'] . "', '" . $products_copy_from['products_attributes_weight'] . "', '" . $products_copy_from['products_attributes_weight_prefix'] . "', '" . $products_copy_from['products_attributes_units'] . "', '" . $products_copy_from['products_attributes_units_price'] . "', '" . $products_copy_from['products_attributes_discount_price'] . "', '" . $products_copy_from['products_attributes_filename'] . "', '" . $products_copy_from['products_attributes_maxdays'] . "', '" . $products_copy_from['products_attributes_maxcount'] . "')");
                                $cur_attributes_id = tep_db_insert_id();
                                self::updateAttributesPrices($cur_attributes_id, $products_copy_from['products_attributes_id']);
                            }
                        }
                        break;
                }
            }
        }
    }
/**
 *
 * @global type $languages_id
 * @global type $cart
 * @param int  $products_id
 * @param array &$attributes
 * @param array $params
 * @return array|boolean
 */
    public static function getDetails($products_id, &$attributes, $params = array()) {

        $real_products_id = \common\helpers\Inventory::normalizeInventoryId($products_id);

        /** @var \common\extensions\Inventory\Inventory $ext */
        if (($ext = \common\helpers\Acl::checkExtension('Inventory', 'getDetails')) && PRODUCTS_INVENTORY == 'True' && !\common\helpers\Inventory::disabledOnProduct($real_products_id)) {
            return $ext::getDetails($products_id, $attributes, $params);
        }
        global $languages_id, $cart;

        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        $currencies = \Yii::$container->get('currencies');
        $currency_id = \Yii::$app->settings->get('currency_id');

        $bundle_attributes = array();
        if (defined('PRODUCTS_BUNDLE_SETS') && PRODUCTS_BUNDLE_SETS == 'True' && is_array($attributes) && count($attributes) > 0) {
            $_attribute_options = array_keys($attributes);
            foreach ($_attribute_options as $_attribute_option) {
                if (strpos($_attribute_option, '-') === false)
                    continue;
                $bundle_attributes[$_attribute_option] = $attributes[$_attribute_option];
                unset($attributes[$_attribute_option]);
            }
        }

        /*$bundle_attributes_valid = true;
        $bundle_attributes_price = 0;
        if (count($bundle_attributes) > 0) {
            foreach ($bundle_attributes as $opt_pid_id => $val_id) {
                list( $opt_id, $bundle_product_id ) = explode('-', $opt_pid_id);
                $attribute_price_query = tep_db_query("select products_attributes_id, options_values_price, price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int) $bundle_product_id . "' and options_id = '" . (int) $opt_id . "' and options_values_id = '" . (int) $val_id . "'");
                if (tep_db_num_rows($attribute_price_query) == 0) {
                    $bundle_attributes_valid = false;
                    continue;
                }
                $attribute_price = tep_db_fetch_array($attribute_price_query);
                $attribute_price['options_values_price'] = self::get_options_values_price($attribute_price['products_attributes_id'], 1);
                if ($attribute_price['price_prefix'] == '-') {
                    $bundle_attributes_price -= $attribute_price['options_values_price'];
                } else {
                    $bundle_attributes_price += $attribute_price['options_values_price'];
                }
            }
        }*/

        $products_id = \common\helpers\Inventory::get_prid($real_products_id);
        $productItem = \common\helpers\Product::itemInstance($products_id);

// find out number of possible values for attributes (not good - price -1). Preselect value if it's only 1
        /*
        $products_options_name_query = tep_db_query("select distinct p.products_id, p.products_tax_class_id, popt.products_options_id, popt.products_options_name, popt.type from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where p.products_id = '" . (int) $products_id . "' and patrib.products_id = p.products_id and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int) $languages_id . "' order by popt.products_options_sort_order, popt.products_options_name");
        while ($products_options_name = tep_db_fetch_array($products_options_name_query)) {
            if (!isset($attributes[$products_options_name['products_options_id']])) {
                $check = tep_db_fetch_array(tep_db_query("select max(pov.products_options_values_id) as values_id, count(pov.products_options_values_id) as values_count from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pa.products_id = '" . (int) $products_id . "' and pa.options_id = '" . (int) $products_options_name['products_options_id'] . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . (int) $languages_id . "'"));
                if ($check['values_count'] == 1) { // if only one option value - it should be selected
                    $attributes[$products_options_name['products_options_id']] = $check['values_id'];
                } else {
                    $attributes[$products_options_name['products_options_id']] = 0;
                }
            }
        }

         */
        $products_options_name_query = tep_db_query(
            "select if(count(pov.products_options_values_id)=1, max(pov.products_options_values_id), 0) as values_id, ".
            " max( if(pa.default_option_value=1, pa.options_values_id, 0)) as default_option_value, ".
            " p.products_id, p.products_tax_class_id, popt.products_options_id, popt.products_options_name, popt.type, popt.products_options_image, popt.products_options_color ".
            "from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov, " . TABLE_PRODUCTS_ATTRIBUTES . " pa "
            . ((USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True')?
             " join " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " pap on pap.products_attributes_id=pa.products_attributes_id and groups_id = '" . (int) $customer_groups_id . "' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int) $currency_id : 0) . "' and pap.attributes_group_price != -1"
            :'')
            . " where p.products_id = '" . (int) $products_id . "' and pa.products_id = p.products_id and pa.options_id = popt.products_options_id and popt.language_id = '" . (int) $languages_id . "' "
            . " and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . (int) $languages_id . "'"
            . " group by p.products_id, p.products_tax_class_id, popt.products_options_id, popt.products_options_name, popt.type "
            . "order by popt.products_options_sort_order, popt.products_options_name");
        while ($products_options_name = tep_db_fetch_array($products_options_name_query)) {
            if (!isset($attributes[$products_options_name['products_options_id']])) {
                if ( $products_options_name['values_id'] ) {
                    $attributes[$products_options_name['products_options_id']] = $products_options_name['values_id'];
                }else{
                    $attributes[$products_options_name['products_options_id']] = $products_options_name['default_option_value'];
                }
            }
        }

        $all_filled = true;
        if (isset($attributes) && is_array($attributes)) {
            foreach ($attributes as $value) {
                $all_filled = $all_filled && (bool) $value;
            }
        }

        $attributes_array = array();
        $attributePirces = []; // need it here to process -1 pseudo price
        if ($products_options_name_query) {
          tep_db_data_seek($products_options_name_query, 0);
        }
        while ($products_options_name = tep_db_fetch_array($products_options_name_query)) {
            $products_options_array = array();
            $products_options_query = tep_db_query(
                    "select pa.products_attributes_id, pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix, pov.products_options_values_color, pov.products_options_values_image " .
                    "from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov " .
                    "where pa.products_id = '" . (int) $products_id . "' and pa.options_id = '" . (int) $products_options_name['products_options_id'] . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . (int) $languages_id . "' " .
                    "order by pa.products_options_sort_order, pov.products_options_values_sort_order, pov.products_options_values_name");
            while ($products_options = tep_db_fetch_array($products_options_query)) {
              $curPrice = $attributePirces[$products_options_name['products_options_id']][$products_options['products_options_values_id']]['price']
                            = self::get_options_values_price($products_options['products_attributes_id'], !empty($params['qty'])?$params['qty']:1);
              $attributePirces[$products_options_name['products_options_id']][$products_options['products_options_values_id']]['price_prefix'] = $products_options['price_prefix'];
              if ($curPrice >= 0 ) { // usual or discounted for group (-2)
                if ($curPrice > 0) {
                    if ( strpos($products_options['price_prefix'],'%')!==false ) {
                        $products_options['products_options_values_name'] .= ' (' . substr($products_options['price_prefix'],0,1).''.\common\helpers\Output::percent($curPrice).') ';
                    }else{
                        $products_options['products_options_values_name'] .= ' (' . $products_options['price_prefix'] . $currencies->display_price($curPrice, (\frontend\design\Info::isTotallyAdmin() ? 0 : \common\helpers\Tax::get_tax_rate($products_options_name['products_tax_class_id']))) . ') ';
                    }
                }
                $products_options_array[] = [
                    'id' => $products_options['products_options_values_id'], 
                    'text' => $products_options['products_options_values_name'],
                    'color' => $products_options['products_options_values_color'], 
                    'image' => $products_options['products_options_values_image'], 
                ];
              } 
            }

            if ($attributes[$products_options_name['products_options_id']] > 0) {
                $selected_attribute = $attributes[$products_options_name['products_options_id']];
            } elseif ($cart->contents[$params['products_id']]['attributes'][$products_options_name['products_options_id']] > 0) {
                $selected_attribute = $cart->contents[$params['products_id']]['attributes'][$products_options_name['products_options_id']];
            } else {
                $selected_attribute = false;
            }

            $attributes_array[] = array(
                'id' => $products_options_name['products_options_id'],
                'title' => htmlspecialchars($products_options_name['products_options_name']),
                'name' => 'id[' . $products_options_name['products_options_id'] . ']',
                'type' => $products_options_name['type'],
                'options' => $products_options_array,
                'selected' => $selected_attribute,
                'color' => $products_options_name['products_options_color'],
                'image' => $products_options_name['products_options_image'],
            );
        }

        $product_query = tep_db_query("select products_id, products_price, products_tax_class_id, stock_indication_id, stock_delivery_terms_id, products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
        if ($product = tep_db_fetch_array($product_query)) {
//          $product_price = \common\helpers\Product::get_products_price($product['products_id'], !empty($params['qty'])?$params['qty']:1, $product['products_price']);
//          $special_price = \common\helpers\Product::get_products_special_price($product['products_id'], !empty($params['qty'])?$params['qty']:1);
          if (isset($attributes) && is_array($attributes)) {
              $_price_uprid = \common\helpers\Inventory::get_uprid($product['products_id'], $attributes);
              $attrPriceInstance = \common\models\Product\Price::getInstance($_price_uprid);
              $product_price = $attrPriceInstance->getInventoryPrice(['qty' => !empty($params['qty'])?$params['qty']:1]);
              $special_price = $attrPriceInstance->getInventorySpecialPrice(['qty' => !empty($params['qty'])?$params['qty']:1]);
              /*
            foreach ($attributes as $opt_id => $val_id) {
              $price = $attributePirces[$opt_id][$val_id]['price'];
              $price_prefix = $attributePirces[$opt_id][$val_id]['price_prefix'];

              if ($price>0) {
                if ($price_prefix == '+' || $price_prefix == '') {
                  $product_price += $price;
                  if ($special_price !== false) {
                    $special_price += $price;
                  }
                } else {
                  $product_price -= $price;
                  if ($special_price !== false) {
                    $special_price -= $price;
                  }
                }
              }
            }
               */
          }else{
              $product_price = \common\helpers\Product::get_products_price($product['products_id'], !empty($params['qty'])?$params['qty']:1, $product['products_price']);
              $special_price = \common\helpers\Product::get_products_special_price($product['products_id'], !empty($params['qty'])?$params['qty']:1);
          }
        }

        ksort($attributes);
        $current_uprid = \common\helpers\Inventory::get_uprid($products_id, $attributes);
//        $image_set = \common\classes\Images::getImageList($current_uprid);	
        //$product_query = tep_db_query("select products_id, products_price, products_tax_class_id, stock_indication_id, stock_delivery_terms_id, products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
        $_backup_products_quantity = 0;
        $_backup_stock_indication_id = $_backup_stock_delivery_terms_id = 0;
        if ($product/* = tep_db_fetch_array($product_query)*/) {
            $_backup_products_quantity = $product['products_quantity'];
            $_backup_stock_indication_id = $product['stock_indication_id'];
            $_backup_stock_delivery_terms_id = $product['stock_delivery_terms_id'];
        }
        $stock_indicator = \common\classes\StockIndication::product_info(array(
                    'products_id' => $products_id,
                    'products_quantity' => $_backup_products_quantity,
                    'stock_indication_id' => $_backup_stock_indication_id,
                    'stock_delivery_terms_id' => $_backup_stock_delivery_terms_id,
        ));
        $stock_indicator_public = $stock_indicator['flags'];
        $stock_indicator_public['id'] = $stock_indicator['id'];
        $stock_indicator_public['quantity_max'] = \common\helpers\Product::filter_product_order_quantity($current_uprid, $stock_indicator['max_qty'], true);
        $stock_indicator_public['stock_code'] = $stock_indicator['stock_code'];
        $stock_indicator_public['text_stock_code'] = $stock_indicator['text_stock_code'];
        $stock_indicator_public['stock_indicator_text'] = $stock_indicator['stock_indicator_text'];
        if ($stock_indicator_public['request_for_quote']) {
            $special_price = false;
        }
        
        $return_data = [
            'product_valid' => ($all_filled /* && $bundle_attributes_valid */ ? '1' : '0'),
            'product_price' => $currencies->display_price($product_price + $bundle_attributes_price, \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']), 1, ($special_price === false ? true : '')),
            'product_unit_price' => $currencies->display_price_clear(($product_price + $bundle_attributes_price), 0),
            'tax' => \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']),
            'special_price' => ($special_price !== false ? $currencies->display_price($special_price + $bundle_attributes_price, \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']), 1, true) : ''),
            'special_unit_price' => ($special_price !== false ? $currencies->display_price_clear(($special_price + $bundle_attributes_price), 0) : ''),
            'product_qty' => ($product['products_quantity'] ? $product['products_quantity'] : '0'),
            'product_in_cart' => \frontend\design\Info::checkProductInCart($current_uprid),
            //'images_active' => array_keys($image_set),
            'current_uprid' => $current_uprid,
            'weight' => $productItem->getProductWeight($current_uprid),
            'attributes_array' => $attributes_array,
            'stock_indicator' => $stock_indicator_public,
            //'dynamic_prop' => $dynamic_prop,
        ];

        if ($ext = \common\helpers\Acl::checkExtension('TypicalOperatingTemp', 'runAttributes')){
            $ext_data = $ext::runAttributes($current_uprid);
            if ( is_array($ext_data) ) $return_data = array_merge($return_data, $ext_data);
        }
        /**
         * $stock_indicator_public['display_price_options']
         * 0 - display
         * 1 - hide
         * 2 - hide if zero
         */
        if (($stock_indicator_public['request_for_quote'] /*&& $stock_indicator_public['display_price_options'] != 0*/) ||
            ($stock_indicator_public['display_price_options'] == 1) ||
            (abs($product_price + $bundle_attributes_price)<0.01 && $stock_indicator_public['display_price_options'] == 2)
            ) {
            $return_data['stock_indicator']['add_to_cart'] = 0;
            $return_data['product_price'] = '';
            $return_data['product_unit_price'] = '';
            $return_data['special_price'] = false;
            $return_data['special_unit_price'] = false;
        }

        return $return_data;
    }

    public static function get_options_values_discount_price($products_attributes_id, $qty, $options_values_price) {
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        $currency_id = \Yii::$app->settings->get('currency_id');
        $apply_discount = false;
        if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True') {
            $query = tep_db_query("select attributes_group_discount_price as products_attributes_discount_price, attributes_group_price as options_values_price from " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " where products_attributes_id=" . (int) $products_attributes_id . " and groups_id = '" . (int) $customer_groups_id . "' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int) $currency_id : 0) . "'");
            $num_rows = tep_db_num_rows($query);
            $data = tep_db_fetch_array($query);
            if (!$num_rows || ($data['products_attributes_discount_price'] == '' && $data['options_values_price'] == -2) || $data['products_attributes_discount_price'] == -2 || (USE_MARKET_PRICES != 'True' && $customer_groups_id == 0)) {
                if (USE_MARKET_PRICES == 'True') {
                    $data = tep_db_fetch_array(tep_db_query("select attributes_group_discount_price as products_attributes_discount_price from " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " where products_attributes_id=" . (int) $products_attributes_id . " and groups_id = '0' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int) $currency_id : 0) . "'"));
                } else {
                    $data = tep_db_fetch_array(tep_db_query("select products_attributes_discount_price from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_attributes_id = '" . (int) $products_attributes_id . "'"));
                }
                $apply_discount = true;
            }
        } else {
            $data = tep_db_fetch_array(tep_db_query("select products_attributes_discount_price from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_attributes_id = '" . (int) $products_attributes_id . "'"));
        }

        if ($data['products_attributes_discount_price'] == '') {
            return $options_values_price;
        }
        $ar = preg_split("/[:;]/", preg_replace('/;\s*$/', '', $data['products_attributes_discount_price'])); // remove final separator
        for ($i = 0, $n = sizeof($ar); $i < $n; $i = $i + 2) {
            if ($qty < $ar[$i]) {
                if ($i == 0) {
                    return $options_values_price;
                } else {
                    $price = $ar[$i - 1];
                    break;
                }
            }
        }
        if ($qty >= $ar[$i - 2]) {
            $price = $ar[$i - 1];
        }
        if ($apply_discount) {
            $discount = \common\helpers\Customer::check_customer_groups($customer_groups_id, 'groups_discount');
            $price = $price * (1 - ($discount / 100));
        }
        return $price;
    }

    public static function get_options_values_price($products_attributes_id, $qty = 1, $curr_id = 0) {
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');

        if ($curr_id == 0) {
            $curr_id = \Yii::$app->settings->get('currency_id');
        }
        if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'checkShowPrice')) {
            if ($ext::checkShowPrice($customer_groups_id)) {
                return false;
            }
        }

        if ( empty($products_attributes_id) ) return null;

        if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True') {
            $query = tep_db_query("select attributes_group_price as options_values_price from " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " where products_attributes_id=" . (int) $products_attributes_id . " and groups_id = '" . (int) $customer_groups_id . "' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int) $curr_id : 0) . "'");
            $num_rows = tep_db_num_rows($query);
            $data = tep_db_fetch_array($query);
            if (!$num_rows || ($data['options_values_price'] == -2) || (USE_MARKET_PRICES != 'True' && $customer_groups_id == 0)) {
                if (USE_MARKET_PRICES == 'True') {
                    $data = tep_db_fetch_array(tep_db_query("select pa.price_prefix, pap.attributes_group_price as options_values_price from " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " pap INNER JOIN ".TABLE_PRODUCTS_ATTRIBUTES." pa ON pa.products_attributes_id=pap.products_attributes_id where pap.products_attributes_id=" . (int) $products_attributes_id . " and pap.groups_id = '0' and pap.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int) $curr_id : 0) . "'"));
                } else {
                    $data = tep_db_fetch_array(tep_db_query("select price_prefix, options_values_price from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_attributes_id = '" . (int) $products_attributes_id . "'"));
                }
                if ( strpos($data['price_prefix'],'%')===false ) {
                    $discount = \common\helpers\Customer::check_customer_groups($customer_groups_id, 'groups_discount');
                    $data['options_values_price'] = $data['options_values_price'] * (1 - ($discount / 100));
                }
            }
        } else {
            $data = tep_db_fetch_array(tep_db_query("select options_values_price from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_attributes_id = '" . (int) $products_attributes_id . "'"));
        }
        if ($qty > 1 && $data['options_values_price'] > 0) {
            return self::get_options_values_discount_price($products_attributes_id, $qty, $data['options_values_price']);
        } else {
            return $data['options_values_price'];
        }
    }

    public static function get_attributes_price($attributes_id, $currency_id = 0, $group_id = 0, $default = '') {
        if (USE_MARKET_PRICES != 'True') {
            $currency_id = 0;
        }
        if (CUSTOMERS_GROUPS_ENABLE != 'True') {
            $group_id = 0;
        }
        if ($currency_id == 0 && $group_id == 0) {
            $data_query = tep_db_query("select options_values_price from " . TABLE_PRODUCTS_ATTRIBUTES . " where  products_attributes_id  = '" . $attributes_id . "'");
        } else {
            $data_query = tep_db_query("select attributes_group_price as options_values_price from " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " where products_attributes_id = '" . $attributes_id . "' and currencies_id = '" . $currency_id . "' and groups_id = '" . $group_id . "'");
        }
        $data = tep_db_fetch_array($data_query);
        if ($data['options_values_price'] == '' && $default != '') {
            $data['options_values_price'] = $default;
        }
        return $data['options_values_price'];
    }

    public static function get_attributes_price_edit_order($attributes_id, $currency_id = 0, $group_id = 0, $qty = 1, $recalculate_value = false) {
        $price = self::get_attributes_price($attributes_id, $currency_id, $group_id, false);
        if (CUSTOMERS_GROUPS_ENABLE == 'True' && $group_id != 0 && ($price === false || $price == -2) && $recalculate_value) {
            $discount = tep_db_fetch_array(tep_db_query('select groups_discount from ' . TABLE_GROUPS . " where groups_id = '" . $group_id . "'"));
            $price = self::get_attributes_price($attributes_id, $currency_id, 0);
            $price = $price * (100 - $discount['groups_discount']) / 100;
        }
        if ($qty > 1) {
            $discount_price = self::get_attributes_discount_price($attributes_id, $currency_id, $group_id, false);
            if (CUSTOMERS_GROUPS_ENABLE == 'True' && $group_id != 0 && $discount_price === false && $recalculate_value) {
                $discount_price = self::get_attributes_discount_price($attributes_id, $currency_id, 0, false);
                $apply_discount = true;
            }
            if ($discount_price !== false && $discount_price != -1) {
                $ar = preg_split("/[:;]/", $discount_price);
                for ($i = 0, $n = sizeof($ar); $i < $n; $i = $i + 2) {
                    if ($qty >= $ar[$i]) {
                        //if ($i > 0){
                        $price = $ar[$i + 1];
                        //}
                        //break;
                    }
                }
                if (sizeof($ar) > 2 && $qty >= $ar[sizeof($ar) - 2]) {
                    $price = $ar[sizeof($ar) - 1];
                }
                if ($apply_discount) {
                    $discount = tep_db_fetch_array(tep_db_query('select groups_discount from ' . TABLE_GROUPS . " where groups_id = '" . $group_id . "'"));
                    $price = $price * (100 - $discount['groups_discount']) / 100;
                }
            }
            return $price;
        } else {
            if ($price == -2) {
                return 0;
            } else {
                return $price;
            }
        }
    }

    public static function get_attributes_discount_price($attributes_id, $currency_id = 0, $group_id = 0, $default = '') {
        if (USE_MARKET_PRICES != 'True') {
            $currency_id = 0;
        }
        if (CUSTOMERS_GROUPS_ENABLE != 'True') {
            $group_id = 0;
        }
        if ($currency_id == 0 && $group_id == 0) {
            $data_query = tep_db_query("select products_attributes_discount_price from " . TABLE_PRODUCTS_ATTRIBUTES . " where  products_attributes_id  = '" . $attributes_id . "'");
        } else {
            $data_query = tep_db_query("select attributes_group_discount_price as products_attributes_discount_price from " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " where products_attributes_id = '" . $attributes_id . "' and currencies_id = '" . $currency_id . "' and groups_id = '" . $group_id . "'");
        }
        $data = tep_db_fetch_array($data_query);
        if ($data['products_attributes_discount_price'] == '' && $default != '') {
            $data['products_attributes_discount_price'] = $default;
        }
        return $data['products_attributes_discount_price'];
    }

    public static function get_attributes_prices($attributes_id, $_tax=0) {
      $currencies = \Yii::$container->get('currencies');
      if ((USE_MARKET_PRICES != 'True') && (CUSTOMERS_GROUPS_ENABLE != 'True')) {
        $data_query = tep_db_query("select 0 as groups_id, 0 as currencies_id, options_values_price as products_group_price, products_attributes_discount_price as products_attributes_discount_price from " . TABLE_PRODUCTS_ATTRIBUTES . " where  products_attributes_id  = '" . $attributes_id . "'");
      } else {
        $data_query = tep_db_query("select groups_id, currencies_id, attributes_group_price as products_group_price, attributes_group_discount_price as products_attributes_discount_price from " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " where products_attributes_id = '" . $attributes_id . "'");
      }
      $ret = [];
      while ($data = tep_db_fetch_array($data_query)) {
        $data['round_to'] = (int)$currencies->get_decimal_places_by_id($data['currencies_id']);
        $data['products_group_price_gross'] = round($data['products_group_price'] + round($data['products_group_price'] * (double)$_tax, 6), $data['round_to']);

        $data['qty_discounts'] = []; $tmp= \common\helpers\Product::parseQtyDiscountArray($data['products_attributes_discount_price']);

        if (is_array($tmp)) {
          foreach ($tmp as $key => $value) {
            $data['qty_discounts'][$key]['price'] = $value;
            $data['qty_discounts'][$key]['price_gross'] = round($value + round($value * (double)$_tax, 6), $data['round_to']);
          }
        }
        unset($data['products_attributes_discount_price']);
        if ((USE_MARKET_PRICES == 'True') && (CUSTOMERS_GROUPS_ENABLE == 'True')) {
          $ret[$data['currencies_id']][$data['groups_id']] = $data;
        } elseif ((USE_MARKET_PRICES == 'True')) {
          $ret[$data['currencies_id']] = $data;
        } elseif ((CUSTOMERS_GROUPS_ENABLE == 'True')) {
          $ret[$data['groups_id']] = $data;
        } else {
          $ret = $data;
        }
      }
      return $ret;
    }

    public static function get_template_attributes_price($attributes_id, $currency_id = 0, $group_id = 0, $default = '') {
        if (USE_MARKET_PRICES != 'True') {
            $currency_id = 0;
        }
        if (CUSTOMERS_GROUPS_ENABLE != 'True') {
            $group_id = 0;
        }
        if ($currency_id == 0 && $group_id == 0) {
            $data_query = tep_db_query("select options_values_price from " . TABLE_OPTIONS_TEMPLATES_ATTRIBUTES . " where options_templates_attributes_id = '" . (int)$attributes_id . "'");
        } else {
            $data_query = tep_db_query("select attributes_group_price as options_values_price from " . TABLE_OPTIONS_TEMPLATES_ATTRIBUTES_PRICES . " where options_templates_attributes_id = '" . (int)$attributes_id . "' and currencies_id = '" . (int)$currency_id . "' and groups_id = '" . (int)$group_id . "'");
        }
        $data = tep_db_fetch_array($data_query);
        if ($data['options_values_price'] == '' && $default != '') {
            $data['options_values_price'] = $default;
        }
        return $data['options_values_price'];
    }

    public static function get_template_attributes_prices($attributes_id, $_tax=0) {
        $currencies = \Yii::$container->get('currencies');
        if ((USE_MARKET_PRICES != 'True') && (CUSTOMERS_GROUPS_ENABLE != 'True')) {
            $data_query = tep_db_query("select 0 as groups_id, 0 as currencies_id, options_values_price as products_group_price, products_attributes_discount_price as products_attributes_discount_price from " . TABLE_OPTIONS_TEMPLATES_ATTRIBUTES . " where  options_templates_attributes_id  = '" . $attributes_id . "'");
        } else {
            $data_query = tep_db_query("select groups_id, currencies_id, attributes_group_price as products_group_price, attributes_group_discount_price as products_attributes_discount_price from " . TABLE_OPTIONS_TEMPLATES_ATTRIBUTES_PRICES . " where options_templates_attributes_id = '" . $attributes_id . "'");
        }
        $ret = [];
        while ($data = tep_db_fetch_array($data_query)) {
            $data['round_to'] = (int)$currencies->get_decimal_places_by_id($data['currencies_id']);
            $data['products_group_price_gross'] = round($data['products_group_price'] + round($data['products_group_price'] * (double)$_tax, 6), $data['round_to']);

            $data['qty_discounts'] = [];
            unset($data['products_attributes_discount_price']);
            if ((USE_MARKET_PRICES == 'True') && (CUSTOMERS_GROUPS_ENABLE == 'True')) {
                $ret[$data['currencies_id']][$data['groups_id']] = $data;
            } elseif ((USE_MARKET_PRICES == 'True')) {
                $ret[$data['currencies_id']] = $data;
            } elseif ((CUSTOMERS_GROUPS_ENABLE == 'True')) {
                $ret[$data['groups_id']] = $data;
            } else {
                $ret = $data;
            }
        }
        return $ret;
    }

    public static function is_virtual_option($products_options_id) {
        static $_cached_virtual_options = array();
        if ( !isset($_cached_virtual_options[$products_options_id]) ) {
            $data = \common\models\ProductsOptions::find()->select('max(is_virtual) as is_virtual')->where(['products_options_id' => (int)$products_options_id])->asArray()->one();
            $_cached_virtual_options[$products_options_id] = $data['is_virtual'];
        }
        return $_cached_virtual_options[$products_options_id];
    }

    public static function get_virtual_attribute_price($products_id, $virtual_vids, $qty = 1, $base_price=null) {
        $total_attribute_price = 0;
        if (is_array($virtual_vids)) {
            $attributes_price_percents = [];
            $attributes_price_percents_base = [];
            $attributes_price_fixed = 0;

            foreach ($virtual_vids as $options_id => $value) {
                $option_arr = explode('-', $options_id);
                $attribute_price_query = tep_db_query("select products_attributes_id, options_values_price, price_prefix, products_attributes_weight, products_attributes_weight_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int) ($option_arr[1] > 0 ? $option_arr[1] : $products_id) . "' and options_id = '" . (int) $option_arr[0] . "' and options_values_id = '" . (int) $value . "'");
                $attribute_price = tep_db_fetch_array($attribute_price_query);
                $attribute_price['options_values_price'] = \common\helpers\Attributes::get_options_values_price($attribute_price['products_attributes_id'], $qty);
                if ($attribute_price['options_values_price'] == -1) {
                    return false;
                }
                if ( is_null($base_price) ) {
                    if ($attribute_price['price_prefix'] != '-') {
                        $total_attribute_price += $attribute_price['options_values_price'];
                    } else {
                        $total_attribute_price -= $attribute_price['options_values_price'];
                    }
                }else{
                    if ( $attribute_price['price_prefix']=='-%' ) {
                        $attributes_price_percents[] = 1-$attribute_price['options_values_price']/100;
                    }elseif($attribute_price['price_prefix'] == '+%'){
                        $attributes_price_percents[] = 1+$attribute_price['options_values_price']/100;
                    }elseif($attribute_price['price_prefix'] == '+%b'){
                        $attributes_price_percents_base[] = $attribute_price['options_values_price']/100;
                    }elseif($attribute_price['price_prefix'] == '-%b'){
                        $attributes_price_percents_base[] = -1*$attribute_price['options_values_price']/100;
                    }else{
                        $attributes_price_fixed += (($attribute_price['price_prefix']=='-')?-1:1)*$attribute_price['options_values_price'];
                    }
                }
            }
            if ( !is_null($base_price) ) {
                $final_price = $base_price;
                $tmp = $final_price += $attributes_price_fixed;
                foreach ($attributes_price_percents_base as $attributes_price_percent) {
                    $final_price += $tmp * $attributes_price_percent;
                }
                foreach ($attributes_price_percents as $attributes_price_percent) {
                    $final_price *= $attributes_price_percent;
                }
                $total_attribute_price = $final_price-$base_price;
            }
        }
        return $total_attribute_price;
    }
    
    public static function getQTAonMixedArray($post, $skipQty = false){
        $response = [];
        if (isset($post['mix'])){
            foreach ($post['mix'] as $it => $products_id) {
                if ($products_id > 0) {
                    if ($post['mix_qty'][$products_id][$it] > 0 || $skipQty) {
                        $qty = (int)$post['mix_qty'][$products_id][$it];
                        $attributes = $post['id'] ?? [];
                        if (isset($post['mix_attr'][$products_id][$it])){
                            $attributes = array_replace($attributes, $post['mix_attr'][$products_id][$it]);
                        }
                        $response[] = [
                            'products_id' => (int)$products_id,
                            'qty' => $qty,
                            'id' => $attributes
                        ];
                    }
                }
            }
        }
        return $response;
    }
}
