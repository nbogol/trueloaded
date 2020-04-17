<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use common\classes\Migration;

/**
 * Class m191206_100804_vat_number_translations
 */
class m191206_100804_vat_number_translations extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('main', [
            'TEXT_VALID' => 'Valid',
            'TEXT_FORMAT_OK' => 'Format Ok',
            'TEXT_NOT_VALID' => 'Not Valid',
            'TEXT_OTHER_COUNTRY' => 'other Country'
        ]);
        $this->addTranslation('admin/main', [
            'TEXT_VALID' => 'Valid',
            'TEXT_FORMAT_OK' => 'Format Ok',
            'TEXT_NOT_VALID' => 'Not Valid',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        //echo "m191206_100804_vat_number_translations cannot be reverted.\n";

        //return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191206_100804_vat_number_translations cannot be reverted.\n";

        return false;
    }
    */
}
