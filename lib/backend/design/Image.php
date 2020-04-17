<?php
/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\design;

use Yii;
use yii\base\Widget;

class Image extends Widget
{
  public $name;
  public $value;
  public $upload;
  public $delete = '';
  public $type;
  public $acceptedFiles;
  public $path;
  public $unlink = true;

  public function init(){
    parent::init();
  }

  public function run()
  {

    $file = \frontend\design\Info::themeImage($this->value, false, false);
    if (!$file){
      $this->value = 0;
    }

    if ($this->type == 'video' && !$this->acceptedFiles) {
      $this->acceptedFiles = 'video/mpeg,video/mp4,video/ogg,video/quicktime,' . 
          'video/webm,video/x-ms-wmv,video/x-flv,video/3gpp,video/3gpp2';
    }
    
    return $this->render('image.tpl', [
      'name' => $this->name,
      'value' => $this->value,
      'upload' => $this->upload,
      'delete' => $this->delete,
      'unlink' => $this->unlink,
      'type' => ($this->type ? $this->type : ''),
      'file' => Yii::getAlias('@web') . '/../' . $file,
      'acceptedFiles' => $this->acceptedFiles,
      'path' => $this->path,
    ]);
  }
}