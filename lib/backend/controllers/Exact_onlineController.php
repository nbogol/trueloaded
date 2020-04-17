<?php
/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\controllers;

use Yii;

class Exact_onlineController extends Sceleton {

  public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_EXACT_ONLINE'];

  public function actionIndex() {
    $this->selectedMenu = array('settings', 'exact_online');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('exact_online/index'), 'title' => HEADING_TITLE);
    $this->view->headingTitle = HEADING_TITLE;

    if ($ext = \common\helpers\Acl::checkExtension('ExactOnline', 'adminIndex')) {
      return $ext::adminIndex();
    }

    return $this->render('index');
  }

  public function actionUpdate() {
    $messageStack = \Yii::$container->get('message_stack');
    \common\helpers\Translation::init('admin/exact_online');

    if ($ext = \common\helpers\Acl::checkExtension('ExactOnline', 'adminUpdate')) {
      $message = $ext::adminUpdate();
    } else {
      $message = 'Error: Please Install Exact Online Extension.';
    }

    list($type, ) = explode(' ', str_replace(':', ' ', $message));
    $messageStack->add_session($message, 'header', strtolower($type));

    tep_redirect(tep_href_link('exact_online'));
  }

  public function actionRun() {
    $messageStack = \Yii::$container->get('message_stack');
    \common\helpers\Translation::init('admin/exact_online');

    if ($ext = \common\helpers\Acl::checkExtension('ExactOnline', 'runFeedNow')) {
      $message = $ext::runFeedNow($_GET['feed']);
    }

    list($type, ) = explode(' ', str_replace(':', ' ', $message));
    $messageStack->add_session($message, 'header', strtolower($type));

    tep_redirect(tep_href_link('exact_online'));
  }

  public function actionProducts() {
    $messageStack = \Yii::$container->get('message_stack');
    \common\helpers\Translation::init('admin/exact_online');

    if ($ext = \common\helpers\Acl::checkExtension('ExactOnline', 'runFeedNow')) {
      $message = $ext::runFeedNow('exact_run_products');
    }

    list($type, ) = explode(' ', str_replace(':', ' ', $message));
    $messageStack->add_session($message, 'header', strtolower($type));

    tep_redirect(tep_href_link('exact_online'));
  }

  public function actionStock() {
    $messageStack = \Yii::$container->get('message_stack');
    \common\helpers\Translation::init('admin/exact_online');

    if ($ext = \common\helpers\Acl::checkExtension('ExactOnline', 'runFeedNow')) {
      $message = $ext::runFeedNow('exact_run_products_qty');
    }

    list($type, ) = explode(' ', str_replace(':', ' ', $message));
    $messageStack->add_session($message, 'header', strtolower($type));

    tep_redirect(tep_href_link('exact_online'));
  }

  public function actionOrders() {
    $messageStack = \Yii::$container->get('message_stack');
    \common\helpers\Translation::init('admin/exact_online');

    if ($ext = \common\helpers\Acl::checkExtension('ExactOnline', 'runFeedNow')) {
      $message = $ext::runFeedNow('exact_run_orders');
    }

    list($type, ) = explode(' ', str_replace(':', ' ', $message));
    $messageStack->add_session($message, 'header', strtolower($type));

    tep_redirect(tep_href_link('exact_online'));
  }

  public function actionOauth() {
    \common\helpers\Translation::init('admin/exact_online');

    if ($ext = \common\helpers\Acl::checkExtension('ExactOnline', 'adminOauth')) {
      $ext::adminOauth();
    }

    tep_redirect(tep_href_link('exact_online'));
  }
}
