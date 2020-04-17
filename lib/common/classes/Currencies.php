<?php

/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\classes;

use \frontend\design\Info;

class Currencies {

    var $currencies;
    var $platform_currencies = [];
    var $dp_currency;
    var $currency_codes = []; //cache: codes by IDs
    var $margin_platform_id;
    var $margin_array;

    function __construct($platform_id = null) {
        $this->currencies = array();

        if (!Info::isTotallyAdmin()) {
            $this->platform_currencies = Info::platformCurrencies();
            $this->dp_currency = \frontend\design\Info::platformDefCurrency();
            if (!is_array($this->platform_currencies) || count($this->platform_currencies) == 0) {
                $this->platform_currencies = array(DEFAULT_CURRENCY);
                $this->dp_currency = DEFAULT_CURRENCY;
            }
        } else {
            $this->dp_currency = DEFAULT_CURRENCY;
            $this->platform_currencies = array(DEFAULT_CURRENCY);
        }

        $currenciesResponse = \common\models\Currencies::find()
                ->where(['status' => 1])
                ->orderBy(['sort_order' => SORT_ASC, 'title' => SORT_ASC])
                ->asArray()
                ->all();
        foreach ($currenciesResponse as $currencies) {
            $this->currencies[$currencies['code']] = array('title' => $currencies['title'],
                'id' => $currencies['currencies_id'],
                'code' => $currencies['code'],
                'symbol_left' => $currencies['symbol_left'],
                'symbol_right' => $currencies['symbol_right'],
                'decimal_point' => $currencies['decimal_point'],
                'thousands_point' => $currencies['thousands_point'],
                'decimal_places' => (int) $currencies['decimal_places'],
                '_value' => $currencies['value'],
                'value' => $currencies['value']);
            $this->currency_codes[$currencies['currencies_id']] = $currencies['code'];
        }

        if (is_null($platform_id)) {
            $platform_id = \Yii::$app->get('platform')->config()->getId();
        }
        $this->applyPlatformMargin($platform_id);

        if (USE_MARKET_PRICES == 'True') {
            $currency = \Yii::$app->settings->get('currency');
            if (empty($currency)) {
                $currency = DEFAULT_CURRENCY;
                \Yii::$app->settings->set('currency', $currency);
            }

            $currency_value = $this->currencies[$currency]['value'];
            foreach ($this->currencies as $code => $curr) {
                $this->currencies[$code]['value'] /= $currency_value;
            }
        }
    }

