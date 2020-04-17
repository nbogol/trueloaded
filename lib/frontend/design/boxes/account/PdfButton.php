<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\account;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class PdfButton extends Widget
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
        if ($this->params['customers_id']) {
            $customersId = $this->params['customers_id'];
        } else {
            $customersId = Yii::$app->user->id;
        }

        return IncludeTpl::widget(['file' => 'boxes/account/pdf-button.tpl', 'params' => [
            'settings' => $this->settings,
            'id' => $this->id,
            'customersId' => $customersId,
        ]]);
    }
}