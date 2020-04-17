<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\api\models\Soap\Products;


use common\api\models\Soap\SoapModel;

class ArrayOfXSell extends SoapModel
{

    /**
     * @var \common\api\models\Soap\Products\XSell XSell {nillable = 0, minOccurs=0, maxOccurs = unbounded}
     * @soap
     */
    public $xsell = [];

    public function __construct(array $config = [])
    {
        foreach( $config as $key=>$data ) {
            $this->xsell[] = new XSell($data);
        }
        parent::__construct([]);
    }


}