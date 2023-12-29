<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2023, LeftCurlyBracket
 */
require_once 'abstract.php';

class LCB_TypeSense_Shell extends Mage_Shell_Abstract
{
    /**
     * @interitDoc
     */
    public function run()
    {
        if ($this->getArg('reindex')) {
            Mage::app()->setCurrentStore(Mage_Core_Model_App::DISTRO_STORE_ID);
            $client = Mage::getModel('lcb_typesense/api')->getAdminClient();
            $collection = Mage::getModel('catalog/product')->getCollection();
            $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

            $attributes = Mage::getResourceModel('lcb_typesense/catalog_product_attribute_collection')->addSearchableAttributeFilter();
            foreach ($attributes as $attribute) {
                $collection->addAttributeToSelect($attribute->getAttributeCode());
            }
            Mage::dispatchEvent('lcb_typesense_catalog_product_collection_reindex_before', array('collection' => $collection));
            foreach ($collection as $product) {
                Mage::dispatchEvent('lcb_typesense_catalog_product_reindex_before', array('product' => $product));
                try {
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
                    $response = $client->collections[Mage::helper('lcb_typesense')->getCollectionName()]->documents->upsert($payload);
                    $this->writeln($product->getSku());
                } catch (Exception $e) {
                    $this->writeln($e->getMessage());
                } catch (Error $e) {
                    $this->writeln($e->getMessage());
                }
            }
        }
    }

    /**
     * @param  string $message
     * @return void
     */
    private function writeln($message)
    {
        echo "$message\n";
    }
}

$typesense = new LCB_TypeSense_Shell();
$typesense->run();
