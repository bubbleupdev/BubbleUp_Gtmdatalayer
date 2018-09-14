<?php
class BubbleUp_Gtmdatalayer_Helper_Config extends Mage_Core_Helper_Abstract
{
    const XML_PATH_CONSENT_VARIABLE_NAME = 'google/gtmdatalayer/consent_variable_name';
    const XML_PATH_REQUIRE_CONSENT       = 'google/gtmdatalayer/require_consent';
    const XML_PATH_CONTAINER_IDS         = 'google/gtmdatalayer/gtm_container_ids';
    const XML_PATH_ENABLE_TAG            = 'google/gtmdatalayer/enable';

    public function getConsentVariableName()
    {
        return Mage::getStoreConfig(self::XML_PATH_CONSENT_VARIABLE_NAME);
    }

    public function getRequireConsent()
    {
        return Mage::getStoreConfig(self::XML_PATH_REQUIRE_CONSENT);
    }


    public function getContainerIdsFromConfigAsArray()
    {
        $ids = Mage::getStoreConfig(self::XML_PATH_CONTAINER_IDS);
        $ids = explode(',', $ids);
        $ids = array_map('trim', $ids);

        return $ids;
    }

    public function getEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_ENABLE_TAG);
    }
}
