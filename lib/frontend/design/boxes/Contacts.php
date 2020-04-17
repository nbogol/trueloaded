<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes;

use frontend\design\Info;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\JsonLd;

class Contacts extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {

        $data = Info::platformData();

        $this->settings[0]['seo_tags'] = false;

        switch ($this->settings[0]['view_item']) {
            case 'phone_number':
                JsonLd::addData(['Organization' => [
                    'telephone' => $data['telephone']
                ]], ['Organization', 'telephone']);
                if ($this->settings[0]['add_link_on_phone']) {
                    $data['telephone'] = '<a href="mailto:' . $data['telephone'] . '">' . $data['telephone'] . '</a>';
                }
                return $data['telephone'];
            case 'email':
                JsonLd::addData(['Organization' => [
                    'email' => $data['email_address']
                ]], ['Organization', 'email']);

                if ($this->settings[0]['use_at_in_email']) {
                    $data['email_address'] = str_replace('@', '(at)', $data['email_address']);
                }
                if ($this->settings[0]['add_link_on_email']) {
                    $data['email_address'] = '<a href="mailto:' . $data['email_address'] . '">' . $data['email_address'] . '</a>';
                }
                return $data['email_address'];
            case 'name':
                JsonLd::addData(['Organization' => [
                    'name' => $data['company']
                ]], ['Organization', 'name']);
                return $data['company'];
            case 'address':

                if (!$this->settings[0]['address_spacer']){
                    $this->settings[0]['address_spacer'] = '<br>';
                }

                $address_format_id = \common\helpers\Address::get_address_format_id($data['country_id']);
                $addressFormat = \common\models\AddressFormat::findOne($address_format_id);
                $addressFormatArr = json_decode($addressFormat->address_format);
                $addressFormatArrFlat = [];
                foreach ($addressFormatArr as $row) {
                    $addressFormatArrFlat = array_merge($addressFormatArrFlat, $row);
                }

                $ldAddress['@type'] = 'PostalAddress';
                if ($data['street_address'] && in_array('street_address', $addressFormatArrFlat)) {
                    $ldAddress['streetAddress'] = $data['street_address'];
                }
                if ($data['city'] && in_array('city', $addressFormatArrFlat)) {
                    $ldAddress['addressLocality'] = $data['city'];
                }
                if ($data['state'] && in_array('state', $addressFormatArrFlat)) {
                    $ldAddress['addressRegion'] = $data['state'];
                }
                if ($data['postcode'] && in_array('postcode', $addressFormatArrFlat)) {
                    $ldAddress['postalCode'] = $data['postcode'];
                }
                if ($data['country'] && in_array('country', $addressFormatArrFlat)) {
                    $ldAddress['addressCountry'] = $data['country'];
                }
                if ($data['suburb'] && in_array('suburb', $addressFormatArrFlat)) {
                    $ldAddress['addressLocality'] = $data['suburb'];
                }

                JsonLd::addData(['Organization' => [
                    'address' => $ldAddress
                ]], ['Organization', 'address']);

                if ($data['company_vat'] && in_array('company_vat', $addressFormatArrFlat)) {
                    JsonLd::addData(['Organization' => [
                        'vatID' => $data['entry_company_vat']
                    ]], ['Organization', 'vatID']);
                }

                if ($this->settings[0]['use_at_in_email']) {
                    $data['email_address'] = str_replace('@', '(at)', $data['email_address']);
                }
                if ($this->settings[0]['add_link_on_email']) {
                    $data['email_address'] = '<a href="mailto:' . $data['email_address'] . '">' . $data['email_address'] . '</a>';
                }
                if ($this->settings[0]['add_link_on_phone']) {
                    $data['telephone'] = '<a href="mailto:' . $data['telephone'] . '">' . $data['telephone'] . '</a>';
                }

                $address = $data;
                $address['name'] = '';
                $address['reg_number'] = '';

                return \common\helpers\Address::address_format(
                    \common\helpers\Address::get_address_format_id($data['country_id']),
                    $address,
                    0,
                    ' ',
                    $this->settings[0]['address_spacer'],
                    true);
            case 'company_no':
                JsonLd::addData(['Organization' => [
                    'leiCode' => $data['reg_number']
                ]], ['Organization', 'leiCode']);
                return $data['reg_number'];
            case 'company_vat_id':
                JsonLd::addData(['Organization' => [
                    'vatID' => $data['entry_company_vat']
                ]], ['Organization', 'vatID']);
                return $data['entry_company_vat'];
            case 'opening_hours':
                return self::openingHours($data, $this->settings[0]['time_format']);
            case 'data_format':
                $content = $this->settings[0]['data_format_content'];
                if ($content) {
                    if ($this->settings[0]['seo_tags'] && strpos($content, '##POST_ADDRESS##') === false) {

                        //find position where address start and finish
                        $firstGlobal = 1000000;
                        $lastGlobal = 0;

                        $first = strpos($content, '##POSTCODE##');
                        if ($first !== false) {
                            if ($first < $firstGlobal) $firstGlobal = $first;
                            $last = $first + 12;
                            if ($last > $lastGlobal) $lastGlobal = $last;
                        }
                        $first = strpos($content, '##STREET_ADDRESS##');
                        if ($first !== false) {
                            if ($first < $firstGlobal) $firstGlobal = $first;
                            $last = $first + 18;
                            if ($last > $lastGlobal) $lastGlobal = $last;
                        }
                        $first = strpos($content, '##SUBURB##');
                        if ($first !== false) {
                            if ($first < $firstGlobal) $firstGlobal = $first;
                            $last = $first + 10;
                            if ($last > $lastGlobal) $lastGlobal = $last;
                        }
                        $first = strpos($content, '##CITY##');
                        if ($first !== false) {
                            if ($first < $firstGlobal) $firstGlobal = $first;
                            $last = $first + 8;
                            if ($last > $lastGlobal) $lastGlobal = $last;
                        }
                        $first = strpos($content, '##STATE##');
                        if ($first !== false) {
                            if ($first < $firstGlobal) $firstGlobal = $first;
                            $last = $first + 9;
                            if ($last > $lastGlobal) $lastGlobal = $last;
                        }
                        $first = strpos($content, '##COUNTRY##');
                        if ($first !== false) {
                            if ($first < $firstGlobal) $firstGlobal = $first;
                            $last = $first + 11;
                            if ($last > $lastGlobal) $lastGlobal = $last;
                        }

                        if ($firstGlobal < $lastGlobal) {
                            $len = $lastGlobal - $firstGlobal;
                            $content = substr($content, 0, $firstGlobal)
                                //. '<address itemprop="address" itemscope="" itemtype="http://schema.org/PostalAddress">'
                                . '<address>'
                                . substr($content, $firstGlobal, $len)
                                . '</address>'
                                . substr($content, $lastGlobal);
                        }
                    }

                    if ($this->settings[0]['use_at_in_email']) {
                        $data['email_address'] = str_replace('@', '(at)', $data['email_address']);
                    }
                    if ($this->settings[0]['add_link_on_email']) {
                        $data['email_address'] = '<a href="mailto:' . $data['email_address'] . '">' . $data['email_address'] . '</a>';
                    }
                    if ($this->settings[0]['add_link_on_phone']) {
                        $data['telephone'] = '<a href="mailto:' . $data['telephone'] . '">' . $data['telephone'] . '</a>';
                    }

                    $content = str_replace('##OWNER##', self::data('owner', $data, $this->settings[0]['seo_tags']), $content);
                    $content = str_replace('##TITLE##', self::data('title', $data, $this->settings[0]['seo_tags']), $content);
                    $content = str_replace('##EMAIL_ADDRESS##', self::data('email_address', $data, $this->settings[0]['seo_tags']), $content);
                    $content = str_replace('##EMAIL_EXTRA##', self::data('email_extra', $data, $this->settings[0]['seo_tags']), $content);
                    $content = str_replace('##TELEPHONE##', self::data('telephone', $data, $this->settings[0]['seo_tags']), $content);
                    $content = str_replace('##LANDLINE##', self::data('landline', $data, $this->settings[0]['seo_tags']), $content);
                    $content = str_replace('##COMPANY##', self::data('company', $data, $this->settings[0]['seo_tags']), $content);
                    $content = str_replace('##COMPANY_VAT##', self::data('company_vat', $data, $this->settings[0]['seo_tags']), $content);
                    $content = str_replace('##POSTCODE##', self::data('postcode', $data, $this->settings[0]['seo_tags']), $content);
                    $content = str_replace('##STREET_ADDRESS##', self::data('street_address', $data, $this->settings[0]['seo_tags']), $content);
                    $content = str_replace('##SUBURB##', self::data('suburb', $data, $this->settings[0]['seo_tags']), $content);
                    $content = str_replace('##CITY##', self::data('city', $data, $this->settings[0]['seo_tags']), $content);
                    $content = str_replace('##STATE##', self::data('state', $data, $this->settings[0]['seo_tags']), $content);
                    $content = str_replace('##REG_NUMBER##', self::data('reg_number', $data, $this->settings[0]['seo_tags']), $content);
                    $content = str_replace('##COUNTRY##', self::data('country', $data, $this->settings[0]['seo_tags']), $content);
                    $content = str_replace('##OPEN##', self::openingHours( $data, $this->settings[0]['time_format']), $content);
                    $content = str_replace('##POST_ADDRESS##', self::data('post_address', $data, $this->settings[0]['seo_tags']), $content);

                    $content = preg_replace('/,[, ]+/', ", ", $content);
                    $content = preg_replace('/[\n]+/', "\n", $content);
                    $content = preg_replace('/[ ]+/', " ", $content);
                    $content = preg_replace('/[\n]+,/', ",", $content);
                    $content = preg_replace('/[ ]{0,},[ ]{0,}\n[ ]{0,}[,\n ]+,/', ",\n", $content);
                    $content = preg_replace('/[,]+,/', ",", $content);
                    //$content = preg_replace('/,[, \n]+/', ",\n", $content);
                    $content = str_replace("\n", '<br>', $content);

                    $content = preg_replace_callback("/\#\#([A-Z_]+)\#\#/", "self::translate", $content);

                    if ($this->settings[0]['seo_tags']) {
                        $content = '<span itemscope="" itemtype="http://schema.org/Organization">' . $content . '</span>';
                    }
                }
                return $content;
        }

