<?php

/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\services;

use Yii;
use common\helpers\Acl;
use common\helpers\Tax;
use common\helpers\Product;
use common\helpers\Inventory;

class ProductInsulatorService {

    public $data = [];
    public $manager;
    public $uprid;
    public $result = [];
    private $product;
    public $edit = false;

    public function __construct($uprid, $manager) {
        $this->uprid = $uprid;
        if (!$this->uprid)
            throw new \Exception('Products id is not defined');
        $this->product = \common\models\Products::find()->alias('p')->where(['p.products_id' => (int) $this->uprid])
                ->joinWith(['productsDescriptions pd' => function($query) use ($manager) {
                        $query->onCondition(['pd.language_id' => $manager->get('languages_id'), 'pd.platform_id' => $manager->getPlatformId()]);
                    },
                ])
                ->joinWith(['productsDescriptions pd1' => function($query) use ($manager) {
                        $query->onCondition(['pd1.language_id' => $manager->get('languages_id'), 'pd1.platform_id' => \common\classes\platform::defaultId()]);
                    }])
                ->select(['p.*', 'pd.*', 'if(length(pd.products_name), pd.products_name, pd1.products_name) as products_name'])
                ->one();
        $this->setManager($manager);
    }

    public function getProduct() {
        return $this->product;
    }

    public function setData($post = []) {
        $this->data = $post;
    }

    public function setManager($manager) {
        $this->manager = $manager;
    }

    public function getWorkingProduct() {
        if ($this->edit) {
            $product = array_shift($this->manager->getCart()->get_products($this->uprid)); //details from basket            
        } else {
            $product = $this->product->getAttributes();
            $product['qty'] = 1;
            $product['units'] = 0;
            $product['packs'] = 0;
            $product['packagings'] = 0;
        }
        $product['products_name'] = $this->product->productsDescriptions[0]->getBackendListingName();
        return $product;
    }

    public function getProductMainDetails() {

        $product = $this->getWorkingProduct();

        $currencies = Yii::$container->get('currencies');
        $product['is_bundle'] = $this->product->is_bundle;
        $product['products_id'] = $this->uprid;
        $product['image'] = \common\classes\Images::getImage((int) $this->uprid, 'Small');
        $product['image_thumb'] = \common\classes\Images::getImage((int) $this->uprid);
        //check giveaway
        $product['ga'] = \common\helpers\Gifts::getGiveAways($this->uprid);
        $product['gift_wrap_allowed'] = \common\helpers\Gifts::allow_gift_wrap($this->uprid);
        if ($product['gift_wrap_allowed']) {
            $product['gift_wrap_price'] = \common\helpers\Gifts::get_gift_wrap_price($this->uprid) * $currencies->get_market_price_rate(DEFAULT_CURRENCY, $this->manager->get('currency'));
        } else {
            $product['gift_wrap_price'] = 0;
        }
        if ($ext = Acl::checkExtension('PackUnits', 'quantityBoxFrontend')) {
            $product['product_details'] = $ext::quantityBoxFrontend($product, ['products_id' => $this->uprid]);
            if ($product['is_pack']) {
                $product['pack_unit'] = $product['product_details']['product']['pack_unit'];
                $product['packaging'] = $product['product_details']['product']['packaging'];
            }
        }
        $product['edit'] = $this->edit;

        return $product;
    }

