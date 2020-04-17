<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\models\ProductEdit;


class ViewPriceData
{
    /**
     * @var \objectInfo
     */
    protected $productInfoRef;

    public function __construct($productInfo)
    {
        if (is_object($productInfo) && $productInfo instanceof \common\models\Products ) {
          $productInfo = new \objectInfo($productInfo->getAttributes());
        }
        $this->productInfoRef = $productInfo;
    }
    
    public function populateView($view)
    {
        $currencies = \Yii::$container->get('currencies');

        $pInfo = $this->productInfoRef;
        
        $price_tabs_data = $pack_unit_price_tabs_data = $packaging_price_tabs_data = $gift_wrap_data = [];

        $_tax =  \common\helpers\Tax::get_tax_rate_value($pInfo->products_tax_class_id)/100;
        $_roundTo = $currencies->get_decimal_places(DEFAULT_CURRENCY);

        $gift_wrap = tep_db_query("select gw_id as gift_wrap_id, groups_id, currencies_id, gift_wrap_price, round(gift_wrap_price +round(gift_wrap_price *" . (double)$_tax . ", 6), " . (int)$_roundTo. ") as gift_wrap_price_gross from " . TABLE_GIFT_WRAP_PRODUCTS . " where products_id ='" . (int) $pInfo->products_id . "'");
        if (tep_db_num_rows($gift_wrap) > 0) {
            while ($data = tep_db_fetch_array($gift_wrap)){
                if (USE_MARKET_PRICES == 'True' && CUSTOMERS_GROUPS_ENABLE == 'True') {
                    $idx = '[' . $data['currencies_id'] . '][' . $data['groups_id'] . ']';
                } elseif (USE_MARKET_PRICES == 'True') {
                    $idx = '[' . $data['currencies_id'] . ']';
                } elseif (CUSTOMERS_GROUPS_ENABLE == 'True') {
                    $idx = '[' . $data['groups_id'] . ']';
                } else {
                    $idx = '';
                }
                eval('$gift_wrap_data' . $idx . ' = $data;');
            }
        }

        if (isset($view->defaultSaleId)) {
          $_def_sale = tep_db_fetch_array(tep_db_query("select * from " . TABLE_SPECIALS . " where products_id = '" . (int) $pInfo->products_id . "' and specials_id='" . (int)$view->defaultSaleId . "'"));
        } else {
          $_def_sale = tep_db_fetch_array(tep_db_query("select * from " . TABLE_SPECIALS . " where products_id = '" . (int) $pInfo->products_id . "' and (status=1 or start_date>=now()) order by status desc, start_date limit 1"));
        }
        if (is_array($_def_sale)) {
          $_def_sale['_status'] = $_def_sale['status'];
          $_def_sale['status'] = 1;
          $pInfo->specials_id = $_def_sale['specials_id'];
        }

        $view->qty_discounts = [];
        $view->qty_discounts_pack_unit = [];
        $view->qty_discounts_packaging = [];
///pseudo group "Main" or simple nothing
        //if (CUSTOMERS_GROUPS_ENABLE == 'True' && !$view->useMarketPrices) {

        if ( !$view->useMarketPrices ) {
            $qty_discounts = []; $tmp = \common\helpers\Product::parseQtyDiscountArray($pInfo->products_price_discount);
            if (count($tmp) > 0 ) {
                foreach ($tmp as $key => $value) {
                    $qty_discounts[$key]['price'] = $value;
                    $qty_discounts[$key]['price_gross'] = round($value + round($value * (double)$_tax, 6), $_roundTo);
                }
            }

            $tmp = [
                'groups_id' => 0,
                'currencies_id' => $view->defaultCurrency,
                'products_group_price' => $pInfo->products_price,
                'products_group_price_gross' => round($pInfo->products_price + round($pInfo->products_price*$_tax, 6), $_roundTo),
                'products_group_special_price' => (isset($_def_sale['status']) ? $_def_sale['specials_new_products_price']:0),
                'products_group_special_price_gross' => (isset($_def_sale['status']) ? round($_def_sale['specials_new_products_price'] + round($_def_sale['specials_new_products_price']*$_tax, 6), $_roundTo):0),
                'expires_date' => !empty($_def_sale['expires_date']) ? $_def_sale['expires_date']:'',
                'start_date' => !empty($_def_sale['start_date']) ? $_def_sale['start_date']:'',
                'supplier_price_manual' => $pInfo->supplier_price_manual,
                'bonus_points_price' => $pInfo->bonus_points_price,
                'bonus_points_cost' => $pInfo->bonus_points_cost,
                'shipping_surcharge_price' => $pInfo->shipping_surcharge_price,
                'shipping_surcharge_price_gross' => round($pInfo->shipping_surcharge_price + round($pInfo->shipping_surcharge_price*$_tax, 6), $_roundTo),
                'gift_wrap_id' => ((CUSTOMERS_GROUPS_ENABLE != 'True')?$gift_wrap_data['gift_wrap_id']:$gift_wrap_data[0]['gift_wrap_id']),
                'gift_wrap_price' => ((CUSTOMERS_GROUPS_ENABLE != 'True')?$gift_wrap_data['gift_wrap_price']:$gift_wrap_data[0]['gift_wrap_price']),
                'gift_wrap_price_gross' => ((CUSTOMERS_GROUPS_ENABLE != 'True')?$gift_wrap_data['gift_wrap_price_gross']:$gift_wrap_data[0]['gift_wrap_price_gross']),
                'tax_rate' => (double)$_tax,
                'round_to' => (int)$_roundTo,
                'qty_discounts' => $qty_discounts
            ];
            if (CUSTOMERS_GROUPS_ENABLE != 'True') {
                $price_tabs_data = $tmp;
            } else {
                $price_tabs_data[0] = $tmp;
            }

            $qty_discounts =[]; $tmp = \common\helpers\Product::parseQtyDiscountArray($pInfo->products_price_discount_pack_unit);
            if (count($tmp) > 0 ) {
                foreach ($tmp as $key => $value) {
                    $qty_discounts[$key]['price'] = $value;
                    $qty_discounts[$key]['price_gross'] = round($value + round($value * (double)$_tax, 6), $_roundTo);
                }
            }
            $tmp = [
                'groups_id' => 0,
                'currencies_id' => $view->defaultCurrency,
                'tax_rate' => (double)$_tax,
                'round_to' => (int)$_roundTo,
                'products_group_price_pack_unit' => $pInfo->products_price_pack_unit,
                'products_group_price_gross_pack_unit' => round($pInfo->products_price_pack_unit + round($pInfo->products_price_pack_unit*$_tax, 6), $_roundTo),
                'qty_discounts' => $qty_discounts
            ];
            if (CUSTOMERS_GROUPS_ENABLE != 'True') {
                $pack_unit_price_tabs_data = $tmp;
            } else {
                $pack_unit_price_tabs_data[0] = $tmp;
            }
            $qty_discounts =[]; $tmp = \common\helpers\Product::parseQtyDiscountArray($pInfo->products_price_discount_packaging);
            if (count($tmp) > 0 ) {
                foreach ($tmp as $key => $value) {
                    $qty_discounts[$key]['price'] = $value;
                    $qty_discounts[$key]['price_gross'] = round($value + round($value * (double)$_tax, 6), $_roundTo);
                }
            }
            $tmp = [
                'groups_id' => 0,
                'currencies_id' => $view->defaultCurrency,
                'tax_rate' => (double)$_tax,
                'round_to' => (int)$_roundTo,
                'products_group_price_packaging' => $pInfo->products_price_packaging,
                'products_group_price_gross_packaging' => round($pInfo->products_price_packaging + round($pInfo->products_price_packaging*$_tax, 6), $_roundTo),
                'qty_discounts' => $qty_discounts
            ];
            if (CUSTOMERS_GROUPS_ENABLE != 'True') {
                $packaging_price_tabs_data = $tmp;
            } else {
                $packaging_price_tabs_data[0] = $tmp;
            }

        }

        if ($view->useMarketPrices || CUSTOMERS_GROUPS_ENABLE == 'True') {
            $products_price_query = tep_db_query("select pp.groups_id, pp.currencies_id, pp.supplier_price_manual, pp.products_group_price, round(pp.products_group_price +round(pp.products_group_price *" . (double)$_tax . ", 6), " . (int)$_roundTo. ") as products_group_price_gross, pp.bonus_points_price,	pp.bonus_points_cost, pp.products_group_discount_price, pp.products_group_price_pack_unit, round(pp.products_group_price_pack_unit +round(pp.products_group_price_pack_unit *" . (double)$_tax . ", 6), " . (int)$_roundTo. ") as products_group_price_gross_pack_unit, pp.products_group_discount_price_pack_unit, pp.products_group_price_packaging, round(pp.products_group_price_packaging +round(pp.products_group_price_packaging *" . (double)$_tax . ", 6), " . (int)$_roundTo. ") as products_group_price_gross_packaging, pp.products_group_discount_price_packaging, sp.specials_new_products_price as products_group_special_price, round(sp.specials_new_products_price +round(sp.specials_new_products_price *" . (double)$_tax . ", 6), " . (int)$_roundTo. ") as products_group_special_price_gross, s.expires_date, s.start_date, ifnull(s.status, 0) as sales_status, pp.shipping_surcharge_price, round(pp.shipping_surcharge_price +round(pp.shipping_surcharge_price *" . (double)$_tax . ", 6), " . (int)$_roundTo. ") as shipping_surcharge_price_gross from " . TABLE_PRODUCTS_PRICES . " pp left join " . TABLE_SPECIALS . " s on pp.products_id=s.products_id and s.specials_id='" . (int)$pInfo->specials_id . "' left join " . TABLE_SPECIALS_PRICES . " sp on pp.groups_id=sp.groups_id and pp.currencies_id=sp.currencies_id and s.specials_id=sp.specials_id where pp.products_id = '" . (int)$pInfo->products_id . "' order by pp.currencies_id, pp.groups_id");
            $_tmp_keys = [
                'price_tabs_data' => ['qty_discounts', 'supplier_price_manual', 'products_group_price', 'products_group_price_gross', 'products_group_special_price', 'products_group_special_price_gross', 'bonus_points_price', 'bonus_points_cost', 'expires_date', 'start_date', 'sales_status', 'gift_wrap_id', 'gift_wrap_price', 'gift_wrap_price_gross', 'shipping_surcharge_price', 'shipping_surcharge_price_gross', 'tax_rate', 'round_to', 'base_price', 'base_price_gross', 'base_specials_price', 'base_specials_price_gross'],
                'pack_unit_price_tabs_data' => ['qty_discounts', 'products_group_price_pack_unit', 'products_group_price_gross_pack_unit', 'tax_rate', 'round_to', 'base_price', 'base_price_gross', 'base_specials_price', 'base_specials_price_gross'],
                'packaging_price_tabs_data' => ['qty_discounts', 'products_group_price_packaging', 'products_group_price_gross_packaging', 'tax_rate', 'round_to', 'base_price', 'base_price_gross', 'base_specials_price', 'base_specials_price_gross']
            ];
            $_base_price = $pInfo->products_price;
            $_base_price_gross = round($pInfo->products_price + round($pInfo->products_price*$_tax, 6), $_roundTo);
            $_base_special_price = ($_def_sale['status']==1 ? $_def_sale['specials_new_products_price']:0);
            $_base_special_price_gross = ($_def_sale['status']==1 ? round($_def_sale['specials_new_products_price'] + round($_def_sale['specials_new_products_price']*$_tax, 6), $_roundTo):0);
            while ($products_price_data = tep_db_fetch_array($products_price_query)){
                //$products_price_data['tax_rate'] = (double)$_tax;
                //$products_price_data['round_to'] = (int)$_roundTo;
                $products_price_data['round_to'] = (int)$currencies->get_decimal_places_by_id($products_price_data['currencies_id']);
                if ($view->useMarketPrices  && $products_price_data['groups_id']==0) {
                    $_base_price = $products_price_data['products_group_price'];
                    $_base_price_gross = $products_price_data['products_group_price_gross'];
                    $_base_special_price = $products_price_data['products_group_special_price'];
                    $_base_special_price_gross = $products_price_data['products_group_special_price_gross'];
                } else {
                    $products_price_data['base_price'] = $_base_price;
                    $products_price_data['base_price_gross'] = $_base_price_gross;
                    $products_price_data['base_specials_price'] = $_base_special_price;
                    $products_price_data['base_specials_price_gross'] = $_base_special_price_gross;
                    if (is_null($products_price_data['products_group_special_price'])) { // group 0 could be saved in products_prices ???
                        $products_price_data['products_group_special_price'] = $_base_special_price;
                        $products_price_data['products_group_special_price_gross'] = $_base_special_price_gross;
                    }
                }


                if (USE_MARKET_PRICES == 'True' && CUSTOMERS_GROUPS_ENABLE == 'True') {
                    foreach ($_tmp_keys as $k => $v) {
                        $_tmp_keys[$k] = array_merge($v, ['groups_id', 'currencies_id']);
                    }
                    $idx = '[' . $products_price_data['currencies_id'] . '][' . $products_price_data['groups_id'] . ']';
                } elseif (USE_MARKET_PRICES == 'True') {
                    foreach ($_tmp_keys as $k => $v) {
                        $_tmp_keys[$k] = array_merge($v, ['currencies_id']);
                    }
                    $idx = '[' . $products_price_data['currencies_id'] . ']';
                } else {
                    foreach ($_tmp_keys as $k => $v) {
                        $_tmp_keys[$k] = array_merge($v, ['groups_id']);
                    }
                    $idx = '[' . $products_price_data['groups_id'] . ']';
                }
                $tmp_qty_discounts = false;

                foreach ($_tmp_keys as $k => $v) { // $k - 3 tabs $v - required fields
                    if ($k == 'price_tabs_data') {
                        eval('$products_price_data[\'gift_wrap_id\'] = $gift_wrap_data' . $idx . '[\'gift_wrap_id\'];');
                        eval('$products_price_data[\'gift_wrap_price\'] = $gift_wrap_data' . $idx . '[\'gift_wrap_price\'];');
                        eval('$products_price_data[\'gift_wrap_price_gross\'] = $gift_wrap_data' . $idx . '[\'gift_wrap_price_gross\'];');
                        if($ext = \common\helpers\Acl::checkExtension('UserGroups', 'allowedGroupDiscounts') ) {
                            $tmp_qty_discounts = \common\helpers\Product::parseQtyDiscountArray($products_price_data['products_group_discount_price']);
                        }
                    }
                    if ($k == 'pack_unit_price_tabs_data' && $ext = \common\helpers\Acl::checkExtension('UserGroups', 'allowedGroupDiscounts') ) {
                        $tmp_qty_discounts = \common\helpers\Product::parseQtyDiscountArray($products_price_data['products_group_discount_price_pack_unit']);
                    }
                    if ($k == 'packaging_price_tabs_data' && $ext = \common\helpers\Acl::checkExtension('UserGroups', 'allowedGroupDiscounts') ) {
                        $tmp_qty_discounts = \common\helpers\Product::parseQtyDiscountArray($products_price_data['products_group_discount_price_packaging']);
                    }
                    if (is_array($tmp_qty_discounts)) {
                        foreach ($tmp_qty_discounts as $key => $value) {
                            $products_price_data['qty_discounts'][$key]['price'] = $value;
                            $products_price_data['qty_discounts'][$key]['price_gross'] = round($value + round($value * (double)$_tax, 6), $products_price_data['round_to']);
                        }
                    }

                    $tmp = array_intersect_key($products_price_data, array_flip($v));
                    eval('$'. $k . $idx . ' = $tmp;');
                    unset($products_price_data['qty_discounts']);
                }
            }
        }
        $view->price_tabs_data = $price_tabs_data;//echo '<pre>';print_r($price_tabs_data);die;
        $view->pack_unit_price_tabs_data = $pack_unit_price_tabs_data;
        $view->packaging_price_tabs_data = $packaging_price_tabs_data;
    }
}