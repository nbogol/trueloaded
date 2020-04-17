<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\models\queries;

use common\models\Inventory;
use yii\db\ActiveQuery;

/**
 * Class InventoryQuery
 * @see Inventory
 */
class InventoryQuery extends ActiveQuery
{
    public function existent($exist = true){
        return $this->andWhere(['non_existent' => $exist? 0 : 1]);
    }
    
    public function restriction($aliases = ['i']){
        $ids = \yii\helpers\ArrayHelper::getColumn(\common\models\ProductsStockIndication::getHidden(), 'stock_indication_id');
        if ($ids){
            foreach($aliases as $alias){
                $this->andWhere(['not in', $alias.'.stock_indication_id', $ids]);
            }
        }
        return $this;
    }
}