    public function getProductDetails() {
        $cart = $this->manager->getCart();
        $products_id = intval($this->uprid);
        $attributes = $this->data['id'];
        $uprid = $products_id;
        if (is_array($attributes)) {
            $uprid = Inventory::get_uprid($products_id, $attributes);
            $uprid = Inventory::normalize_id($uprid);
        } else {
            if (strpos($this->uprid, '{')) {
                $uprid = Inventory::normalize_id($this->uprid, $attributes);
            } else if (Inventory::product_has_inventory($products_id)) {
                $uprid = Inventory::get_first_invetory($products_id);
                $uprid = Inventory::normalize_id($uprid, $attributes);
            } else if (\common\helpers\Attributes::has_product_attributes($products_id, true)) {
                $attributeM = \common\models\ProductsAttributes::find()->where(['products_id' => (int) $products_id])
                                ->groupBy(['options_id'])->orderBy('options_values_price')->all();
                if ($attributeM) {
                    $attributes = \yii\helpers\ArrayHelper::map($attributeM, 'options_id', 'options_values_id');
                    $uprid = Inventory::get_uprid($uprid, $attributes);
                    $uprid = Inventory::normalize_id($uprid);
                }
            }
        }

        if (!is_array($attributes))
            $attributes = [];

        $this->result['attributes_box'] = $this->getAttributesDetails($attributes);
        if (isset($this->result['stock_indicator']) && is_array($this->result['stock_indicator'])) {
            $this->result['stock_indicator']['quantity_max'] += $cart->get_reserved_quantity($uprid);
        }
        if ($this->product->is_bundle) {
            $this->result['bundle_box'] = $this->getBundleDetails($attributes);
        }

        $this->result['order_quantity'] = \common\helpers\Product::get_product_order_quantity($products_id);
        $this->result['pakcunit_box'] = $this->getPackDetails();

        if ($this->product->products_pctemplates_id) {
            $this->result['configurator_box'] = $this->getConfiguratorDetails();
        }

        $this->result['dicount_box'] = $this->getDiscountDetails();
        //$this->result['collection_box'] = $this->getCollectionDetails($products_id);

        return $this->getProductDetailsClear();
    }

    protected function getProductDetailsClear() {
        if ($this->result) {
            $currencies = Yii::$container->get('currencies');
            $this->result['product_info'] = [];
            if ($this->result['attributes_box']) { //
                $this->result['product_info']['html_attributes'] = $this->result['attributes_box']['product_attributes_html'];
                //$this->result['product_info']['attributes_array'] = $this->result['attributes_box']['attributes_array'] ?? $this->result['attributes_box']['inventory_array'];
                $this->result['product_info']['product_qty'] = $this->result['attributes_box']['data']['product_qty'];
                $this->result['product_info']['product_valid'] = $this->result['attributes_box']['data']['product_valid'];
                $this->result['product_info']['product_unit_price'] = $this->result['attributes_box']['data']['product_unit_price'];
                $this->result['product_info']['special_unit_price'] = (float) $this->result['attributes_box']['data']['special_unit_price'];
                $this->result['product_info']['stock_indicator'] = $this->result['attributes_box']['data']['stock_indicator'];
            }
            if ($this->product->is_bundle) {
                $this->result['product_info']['html_bundles'] = $this->result['bundle_box']['bundles_block'];
                $this->result['product_info']['product_unit_price'] = $this->result['bundle_box']['bundles']['actual_bundle_price_unit'];
                $this->result['product_info']['special_unit_price'] = (float) $this->result['attributes_box']['data']['special_unit_price'];
                $this->result['product_info']['stock_indicator'] = $this->result['bundle_box']['bundles']['stock_indicator'];
                $this->result['product_info']['product_valid'] = $this->result['bundle_box']['bundles']['product_valid'];
            }
            $this->result['product_info']['order_quantity_minimal'] = $this->result['order_quantity']['order_quantity_minimal'];
            $this->result['product_info']['order_quantity_max'] = $this->result['order_quantity']['order_quantity_max'];
            $this->result['product_info']['order_quantity_step'] = $this->result['order_quantity']['order_quantity_step'];
            if ($this->result['pakcunit_box']) {
                if ($this->result['pakcunit_box']['product_details']) {
                    $this->result['product_info']['cartoon_details'] = $this->result['pakcunit_box']['product_details'];
                    $this->result['product_info']['product_unit_price'] = $this->result['pakcunit_box']['product_details']['single_price_data']['single_price_base'];
                    $this->result['product_info']['special_unit_price'] = (float) $this->result['pakcunit_box']['product_details']['special_unit_price'];
                }
            }
            if ($this->product->products_pctemplates_id) {
                if ($this->result['configurator_box']['data']['configurator_elements']) {
                    $this->result['product_info']['html_configurator'] = $this->result['configurator_box']['product_configurator_html'];
                    $this->result['product_info']['configurator_price'] = $this->result['configurator_box']['data']['configurator_price'];
                    $this->result['product_info']['configurator_price_unit'] = $this->result['configurator_box']['data']['configurator_price_unit'];
                    //$this->result['product_info']['special_unit_price'] = (float)$this->result['configurator_box']['data']['special_price'];//???
                    $this->result['product_info']['product_valid'] = $this->result['configurator_box']['data']['product_valid'];
                    $this->result['product_info']['stock_indicator'] = $this->result['configurator_box']['data']['stock_indicator'];
                }
            }
            if ($this->result['dicount_box']) {
                $this->result['product_info']['html_discount'] = $this->result['dicount_box']['discount_table_html'];
                $this->result['product_info']['discount_table_data'] = $this->result['dicount_box']['discount_table_data'];
            }
            if (!$this->result['product_info']['stock_indicator']['add_to_cart']){
                $this->result['product_info']['stock_indicator']['quantity_max'] = 0;
            }
        }

        if ($this->edit) {
            $this->result['edit'] = true;
            $cart = $this->manager->getCart();
            if (($final_price = $cart->getOwerwrittenKey($this->uprid, 'final_price')) !== false && $cart->getOwerwrittenKey($this->uprid, 'price_changed')) {
                $this->result['product_info']['final_price'] = $final_price;
            }
            //$product = $this->getWorkingProduct();
        }
        return $this->result;
    }

