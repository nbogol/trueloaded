<?php
/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\design\boxes;

use Yii;
use yii\base\Widget;

class Menu extends Widget
{

  public $id;
  public $params;
  public $settings;
  public $visibility;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    $menus = array();
    $sql = tep_db_query("select * from " . TABLE_MENUS);
    while ($row=tep_db_fetch_array($sql)){
      $menus[] = $row;
    }

    return $this->render('menu.tpl', [
      'id' => $this->id, 'params' => $this->params, 'menus' => $menus, 'settings' => $this->settings,
      'visibility' => $this->visibility,
    ]);
  }
}