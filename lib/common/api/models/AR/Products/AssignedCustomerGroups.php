<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\api\models\AR\Products;


use backend\models\EP\Tools;
use common\api\models\AR\EPMap;

class AssignedCustomerGroups extends EPMap
{
    protected $hideFields = [
        'products_id',
    ];

    public static function tableName()
    {
        return 'groups_products';
    }

    public static function primaryKey()
    {
        return ['products_id', 'groups_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;
        parent::parentEPMap($parentObject);
    }


    public function matchIndexedValue(EPMap $importedObject)
    {
        if ( !is_null($importedObject->groups_id) && !is_null($this->groups_id) && $importedObject->groups_id==$this->groups_id ){
            $this->pendingRemoval = false;
            return true;
        }
        return false;
    }

    public function exportArray(array $fields = [])
    {
        $data = parent::exportArray($fields);
        $data['groups_name'] = Tools::getInstance()->getCustomerGroupName($this->groups_id);
        return $data;
    }

    public function importArray($data)
    {
        if (isset($data['groups_name'])) {
            $data['groups_id'] = Tools::getInstance()->getCustomerGroupId($data['groups_name']);
        }
        return parent::importArray($data);
    }
}