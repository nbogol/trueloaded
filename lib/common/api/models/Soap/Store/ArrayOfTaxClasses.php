<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\api\models\Soap\Store;


class ArrayOfTaxClasses
{

    /**
     * @var \common\api\models\Soap\Store\TaxClass TaxClass {nillable = 0, minOccurs=0, maxOccurs = unbounded}
     * @soap
     */
    public $tax_class = [];


}