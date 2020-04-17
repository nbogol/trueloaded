<?php
/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace app\components;

use Yii;
use common\classes\shopping_cart;
use common\classes\whish_list;

class CartFactory {
    
  public static function initCart() {
    global $cart;
    if (!(tep_session_is_registered('cart') && is_object($cart)))
    {
      tep_session_register('cart');
      $cart = new shopping_cart();
    }
    $cart->cleanup();
    return;
  }
  
  public static function initWish() {
    global $wish_list;
    if ( !tep_session_is_registered('wish_list') ) tep_session_register('wish_list');
    if ( !is_object($wish_list) || !is_a($wish_list, 'wish_list') ) {
      $wish_list = new whish_list(); 
    }
    $wish_list->cleanup();
  }
  
  public static function work() {
    /** @var \common\classes\shopping_cart $cart */
    global $cart, $wish_list, $session_started, $lvnr, $lvanz, $languages_id;
    $messageStack = \Yii::$container->get('message_stack');
    $currency = \Yii::$app->settings->get('currency');
    
    $customer_id = Yii::$app->user->getId();

    // Shopping cart actions
    static $preventProcess = false;
    if (!(tep_session_is_registered('cart') && is_object($cart))) {
        self::initCart();
    } else {
        if (!$preventProcess){
            $cart->cleanup();
        }
    }
    
    if (TEMPORARY_STOCK_ENABLE == 'true' && !$preventProcess) {
        \common\helpers\Product::cleanup_temporary_stock_quantity();
    }
    
    if ($ext = \common\helpers\Acl::checkExtension('Quotations', 'initCart')) {
        $ext::initCart();
    }
    
    if ($ext = \common\helpers\Acl::checkExtension('Samples', 'initCart')) {
        $ext::initCart();
    }

    if( $ext = \common\helpers\Acl::checkExtension( 'MultiCart', 'initCarts' ) ) {
        if ($ext::allowed() && !$preventProcess){
            $ext::initCarts();
        }
    }

    if( $ext = \common\helpers\Acl::checkExtension( 'Newsletters', 'initCarts' ) ) {
        if ($ext::allowed()){
            $ext::initCarts();
        }
    }

    if (count($cart->contents) > 0 && ($cart->currency != $currency || $cart->language_id != $languages_id) && method_exists($cart, 'update_basket_info')) $cart->update_basket_info();    
    
    if (!(tep_session_is_registered('wish_list') && is_object($wish_list))){
      self::initWish();
    }
    
    static $manager = null;
    if (!$preventProcess){
        $manager = \common\services\OrderManager::loadManager();
        $manager->loadCart($cart);
    }
    
    \common\helpers\System::ga_detection($manager);
   
    $preventProcess = true;
    
  if (isset($_GET['action'])) {
// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled
    if ($session_started == false) {
      tep_redirect(tep_href_link(FILENAME_COOKIE_USAGE));
    }

    $goto =  FILENAME_SHOPPING_CART;
    $parameters = array('action', 'cPath', 'products_id', 'pid');
    
    if ($_GET['action'] == 'add_product' && ($_POST['add_to_whishlist_x'] || $_POST['add_to_whishlist_y'] || isset($_POST['add_to_whishlist'])))
    {
      $_GET['action'] = 'add_wishlist';
    }
    if ($_GET['action'] == 'add_product' && isset($_POST['add_to_quote']))
    {
        $_GET['action'] = 'add_quote';
    }
    if ($_GET['action'] == 'add_product' && isset($_POST['add_to_sample']))
    {
        $_GET['action'] = 'add_sample';
    }
    switch ($_GET['action']) {
// {{
      case 'add_giveaway':
        if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
          if ( isset($_POST['giveaway_switch']) ) {
            foreach( $_POST['giveaway_switch'] as $gaw_id => $data) {
              if ( $data != 10 ) continue;
              if ( !isset($_POST['giveaways'][$gaw_id]) || !isset($_POST['giveaways'][$gaw_id]['products_id']) ) continue;
              if ($_POST['giveaways'][$gaw_id]['products_id'] != $_GET['product_id']) continue;
              $ga_data = $_POST['giveaways'][$gaw_id];
              if ( $cart->is_valid_product_data($ga_data['products_id'], isset($ga_data['id'])?$ga_data['id']:'') ){
                $cart->add_cart($ga_data['products_id'], \common\helpers\Gifts::get_max_quantity($ga_data['products_id'], $gaw_id)['qty'], isset($ga_data['id'])?$ga_data['id']:'', false, $gaw_id);
              }else{
                $messageStack->add_session(PLEASE_CHOOSE_ATTRIBUTES, 'shopping_cart', 'error');
              }
            }
          }/* seems no update w/o switch (else you have to add gaw_id to POSTs) else {
            $cart->add_cart($_GET['product_id'], $cart->get_quantity($_GET['product_id'], 1) + $_POST['qty'], '', 1, ($_POST['gaw_id']>0?$_POST['gaw_id']:true));
          }*/
          $parameters[] = 'product_id';
        }
        tep_redirect(tep_href_link($goto, \common\helpers\Output::get_all_get_params($parameters), ''));
      break;
      case 'remove_giveaway':
        if (isset($_GET['product_id']) && $cart->in_giveaway($_GET['product_id'])) {
          $cart->remove_giveaway($_GET['product_id']);
        }
        tep_redirect(tep_href_link($goto, \common\helpers\Output::get_all_get_params($parameters), ''));
      break;
      case 'update_product' : 
          if (isset($_POST['products_id']) && is_array($_POST['products_id'])){
            for ($i=0, $n=sizeof($_POST['products_id']); $i<$n; $i++) {
                if ($_POST['ga'][$i]) continue; // GA are not processed
                $gift_wrap = (isset($_POST['gift_wrap']) && is_array($_POST['gift_wrap']))?$_POST['gift_wrap']:array();
                if (in_array($_POST['products_id'][$i], (is_array($_POST['cart_delete']) ? $_POST['cart_delete'] : array()))) {
                  $cart->remove($_POST['products_id'][$i]);
                } else {

                  $posted_uprid = $_POST['products_id'][$i];
                  // {{ allow update only in cart products - prevent lost props part
                  if ( !$cart->in_cart($posted_uprid) ) continue;
                  // }} allow update only in cart products

                  $attributes = ($_POST['id'][$posted_uprid]) ? $_POST['id'][$posted_uprid] : '';
                  $re_uprid = \common\helpers\Inventory::get_prid($_POST['products_id'][$i]);
                  if ($ext = \common\helpers\Acl::checkExtension('Inventory', 'allowed')) {
                    $re_uprid = \common\helpers\Inventory::get_uprid(\common\helpers\Inventory::get_prid($_POST['products_id'][$i]),$attributes);
                  }
                  $_qty = $_POST['cart_quantity'][$i];
                  
                  if ($ext = \common\helpers\Acl::checkExtension('SupplierPurchase', 'allowed')){
                      if ($ext::allowed() && $ext::getSupplierFromUprid($_POST['products_id'][$i])){
                          $ext::updateCartProduct($_POST['products_id'][$i], $_qty, $attributes);
                          continue;
                      }
                  }
                  
                  if (STOCK_CHECK == 'true'){
                    $stock_info = \common\classes\StockIndication::product_info(array(
                      'products_id' => \common\helpers\Inventory::normalize_id($re_uprid),
                      'cart_qty' => $_qty,
                      'products_quantity' => \common\helpers\Product::get_products_stock($re_uprid),
                    ));

                    if ( !$stock_info['allow_out_of_stock_add_to_cart'] && $stock_info['max_qty']>0 && $_qty>$stock_info['max_qty'] ) {
                      $_qty = (int)$stock_info['max_qty'];
                    }
                  }
                  if (is_array($_POST['cart_quantity_'])) {
                        $packQty = [
                            //'qty' => $_qty,
                            'unit' => (int)$_POST['cart_quantity_'][$_POST['products_id'][$i]][0],
                            'pack_unit' => (int)$_POST['cart_quantity_'][$_POST['products_id'][$i]][1],
                            'packaging' => (int)$_POST['cart_quantity_'][$_POST['products_id'][$i]][2],
                        ];
                        if ($ext = \common\helpers\Acl::checkExtension('PackUnits', 'recalcQauntity')) {
                            $packQty['qty'] = $ext::recalcQauntity(\common\helpers\Inventory::get_prid($_POST['products_id'][$i]), $packQty);
                        }
                  } else {
                      $packQty = $_qty;
                  }
                  /* PC configurator addon begin */
                  if (strpos($_POST['products_id'][$i], '{tpl}') !== false) {
                    $cart->add_cart_cfg($_POST['products_id'][$i], $_POST['cart_quantity'][$i], $attributes);
                  } else {
                    $cart->add_cart($posted_uprid, $packQty, $attributes, false, 0, isset($gift_wrap[$posted_uprid]));
                  }
                  /* PC configurator addon end */
                }
              }
        }

        if ($ext = \common\helpers\Acl::checkExtension('CouponsAndVauchers', 'updateCartFactory')) {
          $ext::updateCartFactory($goto);
        }
        tep_redirect(tep_href_link($goto, \common\helpers\Output::get_all_get_params($parameters)));
        break;
      case 'recovery_restore':
          $email_address = tep_db_prepare_input($_GET['email_address']);
          $token = tep_db_prepare_input($_GET['token']);
          if ($email_address){            
            $customer = new \common\components\Customer(\common\components\Customer::LOGIN_RECOVERY);
            if ($customer->loginCustomer($email_address, $token)){
              if (isset($_GET['utmgclid'])) \common\helpers\System::setcookie('__utmz','utmgclid=' . $_GET['utmgclid'],time()+3600);
              
              if ($ext = \common\helpers\Acl::checkExtension('CouponsAndVauchers', 'restoreCartFactory')) {
                $ext::restoreCartFactory($goto);
              }
              
              tep_db_query("update ". TABLE_SCART . " set recovered = 1 where customers_id = '" . (int)$customer->customers_id . "' and basket_id = '" . (int)$cart->basketID . "'");
            } else {
              \common\helpers\Translation::init('account/login');
              $messageStack->add_session(TEXT_LOGIN_ERROR, 'shopping_cart', 'error');
            }
          }
          tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
          break;
      case 'apply_coupon': /*it is better to use on email link : www.domain.com/?action=apply_coupon&gv_redeem_code=coupon_code */

          $gv_redeem_code = Yii::$app->request->get('gv_redeem_code', '');
          if (!empty($gv_redeem_code)){
              $_POST['credit_apply'] = ['coupon' => ['gv_redeem_code' => $gv_redeem_code]];
              if ($ext = \common\helpers\Acl::checkExtension('CouponsAndVauchers', 'updateCartFactory')) {
                $ext::updateCartFactory($goto);
              }
          }
          tep_redirect(tep_href_link($goto, 'coupon=applying'));
          break;
      case 'payment_request':
          $email_address = tep_db_prepare_input($_GET['email_address']);
          $token = tep_db_prepare_input($_GET['token']);
          if ($email_address && $token){
              $user = new \common\components\Customer();
              $customer = $user->getUserByToken($token);
              if ($customer && strcmp($customer->customers_email_address, $email_address) == 0){
                  //$manager = \common\services\OrderManager::loadManager();
                  $manager->assignCustomer($customer->customers_id);
                  $customer->updateUserToken();//or not
                  tep_redirect(tep_href_link('payer/order-pay', 'order_id=' . $_GET['order_id'], 'SSL'));
              }
          }
          return Yii::$app->controller->goHome();
        break;
      case 'remove_product' :
        $cart->remove($_GET['product_id']);
        tep_redirect(tep_href_link($goto, \common\helpers\Output::get_all_get_params($parameters), ''));
        break;
      case 'add_product' :
        $goto_link = tep_href_link($goto, \common\helpers\Output::get_all_get_params($parameters) . 'popup=1', '');
        if (isset($_POST['products_id']) && is_numeric($_POST['products_id']) && \common\helpers\Product::check_product((int)$_POST['products_id'])) {
          ///comaptibility. added here as existing designs have add_product actions on form. 2DO replace action in all listings types (add_all)
          foreach (['id', 'mix_attr' ] as $_k) {
            if (!Yii::$app->request->post($_k, false) && Yii::$app->request->post('list' . $_k, false) && !empty(Yii::$app->request->post('list'.$_k)[$_POST['products_id']])) {
              $_POST['id'] = Yii::$app->request->post('list'.$_k)[$_POST['products_id']];
            }
          }
          /*
          if (!Yii::$app->request->post('qty', false) && Yii::$app->request->post('listqty', false) && !empty(Yii::$app->request->post('listqty'))) {
            $_POST['qty'] = Yii::$app->request->post('listqty')[0];
          }*/


// {{
          if ($ext = \common\helpers\Acl::checkExtension('SupplierPurchase', 'allowed')){
              if ($ext::allowed() && isset($_POST['wh_product'])){
                  $ext::addToCart($_POST, $goto_link);
              }
          }
          $_qty = (int)(is_array($_POST['qty']) ? array_sum($_POST['qty']): $_POST['qty']);
          // Inventory widget bof
          if (strpos($_POST['inv_uprid'], '{') !== false) {
            $attrib = array();
            $ar = preg_split('/[\{\}]/', $_POST['inv_uprid']);
            for ($i=1; $i<sizeof($ar); $i=$i+2) {
              if (isset($ar[$i+1])) {
                $attrib[$ar[$i]] = $ar[$i+1];
              }
            }
            if (is_array($_POST['id'])){
                $_POST['id'] = $_POST['id'] + $attrib;
            } else {
                $_POST['id'] = $attrib;
            }
          }
          $jsonRequest = [];
          // Inventory widget eof
          if (\common\helpers\Attributes::has_product_attributes((int)$_POST['products_id'])) {
              if (is_array($_POST['id'])){
                foreach ($_POST['id'] as $attr) {
                  if (!$attr) {
                    $_SESSION['product_info'] = PLEASE_CHOOSE_ATTRIBUTES;
                      if ($_POST['json']){
                          $jsonRequest['error'] = 'needs attributes';
                      } elseif (!Yii::$app->request->isAjax){
                          tep_redirect(tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $_POST['products_id']. '&qty=' . (is_numeric($_qty) ? $_qty:1)) );
                      } else {
                          echo '<script type="text/javascript">window.location.href = "' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $_POST['products_id']. '&qty=' . (is_numeric($_qty) ? $_qty:1)) . '"</script>';
                      }
                  }
                }
              }
          }

          $_uprid = $_POST['products_id'];
          if ($ext = \common\helpers\Acl::checkExtension('Inventory', 'allowed')) {
            $_uprid = \common\helpers\Inventory::get_uprid($_POST['products_id'], $_POST['id']);
          }
          $add_qty = $cart->get_quantity($_uprid)+$_qty;
          if ( defined('STOCK_CHECK') && STOCK_CHECK=='true' ) {
              
            $product_qty = \common\helpers\Product::get_products_stock($_uprid);
            $stock_indicator = \common\classes\StockIndication::product_info(array(
              'products_id' => $_uprid,
              'products_quantity' => $product_qty/* - (is_numeric($_POST['qty']) ? (int)$_POST['qty']:1)*/,
            ));
            if ($add_qty>$product_qty && !$stock_indicator['allow_out_of_stock_add_to_cart']) {
              $_SESSION['product_info'] = TEXT_PRODUCT_OUT_STOCK;
                if ($_POST['json']){
                    $jsonRequest['error'] = 'not in stock';
                } elseif (!Yii::$app->request->isAjax){
                    tep_redirect(tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $_POST['products_id'] . '&qty=' . (is_numeric($_qty) ? $_qty : 1)));
                } else {
                    echo '<script type="text/javascript">window.location.href = "' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $_POST['products_id']. '&qty=' . (is_numeric($_qty) ? $_qty:1)) . '"</script>';
                }
            }
          }
