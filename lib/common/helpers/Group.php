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

class Group {

/**
 *
 * @staticvar array $groups
 * @staticvar array $e_groups
 * @param int $code
 * @return array
 */
    public static function get_customer_groups($code = '') {
      static $groups = false;
      static $e_groups = false;

      /** @var \common\extensions\UserGroups\UserGroups $ext */
      $ext = \common\helpers\Acl::checkExtension('UserGroups', 'getGroupsArray');

      if (!$ext) {
        
        if (!is_array($groups)) {
            $groups = \common\models\Groups::find()->orderBy('groups_name')->indexBy('groups_id')->asArray()->all();
            if (!is_array($groups)) {
              $groups = [];
            }
        }

        $ret = $groups;

      } else {

        if (!is_array($e_groups[$code])) {
          $e_groups[$code] = $ext::getGroupsArray($code);
          if (!is_array($e_groups[$code])) {
            $e_groups[$code] = [];
          }
        }

        $ret = $e_groups[$code];

      }
      
      return $ret;

    }

    /**
     * uses cached get_customer_groups
     * @param int $code (type id - extra groups extension)
     * @return array (id=>name)
     */
    public static function get_customer_groups_list($code = '') {
        return \yii\helpers\ArrayHelper::map(self::get_customer_groups($code), 'groups_id', 'groups_name');
    }

/**
 * get group name by id
 * @param type $id
 * @return string
 */
    public static function get_user_group_name($id) {
        if ($id == 0) {
            $ret = TEXT_NONE;
        } else {
          $group = \common\models\Groups::findOne($id);
          if ($group) {
            $ret = $group->groups_name;
          }
        }
        return $ret;
    }

}
