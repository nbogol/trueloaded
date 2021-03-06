<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\api\models\Soap;


/**
 * Class ProductDescription
 * @package common\api\models
 * @soap-wsdl <xsd:sequence>
 * @soap-wsdl <xsd:element name="text" type="xsd:string"/>
 * @soap-wsdl </xsd:sequence>
 * @soap-wsdl <xsd:attribute name="language" type="xsd:string" use="required"/>
 */

class LanguageValue extends SoapModel
{
    public $language;

    public $text;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }


}