// }}
          if (is_array($_POST['qty_'])) {
                $packQty = [
                    'qty' => $add_qty,
                    'unit' => (int)$_POST['qty_'][0],
                    'pack_unit' => (int)$_POST['qty_'][1],
                    'packaging' => (int)$_POST['qty_'][2],
                ];
          } else {
              $packQty = $add_qty;
          }
          $props = Yii::$app->get('PropsHelper')::ParamsToXml($_POST, (int)$_POST['products_id']);
          // Collections addon
          if (is_array($_POST['collections']) && count($_POST['collections']) > 1) {
            foreach ($_POST['collections'] as $products_id) {
              if ($products_id > 0) {
                if ($_POST['collections_qty'][$products_id] > 0) {
                  $qty = (int)$_POST['collections_qty'][$products_id];
                } else {
                  $qty = 1;
                }
                $cart->add_cart((int)$products_id, $qty, $_POST['collections_attr'][$products_id], true, 0, null, $props);
              }
            }
          } else {
              /* PC configurator addon begin */
            if (is_array($_POST['elements'])) {
                if (is_array($_POST['mix']) && count($_POST['mix'])) {
                    $mix = \common\helpers\Attributes::getQTAonMixedArray($_POST);
                    foreach($mix as $data){
                        $data['elements'] = $_POST['elements'];
                        $cart->add_configuration($data, true, 'add');
                    }
                } else {
                    $cart->add_configuration($_POST, true, 'add');
                }
            // Custom bundles addon
            } elseif (is_array($_POST['custom_bundles'])) {
              $cart->add_custom_bundle(true, 'add');
            } else {
                if (is_array($_POST['mix']) && count($_POST['mix'])) {
                    $mix = \common\helpers\Attributes::getQTAonMixedArray($_POST);
                    foreach($mix as $data){
                        $cart->add_cart((int)$data['products_id'], $data['qty'], $data['id'], true, 0, null, $props);
                    }
                } else {
                    $cart->add_cart((int)$_POST['products_id'], $packQty, $_POST['id'], true, 0, null, $props);
                }
            }
            /* PC configurator addon end */
          }
          
          (new \common\components\Popularity())->updateProductVisit($_POST['products_id']);
          

          $wish_list->remove_any_product_id(\common\helpers\Inventory::get_prid($_POST['products_id']));

          $jsonRequest['products'] = $cart->get_products();
          $payment_modules = $manager->getPaymentCollection();
          $expressModules = $payment_modules->getExpressPayments();
          if (!empty($_POST['purchase']) && in_array($_POST['purchase'], $expressModules) ) {
            $class = $_POST['purchase'];
            $payment = $payment_modules->get($class);
            if ($_POST['popup']){
              if (method_exists($payment, 'toObserve')) {
                $payment->toObserve(true);//same redirect as below but with JS
                exit();
              } else {
                echo json_encode(['added' => 1]); //, 'ddd'=>$class, 'eee' => print_r($expressModules,1)
                exit();
              }
            } else {
              if (method_exists($payment, 'getObserveUrl')) {
                tep_redirect($payment->getObserveUrl());
              } else {
                echo addslashes(json_encode($jsonRequest));
                exit();
              }
            }
          }
        }

          if ($_POST['json']) {
              echo json_encode($jsonRequest);
              exit();
          } else {
              tep_redirect($goto_link);
          }

   break;

      case 'add_all' :
        
        $_POST['products_id'] = Yii::$app->request->post('pid', Yii::$app->request->post('products_id', false));
        $_POST['id'] = Yii::$app->request->post('listid', Yii::$app->request->post('id', false));
        $_POST['qty'] = Yii::$app->request->post('listqty', Yii::$app->request->post('qty', false));
        $_POST['mix_attr'] = Yii::$app->request->post('listmix_attr', Yii::$app->request->post('mix_attr', false));

        if (isset($_POST['products_id']) && is_array($_POST['products_id'])){
            foreach($_POST['products_id'] as $key => $products_id){
                if (isset($_POST['mix_qty'][$products_id])){
                    foreach($_POST['mix_qty'][$products_id] as $it => $qty){
                        if ($qty){
                            $attributes = $_POST['id'][$products_id] ?? [];
                            if (isset($_POST['mix_attr'][$products_id][$it])){
                                $attributes = array_replace($attributes, $_POST['mix_attr'][$products_id][$it]);
                            }
                            $cart->add_cart((int)$products_id, $qty, $attributes);
                        }
                    }
                } else {
                    $qty = (int)$_POST['qty'][$key];
                    if ( !($qty > 0) ) continue;
                    $cart->add_cart((int)$_POST['products_id'][$key], $cart->get_quantity($_POST['products_id'][$key])+$qty, $_POST['id'][$products_id]);
                }
            }
            if (Yii::$app->request->post('popup', false)) {
              tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'popup=1'));
            } else {
              tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
            }
        }

        break;
        
      case 'quick_buy':
        $goto_link = tep_href_link($goto, \common\helpers\Output::get_all_get_params($parameters) . 'popup=1', '');
        if (isset($_POST['qty']) && is_array($_POST['qty'])) {
          foreach ($_POST['qty'] as $uprid => $qty) {
            if ($qty > 0) {
              $attrib = array();
              $ar = preg_split('/[\{\}]/', $uprid);
              for ($i=1; $i<sizeof($ar); $i=$i+2) {
                if (isset($ar[$i+1])) {
                  $attrib[$ar[$i]] = $ar[$i+1];
                }
              }
              $cart->add_cart(\common\helpers\Inventory::get_prid($uprid), $cart->get_quantity($uprid) + $qty, $attrib);
            }
          }
        }
        tep_redirect($goto_link);

        break;
      case 'taxabeling':
        Yii::$app->storage->set('taxable', \Yii::$app->request->post('taxable', 1));
        /** @var \common\extensions\CustomerTaxable $cTaxable */
        if ($cTaxable = \common\helpers\Acl::checkExtension('CustomerTaxable', 'saveCustomer')) {
          $cTaxable::saveCustomer();
        }

        tep_redirect(Yii::$app->request->getReferrer());
      break;
