<?php

/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * Price for all conditions
 */

namespace common\models\Product;

use Yii;
use common\helpers\Tax;
use common\helpers\Product;
use common\classes\platform;
use common\helpers\Customer;
use common\helpers\Inventory as InventoryHelper;

class BundlePrice extends Price {

    use \common\helpers\SqlTrait;

    private static $instanses = [];
    private static $vars = [];

    /*
     * [urpid] => [
     *          'bundle_price' => [
     *               'value' => 0,
     *               'vars' => [] - settings for caluclation
     *           ],
     *        ]
     */

    private function __construct() {
        
    }

    public static function getInstance($uprid) {
        if (!isset(self::$instanses[$uprid])) {
            self::$instanses[$uprid] = new self();
            self::$instanses[$uprid]->uprid = $uprid;
            self::$instanses[$uprid]->bundle_price = [
                'value' => null,
                'vars' => []
            ];
            $_uprid = \common\helpers\Inventory::normalize_id($uprid);
            self::$instanses[$uprid]->relative = parent::getInstance($_uprid);
        }        
        return self::$instanses[$uprid];
    }

    public function getBundlePrice($params) {

        $this->setParams($params);

        if (isset($this->bundle_price['vars']) && $this->bundle_price['vars'] == $params) {
            return $this->bundle_price['value'];
        } else {
            $this->bundle_price['vars'] = $params;
        }

        $this->bundle_price['value'] = $this->relative->getInventoryPrice($params);

        $check_parent = \common\models\Products::find()->select('use_sets_discount, products_sets_discount')->where('products_id=:products_id', [':products_id' => (int)\common\helpers\Inventory::get_prid($params['parent'])])->asArray()->one();
        $check_product = \common\models\SetsProducts::find()->select('discount')->where('sets_id=:sets_id and product_id=:product_id', [':sets_id' => (int)\common\helpers\Inventory::get_prid($params['parent']), ':product_id' => (int)\common\helpers\Inventory::get_prid($this->uprid)])->asArray()->one();
        if ($check_parent['use_sets_discount']) {
            if ($this->relative->dSettings->applyBundleDiscount()) {
                $this->bundle_price['value'] *= (1 - (($check_parent['products_sets_discount'] + $check_product['discount']) / 100));
            }
        } else {
            // Attributes price only
            $this->bundle_price['value'] -= (float)$this->relative->getProductPrice($params);
        }

        return $this->bundle_price['value'];
    }

    public function getBundleSpecialPrice($params) {

        $this->setParams($params);

        if (is_null($this->bundle_special_price['value'])) {
            $this->bundle_special_price = [
                'value' => false,
                'vars' => []
            ];
        }

        if (isset($this->bundle_special_price['vars']) && $this->bundle_special_price['vars'] == $params) {
            if ($this->bundle_special_price['value']) {
                $this->attachToProduct(['promo_class' => 'sale']);
            }
            return $this->bundle_special_price['value'];
        } else {
            $this->bundle_special_price['vars'] = $params;
        }

        if (!is_object($this->parent)){
            $this->parent = parent::getInstance((int)\common\helpers\Inventory::get_prid($params['parent']));
        }
        
        if ($this->parent && !$this->parent->dSettings->applyBundleDiscount()){
            return false;
        } else {
            $this->bundle_special_price['value'] = $this->relative->getInventorySpecialPrice($params);

            if ($this->bundle_special_price['value']) {
                $check_parent = \common\models\Products::find()->select('use_sets_discount, products_sets_discount')->where('products_id=:products_id', [':products_id' => (int)\common\helpers\Inventory::get_prid($params['parent'])])->asArray()->one();
                $check_product = \common\models\SetsProducts::find()->select('discount')->where('sets_id=:sets_id and product_id=:product_id', [':sets_id' => (int)\common\helpers\Inventory::get_prid($params['parent']), ':product_id' => (int)\common\helpers\Inventory::get_prid($this->uprid)])->asArray()->one();
                if ($check_parent['use_sets_discount']) {
                   if ($this->relative->dSettings->applyBundleDiscount()) {
                        $this->bundle_special_price['value'] *= (1 - (($check_parent['products_sets_discount'] + $check_product['discount']) / 100));
                   }
                } else {
                    // Attributes price only
                    $base_special_price = $this->relative->getProductSpecialPrice($params);
                    if ($base_special_price) {
                        $this->bundle_special_price['value'] -= (float)$base_special_price;
                    } else {
                        $this->bundle_special_price['value'] -= (float)$this->relative->getProductPrice($params);
                    }
                }
            }
        }
        return $this->bundle_special_price['value'];
    }

}
