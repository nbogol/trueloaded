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

class Tax {

    public static function getDefaultTaxClassIdForProducts()
    {
        $mostUsedTaxClassId = \common\models\Products::find()
            ->select('products_tax_class_id')
            ->groupBy('products_tax_class_id')
            ->orderBy(new \yii\db\Expression('COUNT(*) DESC'))
            ->limit(1)
            ->scalar();
        return (int)$mostUsedTaxClassId;
    }

    public static function add_tax($price, $tax) {
/*      // Rounding to currency decimal places moved to $currencies->calculate_price()
        $currency = \Yii::$app->settings->get('currency');
        $currencies = \Yii::$container->get('currencies');
        if (is_null($currency)) {
            $currency = DEFAULT_CURRENCY;
        }
*/
        if (defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True') {
            return round($price, 6);
        } elseif ((DISPLAY_PRICE_WITH_TAX == 'true') && ($tax > 0)) {
            return round($price + self::roundTax(self::calculate_tax($price, $tax)), 6); //$currencies->currencies[$currency]['decimal_places'];
        } else {
            return round($price, 6); //$currencies->currencies[$currency]['decimal_places'];
        }
    }

    public static function get_untaxed_value($price, $tax) {
        if ($tax == 100) return $price / 2;
        return $price * 100 / ($tax + 100);
    }

    public static function add_tax_always($price, $tax) {
/*
        $currency = \Yii::$app->settings->get('currency');
        $currencies = \Yii::$container->get('currencies');
        if (is_null($currency)) {
            $currency = DEFAULT_CURRENCY;
        }
*/
        return round($price + self::roundTax(self::calculate_tax($price, $tax)), 6); //$currencies->currencies[$currency]['decimal_places'];
    }
    
    public static function reduce_tax_always($price, $tax) {
        return round(self::get_untaxed_value($price, $tax), 6);
    }

    public static function calculate_tax($price, $tax) {
        //$currency = \Yii::$app->settings->get('currency');
        //$currencies = \Yii::$container->get('currencies');
        return round($price * $tax / 100, 6); //$currencies->currencies[$currency]['decimal_places']
    }

    public static function get_tax_description($class_id, $country_id, $zone_id) {
        static $_cached = [];
        $key = (int)$class_id.'@'.(int)$country_id.'@'.(int)$zone_id;
        if ( !isset($_cached[$key]) ) {
            $tax_query = tep_db_query("SELECT tax_description FROM " . TABLE_TAX_RATES . " tr LEFT JOIN " . TABLE_ZONES_TO_TAX_ZONES . " za ON (tr.tax_zone_id = za.geo_zone_id) LEFT JOIN " . TABLE_TAX_ZONES . " tz ON (tz.geo_zone_id = tr.tax_zone_id) WHERE (za.zone_country_id IS NULL OR za.zone_country_id = '0' OR za.zone_country_id = '" . (int)$country_id . "') AND (za.zone_id IS NULL OR za.zone_id = '0' OR za.zone_id = '" . (int)$zone_id . "') AND tr.tax_class_id = '" . (int)$class_id . "' ORDER BY tr.tax_priority");
            if (tep_db_num_rows($tax_query)) {
                $tax_description = '';
                while ($tax = tep_db_fetch_array($tax_query)) {
                    $tax_description .= $tax['tax_description'] . ' + ';
                }
                $tax_description = substr($tax_description, 0, -3);

                $_cached[$key] = $tax_description;
            } else {
                $_cached[$key] = TEXT_UNKNOWN_TAX_RATE;
            }
        }
        return $_cached[$key];
    }

    public static function display_tax_value($value, $padding = TAX_DECIMAL_PLACES) {
        if (strpos($value, '.')) {
            $loop = true;
            while ($loop) {
                if (substr($value, -1) == '0') {
                    $value = substr($value, 0, -1);
                } else {
                    $loop = false;
                    if (substr($value, -1) == '.') {
                        $value = substr($value, 0, -1);
                    }
                }
            }
        }

        if ($padding > 0) {
            if ($decimal_pos = strpos($value, '.')) {
                $decimals = strlen(substr($value, ($decimal_pos + 1)));
                for ($i = $decimals; $i < $padding; $i++) {
                    $value .= '0';
                }
            } else {
                $value .= '.';
                for ($i = 0; $i < $padding; $i++) {
                    $value .= '0';
                }
            }
        }

        return $value;
    }


