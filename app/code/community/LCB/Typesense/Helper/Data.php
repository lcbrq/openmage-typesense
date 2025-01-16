<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2024, LeftCurlyBracket
 */
class LCB_Typesense_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @var string
     */
    private const XPATH_TYPESENSE_AUTOCOMPLETE_ENABLED = 'lcb_typesense/autocomplete/enabled';

    /**
     * @var string
     */
    private const XPATH_TYPESENSE_AUTOCOMPLETE_TYPE = 'lcb_typesense/autocomplete/type';

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
    private const REDIRECT_CATALOG_SEARCH = 'lcb_typesense/connection/redirect_catalog_search';

    /**
     * @var string
     */
    private const TYPESENSE_LOG_FILE = 'typesense.log';

    /**
     * @return bool
     */
    public function getAutocompleteEnabled($storeId = null): bool
    {
        return Mage::getStoreConfigFlag(self::XPATH_TYPESENSE_AUTOCOMPLETE_ENABLED, $storeId ?? Mage::app()->getStore()->getId());
    }

    /**
     * @return string|null
     */
    public function getAutocompleteType($storeId = null): ?string
    {
        return Mage::getStoreConfig(self::XPATH_TYPESENSE_AUTOCOMPLETE_TYPE, $storeId ?? Mage::app()->getStore()->getId());
    }

    /**
     * @return string
     */
    public function getCollectionName($storeId = null): string
    {
        return Mage::getStoreConfig(self::XPATH_TYPESENSE_SEARCH_COLLECTION_NAME, $storeId ?? Mage::app()->getStore()->getId());
    }

    /**
     * @return string|null
     */
    public function getHost($storeId = null): ?string
    {
        return Mage::getStoreConfig(self::XPATH_TYPESENSE_HOST, $storeId ?? Mage::app()->getStore()->getId());
    }

    /**
     * @return string
     */
    public function getAdminApiKey($storeId = null): string
    {
        $key = (string) Mage::getStoreConfig(self::XPATH_TYPESENSE_SEARCH_ADMIN_API_KEY, $storeId ?? Mage::app()->getStore()->getId());

        return Mage::getModel('core/encryption')->decrypt($key);
    }

    /**
     * @return string|null
     */
    public function getSearchOnlyApiKey($storeId = null): ?string
    {
        return Mage::getStoreConfig(self::XPATH_TYPESENSE_SEARCH_ONLY_API_KEY, $storeId ?? Mage::app()->getStore()->getId());
    }

    /**
     * @return bool
     */
    public function getRedirectCatalogSearch($storeId = null): bool
    {
        return Mage::getStoreConfigFlag(self::REDIRECT_CATALOG_SEARCH, $storeId ?? Mage::app()->getStore()->getId());
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