    public function getAttributesDetails($attributes) {
        $response['data'] = \common\helpers\Attributes::getDetails($this->uprid, $attributes, $this->data);
        $response['product_attributes_html'] = '';
        if ($response['data']['attributes_array']) {
            $response['product_attributes_html'] = $this->manager->render('Attributes', ['attributes' => $response['data']['attributes_array']]);
        }
        return $response;
    }

    public function getPackDetails() {
        $response = [];
        if ($this->product->pack_unit || $this->product->packaging) {
            if ($ext = Acl::checkExtension('PackUnits', 'quantityBoxFrontend')) {
                $params = $this->data;
                $params['isAjax'] = true;
                if (isset($this->data['qty'])) {
                    $params['qty'] = is_array($this->data['qty']) ? $this->data['qty'][0] : $this->data['qty'];
                }
                if (!$params['qty']) {
                    $params['qty'] = 1;
                }
                $response['product_details'] = $ext::quantityBoxFrontend($params, ['products_id' => $this->uprid]);
                $data = $ext::getPricePack($this->uprid, true, $params);
                $response['product_details']['single_price_data'] = $data;
            }
        }
        return $response;
    }

    public function getBundleDetails($attributes) {
        $bundles = \common\helpers\Bundles::getDetails(['products_id' => $this->uprid, 'id' => $attributes]);
        $response['bundles_block'] = '';
        $response['bundles'] = array();
        if ($bundles) {
            $response['bundles'] = $bundles;
            $response['bundles_block'] = $this->manager->render('Bundle', ['products' => $bundles, 'manager' => $this->manager]);
        }
        return $response;
    }

    public function getDiscountDetails() {
        $response = $discounts = array();
        $dTable = \common\helpers\Product::get_products_discount_table($this->uprid, 0, $this->manager->get('customer_groups_id'));
        if ($dTable && is_array($dTable) && count($dTable)) {
            $discounts[] = array(
                'count' => 1,
                'price' => \common\helpers\Product::get_products_price($this->uprid),
            );
            for ($i = 0, $n = sizeof($dTable); $i < $n; $i = $i + 2) {
                if ($dTable[$i] > 0) {
                    $discounts[] = array(
                        'count' => $dTable[$i],
                        'price' => $dTable[$i + 1],
                    );
                }
            }
            $response['discount_table_data'] = $discounts;
            $response['discount_table_html'] = $this->manager->render('QuantityDiscounts', ['discounts' => $discounts]);
        }

        return $response;
    }

