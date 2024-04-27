<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2023, LeftCurlyBracket
 */
require_once 'abstract.php';

class LCB_Typesense_Shell extends Mage_Shell_Abstract
{
    /**
     * @var LCB_Typesense_Model_Resource_Catalog_Product_Attribute_Collection|null
     */
    protected $attributes = null;

    /**
     * @interitDoc
     */
    public function run()
    {
        session_start();

        $storeId = $this->getArg('store-id') ? (int) $this->getArg('store-id') : Mage_Core_Model_App::DISTRO_STORE_ID;
        Mage::app()->setCurrentStore($storeId);

        if ($this->getArg('reindex-all')) {
            $collection = $this->getProductCollection();
            $this->writeln(sprintf("Found %s products to reindex", $collection->getSize()));
            foreach ($collection as $product) {
                $this->updateSingleProduct($product);
            }

            return true;
        }

        if ($fromId = $this->getArg('reindex-from-id')) {
            $collection = $this->getProductCollection()->addFieldToFilter('entity_id', array('gt' => $fromId));
            $this->writeln(sprintf("Found %s products to reindex", $collection->getSize()));
            foreach ($collection as $product) {
                $this->updateSingleProduct($product);
            }

            return true;
        }

        if ($updatedFromDate = $this->getArg('reindex-from-date')) {
            if (!$this->validateDate($updatedFromDate)) {
                return $this->writeln('Please specify from-date in Y-m-d format');
            }
            $collection = $this->getProductCollection()->addFieldToFilter('updated_at', array('gt' => $updatedFromDate));
            $this->writeln(sprintf("Found %s products to reindex", $collection->getSize()));
            foreach ($collection as $product) {
                $this->updateSingleProduct($product);
            }

            return true;
        }

        print($this->usageHelp());

        return false;
    }

    /**
     * Retrieve Usage Help Message
     *
     * @return void
     */
    public function usageHelp()
    {
        return <<<USAGE

   Usage:  php typesense.php [options]

  -h                  Short alias for help
  -reindex-all        Reindex all products
  -reindex-from-id    Reindex from given product id
  -reindex-from-date  Reindex from date given in Y-m-d format
  -store-id           Store identifier

USAGE;
    }

    /**
     * @return Mage_Catalog_Model_Collection
     */
    protected function getProductCollection()
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
                            ->addAttributeToSelect('sku')
                            ->addAttributeToSelect('thumbnail')
                            ->addAttributeToSelect('url_key');
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

        foreach ($this->getAttributes() as $attribute) {
            $collection->addAttributeToSelect($attribute->getAttributeCode());
        }

        Mage::dispatchEvent('lcb_typesense_catalog_product_collection_reindex_before', array('collection' => $collection));

        return $collection;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return void
     */
    protected function updateSingleProduct($product)
    {
        try {
            Mage::getSingleton('lcb_typesense/index')->reindex($product, $this->getAttributes());
            $this->writeln("\033[0;32m" .  sprintf('Reindexed SKU %s', $product->getSku()) . "\033[0m");
        } catch (Exception $e) {
            $this->writeln("\033[0;33m" . $e->getMessage() . "\033[0m");
        } catch (Error $e) {
            $this->writeln("\033[0;31m" . $e->getMessage() . "\033[0m");
        }
    }

    /**
     * @return LCB_Typesense_Model_Resource_Catalog_Product_Attribute_Collection
     */
    protected function getAttributes()
    {
        if (!$this->attributes) {
            $this->attributes = Mage::getResourceModel('lcb_typesense/catalog_product_attribute_collection')->addSearchableAttributeFilter();
        }

        return $this->attributes;
    }

    /**
     * @param string $date
     * @param string $format
     * @return bool
     */
    private function validateDate($date, $format = 'Y-m-d')
    {
        $dateObject = DateTime::createFromFormat($format, $date);
        return $dateObject && $dateObject->format($format) === $date;
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
