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
use backend\components\Information;

class PopupsController extends Sceleton {

  public function actionEditor() {
    $this->layout = 'popup.tpl';
    return $this->render('editor.tpl', []);

  }

    public function actionPriceFormulaEditor()
    {
        $formula_input = Yii::$app->request->get('formula_input','');
        $formula_input = Yii::$app->request->post('formula_input',$formula_input);

        $allowed_params = Yii::$app->request->get('allowed_params','');
        $allowed_params = Yii::$app->request->post('allowed_params',$allowed_params);

        $allowParams = [
            //CODE => 'label',
            'PRICE' => 'PRICE',
            'DISCOUNT' => 'DISCOUNT',
            'SURCHARGE' => 'SURCHARGE',
            //'MARGIN' => 'MARGIN',
        ];
        if ( !empty($allowed_params) ) {
            $allowed_params_array = explode(',',$allowed_params);
            foreach (array_keys($allowParams) as $key) {
                if ( !in_array($key,$allowed_params_array) ) unset($allowParams[$key]);
            }
        }

        $this->layout = 'popup.tpl';
        return $this->render('price_formula.tpl', [
            'formula_input' => $formula_input,
            'allowParams' => $allowParams,
        ]);
    }

}
