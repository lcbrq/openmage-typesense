<?php

/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2024, LeftCurlyBracket
 */

use LCB_Typesense_Model_Resource_Catalog_Product_Attribute_Collection as AttributesCollection;
use Mage_Catalog_Model_Product as Product;
use Typesense\Client;

class LCB_Typesense_Model_Index
{
    protected ?Client $client;

    /**
     * @param  Product                   $product
     * @param  AttributesCollection|null $attributes
     * @return void
     * @throws Exception
     */
    public function reindex(Product $product, ?AttributesCollection $attributes = null): void
    {
        if (!$attributes) {
            $attributes = Mage::getResourceModel('lcb_typesense/catalog_product_attribute_collection')->addSearchableAttributeFilter();
        }

        Mage::dispatchEvent('lcb_typesense_catalog_product_reindex_before', array('product' => $product, 'attributes' => $attributes));

        $requestPath = $product->getRequestPath();
        $payload = new Varien_Object([
            'id' => (string) $product->getId(),
            'sku' => (string) $product->getSku(),
            'url_key' => (string) $product->getUrlKey(),
            'request_path' => $requestPath ? (string) $product->getRequestPath() : 'catalog/product/view/id/' . $product->getId(),
            'category_ids' => $product->getCategoryIds(),
            'thumbnail' => $product->getThumbnail(),
        ]);

        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            if ($attribute->getBackendType() === 'decimal') {
                $payload->setData($code, (float) $product->getData($code));
            } elseif (in_array($code, ['status', 'visibility'])) {
                $payload->setData($code, (int) $product->getData($code));
            } elseif ($attribute->getFrontendInput() === 'select') {
                $payload->setData($code, (string) $product->getAttributeText($code));
            } else {
                $payload->setData($code, (string) $product->getData($code));
            }
        }

        Mage::dispatchEvent('lcb_typesense_catalog_product_upsert_before', array('product' => $product, 'payload' => $payload));

        $this->getClient()->collections[Mage::helper('lcb_typesense')->getCollectionName()]->documents->upsert($payload->getData());
    }

    protected function getClient(): Client
    {
        if (empty($this->client)) {
            $this->client = Mage::getModel('lcb_typesense/api')->getAdminClient();
        }

        return $this->client;
    }
}
