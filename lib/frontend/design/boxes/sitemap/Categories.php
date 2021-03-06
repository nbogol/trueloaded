<?php
/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\sitemap;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\models\promotions\PromotionsBonusNotify;

class Categories extends Widget {
    
    public $file;
    public $params;
    public $settings;
    public $isAjax;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $osC_CategoryTree = new \common\classes\osC_CategoryTree;
        $description = trim($osC_CategoryTree->buildTree());
        if (substr($description,-5)=='</ul>'){
            $description = substr($description,0,-5). '<li><a href="' . Yii::$app->urlManager->createUrl(FILENAME_ADVANCED_SEARCH_RESULT) . '">' . TEXT_ALL_PRODUCTS . '</a></li>' .'</ul>';
        }

        return $description;
    }
    
}