// Add product to the wishlist
///// CHANGES TO case 'add_wishlist' BY DREAMSCAPE /////
   case 'add_wishlist' :
    if (isset($_POST['products_id']) && is_numeric($_POST['products_id']) && \common\helpers\Product::check_product((int)$_POST['products_id'])) {
// {{
        $wProds = [];
        if (\common\helpers\Attributes::has_product_attributes((int)$_POST['products_id'])) {
            if (is_array($_POST['mix']) && count($_POST['mix'])) {
                $wProds = \common\helpers\Attributes::getQTAonMixedArray($_POST, true);
            } else {
                $wProds[] = ['products_id' => $_POST['products_id'], 'id' => $_POST['id']];
            }
        } else {
            $wProds[] = ['products_id' => $_POST['products_id'], 'id' => []];
        }
        if ($wProds){
            foreach($wProds as $wProd){
                $attrs = $wProd['id']??[];
                foreach ($attrs as $attr => $value) {
                   if (!$value) {						 
                     if ($_POST['popup']) {
                       echo '<div id="error_wishlist_popup" style="padding: 20px 30px;">Select attributes from <strong>'.( strpos($attr, '-') ? \common\helpers\Product::get_products_name(preg_replace("/.*\-/s", '', $attr)): \common\helpers\Product::get_products_name((int)$_POST['products_id'])).'</strong></div>';
                       die;
                     }
                     $_SESSION['product_info'] = PLEASE_CHOOSE_ATTRIBUTES;
                       if (!Yii::$app->request->isAjax){
                           tep_redirect(tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . \common\helpers\Inventory::get_uprid($_POST['products_id'], $_POST['id'])));
                       } else {
                           echo '<script type="text/javascript">window.location.href = "' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . \common\helpers\Inventory::get_uprid($_POST['products_id'], $_POST['id'])) . '"</script>';
                       }
                   }
                }
                $wish_list->add_product($wProd['products_id'], $attrs);
            }
        }
        Yii::$app->controller->promoActionsObs->triggerAction('add_to_wishlist');
    }
    tep_redirect(tep_href_link(FILENAME_WISHLIST, \common\helpers\Output::get_all_get_params($parameters), 'SSL'));
    break;
 
