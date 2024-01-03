<?php

/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2024, LeftCurlyBracket
 */

use LCB_TypeSense_Model_Resource_Catalog_Product_Attribute_Collection as AttributesCollection;
use Mage_Catalog_Model_Product as Product;
use Typesense\Client;

class LCB_TypeSense_Model_Index
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

        $payload = [
            'id' => (string) $product->getId(),
            'sku' => (string) $product->getSku(),
            'category_ids' => $product->getCategoryIds(),
        ];
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            if ($attribute->getBackendType() === 'decimal') {
                $payload[$code] = (float) $product->getData($code);
            } elseif (in_array($code, ['status', 'visibility'])) {
                $payload[$code] = (int) $product->getData($code);
            } elseif ($attribute->getFrontendInput() === 'select') {
                $payload[$code] = (string) $product->getAttributeText($code);
            } else {
                $payload[$code] = (string) $product->getData($code);
            }
        }

        $this->getClient()->collections[Mage::helper('lcb_typesense')->getCollectionName()]->documents->upsert($payload);
    }

    protected function getClient(): Client
    {
        if (empty($this->client)) {
            $this->client = Mage::getModel('lcb_typesense/api')->getAdminClient();
        }

        return $this->client;
    }
}