    public function applyPlatformMargin($platformId) {
        $this->margin_platform_id = $platformId;
        $this->margin_array = [];
        foreach ($this->currencies as $currency) {
            $this->margin_array[$currency['id']] = [
                'margin_value' => 0,
                'use_custom_currency_value' => false,
                'custom_currency_value' => 1,
                'margin_value_show' => '0',
                'margin_type' => '%',
            ];
        }
        $get_platform_margin_r = \common\models\PlatformsCurrenciesMargin::find()->where(['platform_id' => $this->margin_platform_id])->asArray()->all();

        $count = count($get_platform_margin_r);
        if ($count > 0) {
            foreach ($get_platform_margin_r as $array => $get_platform_margin) {
                if (!isset($this->margin_array[$get_platform_margin['currencies_id']]))
                    continue;
                $this->margin_array[$get_platform_margin['currencies_id']] = [
                    'use_custom_currency_value' => !!$get_platform_margin['use_custom_currency_value'],
                    'custom_currency_value' => $get_platform_margin['currency_value'],
                    'margin_value' => floatval($get_platform_margin['margin_value']),
                    'margin_value_show' => rtrim(rtrim($get_platform_margin['margin_value'], '0'), '.'),
                    'margin_type' => $get_platform_margin['margin_type'],
                ];
            }
        }

        foreach ($this->currencies as $currencyCode => $currency) {
            $newValue = $currency['_value'];
            if ($this->margin_array[$currency['id']]['use_custom_currency_value']) {
                $newValue = $this->margin_array[$currency['id']]['custom_currency_value'];
            }
            $margin_type = $this->margin_array[$currency['id']]['margin_type'];
            $margin_value = $this->margin_array[$currency['id']]['margin_value'];
            if ($margin_value != 0) {
                if ($margin_type == '+') {
                    $newValue += $margin_value;
                } elseif ($margin_type == '%') {
                    $newValue = $newValue * (100 + $margin_value) / 100;
                }
            }
            $this->currencies[$currencyCode]['value'] = $newValue;
        }
    }

/**
 *
 * @param decimal $number
 * @param bool  $calculate_currency_value default true
 * @param string $currency_type def ''
 * @param decimal $currency_value def ''
 * @param bool $microdata def false
 * @param bool $metaTags def false
 * @return string
 */
    function format($number, $calculate_currency_value = true, $currency_type = '', $currency_value = '', $microdata = false, $metaTags = false) {
        $currency = \Yii::$app->settings->get('currency');

        if (\frontend\design\Info::isTotallyAdmin() && empty($currency)) {
            $currency = DEFAULT_CURRENCY;
            \Yii::$app->settings->set('currency', $currency);
        }
        
        if (empty($currency_type)) {
            $currency_type = $currency;
        }

        $format_string = '';

        if ($this->currencies[$currency_type]['symbol_left']) {
            if ($microdata) {
                $format_string .= '<span itemprop="priceCurrency" content="' . $currency_type . '">' . $this->currencies[$currency_type]['symbol_left'] . '</span>';
            } else {
                $format_string .= $this->currencies[$currency_type]['symbol_left'];
            }
        }

        if ($calculate_currency_value == true && USE_MARKET_PRICES != 'True') {
            $rate = (tep_not_null($currency_value)) ? $currency_value : $this->currencies[$currency_type]['value'];
            $seoPrice = number_format(round($number * $rate, $this->currencies[$currency_type]['decimal_places']), 2, '.', '');
            $format_string .= ($microdata ? '<span itemprop="price" content="' . $seoPrice . '">' : '') .
                    number_format(round($number * $rate, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) .
                    ($microdata ? '</span>' : '');
// if the selected currency is in the european euro-conversion and the default currency is euro,
// the currency will displayed in the national currency and euro currency
            if ((DEFAULT_CURRENCY == 'EUR') && ($currency_type == 'DEM' || $currency_type == 'BEF' || $currency_type == 'LUF' || $currency_type == 'ESP' || $currency_type == 'FRF' || $currency_type == 'IEP' || $currency_type == 'ITL' || $currency_type == 'NLG' || $currency_type == 'ATS' || $currency_type == 'PTE' || $currency_type == 'FIM' || $currency_type == 'GRD')) {
                $format_string .= ' <small>[' . $this->format($number, true, 'EUR') . ']</small>';
            }

            if ($metaTags) {
                \Yii::$app->getView()->registerMetaTag([
                    'property' => 'product:price:amount',
                    'content' => $seoPrice
                        ], 'product:price:amount');
                \Yii::$app->getView()->registerMetaTag([
                    'property' => 'product:price:currency',
                    'content' => $currency_type
                        ], 'product:price:currency');
            }
        } else {
            $format_string .= number_format(round($number, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']);
        }

        if ($this->currencies[$currency_type]['symbol_right']) {
            if ($microdata) {
                $format_string .= '<span itemprop="priceCurrency" content="' . $currency_type . '">' . $this->currencies[$currency_type]['symbol_right'] . '</span>';
            } else {
                $format_string .= $this->currencies[$currency_type]['symbol_right'];
            }
        }
        
        if ($number < 0){
            $format_string = "-" . preg_replace("/\-/", "", $format_string);
        }

// BOF: WebMakers.com Added: Down for Maintenance
        if (DOWN_FOR_MAINTENANCE == 'true' && DOWN_FOR_MAINTENANCE_PRICES_OFF == 'true') {
            $format_string = '';
        }
// BOF: WebMakers.com Added: Down for Maintenance

        if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'checkPriceIsHidden')) {
            $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
            if ($ext::checkPriceIsHidden($customer_groups_id)) {
              $checkout = (in_array(\Yii::$app->controller->id, ['checkout', 'sample-checkout', 'quote-checkout'])  && \Yii::$app->controller->action->id == 'process') ||
                  (\Yii::$app->controller->id=='callback');
              if (!$checkout) {
                $format_string = '&nbsp;';
              }
            }
        }

        return $format_string;
    }

    function format_clear($number, $calculate_currency_value = true, $currency_type = '', $currency_value = '', $unclear = false) {
        $currency = \Yii::$app->settings->get('currency');

        if (\frontend\design\Info::isTotallyAdmin() && empty($currency)) {
            $currency = DEFAULT_CURRENCY;
            \Yii::$app->settings->set('currency', $currency);
        }

        if (empty($currency_type)) {
            $currency_type = $currency;
        }

        if ($calculate_currency_value == true && USE_MARKET_PRICES != 'True') {
            $rate = (tep_not_null($currency_value)) ? $currency_value : $this->currencies[$currency_type]['value'];
            $format_string = number_format(round(($unclear ? ($number / $rate) : ($number * $rate)), $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], '.', '');
        } else {
            $format_string = number_format(round($number, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], '.', '');
        }
        if (DOWN_FOR_MAINTENANCE == 'true' && DOWN_FOR_MAINTENANCE_PRICES_OFF == 'true') {
            $format_string = '';
        }
        /*
        if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'checkPriceIsHidden')) {
            $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
            if ($ext::checkPriceIsHidden($customer_groups_id)) {
              $format_string = '';
            }
        }*/

        return $format_string;
    }

    function formatById($number, $calculate_currency_value = true, $currency_id = '', $currency_value = '', $microdata = false) {
        $currency = \Yii::$app->settings->get('currency');

        if (\frontend\design\Info::isTotallyAdmin() && empty($currency)) {
            $currency = DEFAULT_CURRENCY;
            \Yii::$app->settings->set('currency', $currency);
        }

        if (!empty($currency_id) && isset($this->currency_codes[$currency_id])) {
            $currency_type = $this->currency_codes[$currency_id];
        } else {
            $currency_type = $currency;
        }

        $format_string = '';

        if ($this->currencies[$currency_type]['symbol_left']) {
            if ($microdata) {
                $format_string .= '<span itemprop="priceCurrency" content="' . $currency_type . '">' . $this->currencies[$currency_type]['symbol_left'] . '</span>';
            } else {
                $format_string .= $this->currencies[$currency_type]['symbol_left'];
            }
        }

        if ($calculate_currency_value == true && USE_MARKET_PRICES != 'True') {
            $rate = (tep_not_null($currency_value)) ? $currency_value : $this->currencies[$currency_type]['value'];
            $format_string .= ($microdata ? '<span itemprop="price" content="' . number_format(round($number * $rate, $this->currencies[$currency_type]['decimal_places']), 2, '.', '') . '">' : '') .
                    number_format(round($number * $rate, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) .
                    ($microdata ? '</span>' : '');
            if ((DEFAULT_CURRENCY == 'EUR') && ($currency_type == 'DEM' || $currency_type == 'BEF' || $currency_type == 'LUF' || $currency_type == 'ESP' || $currency_type == 'FRF' || $currency_type == 'IEP' || $currency_type == 'ITL' || $currency_type == 'NLG' || $currency_type == 'ATS' || $currency_type == 'PTE' || $currency_type == 'FIM' || $currency_type == 'GRD')) {
                $format_string .= ' <small>[' . $this->format($number, true, 'EUR') . ']</small>';
            }
        } else {
            $format_string .= number_format(round($number, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']);
        }

        if ($this->currencies[$currency_type]['symbol_right']) {
            if ($microdata) {
                $format_string .= '<span itemprop="priceCurrency" content="' . $currency_type . '">' . $this->currencies[$currency_type]['symbol_right'] . '</span>';
            } else {
                $format_string .= $this->currencies[$currency_type]['symbol_right'];
            }
        }

        if (DOWN_FOR_MAINTENANCE == 'true' && DOWN_FOR_MAINTENANCE_PRICES_OFF == 'true') {
            $format_string = '';
        }

        if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'checkPriceIsHidden')) {
            $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
            if ($ext::checkPriceIsHidden($customer_groups_id)) {
              $format_string = '';
            }
        }

        return $format_string;
    }

    function is_set($code) {
        if (isset($this->currencies[$code]) && tep_not_null($this->currencies[$code])) {
            return true;
        } else {
            return false;
        }
    }

    function get_value($code) {
        return $this->currencies[$code]['value'];
    }

    function get_decimal_places($code) {
        return $this->currencies[$code]['decimal_places'];
    }

    function get_decimal_places_by_id($id) {
        if (isset($this->currency_codes[$id])) {
            $code = $this->currency_codes[$id];
        } else {
            $code = DEFAULT_CURRENCY;
        }
        return $this->currencies[$code]['decimal_places'];
    }

    function calculate_price_in_order($order_info, $products_price, $products_tax = 0, $quantity = 1) {
        if (\common\helpers\Currencies::currency_exists($order_info['currency'])) {
            if (defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True') {
                $products_tax = 0;
            }
            if ($order_info['products_price_qty_round']) {
                return round(\common\helpers\Tax::add_tax_always($products_price, $products_tax), $this->currencies[$order_info['currency']]['decimal_places']) * $quantity;
            } else {
                return round(\common\helpers\Tax::add_tax_always($products_price, $products_tax) * $quantity, $this->currencies[$order_info['currency']]['decimal_places']);
            }
        }
    }

/**
 *
 * @param float $products_price ex 9.99
 * @param float $products_tax 19.5 (usually <100)
 * @param int $quantity default 1
 * @param string $currency
 * @return number
 */
    function calculate_price($products_price, $products_tax, $quantity = 1, $currency = '') {
        if (empty($currency)) {
            $currency = \Yii::$app->settings->get('currency');
        }

        if (\frontend\design\Info::isTotallyAdmin() && empty($currency)) {
            $currency = DEFAULT_CURRENCY;
            \Yii::$app->settings->set('currency', $currency);
        }

        if (PRODUCTS_PRICE_QTY_ROUND == 'true') {
            return round(\common\helpers\Tax::add_tax($products_price, $products_tax), $this->currencies[$currency]['decimal_places']) * $quantity;
        } else {
            return round(\common\helpers\Tax::add_tax($products_price, $products_tax) * $quantity, $this->currencies[$currency]['decimal_places']);
        }
    }

/**
 * Display nothing or price with/without tax (format(calculate_price) )
 * @param decimal|bool $products_price ex 9.99
 * @param float  $products_tax 19.5 (usually <100)
 * @param int $quantity default 1
 * @param bool $microdata add microdata <span itemprop="priceCurrency|price">  default true
 * @param bool $metaTags register meta microdata  default false
 * @return string formatted price
 */
    function display_price($products_price, $products_tax, $quantity = 1, $microdata = true, $metaTags = false) {
        if ($products_price === false) {
            return '';
        } else {
            if (\Yii::$app->storage->has('taxable')){
                return (\Yii::$app->storage->get('taxable') 
                        ?$this->format($this->calculate_price($products_price, $products_tax, $quantity), true, '', '', $microdata, $metaTags)
                        :$this->format($this->calculate_price($products_price, 0, $quantity), true, '', '', $microdata, $metaTags)
                        );
            }
            return $this->format($this->calculate_price($products_price, $products_tax, $quantity), true, '', '', $microdata, $metaTags);
        }
    }
    
/**
 *
 * @param type $products_price
 * @param type $products_tax
 * @param type $quantity
 * @return string
 */
    function display_price_clear($products_price, $products_tax, $quantity = 1) {
        if ($products_price === false) {
            return '';
        } else {
            if (\Yii::$app->storage->has('taxable')){
                return (\Yii::$app->storage->get('taxable') 
                        ?$this->format_clear($this->calculate_price($products_price, $products_tax, $quantity))
                        :$this->format_clear($this->calculate_price($products_price, 0, $quantity))
                        );
            }
            return $this->format_clear($this->calculate_price($products_price, $products_tax, $quantity));
        }
    }

    function display_gift_card_price($products_price, $products_tax, $gift_card_currency = '') {
        $currency = \Yii::$app->settings->get('currency');
        if (tep_not_null($gift_card_currency) && \common\helpers\Currencies::currency_exists($gift_card_currency)) {
            $currency = $gift_card_currency;
        }
        $old_decimal_places = $this->currencies[$currency]['decimal_places'];
        $this->currencies[$currency]['decimal_places'] = 0;
        $return = $this->format(\common\helpers\Tax::add_tax($products_price, $products_tax));
        $this->currencies[$currency]['decimal_places'] = $old_decimal_places;
        return $return;
    }

    function get_market_price_rate($from_currency, $to_currency) {
        $div = $this->get_value($from_currency);
        if (!$div)
            $div = 1;
        return $this->get_value($to_currency) / $div;
    }

    public function addCurrencyIcon(float $number, string $currency_type = ''): string
    {
        $currency = \Yii::$app->settings->get('currency');
        if (\frontend\design\Info::isTotallyAdmin() && empty($currency)) {
            $currency = DEFAULT_CURRENCY;
            \Yii::$app->settings->set('currency', $currency);
        }
        if (empty($currency_type)) {
            $currency_type = $currency;
        }
        $format_string = '';
        if ($this->currencies[$currency_type]['symbol_left']) {
            $format_string .= $this->currencies[$currency_type]['symbol_left'];
        }
        $format_string .= $number;
        if ($this->currencies[$currency_type]['symbol_right']) {
            $format_string .= $this->currencies[$currency_type]['symbol_right'];
        }
        return $format_string;
    }

    public function rate($currency_type = '', $currency_value = '') {
        $currency = \Yii::$app->settings->get('currency');

        if (\frontend\design\Info::isTotallyAdmin() && empty($currency)) {
            $currency = DEFAULT_CURRENCY;
            \Yii::$app->settings->set('currency', $currency);
        }

        if (empty($currency_type)) {
            $currency_type = $currency;
        }
        return (tep_not_null($currency_value)) ? $currency_value : $this->currencies[$currency_type]['value'];
    }
}
