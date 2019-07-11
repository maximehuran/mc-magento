<?php

$installer = $this;

try {
    /* Mailchimp field change and migration */
    $installer->run(
        "ALTER TABLE `{$this->getTable('mailchimp_stores')}`
        CHANGE COLUMN `apikey` `apikey` VARCHAR(128) NOT NULL;"
    );

    $installer->run(
        "TRUNCATE `{$this->getTable('mailchimp_stores')}`;"
    );

    $configDataCollection = Mage::getModel('core/config_data')
        ->getCollection()
        ->addFieldToFilter('path', 'mailchimp/general/apikey');

    foreach ($configDataCollection as $data) {
        $dbApiKey = $data->getValue();
        if (strpos($dbApiKey, '-us') !== false && strlen($dbApiKey) === 37) {
            $encryptedApiKey = Mage::helper('core')->encrypt($dbApiKey);
            $installer->setConfigData(
                'mailchimp/general/apikey',
                $encryptedApiKey,
                $data->getScope(),
                $data->getScopeId()
            );
        }
    }

    /* Mandrill migration */
    $configDataCollection = Mage::getModel('core/config_data')
        ->getCollection()
        ->addFieldToFilter('path', 'mandrill/general/apikey');

    foreach ($configDataCollection as $data) {
        $dbApiKey = $data->getValue();
        if (strlen($dbApiKey) == 22) {
            $encryptedApiKey = Mage::helper('core')->encrypt($dbApiKey);
            $installer->setConfigData(
                'mandrill/general/apikey',
                $encryptedApiKey,
                $data->getScope(),
                $data->getScopeId()
            );
        }
    }
} catch (Exception $e) {
    Mage::log($e->getMessage(), null, 'MailChimp_Errors.log', true);
}


$installer->endSetup();
