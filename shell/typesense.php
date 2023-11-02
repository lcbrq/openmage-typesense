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
            $collection->addAttributeToSelect('*');
            Mage::dispatchEvent('lcb_typesense_catalog_product_collection_reindex_before', array('collection' => $collection));
            foreach ($collection as $product) {
                try {
                    $response = $client->collections[Mage::helper('lcb_typesense')->getCollectionName()]->documents->upsert(
                        [
                            'id' => (string) $product->getId(),
                            'name' => (string) $product->getName(),
                            'sku' => (string) $product->getSku(),
                            'short_description' => (string) $product->getShortDescription(),
                            'description' => (string) $product->getDescription(),
                        ]
                    );

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
