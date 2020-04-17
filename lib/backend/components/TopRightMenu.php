<?php
/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\components;

use yii\base\Widget;

class TopRightMenu extends Widget {

    
    public function run() {
        return $this->render('TopRightMenu');
    }

}

