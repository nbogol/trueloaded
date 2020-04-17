<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\services;


use common\models\repositories\ConfigurationRepository;

final class ConfigurationService
{
    /** @var ConfigurationRepository */
    private $configurationRepository;

    public function __construct(ConfigurationRepository $configurationRepository)
    {
        $this->configurationRepository = $configurationRepository;
    }

    public function isDefaultOrderStatusIdForOnlinePayment(int $orderStatusId): bool
    {
        return (defined('DEFAULT_ONLINE_PAYMENT_ORDERS_STATUS_ID') && (int) DEFAULT_ONLINE_PAYMENT_ORDERS_STATUS_ID === $orderStatusId);
    }

    /**
     * @param string $key
     * @param string $value
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function updateByKey(string $key, string $value)
    {
        return $this->configurationRepository->updateByKey($key, $value);
    }

    /**
     * @param int $orderStatusId
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function setDefaultOrderStatusIdForOnlinePayment(int $orderStatusId)
    {
        return $this->configurationRepository->updateByKey('DEFAULT_ONLINE_PAYMENT_ORDERS_STATUS_ID', (string) $orderStatusId);
    }
}
