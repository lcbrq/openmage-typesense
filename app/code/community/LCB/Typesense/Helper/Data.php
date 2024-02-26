<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2023, LeftCurlyBracket
 */
class LCB_Typesense_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @var string
     */
    private const XPATH_TYPESENSE_HOST = 'lcb_typesense/connection/host';

    /**
     * @var string
     */
    private const XPATH_TYPESENSE_SEARCH_ADMIN_API_KEY = 'lcb_typesense/connection/admin_api_key';

    /**
     * @var string
     */
    private const XPATH_TYPESENSE_SEARCH_ONLY_API_KEY = 'lcb_typesense/connection/search_only_api_key';

    /**
     * @var string
     */
    private const XPATH_TYPESENSE_SEARCH_COLLECTION_NAME = 'lcb_typesense/connection/collection_name';

    /**
     * @var string
     */
    private const TYPESENSE_LOG_FILE = 'typesense.log';

    /**
     * @return string
     */
    public function getCollectionName(): string
    {
        return Mage::getStoreConfig(self::XPATH_TYPESENSE_SEARCH_COLLECTION_NAME, Mage::app()->getStore()->getId());
    }

    /**
     * @return string|null
     */
    public function getHost(): ?string
    {
        return Mage::getStoreConfig(self::XPATH_TYPESENSE_HOST, Mage::app()->getStore()->getId());
    }

    /**
     * @return string
     */
    public function getAdminApiKey(): string
    {
        $key = (string) Mage::getStoreConfig(self::XPATH_TYPESENSE_SEARCH_ADMIN_API_KEY, Mage::app()->getStore()->getId());

        return Mage::getModel('core/encryption')->decrypt($key);
    }

    /**
     * @return string|null
     */
    public function getSearchOnlyApiKey(): ?string
    {
        return Mage::getStoreConfig(self::XPATH_TYPESENSE_SEARCH_ONLY_API_KEY, Mage::app()->getStore()->getId());
    }

    /**
     * @param  string $message
     * @return void
     */
    public function log($message)
    {
        Mage::log($message, null, self::TYPESENSE_LOG_FILE, true);
    }
}