    public function getConfiguratorDetails() {
        $response['data'] = \common\helpers\Configurator::getDetails($this->data, $this->result['attributes_box']['data']);
        $response['product_configurator_html'] = '';
        if ($response['data']['configurator_elements']) {
            if ($this->edit) {
                $cart = $this->manager->getCart();
                $sproducts = $cart->get_subproducts($this->uprid);
                if (is_array($sproducts)) {
                    foreach ($sproducts as $sproduct) {
                        foreach ($response['data']['configurator_elements'] as &$el) {
                            if (strpos($sproduct, $el['selected_uprid']) !== false && $cart->existOwerwritten($sproduct)) {
                                $el['overwritten'] = $cart->getOwerwritten($sproduct);
                            }
                        }
                    }
                }
            }
            $response['product_configurator_html'] = $this->manager->render('Configurator', ['elements' => $response['data']['configurator_elements'], 'pctemplates_id' => $response['data']['pctemplates_id'], 'manager' => $this->manager]);
        }
        return $response;
    }

    public function getCollectionDetails($products_id) {
        $this->data['products_id'] = $products_id;
        $response['product_collection_html'] = '';
        $response['data'] = \common\helpers\Collections::getDetails($this->data);
        if ($response['data']) {
            $response['product_collection_html'] = $this->manager->render('Collection', ['collection' => $response['data'], 'manager' => $this->manager]);
        }
    }

    public function addProduct() {
        $cart = $this->manager->getCart();
        $_qty = (int) (is_array($this->data['qty']) ? array_sum($this->data['qty']) : $this->data['qty']);
        $_uprid = Inventory::get_uprid($this->uprid, $this->data['id']);
        $_uprid = Inventory::normalize_id($_uprid);
        $reserved_qty = $cart->get_reserved_quantity($_uprid); //+$_qty;
        if (defined('STOCK_CHECK') && STOCK_CHECK == 'true') {
            $product_qty = \common\helpers\Product::get_products_stock($_uprid);
            $stock_indicator = \common\classes\StockIndication::product_info(array(
                        'products_id' => $_uprid,
                        'products_quantity' => $product_qty,
            ));

            if ($_qty > $reserved_qty) {
                if ($_qty > $product_qty && !$stock_indicator['allow_out_of_stock_add_to_cart']) {
                    $_qty = $product_qty;
                }

                if ($_qty < 1) {
                    $messageStack = Yii::$container->get('message_stack');
                    $pDesc = \common\models\ProductsDescription::find()
                            ->select('products_name')->where(['products_id' => intval($this->uprid), 'language_id' => $this->manager->get('language_id'), 'platform_id' => $this->manager->getPlatformId()])
                            ->one();
                    $messageStack->add_session($pDesc->products_name . " has not enought quantity", 'edit_order');
                    return false;
                }
            }
            if ($_qty < 1)
                return false;
        }

        if (is_array($this->data['qty_'])) {
            $packQty = [
                'qty' => $_qty,
                'unit' => (int) $this->data['qty_'][0],
                'pack_unit' => (int) $this->data['qty_'][1],
                'packaging' => (int) $this->data['qty_'][2],
            ];
        } else {
            $packQty = $_qty;
        }
        $added = null;
        if (is_array($this->data['collections']) && count($this->data['collections']) > 1) {
            foreach ($this->data['collections'] as $products_id) {
                if ($products_id > 0) {
                    if ($this->data['collections_qty'][$products_id] > 0) {
                        $qty = (int) $this->data['collections_qty'][$products_id];
                    } else {
                        $qty = 1;
                    }
                    $added = $cart->add_cart((int) $products_id, $qty, $this->data['collections_attr'][$products_id]);
                }
            }
        } else {
            if (is_array($this->data['elements'])) {
                $added = $cart->add_configuration($this->data, true, 'add');
            } elseif (is_array($this->data['custom_bundles'])) {
                $added = $cart->add_custom_bundle(true, 'add');
            } else {
                $added = $cart->add_cart(Inventory::get_prid($this->uprid), $packQty, $this->data['id'], false, 0, $this->data['gift_wrap']);
            }
        }
        if (!is_null($added)) {
            if (!is_array($added))
                $added = [$added];
        }
        //collect manual changes    
        $newAdded = null;
        if (is_array($added)) {
            foreach ($added as $key => $_added) {
                if ($_added) {
                    if ($key == 0) { //only for main product
                        $newAdded = $_added;
                        $this->setPrice($_added);
                        $this->setName($_added);
                    }
                    $this->setProductTax($_added);
                }
            }
        }
        $this->clearModifiedProducts($newAdded);

        return $_added;
    }

