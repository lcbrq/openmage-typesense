<?php

/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2023, LeftCurlyBracket
 */
class LCB_Typesense_Block_Product_Search_Facets extends Mage_Core_Block_Template
{
    /**
     * @return Varien_Data_Collection
     */
    public function getFacets()
    {
        $query = $this->getRequest()->getParam('q');
        $result = Mage::getSingleton('lcb_typesense/api')->getFacets($query);

        $collection = new Varien_Data_Collection();
        $filters = $this->getRequest()->getParam('fq');
        foreach ($result as $code => $values) {
            if ($code === 'category_ids') {
                $values['label'] = $this->__('Categories');
            }
            $facet = new Varien_Object([
                'label' => $values['label'],
                'code' => $code,
            ]);
            $items = new Varien_Data_Collection();
            foreach ($values as $data) {
                if (is_array($data)) {
                    $item = new Varien_Object($data);
                    $item->setUrl($this->getUrl('*/*/*') . '?' . "fq[$code]=" . $data['value'] . '&q=' . $query);
                    if (isset($filters[$code])) {
                        if ($data['value'] === $filters[$code]) {
                            $item->setSelected(true);
                        }
                    }
                    $items->addItem($item);
                }
            }
            $facet->setItems($items);
            $collection->addItem($facet);
        }

        return $collection;
    }

    /**
     * Prepare child blocks
     *
     * @inheritDoc
     */
    protected function _prepareLayout()
    {
        if ($filters = $this->getRequest()->getParam('fq')) {
            Mage::getSingleton('lcb_typesense/layer')->getState()->setFilters($filters);
        }
        return parent::_prepareLayout();
    }
}
