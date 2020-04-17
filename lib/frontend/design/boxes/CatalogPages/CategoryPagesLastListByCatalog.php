<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\CatalogPages;

use common\classes\Images;
use common\classes\platform;
use frontend\models\repositories\CatalogPagesReadRepository;
use frontend\models\repositories\InformationReadRepository;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class CategoryPagesLastListByCatalog extends Widget
{

	public $file;
	public $params;
	public $content;
	public $settings;

	private $catalogPagesId = 0;
	private $limit = 0;
	private $catalogPagesRepository;
	private $informationRepository;
    private $platformId;
	public function __construct( CatalogPagesReadRepository $catalogPagesRepository, InformationReadRepository $informationRepository, $config = [])
	{
		parent::__construct($config);
		$this->catalogPagesRepository = $catalogPagesRepository;
		$this->informationRepository = $informationRepository;
	}

	public function init()
	{
		parent::init();
		$this->catalogPagesId = (int)$this->settings[0]['selectCatalogPageLastListById'];
        $this->limit = (int)$this->settings[0]['limitInformationLastListByIdPage'];
        if($this->limit < 1){
            $this->limit = 6;
        }
        $this->platformId = (bool)platform::currentId()?(int)platform::currentId():(int)platform::currentId();
	}

	public function run()
	{
		$languages_id = \Yii::$app->settings->get('languages_id');

		if($this->catalogPagesId < 1){
			return '';
		}
        $catalogPage = $this->catalogPagesRepository->getShortInfo($this->catalogPagesId,$languages_id,true);
        if(empty($catalogPage)){
            return '';
        }
        $infoPages = $this->informationRepository->getLastList($languages_id,$this->platformId,$this->limit,true,$catalogPage['catalog_pages_id'],true);
		$imagePageCatalogPath = Images::getWSCatalogImagesPath().$this->catalogPagesRepository->imagesLocation();
        $imageInformationPath = Images::getWSCatalogImagesPath().$this->informationRepository->imagesLocation();
		return IncludeTpl::widget(['file' => 'boxes/category-pages/category-pages-last-list-by-id.tpl', 'params' => [
			'infoPages' => $infoPages,
            'catalogPage' => $catalogPage,
			'imagePageCatalogPath' => $imagePageCatalogPath,
            'imageInformationPath' => $imageInformationPath,
		]]);
	}
}