    private function clearModifiedProducts($newAdded) {
        if ($this->edit) {
            if (!is_null($newAdded) && $newAdded != $this->uprid) {
                $cart = $this->manager->getCart();
                $cart->remove($this->uprid);
            }
        }
    }

    public function addGiveAway($gaw_id = null) {
        $cart = $this->manager->getCart();
        if (is_null($gaw_id) && isset($this->data['giveaway_switch'])) {
            $gaw_id = key($this->data['giveaway_switch']);
        }
        if ($gaw_id && $this->data['products_id'] == $this->uprid) {
            if ($cart->is_valid_product_data($this->data['products_id'], isset($this->data['id']) ? $this->data['id'] : '')) {
                return $cart->add_cart($this->data['products_id'], \common\helpers\Gifts::get_max_quantity($this->data['products_id'], $gaw_id)['qty'], isset($this->data['id']) ? $this->data['id'] : '', false, $gaw_id);
            }
        }
        return false;
    }

    public function setPrice($cartUprids) {
        $cart = $this->manager->getCart();
        $_uprid = is_array($cartUprids) ? array_shift($cartUprids) : $cartUprids;
        if (!is_null($this->data['final_price']) && $this->data['price_changed']) {
            $final_price = $this->data['final_price'] * Yii::$container->get('currencies')->get_market_price_rate($this->manager->get('currency'), DEFAULT_CURRENCY);
            $cart->setOverwrite($_uprid, 'final_price', $final_price);
            $cart->setOverwrite($_uprid, 'price_changed', true);
        } else {
            $cart->clearOverwritenKey($_uprid, 'final_price');
            $cart->clearOverwritenKey($_uprid, 'price_changed');
        }
    }

    public function setName($cartUprids) {
        $cart = $this->manager->getCart();
        $_uprid = is_array($cartUprids) ? array_shift($cartUprids) : $cartUprids;
        if (!is_null($this->data['name']) && $this->data['name_changed']) {
            $cart->setOverwrite($_uprid, 'name', $this->data['name']);
        } else {
            $cart->clearOverwritenKey($_uprid, 'name');
        }
    }

    private function getCartUprid($_partUprid, $cartUprids) {
        $_partUprid = preg_quote($_partUprid);
        if (is_array($cartUprids)) {
            foreach ($cartUprids as $_uprid) {
                if (preg_match("/^$_partUprid/", $_uprid)) {
                    return $_uprid;
                }
            }
        } else if (is_string($cartUprids)) {
            return (preg_match("/^$_partUprid/", $cartUprids) ? $cartUprids : false);
        }
        return false;
    }

    private function _setProductsTax($cartUprid, $selected, $rate, $id) {
        $cart = $this->manager->getCart();
        if ($cart->in_cart($cartUprid)) {
            $cart->setOverwrite($cartUprid, 'tax_selected', $selected);
            $cart->setOverwrite($cartUprid, 'tax_rate', $rate);
            $cart->setOverwrite($cartUprid, 'tax_class_id', $id);
            //$cart->setOverwrite($_uprid, 'tax_description', \common\helpers\Tax::get_tax_description($ex[0], $order->tax_address['entry_country_id'], $ex[1]));
        }
    }

    private function _setProductsTaxZero($cartUprid) {
        $cart = $this->manager->getCart();
        if ($cart->in_cart($cartUprid)) {
            $cart->setOverwrite($cartUprid, 'tax_selected', 0);
            $cart->setOverwrite($cartUprid, 'tax_rate', 0);
            $cart->setOverwrite($cartUprid, 'tax_class_id', 0);
            //$cart->setOverwrite($_uprid, 'tax_description', '');            
        }
    }