// Add wishlist item to the cart
case 'wishlist_add_cart':   reset ($lvnr);
                            reset ($lvanz);
                                 while (list($key,$elem) =each ($lvnr))
                                       {
                                        (list($key1,$elem1) =each ($lvanz));
                                        tep_db_query("update " . TABLE_WISHLIST . " SET products_quantity='".(int)$elem1."' WHERE customers_id='".(int)$customer_id."' AND products_id='".(int)$elem."'");
                                        tep_db_query("delete from " . TABLE_WISHLIST . " WHERE customers_id='".(int)$customer_id."' AND products_quantity='999'");
                                        $produkte_mit_anzahl=tep_db_query("select * from " . TABLE_WISHLIST . " WHERE customers_id='".(int)$customer_id."' AND products_id='".(int)$elem."' AND products_quantity<>'0'");

                                        while ($_POST=tep_db_fetch_array($produkte_mit_anzahl))
                                              {
                                               $cart->add_cart($_POST['products_id'], $_POST['products_quantity']);
                                               }
                                        }
                                  reset ($lvanz);
                              tep_redirect(tep_href_link($goto, \common\helpers\Output::get_all_get_params($parameters), ''));
                              break;


// remove item from the wishlist
///// CHANGES TO case 'remove_wishlisy' BY DREAMSCAPE /////
      case 'wishlist_move_to_cart':
        
        if (\Yii::$app->request->isPost || \Yii::$app->request->post('popup', false) ) {
          $inPopup = true;
        } else {
          $inPopup = false;
        }

        if(isset($_GET['products_id'])){

          $__product_info = $wish_list->get_product_info($_GET['products_id']);

          if ( is_array($__product_info) ) {
            $_prid = \common\helpers\Inventory::get_prid($_GET['products_id']);
            $_attr = isset($__product_info['attributes'])?$__product_info['attributes']:'';
            if ( \common\helpers\Product::check_product($_prid) && $cart->is_valid_product_data($_prid, $_attr) ){
              $qty_add = \Yii::$app->request->post('qty', 1);
              $cart->add_cart((int)$_prid, $cart->get_quantity(\common\helpers\Inventory::get_uprid($_prid, $_attr))+$qty_add, $_attr);
              
              if(!$inPopup){
                $wish_list->remove_product($_GET['products_id']);
                tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', ''));
              } else {
                tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'popup=1'));
              }

            }else{
              if ( \common\helpers\Attributes::has_product_attributes((int)$_GET['products_id']) ) {
                $_SESSION['product_info'] = PLEASE_CHOOSE_ATTRIBUTES;
              }
              if(!$inPopup){
                tep_redirect(tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . \common\helpers\Inventory::get_uprid($_prid, $_attr)));
              } else {
                echo PLEASE_CHOOSE_ATTRIBUTES; die;
              }
            }
          }

        }
        if(!$inPopup){
          tep_redirect(tep_href_link(FILENAME_WISHLIST, \common\helpers\Output::get_all_get_params(array('action', 'products_id')), 'SSL'));
        } else {
          tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'popup=1'));
        }
        break;
      case 'remove_wishlist':
        if ( isset($_GET['pid']) ) {
          $wish_list->remove_any_product_id($_GET['pid']);
        }elseif(isset($_GET['products_id'])){
          if ( $wish_list->in_wish_list($_GET['products_id']) ) {
            $wish_list->remove_product($_GET['products_id']);
          }else{
            $wish_list->remove_any_product_id($_GET['products_id']);
          }
        }
        tep_redirect(tep_href_link(FILENAME_WISHLIST, \common\helpers\Output::get_all_get_params(array('action', 'pid', 'products_id')), 'SSL'));
        break;



      // performed by the 'buy now' button in product listings and review page
      case 'buy_now' :
