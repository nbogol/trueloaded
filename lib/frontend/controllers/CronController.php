<?php
namespace frontend\controllers;

use Yii;

/**
 * Site controller
 */
class CronController extends Sceleton
{

    public function actionIndex()
    {
    }

    public function actionNotifyBackInStock()
    {
      $languages_id = \Yii::$app->settings->get('languages_id');
      $products = tep_db_query("select products_notify_products_id, suppliers_id, count(products_notify_id) as count_notifies from " . TABLE_PRODUCTS_NOTIFY . " where products_notify_sent is null group by products_notify_products_id, suppliers_id");
      while ($product = tep_db_fetch_array($products)) {
        if (\common\helpers\Warehouses::get_products_quantity($product['products_notify_products_id'], 0, $product['suppliers_id']) > 0) {
          $products_id = \common\helpers\Inventory::get_prid($product['products_notify_products_id']);
          $notifies = tep_db_query("select * from " . TABLE_PRODUCTS_NOTIFY . " where products_notify_products_id = '" . tep_db_input($product['products_notify_products_id']) . "' and suppliers_id = '" . (int)$product['suppliers_id'] . "' and products_notify_sent is null");
          $product = tep_db_fetch_array(tep_db_query("select p.products_image, pd.products_name, p.products_id as prid from " . TABLE_PRODUCTS . " as p left join " . TABLE_PRODUCTS_DESCRIPTION . " as pd on pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "' where p.products_id = '" . (int)$products_id . "' and p.products_status = '1'"));
          if (!$product) {
            continue;
          }
          while ($notify = tep_db_fetch_array($notifies)) {
            // {{
            $email_params = array();
            $email_params['STORE_NAME'] = STORE_NAME;
            $email_params['CUSTOMER_NAME'] = ($notify['products_notify_name'] ? $notify['products_notify_name'] : 'Customer');
            $email_params['PRODUCT_NAME'] = \common\helpers\Product::get_products_name($products_id);
            $email_params['PRODUCT_URL'] = tep_href_link('catalog/product', 'products_id='. $products_id);
            $email_params['PRODUCT_IMAGE'] = \common\classes\Images::getImageUrl($products_id, 'Small');
            list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Notify Back in Stock', $email_params);
            // }}
            \common\helpers\Mail::send($notify['products_notify_name'], $notify['products_notify_email'], $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
            tep_db_query("update " . TABLE_PRODUCTS_NOTIFY . " set products_notify_sent = now() where products_notify_id = '" . tep_db_input($notify['products_notify_id']) . "'");
          }
        }
      }
    }

    public function actionGooglePagespeedCrawl()
    {
      $languages_id = \Yii::$app->settings->get('languages_id');
      tep_db_query("CREATE TABLE IF NOT EXISTS google_pagespeed_urls (
        id int(11) NOT NULL AUTO_INCREMENT,
        google_pagespeed_url varchar(255) NOT NULL,
        date_processed datetime DEFAULT NULL,
        google_pagespeed_result TEXT NOT NULL,
        google_pagespeed_score SMALLINT(7) NOT NULL,
        PRIMARY KEY (id),
        INDEX idx_google_pagespeed_url (google_pagespeed_url)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      // Home page
      google_pagespeed_url_process(tep_href_link(FILENAME_DEFAULT));

      // Information pages
      $information_query = tep_db_query("select i.information_id, i.info_title, i.page, i.page_type from " . TABLE_INFORMATION ." i where i.visible = '1' and i.languages_id = " . (int)$languages_id . " and i.affiliate_id = 0 order by i.v_order");
      while ($information = tep_db_fetch_array($information_query)) {
        $url = tep_href_link(FILENAME_INFORMATION, 'info_id=' . $information['information_id'], 'NONSSL', false);
        google_pagespeed_url_process($url);
      }

      // Categories pages
      $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES . " c inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on c.categories_id = plc.categories_id  and plc.platform_id = '" . \common\classes\platform::currentId() . "' where c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and c.categories_status = '1' and cd.affiliate_id = '0' order by c.parent_id, sort_order, categories_name");
      while ($categories = tep_db_fetch_array($categories_query)) {
        $url = tep_href_link('catalog', \common\helpers\Categories::get_path($categories['categories_id']), 'NONSSL', false);
        google_pagespeed_url_process($url);
      }

      // Products pages
      $products_sql = \frontend\design\ListingSql::query(array('filename' => FILENAME_PRODUCTS_NEW, 'sort' => 'dd'));
      $products_query = tep_db_query($products_sql);
      while ($products = tep_db_fetch_array($products_query)) {
        $url = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products['products_id'], 'NONSSL', false);
        google_pagespeed_url_process($url);
      }

      // Additional pages
      if (defined('GOOGLE_PAGESPEED_CRAWL_PAGES') && tep_not_null(GOOGLE_PAGESPEED_CRAWL_PAGES)) {
        foreach(explode("\n", GOOGLE_PAGESPEED_CRAWL_PAGES) as $page) {
          $url = tep_href_link(trim($page), '', 'NONSSL', false);
          google_pagespeed_url_process($url);
        }
      }

      tep_db_query("delete from google_pagespeed_urls where date_processed < (now() - interval 180 day)");
    }

    public function actionCheckGuestAccounts() {//check-guest-accounts
        //if account created 3 months ago and over then delete
        $date_to = date('Y-m-d', strtotime('-3 months')) . ' 23:59:59';
        $check_customer_query = tep_db_query("select c.customers_id from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " ci on c.customers_id = ci.customers_info_id where c.opc_temp_account = '1' and ci.customers_info_date_account_created <= '" . tep_db_input($date_to) . "'");
        while ($check_customer = tep_db_fetch_array($check_customer_query)) {
            \common\helpers\Customer::deleteCustomer($check_customer['customers_id']);//delete with notification
        }
        
        //if account created 2 weeks ago then notify
        $currentPlatformId = \Yii::$app->get('platform')->config()->getId();
        $platform_config = \Yii::$app->get('platform')->config($currentPlatformId);
        $STORE_NAME = $platform_config->const_value('STORE_NAME');
        $STORE_OWNER_EMAIL_ADDRESS = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
        $STORE_OWNER = $platform_config->const_value('STORE_OWNER');

        $reminders = [
            [
                'period' => '2 weeks',
                'date' => date('Y-m-d', strtotime('-3 months +2 weeks')),
            ],
            [
                'period' => 'one week',
                'date' => date('Y-m-d', strtotime('-3 months +1 week')),
            ],
            [
                'period' => '2 days',
                'date' => date('Y-m-d', strtotime('-3 months +2 days')),
            ],
        ];
        
        foreach ($reminders as $reminder) {
            $date_from = $reminder['date'] . ' 00:00:00';
            $date_to = $reminder['date'] . ' 23:59:59';
            $check_customer_query = tep_db_query("select c.customers_id, c.customers_firstname, c.customers_lastname, c.customers_email_address from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " ci on c.customers_id = ci.customers_info_id where c.opc_temp_account = '1' and ci.customers_info_date_account_created >= '" . tep_db_input($date_from) . "' and ci.customers_info_date_account_created <= '" . tep_db_input($date_to) . "'");
            while ($check_customer = tep_db_fetch_array($check_customer_query)) {
                tep_db_query("DELETE FROM guest_check WHERE date_send < '" . date('Y-m-d') . "' and customers_id=" . (int)$check_customer['customers_id']);
                $guest_check_query = tep_db_query("select * from guest_check where customers_id = '" . (int)$check_customer['customers_id'] . "'");
                if (tep_db_num_rows($guest_check_query) == 0) {
                    do {
                        $new_token = \common\helpers\Password::create_random_value(32);
                        $token_check_query = tep_db_query("select token from guest_check where token = '" . $new_token . "'");
                    } while (tep_db_num_rows($token_check_query) > 0);
                    $sql_data_array = [
                        'customers_id' => (int)$check_customer['customers_id'],
                        'email' => $check_customer['customers_email_address'],
                        'date_send' => 'now()',
                        'token' => $new_token,
                    ];
                    tep_db_perform('guest_check', $sql_data_array);

                    $email_params = array();
                    $email_params['STORE_NAME'] = $STORE_NAME;
                    $email_params['STORE_URL'] = \common\helpers\Output::get_clickable_link(tep_href_link('', '', 'NONSSL'/* , $store['store_url'] */));
                    $email_params['CUSTOMER_FIRSTNAME'] = $check_customer['customers_firstname'];
                    $email_params['STORE_OWNER_EMAIL_ADDRESS'] = $STORE_OWNER_EMAIL_ADDRESS;
                    $email_params['HTTP_HOST'] = \common\helpers\Output::get_clickable_link(tep_href_link('account/recreate', 'token=' . $new_token, 'SSL'));
                    $email_params['BEFORE'] = $reminder['period'];
                    list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('GDPR guest request', $email_params);

                    \common\helpers\Mail::send($check_customer['customers_firstname'] . ' ' . $check_customer['customers_lastname'], $check_customer['customers_email_address'], $email_subject, $email_text, $STORE_OWNER, $STORE_OWNER_EMAIL_ADDRESS);
                }
            }
        }
    }
    
    public function actionCheckOldAccounts() {//check-old-accounts
        //not used over 7 years
        $date_to = date('Y-m-d', strtotime('-7 years')) . ' 23:59:59';
        $check_customer_query = tep_db_query("select c.customers_id from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " ci on c.customers_id = ci.customers_info_id where c.opc_temp_account = '0' and (ci.customers_info_date_of_last_logon <= '" . tep_db_input($date_to) . "' or (ci.customers_info_date_account_created <= '" . tep_db_input($date_to) . "' and ci.customers_info_date_of_last_logon IS NULL ) )");
        while ($check_customer = tep_db_fetch_array($check_customer_query)) {
            \common\helpers\Customer::deleteCustomer($check_customer['customers_id']);//delete with notification
        }
    }
    
    public function actionCheckRegularOffers() {//check-regular-offers
        // disable
        $regular_offers_query = tep_db_query("select * from regular_offers where date_end <= '" . date('Y-m-d') . "'");
        while ($regular_offers = tep_db_fetch_array($regular_offers_query)) {
            tep_db_query("update " . TABLE_CUSTOMERS . " set customers_newsletter = 0 where customers_id = '" . (int)$regular_offers['customers_id'] . "'");
            tep_db_query("DELETE FROM regular_offers WHERE customers_id=" . (int)$regular_offers['customers_id']);
        }
            
        //if account created 2 weeks ago then notify
        $currentPlatformId = \Yii::$app->get('platform')->config()->getId();
        $platform_config = \Yii::$app->get('platform')->config($currentPlatformId);
        $STORE_NAME = $platform_config->const_value('STORE_NAME');
        $STORE_OWNER_EMAIL_ADDRESS = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
        $STORE_OWNER = $platform_config->const_value('STORE_OWNER');

        $reminders = [
            [
                'period' => '2 weeks',
                'date' => date('Y-m-d', strtotime('+2 weeks')),
            ],
            [
                'period' => '2 days',
                'date' => date('Y-m-d', strtotime('+2 days')),
            ],
        ];
        foreach ($reminders as $reminder) {
            $check_customer_query = tep_db_query("select c.customers_id, c.customers_firstname, c.customers_lastname, c.customers_email_address, ro.token, ro.date_send from " . TABLE_CUSTOMERS . " c left join regular_offers ro on c.customers_id = ro.customers_id where c.opc_temp_account = '0' and ro.date_end = '" . tep_db_input($reminder['date']) . "'");
            while ($check_customer = tep_db_fetch_array($check_customer_query)) {
                if ($check_customer['date_send'] != date('Y-m-d')) {
                        $sql_data_array = [
                            'date_send' => 'now()',
                        ];
                    if (empty($check_customer['token'])) {
                        do {
                            $new_token = \common\helpers\Password::create_random_value(32);
                            $token_check_query = tep_db_query("select token from regular_offers where token = '" . $new_token . "'");
                        } while (tep_db_num_rows($token_check_query) > 0);
                        $sql_data_array['token'] = $new_token;
                    } else {
                        $new_token = $check_customer['token'];
                    }
                    tep_db_perform('regular_offers', $sql_data_array, 'update', "customers_id = '" . (int) $check_customer['customers_id'] . "'");
                    
                    $email_params = array();
                    $email_params['STORE_NAME'] = $STORE_NAME;
                    $email_params['STORE_URL'] = \common\helpers\Output::get_clickable_link(tep_href_link('', '', 'NONSSL'/* , $store['store_url'] */));
                    $email_params['CUSTOMER_FIRSTNAME'] = $check_customer['customers_firstname'];
                    $email_params['STORE_OWNER_EMAIL_ADDRESS'] = $STORE_OWNER_EMAIL_ADDRESS;
                    $email_params['HTTP_HOST'] = \common\helpers\Output::get_clickable_link(tep_href_link('account/subscription-renewal', 'token=' . $new_token, 'SSL'));
                    $email_params['BEFORE'] = $reminder['period'];
                    list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('GDPR regular offers request', $email_params);

                    \common\helpers\Mail::send($check_customer['customers_firstname'] . ' ' . $check_customer['customers_lastname'], $check_customer['customers_email_address'], $email_subject, $email_text, $STORE_OWNER, $STORE_OWNER_EMAIL_ADDRESS);
                }
            }
        }
    }
 
    public function actionReminder() {

        if (defined('EMAIL_REMINDER_AFTER_YEAR') && (EMAIL_REMINDER_AFTER_YEAR == 'True')) {
            //$languages_id = \Yii::$app->settings->get('languages_id');

            $currencies = new \common\classes\currencies();

            $daysAfter = [
                'Reminder 12 months' => '-12 months',
                //'Reminder 6 months' => '-6 months',
            ];
            foreach ($daysAfter as $daysKey => $daysValue) {
                //$check_orders_query = tep_db_query("select orders_id, customers_name, customers_email_address, orders_status, date_purchased, delivery_date, platform_id, currency, currency_value, customers_id from " . TABLE_ORDERS . " where orders_id = '" . 304995 . "'");
                $check_orders_query = tep_db_query("select orders_id, customers_name, customers_email_address, orders_status, date_purchased, delivery_date, platform_id, currency, currency_value, customers_id from " . TABLE_ORDERS . " where date_purchased >= '" . date('Y-m-d', strtotime($daysValue)) . " 00:00:00' and date_purchased <= '" . date('Y-m-d', strtotime($daysValue)) . " 23:59:59' and ebay_orders_id = '' and amazon_orders_id = ''");
                while ($check_orders = tep_db_fetch_array($check_orders_query)) {
                    /*$check_newest_orders_query = tep_db_query("select count(*) as total from " . TABLE_ORDERS . " where customers_id = '" . $check_orders['customers_id'] . "' and date_purchased > '" . date('Y-m-d', strtotime($daysValue)) . " 23:59:59' and ebay_orders_id = '' and amazon_orders_id = ''");
                    $check_newest_orders = tep_db_fetch_array($check_newest_orders_query);*/
                    if (true /*$check_newest_orders['total'] == 0*/) {
                        $order = new \common\classes\Order($check_orders['orders_id']);

                        $products_ordered = '';
                        for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
                            $prid = \common\helpers\Inventory::get_prid($order->products[$i]['id']);
                            if (EMAIL_USE_HTML == 'true') {
                                $image = \common\classes\Images::getImage($order->products[$i]['id']);
                                $products_ordered .= '   <blockquote valign="middle">' . ($image ? $image . '&nbsp;' : '') . $order->products[$i]['qty'] . ' x ' . '<strong>' . $order->products[$i]['name'] . "</strong> - " . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . "</blockquote>";
                            } else {
                                $products_ordered .= $order->products[$i]['qty'] . ' x  ' . $order->products[$i]['name'] . "-" . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . "\n";
                            }
                        }
                        if (empty($products_ordered)) {
                            continue;
                        }
                        $products_ordered .= "\n";

                        $platform_config = new \common\classes\platform_config($check_orders['platform_id']);
                        $eMail_store = $platform_config->const_value('STORE_NAME');
                        $eMail_address = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
                        $eMail_store_owner = $platform_config->const_value('STORE_OWNER');

                        $email_params = array();
                        $email_params['STORE_NAME'] = $eMail_store;
                        $email_params['ORDER_NUMBER'] = $check_orders['orders_id'];
                        //$email_params['USER_GREETING'] = trim(\common\helpers\Translation::getTranslationValue('EMAIL_TEXT_SALUTATION', 'admin/recover_cart_sales', $languages_id) . $check_orders['customers_name']);
                        $email_params['CUSTOMER_FIRSTNAME'] = $check_orders['customers_name'];
                        $email_params['PRODUCTS_ORDERED'] = substr($products_ordered, 0, -1);
                        list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template($daysKey, $email_params, -1, $check_orders['platform_id']);
                        \common\helpers\Mail::send($check_orders['customers_name'], $check_orders['customers_email_address'], $email_subject, $email_text, $eMail_store_owner, $eMail_address);
                    }
                }
            
            }
            
        }
    }
    
    public function actionCrossUpSelling() {
        global $platform;

        $orderRepository = new \common\models\repositories\OrderRepository();
        $productRepository = new \common\models\repositories\ProductsRepository();
        $platform = new \common\classes\platform_config($platform['platform_id']);
        $defCurrency = $platform->getDefaultCurrency();
        $defLanguage = \common\helpers\Language::get_default_language_id();

        $languages_id = \Yii::$app->settings->get('languages_id');
        $currencies = \Yii::$container->get('currencies');
        $template_name = 'Cross-selling and up-selling products';

        $result = $orderRepository->getCrossUpSellingProductsForDelivery();

        $currencies = \Yii::$container->get('currencies');
        $template_name = 'Cross-selling and up-selling products';

        $result = $orderRepository->getCrossUpSellingProductsForDelivery();
        if (!$result) {
            exit;
        }

        foreach ($result as $row) {
            //if((int)$row['customers_id'] != 520) continue;

            if (!empty($row['xsells']) || !empty($row['upsells'])) {
                $customer = \common\models\Customers::findOne((int) $row['customers_id']);
                $group_id = $customer->groups_id;


                if ($row['xsells']) {

                    $Products = $productRepository->getWithDescription(explode(',', $row['xsells']), $defLanguage, true);
                    $ptoduct = '';
                    $ptoductArr = [];
                    $columns = 3;

                    foreach ($Products as $product) {

//			$sprice = \common\helpers\Product::get_products_price($product['products_id'],1,0,0, $group_id);

                        $sprice = \common\helpers\Product::get_products_special_price($product['products_id']);
                        if ($sprice < 1) {
                            $sprice = \common\helpers\Product::get_products_price($product['products_id'], 1, 0, 0, 0);
                        }
                        $sprice += ( $sprice * \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']) / 100 );

                        $pprice_formated = $currencies->format($sprice, false, $defCurrency, $defCurrency);
                        $product_link = Yii::$app->urlManager->createAbsoluteUrl([
                            'catalog/product',
                            'products_id' => $product['products_id']
                        ]);
                        if (EMAIL_USE_HTML == 'true') {
                            $image = \common\classes\Images::getImage($product['products_id'], 'Small');
                            $ptoductArr[] = '
<div style="text-align: center; padding: 20px;">
    <div>' . ( $image ? '<a href="' . $product_link . '">' . $image . '</a>' : '' ) . '</div>
    <div style="margin-bottom: 10px"><a href="' . $product_link . '" style="font-size: 16px; font-weight: bold; color: #444444; text-decoration:none;">' . $product['descriptions'][0]['products_name'] . '</a></div>
    <div style="font-size: 24px">' . $pprice_formated . '</div>
</div>';
                        } else {
                            $ptoduct .= $product['products_name'] . " - " . $pprice_formated . "\n";
                        }
                    }

                    if (EMAIL_USE_HTML == 'true') {
                        $count = count($ptoductArr);
                        $last = $count % $columns;
                        $ptoduct .= '<table  cellpadding="0" cellspacing="0" width="100%" border="0"><tr style="vertical-align: top">';
                        for ($i = 0; $i < ( $count - $last ); $i ++) {
                            if ($i != 0 && $i % $columns == 0) {
                                $ptoduct .= '</tr><tr style="vertical-align: top">';
                            }
                            $ptoduct .= '<td width="' . floor(100 / $columns) . '%">' . $ptoductArr[$i] . '</td>';
                        }
                        $ptoduct .= '</tr></table>';

                        $ptoduct .= '<table  cellpadding="0" cellspacing="0" width="100%" border="0"><tr style="vertical-align: top">';
                        for ($i; $i < $count; $i ++) {
                            $ptoduct .= '<td width="' . floor(100 / $last) . '%">' . $ptoductArr[$i] . '</td>';
                        }
                        $ptoduct .= '</tr></table>';
                    }
                    $xmline = $ptoduct;
                }


                if ($row['upsells']) {
                    $Products = $productRepository->getWithDescription(explode(',', $row['upsells']), $defLanguage, true);

                    $ptoduct = '';
                    $ptoductArr = [];
                    $columns = 3;
                    foreach ($Products as $product) {

                        //$sprice = \common\helpers\Product::get_products_price($product['products_id'],1,0,0, $group_id);
                        $sprice = \common\helpers\Product::get_products_special_price($product['products_id']);
                        if ($sprice < 1) {
                            $sprice = \common\helpers\Product::get_products_price($product['products_id'], 1, 0, 0, 0);
                        }

                        $sprice += ( $sprice * \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']) / 100 );

                        $pprice_formated = $currencies->format($sprice, false, $defCurrency, $defCurrency);
                        $product_link = Yii::$app->urlManager->createAbsoluteUrl([
                            'catalog/product',
                            'products_id' => $product['products_id']
                        ]);

                        if (EMAIL_USE_HTML == 'true') {
                            $image = \common\classes\Images::getImage($product['products_id'], 'Small');
                            $ptoductArr[] = '
<div style="text-align: center; padding: 20px;">
    <div>' . ( $image ? '<a href="' . $product_link . '">' . $image . '</a>' : '' ) . '</div>
    <div style="margin-bottom: 10px"><a href="' . $product_link . '" style="font-size: 16px; font-weight: bold; color: #444444; text-decoration:none;">' . $product['descriptions'][0]['products_name'] . '</a></div>
    <div style="font-size: 24px">' . $pprice_formated . '</div>
</div>';
                        } else {
                            $ptoduct .= $product['products_name'] . "-" . $pprice_formated . "\n";
                        }
                    }

                    if (EMAIL_USE_HTML == 'true') {
                        $count = count($ptoductArr);
                        $last = $count % $columns;
                        $ptoduct .= '<table  cellpadding="0" cellspacing="0" width="100%" border="0"><tr style="vertical-align: top">';
                        for ($i = 0; $i < ( $count - $last ); $i ++) {
                            if ($i != 0 && $i % $columns == 0) {
                                $ptoduct .= '</tr><tr style="vertical-align: top">';
                            }
                            $ptoduct .= '<td width="' . floor(100 / $columns) . '%">' . $ptoductArr[$i] . '</td>';
                        }
                        $ptoduct .= '</tr></table>';

                        $ptoduct .= '<table  cellpadding="0" cellspacing="0" width="100%" border="0"><tr style="vertical-align: top">';
                        for ($i; $i < $count; $i ++) {
                            $ptoduct .= '<td width="' . floor(100 / $last) . '%">' . $ptoductArr[$i] . '</td>';
                        }
                        $ptoduct .= '</tr></table>';
                    }

                    $upmline = $ptoduct;
                }

                $customerName = $row['customers_firstname'] . " " . $row['customers_lastname'];
                $outEmailAddr = '"' . $customerName . '" <' . $row['customers_email_address'] . '>';

                if (tep_not_null(RCS_EMAIL_COPIES_TO)) {
                    $outEmailAddr .= ', ' . RCS_EMAIL_COPIES_TO;
                }

                $email_params = array();
                $email_params['CUSTOMER_NAME'] = $customerName;
                $email_params['STORE_NAME'] = $platform->const_value('STORE_NAME');
                $email_params['STORE_OWNER_EMAIL_ADDRESS'] = $platform->const_value('STORE_OWNER_EMAIL_ADDRESS');
                $email_params['XSELLS'] = $xmline;
                $email_params['UPSELLS'] = $upmline;
                list( $email_subject, $email_text ) = \common\helpers\Mail::get_parsed_email_template($template_name, $email_params, $languages_id, $platform->getId());
                if ((int) $row->customer->customers_newsletter) {
                    \common\helpers\Mail::send(
                            $customerName, //$to_name
                            $row['customers_email_address'], //$to_email_address
                            $email_subject, //$email_subject
                            $email_text, //$email_text
                            $platform->const_value('STORE_OWNER'), //$from_email_name
                            $platform->const_value('STORE_OWNER_EMAIL_ADDRESS'), //$from_email_address
                            [], //$email_params
                            '', //$headers
                            false, //$attachments
                            ['add_br' => 'no']//$settings
                    );
                }
                \common\models\Orders::updateAll(['cross_up_email_send' => 1], ['orders_id' => explode(',', $row['orders_ids'])]);
            }
        }
        EXIT;
    }

    public function actionEbay() {
        if ($ext = \common\helpers\Acl::checkExtension('Ebay', 'cron')) {
            return $ext::cron();
        }
    }
    
    public function actionAmazon() {
        if ($ext = \common\helpers\Acl::checkExtension('Amazon', 'cron')) {
            return $ext::cron();
        }
    }
    
    public function actionPurchaseOrders() {
        if ($ext = \common\helpers\Acl::checkExtension('PurchaseOrders', 'cron')) {
            return $ext::cron();
        }
    }
}

// =============================================================

function google_pagespeed_url_process($url) {
  $check = tep_db_fetch_array(tep_db_query("select id from google_pagespeed_urls where google_pagespeed_url = '" . tep_db_input($url) . "' and to_days(date_processed) = to_days(now())"));
  if ( !($check['id'] > 0) ) {
    echo $result = file_get_contents(tep_href_link('google_pagespeed.php', 'page=' . str_replace(HTTP_SERVER . DIR_WS_HTTP_CATALOG, '', $url), 'NONSSL', false));
    if (preg_match("/Score: (\d+)\./i", $result, $matches)) {
        $score = $matches[1];
    }
    tep_db_query("insert into google_pagespeed_urls set google_pagespeed_url = '" . tep_db_input($url) . "', google_pagespeed_result = '" . tep_db_input($result) . "', date_processed = now(), google_pagespeed_score = '" . (int)$score . "'");
    exit;
  }
}