    public function setProductTax($cartUprids) {
        if (!is_null($this->data['tax'])) {
            $cart = $this->manager->getCart();
            if (is_array($this->data['tax'])) {
                foreach ($this->data['tax'] as $_partUprid => $tax) {
                    $cartUprid = $this->getCartUprid($_partUprid, $cartUprids);
                    if ($cartUprid) {
                        $ex = explode("_", $tax);
                        $tax_value = 0;
                        if (count($ex) == 2) {
                            $tax_value = \common\helpers\Tax::get_tax_rate_value_edit_order($ex[0], $ex[1]);
                            $this->_setProductsTax($cartUprid, $tax, $tax_value, $ex[0]);
                        } else {
                            $this->_setProductsTaxZero($cartUprid);
                        }
                        break;
                    }
                }
            } else {
                $ex = explode("_", $this->data['tax']);
                $tax_value = 0;
                if (count($ex) == 2) {
                    $tax_value = \common\helpers\Tax::get_tax_rate_value_edit_order($ex[0], $ex[1]);
                    $this->_setProductsTax($cartUprids, $this->data['tax'], $tax_value, $ex[0]);
                } else {
                    $this->_setProductsTaxZero($cartUprids);
                }
            }
        } else {
            if (is_array($cartUprids)) {
                foreach ($cartUprids as $cartUprid) {
                    $this->_setProductsTaxZero($cartUprid);
                }
            }
        }
    }
    
    public $manualPriceChanged = false;
    
    public function setExtraCharge(){
        $cart = $this->manager->getCart();
        $cart->clearOverwritenKey($this->uprid, 'final_price_formula');
        $cart->clearOverwritenKey($this->uprid, 'final_price_formula_data');

        $product = array_shift($cart->get_products($this->uprid));
        $uprid = $this->uprid;        
        if ($product){
            if ($this->manualPriceChanged){
                if (isset($this->data['price']) && $product['final_price'] != $this->data['price']) {
                    if ($product['final_price'] > $this->data['price']){
                        $this->data['dis_action_fixed'][$uprid] = '-';
                        $this->data['dis_action_fixed_value'][$uprid] = ($product['final_price'] - $this->data['price']);
                    } else if ($product['final_price'] < $this->data['price']){
                        $this->data['dis_action_fixed'][$uprid] = '+';
                        $this->data['dis_action_fixed_value'][$uprid] = ($this->data['price'] - $product['final_price']);
                    }
                    $this->data['dis_action_percent'][$uprid] = '-';
                    $this->data['dis_action_percent_value'][$uprid] = 0;
                    $cart->setOverwrite($uprid, 'price_changed', true);
                }
            }
            if (isset($this->data['dis_action_percent_value'][$uprid]) || $this->data['dis_action_fixed_value'][$uprid]){
                $formula = ['final_price', [
                        'action' => 'extra_charge',
                        'vars' => [
                            'init_value' => $product['final_price'],
                            'percent_action' => $this->data['dis_action_percent'][$uprid],
                            'percent_value' => floatval($this->data['dis_action_percent_value'][$uprid]),
                            'fixed_action' => $this->data['dis_action_fixed'][$uprid],
                            'fixed_value' => abs(floatval($this->data['dis_action_fixed_value'][$uprid])),
                        ],
                        'formula' => '{init_value}{percent_action}{init_value}*({percent_value}/100){fixed_action}{fixed_value}',
                ]];
                $cart->setOverwrite($uprid, 'final_price_formula', ['\common\helpers\PriceFormula', 'calculateExtraOrderPrice']);
                $cart->setOverwrite($uprid, 'final_price_formula_data', $formula);

                if ($cart->getOwerwrittenKey($uprid, 'final_price') === false) {
                    $cart->setOverwrite($uprid, 'final_price', $product['final_price']);
                }
            }
        }
    }

}