// {{
            if ( !($_GET['products_id'] > 0) ) {
                // TlUrlRule not loaded here yet
                $product = tep_db_fetch_array(tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and pd.platform_id='".(int)PLATFORM_ID."' where if(length(pd.products_seo_page_name) > 0, pd.products_seo_page_name, p.products_seo_page_name) = '" . tep_db_input(\common\helpers\System::get_seo_path()) . "' limit 1"));

                if ($product['products_id'] > 0) {
                  $products_id = $_GET['products_id'] = $_GET['products_id'] = $product['products_id'];
                }
            }
            if (STOCK_ALLOW_CHECKOUT != 'true' && !(\common\helpers\Product::get_products_stock($_GET['products_id']) > 0)) {
                $_SESSION['product_info'] = TEXT_PRODUCT_OUT_STOCK;
                if (!Yii::$app->request->isAjax){
                    tep_redirect(tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $_GET['products_id']));
                } else {
                    echo '<script type="text/javascript">window.location.href = "' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $_GET['products_id']) . '"</script>';
                }
            }
// }}
            if (isset($_GET['products_id']) && \common\helpers\Product::check_product((int)$_GET['products_id'])) {
                if (!Yii::$app->user->isGuest) { tep_db_query("delete from " . TABLE_WISHLIST . " WHERE customers_id='".(int)$customer_id."' AND products_id=" . (int)$products_id); }
                if (\common\helpers\Attributes::has_product_attributes($_GET['products_id'])) {
                    if (!Yii::$app->request->isAjax){
                        tep_redirect(tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $_GET['products_id']. '&qty=' . (is_numeric($_GET['qty']) ? $_GET['qty']:1)));
                    } else {
                        echo '<script type="text/javascript">window.location.href = "' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $_GET['products_id']. '&qty=' . (is_numeric($_GET['qty']) ? $_GET['qty']:1)) . '"</script>';
                    }
                } else {
                  $cart->add_cart($_GET['products_id'], $cart->get_quantity($_GET['products_id'])+(is_numeric($_GET['qty'])?(int)$_GET['qty']:1));
                }
            }
            tep_redirect(tep_href_link($goto, \common\helpers\Output::get_all_get_params($parameters) . 'popup=1'));
            break;

      case 'notify' :         if (!Yii::$app->user->isGuest) {
                                if (isset($_GET['products_id'])) {
                                  $notify = $_GET['products_id'];
                                } elseif (isset($_GET['notify'])) {
                                  $notify = $_GET['notify'];
                                } elseif (isset($_POST['notify'])) {
                                  $notify = $_POST['notify'];
                                } else {
                                  tep_redirect(tep_href_link(basename($PHP_SELF), \common\helpers\Output::get_all_get_params(array('action', 'notify'))));
                                }
                                if (!is_array($notify)) $notify = array($notify);
                                for ($i=0, $n=sizeof($notify); $i<$n; $i++) {
                                  $check_query = tep_db_query("select count(*) as count from " . TABLE_PRODUCTS_NOTIFICATIONS . " where products_id = '" . (int)$notify[$i] . "' and customers_id = '" . (int)$customer_id . "'");
                                  $check = tep_db_fetch_array($check_query);
                                  if ($check['count'] < 1) {
                                    tep_db_query("insert into " . TABLE_PRODUCTS_NOTIFICATIONS . " (products_id, customers_id, date_added) values ('" . (int)$notify[$i] . "', '" . (int)$customer_id . "', now())");
                                  }
                                }
                                tep_redirect(tep_href_link(basename($PHP_SELF), \common\helpers\Output::get_all_get_params(array('action', 'notify'))));
                              } else {
                                $navigation->set_snapshot();
                                tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
                              }
                              break;
      case 'notify_remove' :  if (!Yii::$app->user->isGuest && isset($_GET['products_id'])) {
                                $check_query = tep_db_query("select count(*) as count from " . TABLE_PRODUCTS_NOTIFICATIONS . " where products_id = '" . (int)$_GET['products_id'] . "' and customers_id = '" . (int)$customer_id . "'");
                                $check = tep_db_fetch_array($check_query);
                                if ($check['count'] > 0) {
                                  tep_db_query("delete from " . TABLE_PRODUCTS_NOTIFICATIONS . " where products_id = '" . (int)$_GET['products_id'] . "' and customers_id = '" . (int)$customer_id . "'");
                                }
                                tep_redirect(tep_href_link(basename($PHP_SELF), \common\helpers\Output::get_all_get_params(array('action'))));
                              } else {
                                $navigation->set_snapshot();
                                tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
                              }
                              break;
      case 'cust_order' :     if (!Yii::$app->user->isGuest && isset($_GET['pid']) &&  \common\helpers\Product::check_product((int)$_GET['pid'])) {

                                if (\common\helpers\Attributes::has_product_attributes($_GET['pid'])) {
                                  tep_db_query("delete from " . TABLE_WISHLIST . " WHERE customers_id='".(int)$customer_id."' AND products_id='".(int)$_GET['pid']."'");
                                    if (!Yii::$app->request->isAjax){
                                        tep_redirect(tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $_GET['pid'], ''));
                                    } else {
                                        echo '<script type="text/javascript">window.location.href = "' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $_GET['pid'], '') . '"</script>';
                                    }
                                 } else {
                                  tep_db_query("delete from " . TABLE_WISHLIST . " WHERE customers_id='".(int)$customer_id."' AND products_id='".(int)$_GET['pid']."'");
                                  $cart->add_cart($_GET['pid'], $cart->get_quantity($_GET['pid'])+1);
                                }
                              }
                              tep_redirect(tep_href_link($goto, \common\helpers\Output::get_all_get_params($parameters), ''));
                              break;
      case 'add_quote' :
            if ($ext = \common\helpers\Acl::checkExtension('Quotations', 'addCart')) {
                $ext::addCart();
            }
          break;
      case 'update_quote' :
            if ($ext = \common\helpers\Acl::checkExtension('Quotations', 'updateCart')) {
                $ext::updateCart();
            }
          break;
      case 'remove_quote' :
            if ($ext = \common\helpers\Acl::checkExtension('Quotations', 'removeCart')) {
                $ext::removeCart();
            }
          break;
      case 'add_sample' :
            if ($ext = \common\helpers\Acl::checkExtension('Samples', 'addCart')) {
                $ext::addCart();
            }
          break;
      case 'remove_sample' :
            if ($ext = \common\helpers\Acl::checkExtension('Samples', 'removeCart')) {
                $ext::removeCart();
            }
          break;
    }
  }
  }
}