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


class ArrayOfProductRef
{

    /**
     * @var \common\api\models\Soap\Products\ProductRef Array of ProductRef {nillable = 0, minOccurs=1, maxOccurs = unbounded}
     * @soap
     */
    public $product = [];
}