    public static function get_tax_rate($class_id, $country_id = -1, $zone_id = -1) {
        global $tax_rates_array;
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');

        if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'checkTaxRate')) {
            if ($ext::checkTaxRate($customer_groups_id)) {
                return 0;
            }
        }

        /** @var \common\extensions\VatOnOrder\VatOnOrder $VatOnOrder */
        if ($VatOnOrder = \common\helpers\Acl::checkExtension('VatOnOrder', 'check_tax_rate')) {
            if ($VatOnOrder::check_tax_rate($country_id != -1? true : false)) {
                return 0;
            }
        }

        if (($country_id == -1) && ($zone_id == -1)) {
            if (\Yii::$app->user->isGuest){
                $country_id = \Yii::$app->storage->has('customer_country_id')? \Yii::$app->storage->get('customer_country_id') : PlatformConfig::getValue('STORE_COUNTRY');
                $zone_id = \Yii::$app->storage->has('customer_zone_id')? \Yii::$app->storage->get('customer_zone_id') : PlatformConfig::getValue('STORE_ZONE');
                //$country_id = STORE_COUNTRY;
                //$zone_id = STORE_ZONE;
            } else {
                $country_id = \Yii::$app->user->getIdentity()->get('customer_country_id') ?? PlatformConfig::getValue('STORE_COUNTRY');
                $zone_id = \Yii::$app->user->getIdentity()->get('customer_zone_id') ?? PlatformConfig::getValue('STORE_ZONE');
            }
        }
        if (isset($tax_rates_array[$class_id][$country_id][$zone_id])) {
            return $tax_rates_array[$class_id][$country_id][$zone_id];
        }

        $tax_query = tep_db_query("select sum(tax_rate) as tax_rate from " . TABLE_TAX_RATES . " tr left join " . TABLE_ZONES_TO_TAX_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id) left join " . TABLE_TAX_ZONES . " tz on (tz.geo_zone_id = tr.tax_zone_id) where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int) $country_id . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int) $zone_id . "') and tr.tax_class_id = '" . (int) $class_id . "' group by tr.tax_priority");

        if (tep_db_num_rows($tax_query)) {
            $tax_multiplier = 1.0;
            while ($tax = tep_db_fetch_array($tax_query)) {
                $tax_multiplier *= 1.0 + ($tax['tax_rate'] / 100);
            }
            $tax_rates_array[$class_id][$country_id] = array($zone_id => ($tax_multiplier - 1.0) * 100);//echo '<pre>';print_r($tax_rates_array);die;
            return ($tax_multiplier - 1.0) * 100;
        } else {
            $tax_rates_array[$class_id][$country_id] = array($zone_id => 0);
            return 0;
        }
    }
    
    public static function getTaxZones($country_id, $zone_id) {
        $key = (int)$country_id.'^'.(int)$zone_id;
        static $fetched = [];
        if ( isset($fetched[$key]) ) return $fetched[$key];
        
        $geo_zones_array = [];
        $geo_zones_query = tep_db_query("select geo_zone_id from " . TABLE_ZONES_TO_TAX_ZONES . " za where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int) $country_id . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int) $zone_id . "')");
        while ($geo_zones = tep_db_fetch_array($geo_zones_query)) {
            $geo_zones_array[] = $geo_zones['geo_zone_id'];
        }

        $fetched[$key] = $geo_zones_array;
        return $geo_zones_array;
    }

    public static function getTaxValues($platform_id, $tax_class_id, $tax_country_id, $tax_zone_id) {
        $tax = 0;
        $tax_description = '';

        $paltform_config = \Yii::$app->get('platform')->getConfig($platform_id);
        $platform_address = $paltform_config->getPlatformAddress();
        if ($platform_address) {
            $check_tax_zones = \common\helpers\Tax::getTaxZones($tax_country_id, $tax_zone_id);

            $check_platform_zone = tep_db_fetch_array(tep_db_query("select za.geo_zone_id from " . TABLE_TAX_RATES . " tr, " . TABLE_ZONES_TO_TAX_ZONES . " za where tr.tax_zone_id = za.geo_zone_id and (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int) $platform_address['country_id'] . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int) $platform_address['zone_id'] . "') and tr.tax_class_id = '" . (int) $tax_class_id . "'"));

            if (in_array($check_platform_zone['geo_zone_id'], $check_tax_zones)) {
                $tax = \common\helpers\Tax::get_tax_rate($tax_class_id, $platform_address['country_id'], $platform_address['zone_id']);
                $tax_description = \common\helpers\Tax::get_tax_description($tax_class_id, $platform_address['country_id'], $platform_address['zone_id']);
            }
        } else {
            $tax = \common\helpers\Tax::get_tax_rate($tax_class_id, $tax_country_id, $tax_zone_id);
            $tax_description = \common\helpers\Tax::get_tax_description($tax_class_id, $tax_country_id, $tax_zone_id);
        }
        return [
            'tax_class_id' => $tax_class_id,
            'tax' => $tax,
            'tax_description' => $tax_description
        ];
    }

    /*public static function et_tax_rate($class_id, $country_id = -1, $zone_id = -1) {
        global $customer_zone_id, $customer_country_id;

        if (($country_id == -1) && ($zone_id == -1)) {
            if (!tep_session_is_registered('customer_id')) {
                $country_id = STORE_COUNTRY;
                $zone_id = STORE_ZONE;
            } else {
                $country_id = $customer_country_id;
                $zone_id = $customer_zone_id;
            }
        }

        $tax_query = tep_db_query("select SUM(tax_rate) as tax_rate from " . TABLE_TAX_RATES . " tr left join " . TABLE_ZONES_TO_TAX_ZONES . " za ON tr.tax_zone_id = za.geo_zone_id left join " . TABLE_TAX_ZONES . " tz ON tz.geo_zone_id = tr.tax_zone_id WHERE (za.zone_country_id IS NULL OR za.zone_country_id = '0' OR za.zone_country_id = '" . (int) $country_id . "') AND (za.zone_id IS NULL OR za.zone_id = '0' OR za.zone_id = '" . (int) $zone_id . "') AND tr.tax_class_id = '" . (int) $class_id . "' GROUP BY tr.tax_priority");
        if (tep_db_num_rows($tax_query)) {
            $tax_multiplier = 0;
            while ($tax = tep_db_fetch_array($tax_query)) {
                $tax_multiplier += $tax['tax_rate'];
            }
            return $tax_multiplier;
        } else {
            return 0;
        }
    }*/

    public static function get_tax_rate_value($class_id) {
        $tax_query = tep_db_query("select SUM(tax_rate) as tax_rate from " . TABLE_TAX_RATES . " where tax_class_id = '" . (int) $class_id . "' group by tax_priority");
        if (tep_db_num_rows($tax_query)) {
            $tax_multiplier = 0;
            while ($tax = tep_db_fetch_array($tax_query)) {
                $tax_multiplier += $tax['tax_rate'];
            }
            return $tax_multiplier;
        } else {
            return 0;
        }
    }

    public static function get_tax_rate_value_edit_order($class_id, $tax_zone_id) {
        $tax_query = tep_db_query("select SUM(tax_rate) as tax_rate from " . TABLE_TAX_RATES . " where tax_class_id = '" . (int) $class_id . "' and tax_zone_id = '" . $tax_zone_id . "' group by tax_priority");
        if (tep_db_num_rows($tax_query)) {
            $tax_multiplier = 0;
            while ($tax = tep_db_fetch_array($tax_query)) {
                $tax_multiplier += $tax['tax_rate'];
            }
            return $tax_multiplier;
        } else {
            return 0;
        }
    }

/**
 * Tax descriptions should be unique ....
 * @param string  $tax_desc
 * @return 0-100
 */
    public static function get_tax_rate_from_desc($tax_desc) {
        $tax_query = tep_db_query("select tax_rate from " . TABLE_TAX_RATES . " where tax_description = '" . tep_db_input($tax_desc) . "'");
        $tax = tep_db_fetch_array($tax_query);
        return $tax['tax_rate'];
    }

    public static function get_tax_class_title($tax_class_id) {
        if ($tax_class_id == '0') {
            return TEXT_NONE;
        } else {
            $classes_query = tep_db_query("select tax_class_title from " . TABLE_TAX_CLASS . " where tax_class_id = '" . (int) $tax_class_id . "'");
            $classes = tep_db_fetch_array($classes_query);

            return $classes['tax_class_title'];
        }
    }
	
    public static function get_zone_id($class_id, $country_id, $zone_id){
        $tax = tep_db_fetch_array(tep_db_query("select geo_zone_id from " . TABLE_TAX_RATES . " tr left join " . TABLE_ZONES_TO_TAX_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id) where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int) $country_id . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int) $zone_id . "') and tr.tax_class_id = '" . (int) $class_id . "' group by tr.tax_priority"));
        if ($tax) {
            return $tax['geo_zone_id'];
        } else {
            return false;
        }
    }

    public static function getTaxClassesVariants($withNone = false)
    {
        $taxClasses = [];
        if ($withNone) {
            $taxClasses[] = [
                'id' => 0,
                'text' => '',
            ];
        }
        $classes_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
        if ( tep_db_num_rows($classes_query)>0 ) {
            while ($classes = tep_db_fetch_array($classes_query)) {
                $taxClasses[] = [
                    'id' => $classes['tax_class_id'],
                    'text' => $classes['tax_class_title'],
                ];
            }
        }
        return $taxClasses;
    }

    public static function tax_classes_pull_down($parameters, $selected = '') {
        $select_string = '<select ' . $parameters . '>';
        foreach( self::getTaxClassesVariants() as $variant){
            $select_string .= '<option value="' . $variant['id'] . '"';
            if ($selected == $variant['id'])
                $select_string .= ' SELECTED';
            $select_string .= '>' . $variant['text'] . '</option>';
        }
        $select_string .= '</select>';

        return $select_string;
    }
	
    public static function get_complex_classes_list(){
        $tax_class_array = [];
        $tax_class_query = tep_db_query("select tr.tax_class_id, tr.tax_zone_id, sum(tr.tax_rate) as rate, tr.tax_description, tc.tax_class_title from " . TABLE_TAX_RATES . " tr inner join " . TABLE_TAX_CLASS . " tc on tc.tax_class_id = tr.tax_class_id where 1 group by tax_class_id, tax_zone_id order by tax_description");
        while ($tax_class = tep_db_fetch_array($tax_class_query)) {
            $query = tep_db_query("select * from " . TABLE_TAX_RATES . " tr left join " . TABLE_TAX_CLASS . " tc on tc.tax_class_id = tr.tax_class_id where tr.tax_class_id = '" . $tax_class['tax_class_id'] . "' and tr.tax_zone_id = '" . $tax_class['tax_zone_id'] . "'");
            if (tep_db_num_rows($query) > 1){
                $str = '';
                while ($data = tep_db_fetch_array($query)){
                  if ($str == ''){
                        $str .= $data['tax_class_title'];
                  }else{
                        $str .= " + " . $data['tax_class_title'];
                  }
                }
                $tax_class['tax_class_title'] = $str;
            }
            $tax_class_array[$tax_class['tax_class_id'] . '_' . $tax_class['tax_zone_id']] = //[
                $tax_class['tax_class_title'];
                        //'text' => $tax_class['tax_class_title'],
                        //'rate' => $tax_class['rate']
                //];
                    
        }
        return $tax_class_array;
    }
    
    public static function getOrderTaxRates(){
        $rates_query = tep_db_query("select tr.tax_class_id, tr.tax_zone_id, tr.tax_rate from " . TABLE_TAX_RATES . " tr inner join " . TABLE_TAX_CLASS . " tc on tc.tax_class_id = tr.tax_class_id where 1 group by tr.tax_class_id, tr.tax_zone_id");
        $rates = [];
        if (tep_db_num_rows($rates_query)) {
            while ($row = tep_db_fetch_array($rates_query)) {
                $rates[$row['tax_class_id'] . '_' . $row['tax_zone_id']] = $row['tax_rate'];
            }
        }
        return $rates;
    }


    public static function roundTax($price) {
      $ret = $price;
      if (defined('TAX_ROUND_PER_ROW') && TAX_ROUND_PER_ROW=='True') {
        try {
          $currency = \Yii::$app->settings->get('currency');
          $currencies = \Yii::$container->get('currencies');
          if ($currency && !empty($currencies->currencies[$currency])) {
            $ret = round($price, $currencies->currencies[$currency]['decimal_places']);
          }
        } catch (\Exception $e) {
          \Yii::warning($e->getMessage() . ' ' . $e->getTraceAsString());
        }
      }
      return $ret;
    }
}
