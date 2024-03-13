<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2023, LeftCurlyBracket
 */
require_once 'abstract.php';

class LCB_Typesense_Shell extends Mage_Shell_Abstract
{
    /**
     * @interitDoc
     */
    public function run()
    {
        if ($this->getArg('reindex')) {
            session_start();
            Mage::app()->setCurrentStore(Mage_Core_Model_App::DISTRO_STORE_ID);
            $client = Mage::getModel('lcb_typesense/api')->getAdminClient();
            $collection = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect('sku')
                    ->addAttributeToSelect('thumbnail')
                    ->addAttributeToSelect('url_key');
            $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

            $attributes = Mage::getResourceModel('lcb_typesense/catalog_product_attribute_collection')->addSearchableAttributeFilter();
            foreach ($attributes as $attribute) {
                $collection->addAttributeToSelect($attribute->getAttributeCode());
            }
            Mage::dispatchEvent('lcb_typesense_catalog_product_collection_reindex_before', array('collection' => $collection));
            $this->writeln(sprintf("Found %s products to reindex", $collection->getSize()));
            foreach ($collection as $product) {
                try {
                    Mage::getSingleton('lcb_typesense/index')->reindex($product, $attributes);
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

$typesense = new LCB_Typesense_Shell();
$typesense->run();
