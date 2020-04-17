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

class Navigation extends Widget {

    public $box_files_list = array();
    public $selectedMenu = array();

    private function buildTree($parent_id, $queryResponse, $rule = []) {
        $tree = [];
        if ($parent_id == 0) {
            $tree[] = [
                'box_type' => 0,
                'path' => 'index',
                'title' => TEXT_DASHBOARD,
                'filename' => 'dashboard'
            ];
            if (defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
                $tree[] = [
                    'box_type' => 1,
                    'path' => 'departments',
                    'title' => BOX_HEADING_DEPARTMENTS,
                    'acl' => 'BOX_HEADING_DEPARTMENTS',
                    'filename' => 'cubes',
                    'child' => [
                        [
                            'box_type' => 0,
                            'path' => 'departments',
                            'title' => BOX_HEADING_DEPARTMENTS,
                            'acl' => 'BOX_HEADING_DEPARTMENTS',
                            'filename' => ''
                        ],
                        [
                            'box_type' => 0,
                            'path' => 'departments-adminmembers',
                            'title' => BOX_DEPARTMENTS_MEMBERS,
                            'acl' => 'BOX_DEPARTMENTS_MEMBERS',
                            'filename' => ''
                        ],
                        [
                            'box_type' => 0,
                            'path' => 'departments-adminfiles',
                            'title' => BOX_DEPARTMENTS_BOXES,
                            'acl' => 'BOX_DEPARTMENTS_BOXES',
                            'filename' => ''
                        ],
                    ],
                ];
            }
        }
        foreach ($queryResponse as $response) {
            if ($response['parent_id'] == $parent_id) {
                $ruleTmp = $rule;
                $ruleTmp[] = $response['title'];
                if (\common\helpers\Acl::rule($ruleTmp)) {// enabled
                    
                    if ($response['box_type'] == 1) {
                        $response['child'] = $this->buildTree($response['box_id'], $queryResponse, $ruleTmp);
                        if ($response['title'] == 'BOX_HEADING_CONFIGURATION' && defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
                            $response['child'][] = [
                                'box_type' => 0,
                                'path' => 'configuration/index?groupid=26',
                                'title' => 'Control panel',
                                'filename' => ''
                            ];
                        }
                    }
                    if (defined($response['title'])) {
                        eval('$currentName =  ' . $response['title'] . ';');
                    } else {
                        $currentName = $response['title'];
                    }
                    $response['acl'] = $response['title'];
                    $response['title'] = $currentName;
                    
                    $response['dis_module'] = false;
                    $response['disabled'] = false;
                    if (!empty($response['acl_check'])) {
                        list($moduleName, $actionName) = explode(',', $response['acl_check']);
                        if (false === \common\helpers\Acl::checkExtensionAllowed($moduleName, $actionName)) {
                            $response['disabled'] = true;
                            $response['dis_module'] = true;
                        } else if (false === \common\helpers\Acl::checkExtensionAllowed($moduleName, $actionName)) {
                            $response['dis_module'] = true;
                        }
                    }
                    
                    if (!empty($response['config_check'])) {
                        list($configKey, $configValue) = explode(',', $response['config_check']);
                        if (!defined($configKey) || constant($configKey) != $configValue) {
                            $response['dis_module'] = true;
                        }
                    }
                    
                    $tree[] = $response;
                    
                }
                
            }
        }
        /*if ($parent_id == 94 && defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
            $tree[] = [
                'box_type' => 0,
                'path' => 'configuration/index?groupid=26',
                'title' => 'Control panel',
                'filename' => ''
            ];
        }*/
        return $tree;
    }
    
    private function parse_menu() {
        $totalRecords = \common\models\AdminBoxes::find()->count();
        if ($totalRecords > 0) {
            return false;
        }
        
        $path = \Yii::getAlias('@webroot');
        $filename = $path . DIRECTORY_SEPARATOR . 'includes' .DIRECTORY_SEPARATOR . 'default_menu.xml';
        
        $xmlfile = file_get_contents($filename);
        $ob= simplexml_load_string($xmlfile);
        if (isset($ob)) {
            tep_db_query("TRUNCATE TABLE admin_boxes;");
            \common\helpers\MenuHelper::importAdminTree($ob);
        }
        return true;
    }

    public function run() {

        $this->parse_menu();
        
        if (isset(\Yii::$app->controller->acl)) {
            $this->selectedMenu = \Yii::$app->controller->acl;
        } else {
            $this->selectedMenu = array("index");
        }
        
        $queryResponse = \common\models\AdminBoxes::find()
                ->orderBy(['sort_order' => SORT_ASC])
                ->asArray()
                ->all(); 
        
        $currentMenu = $this->buildTree(0, $queryResponse, []);
        
        return $this->render('Navigation', [
            'context' => $this,
            'currentMenu' => $currentMenu,
        ]);
    }

}

