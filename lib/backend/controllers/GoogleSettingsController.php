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
use common\components\Socials;

class GoogleSettingsController extends Sceleton {

    public $acl = ['TEXT_SETTINGS', 'BOX_GOOGLE_SETTINGS', 'BOX_GOOGLE_MAIN_SETTINGS'];

    public function __construct($id, $module) {
        parent::__construct($id, $module);
    }

    public function actionIndex() {

        $this->selectedMenu = array('settings', 'google-settings', 'google-settings');
        $this->navigation[] = array('link' => \Yii::$app->urlManager->createUrl('google-settings/index'), 'title' => BOX_GOOGLE_MAIN_SETTINGS);
        $this->view->headingTitle = BOX_GOOGLE_MAIN_SETTINGS;
        
        $googleTools = new \common\components\GoogleTools();
        
        $selected = null;
        $messages = [];
        if (Yii::$app->request->isPost){
            $provider = $googleTools->getProvider(Yii::$app->request->post('provider'));
            if ($provider){
                $platform_id = (int)Yii::$app->request->post('platform_id');
                if ($googleTools->updateProviderConfig($provider, Yii::$app->request->post($provider->getClassName()), $platform_id)){
                    $messages[] = [
                        'type' => 'alert-success',
                        'info' => $provider->getName() .' saved successfully',
                    ];
                } else {
                    $messages[] = [
                        'type' => 'alert-danger',
                        'info' => $provider->getName() . ' error',
                    ];
                }
            }
        }
        
        $providers = [
            'independed' => [
                $googleTools->getMapProvider(),
                $googleTools->getCaptchaProvider(),
            ],
            'platformed' => [
                
            ],
            'selected' => $selected,
        ];
        
        $platforms = \common\classes\platform::getList(false, true);
        foreach([$googleTools->getAnalyticsProvider(), ] as $_provider){
            $_provider->platforms = $platforms;
            $providers['platformed'][] = $_provider;
        }
        
        return $this->render('index', [
            'providers' => $providers,
            'messages' => $messages,
            'platforms' => $platforms,
        ]);
    }

}
