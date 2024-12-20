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
    public function getAutocompleteEnabled(): bool
    {
        return Mage::getStoreConfigFlag(self::XPATH_TYPESENSE_AUTOCOMPLETE_ENABLED, Mage::app()->getStore()->getId());
    }

    /**
     * @return string|null
     */
    public function getAutocompleteType(): ?string
    {
        return Mage::getStoreConfig(self::XPATH_TYPESENSE_AUTOCOMPLETE_TYPE, Mage::app()->getStore()->getId());
    }

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
     * @return bool
     */
    public function getRedirectCatalogSearch(): bool
    {
        return Mage::getStoreConfigFlag(self::REDIRECT_CATALOG_SEARCH, Mage::app()->getStore()->getId());
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
