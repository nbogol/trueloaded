<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
namespace common\modules\orderShipping;

use common\classes\modules\ModuleShipping;
use common\classes\modules\ModuleStatus;
use common\classes\modules\ModuleSortOrder;

class percent extends ModuleShipping{
    var $code, $title, $description, $icon, $enabled;

    protected $defaultTranslationArray = [
        'MODULE_SHIPPING_PERCENT_TEXT_TITLE' => 'Shipping',
        'MODULE_SHIPPING_PERCENT_TEXT_DESCRIPTION' => 'Percent Rate',
        'MODULE_SHIPPING_PERCENT_TEXT_WAY' => 'Total'
    ];

// class constructor
    function __construct() {
        parent::__construct();

        $this->code = 'percent';
        $this->title = MODULE_SHIPPING_PERCENT_TEXT_TITLE;
        $this->description = MODULE_SHIPPING_PERCENT_TEXT_DESCRIPTION;
        if (!defined('MODULE_SHIPPING_PERCENT_STATUS')) {
            $this->enabled = false;
            return false;
        }
        $this->sort_order = MODULE_SHIPPING_PERCENT_SORT_ORDER;
        $this->icon = '';
        $this->tax_class = MODULE_SHIPPING_PERCENT_TAX_CLASS;
        $this->enabled = ((MODULE_SHIPPING_PERCENT_STATUS == 'True') ? true : false);

      if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_PERCENT_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_PERCENT_ZONE . "' and zone_country_id = '" . $this->delivery['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $this->delivery['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

// class methods
    function quote($method = '') {
      $cart = $this->manager->getCart();

	  if (MODULE_SHIPPING_PERCENT_STATUS == 'True') {
        $order_total = $cart->show_total();
      }
	  if ($order_total >= MODULE_SHIPPING_PERCENT_LESS_THEN) {
      $shipping_percent = $order_total * MODULE_SHIPPING_PERCENT_RATE;
	  }
	  else {
	  $shipping_percent = MODULE_SHIPPING_PERCENT_FLAT_USE;
	  }

      $this->quotes = array('id' => $this->code,
                            'module' => MODULE_SHIPPING_PERCENT_TEXT_TITLE,
                            'methods' => array(array('id' => $this->code,
                                                     'title' => MODULE_SHIPPING_PERCENT_TEXT_WAY,
                                                     'cost' => $shipping_percent)));

      if ($this->tax_class > 0) {
        $this->quotes['tax'] = \common\helpers\Tax::get_tax_rate($this->tax_class, $this->delivery['country']['id'], $this->delivery['zone_id']);
      }

      if (tep_not_null($this->icon)) $this->quotes['icon'] = tep_image($this->icon, $this->title);

      return $this->quotes;
    }

    public function configure_keys()
    {
      return array (
        'MODULE_SHIPPING_PERCENT_STATUS' =>
          array (
            'title' => 'Enable Percent Shipping',
            'value' => 'True',
            'description' => 'Do you want to offer percent rate shipping?',
            'sort_order' => '0',
            'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
          ),
        'MODULE_SHIPPING_PERCENT_RATE' =>
          array (
            'title' => 'Percentage Rate',
            'value' => '.18',
            'description' => 'The Percentage Rate all .01 to .99 for all orders using this shipping method.',
            'sort_order' => '0',
          ),
        'MODULE_SHIPPING_PERCENT_LESS_THEN' =>
          array (
            'title' => 'Percentage A Flat Rate for orders under',
            'value' => '34.75',
            'description' => 'A Flat Rate for all orders that are under the amount shown.',
            'sort_order' => '0',
          ),
        'MODULE_SHIPPING_PERCENT_FLAT_USE' =>
          array (
            'title' => 'Percentage A Flat Rate of',
            'value' => '6.50',
            'description' => 'A Flat Rate used for all orders.',
            'sort_order' => '0',
          ),
        'MODULE_SHIPPING_PERCENT_TAX_CLASS' =>
          array (
            'title' => 'Percentage Tax Class',
            'value' => '0',
            'description' => 'Use the following tax class on the shipping fee.',
            'sort_order' => '0',
            'use_function' => '\\common\\helpers\\Tax::get_tax_class_title',
            'set_function' => 'tep_cfg_pull_down_tax_classes(',
          ),
        'MODULE_SHIPPING_PERCENT_ZONE' =>
          array (
            'title' => 'Percentage Shipping Zone',
            'value' => '0',
            'description' => 'If a zone is selected, only enable this shipping method for that zone.',
            'sort_order' => '0',
            'use_function' => '\\common\\helpers\\Zones::get_zone_class_title',
            'set_function' => 'tep_cfg_pull_down_zone_classes(',
          ),
        'MODULE_SHIPPING_PERCENT_SORT_ORDER' =>
          array (
            'title' => 'Percentage Sort Order',
            'value' => '0',
            'description' => 'Sort order of display.',
            'sort_order' => '0',
          ),
      );
    }

    public function describe_status_key()
    {
      return new ModuleStatus('MODULE_SHIPPING_PERCENT_STATUS','True','False');
    }

    public function describe_sort_key()
    {
      return new ModuleSortOrder('MODULE_SHIPPING_PERCENT_SORT_ORDER');
    }

  }
