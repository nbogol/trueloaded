<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\quote;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\forms\registration\CustomerRegistration;

class FastOrder extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        if (USE_QUOTE_FAST_ORDER == 'True'){
            return IncludeTpl::widget(['file' => 'boxes/quote/fast-order.tpl', 'params' => array_merge($this->params, [
                'settings' => $this->settings,
                'id' => $this->id,
                'fastModel' => $this->params['enterModels']['fast']
            ])]);
        }
    }
}