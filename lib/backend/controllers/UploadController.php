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
/**
 *
 */
class UploadController extends Sceleton
{
  /**
   *
   */
  public function actionIndex()
  {
    if (isset($_FILES['file'])) {
      $path = \Yii::getAlias('@webroot');
      $path .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
      $uploadfile = $path . $this->basename($_FILES['file']['name']);

      if ( !is_writeable(dirname($uploadfile)) ) {
          $response = ['status' => 'error', 'text'=> 'Directory "'.$this->basename(\Yii::getAlias('@webroot')).DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR.'" not writeable'];
      }elseif(!is_uploaded_file($_FILES['file']['tmp_name']) || filesize($_FILES['file']['tmp_name'])==0){
          $response = ['status' => 'error', 'text'=> 'File upload error'];
      }else
      if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
        $text = '';
        $response = ['status' => 'ok', 'text' => $text];
      } else {
        $response = ['status' => 'error'];
      }
    }
    echo json_encode($response);
  }

    public function actionScreenshot()
    {
        $post = tep_db_prepare_input(Yii::$app->request->post());
        if (isset($post['image'])) {
            $path = \Yii::getAlias('@webroot');
            $path .= DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
            $path .= 'themes' . DIRECTORY_SEPARATOR . $post['theme_name'];

            if (!file_exists($path)) {
                mkdir($path);
            }

            if ($post['file_name']){
                $file_name = $post['file_name'];
                $folders = explode('/', $file_name);
                $path2 = '';
                for ($i = 0; $i < count($folders) - 1; $i++){
                    $path2 .= $folders[$i] . DIRECTORY_SEPARATOR;
                    if (!file_exists($path . DIRECTORY_SEPARATOR . $path2)){
                        mkdir($path . DIRECTORY_SEPARATOR . $path2);
                    }
                }
            } else {
                $file_name = 'screenshot';
            }
            file_put_contents($path . DIRECTORY_SEPARATOR . $file_name . '.png', base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $post['image'])));
        }
        echo $post['image'];
    }

    protected function basename($param, $suffix=null,$charset = 'utf-8'){
        if ( $suffix ) {
            $tmpstr = ltrim(mb_substr($param, mb_strrpos($param, DIRECTORY_SEPARATOR, null, $charset), null, $charset), DIRECTORY_SEPARATOR);
            if ( (mb_strpos($param, $suffix, null, $charset)+mb_strlen($suffix, $charset) )  ==  mb_strlen($param, $charset) ) {
                return str_ireplace( $suffix, '', $tmpstr);
            } else {
                return ltrim(mb_substr($param, mb_strrpos($param, DIRECTORY_SEPARATOR, null, $charset), null, $charset), DIRECTORY_SEPARATOR);
            }
        } else {
            return ltrim(mb_substr($param, mb_strrpos($param, DIRECTORY_SEPARATOR, null, $charset), null, $charset), DIRECTORY_SEPARATOR);
        }
    }



}
