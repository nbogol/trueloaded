<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\design\boxes\account;

use Yii;
use yii\base\Widget;

class AccountLink extends Widget
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
        $activeArr = explode(',', $this->settings[0]['active_link']);
        $settings = tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . $this->settings['theme_name'] . "' and setting_group = 'added_page' and setting_name = 'account'");
        $links = [];
        $activeLinks = [];
        while ($item = tep_db_fetch_array($settings)) {
            $links[] = $item['setting_value'];
            $activeLinks[] = [
                'name' => $item['setting_value'],
                'active' => in_array($item['setting_value'], $activeArr)
            ];
        }

        return $this->render('../../views/account/account-link.tpl', [
            'id' => $this->id,
            'params' => $this->params,
            'settings' => $this->settings,
            'visibility' => $this->visibility,
            'links' => $links,
            'activeLinks' => $activeLinks,
        ]);
    }
}