        return '';

    }

    public static function translate($matches) {
        return constant($matches[1]);
    }

    public static function data($name, $data, $seo)
    {
        $content = '';
        switch ($name) {
            case 'owner':
                if ($seo && $data[$name]) $content .= '<span itemprop="founder">';
                $content .= $data[$name];
                if ($seo && $data[$name]) $content .= '</span>';
                break;
            case 'title':
                if ($seo && $data[$name]) $content .= '<span itemprop="name">';
                $content .= $data[$name];
                if ($seo && $data[$name]) $content .= '</span>';
                break;
            case 'email_address':
                if ($seo && $data[$name]) $content .= '<span itemprop="email">';
                $content .= $data[$name];
                if ($seo && $data[$name])  $content .= '</span>';
                break;
            case 'email_extra':
                $content .= $data[$name];
                break;
            case 'telephone':
                if ($seo && $data[$name]) $content .= '<span itemprop="telephone">';
                $content .= $data[$name];
                if ($seo && $data[$name])  $content .= '</span>';
                break;
            case 'landline':
                $content .= $data[$name];
            case 'company':
                if ($seo && $data[$name]) $content .= '<span itemprop="legalName">';
                $content .= $data[$name];
                if ($seo && $data[$name])  $content .= '</span>';
                break;
            case 'company_vat':
                if ($seo && $data[$name]) $content .= '<span itemprop="vatID">';
                $content .= $data[$name];
                if ($seo && $data[$name])  $content .= '</span>';
                break;
            case 'postcode':
                if ($seo && $data[$name]) $content .= '<span itemprop="postalCode">';
                $content .= $data[$name];
                if ($seo && $data[$name])  $content .= '</span>';
                break;
            case 'street_address':
                if ($seo && $data[$name]) $content .= '<span itemprop="streetAddress">';
                $content .= $data[$name];
                if ($seo && $data[$name])  $content .= '</span>';
                break;
            case 'suburb':
                if ($seo && $data[$name]) $content .= '<span itemprop="addressRegion">';
                $content .= $data[$name];
                if ($seo && $data[$name])  $content .= '</span>';
                break;
            case 'city':
                if ($seo && $data[$name]) $content .= '<span itemprop="addressLocality">';
                $content .= $data[$name];
                if ($seo && $data[$name])  $content .= '</span>';
                break;
            case 'state':
                if ($seo && $data[$name]) $content .= '<span itemprop="addressLocality">';
                $content .= $data[$name];
                if ($seo && $data[$name])  $content .= '</span>';
                break;
            case 'reg_number':
                if ($seo && $data[$name]) $content .= '<span itemprop="leiCode">';
                $content .= $data[$name];
                if ($seo && $data[$name])  $content .= '</span>';
                break;
            case 'country':
                if ($seo && $data[$name]) $content .= '<span itemprop="addressCountry">';
                $content .= $data[$name];
                if ($seo && $data[$name])  $content .= '</span>';
                break;
            case 'post_address':
                if ($seo) $content .= '<address itemprop="address" itemscope="" itemtype="http://schema.org/PostalAddress">';
                $address = $data;
                $address['name'] = '';
                $address['reg_number'] = '';
                $content .= \common\helpers\Address::address_format(
                    \common\helpers\Address::get_address_format_id($data['country_id']),
                    $address,
                    0,
                    ' ',
                    ', ',
                    $seo);
                if ($seo)  $content .= '</address>';
                $content = str_replace("\n", ' ', $content);
                break;
        }
        return $content;

    }

    public static function openingHours($data, $timeFormat)
    {
        if ($timeFormat == '24') {
            foreach ($data['open'] as $key => $item) {
                $data['open'][$key]['time_from'] = date("G:i", strtotime($item['time_from']));
                $data['open'][$key]['time_to'] = date("G:i", strtotime($item['time_to']));
            }
        }
        foreach ($data['open'] as $key => $item) {
            $data['open'][$key]['from'] = date("G:i", strtotime($item['time_from']));
            $data['open'][$key]['to'] = date("G:i", strtotime($item['time_to']));
        }

        $ours = '';
        $jsonOurs = [];
        foreach ($data['open'] as $item){
            if (!$item['days_short']) {
                $item['days_short'] = 'Everyday';
            }
            $ours .= '<span>' . $item['days_short'] . ' (' . $item['time_from'] . '-' . $item['time_to'] . ')</span>';

            $jsonOurs[] = [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => $item['days_arr'],
                'opens' => $item['from'],
                'closes' => $item['to'],
            ];
        }

        JsonLd::addData(['Organization' => [
            'openingHoursSpecification' => $jsonOurs
        ]], ['Organization', 'openingHoursSpecification']);

        return $ours;
    }
}