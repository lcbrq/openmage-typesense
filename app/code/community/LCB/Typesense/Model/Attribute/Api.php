<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2024, LeftCurlyBracket
 */

class LCB_Typesense_Model_Attribute_Api extends LCB_Typesense_Model_Api
{
    /**
     * @param  Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return void
     */
    public function update($attribute): void
    {
        $isSearchable = $attribute->getIsSearchable();
        $isFilterable = $attribute->getIsFilterableInSearch();

        $client = $this->getAdminClient();
        $collection = $client->collections[Mage::helper('lcb_typesense')->getCollectionName()]->retrieve();

        $fieldExists = false;
        foreach ($collection['fields'] as $field) {
            if ($field['name'] === $attribute->getAttributeCode()) {
                $fieldExists = true;
            }
        }

        if ($isSearchable || $isFilterable) {
            $type = Mage::getResourceModel('lcb_typesense/catalog_eav_attribute')
                    ->setBackendType($attribute->getBackendType())
                    ->getTypesenseType();
            $payload = [
              'fields'    => [
                [
                  'name'  => $attribute->getAttributeCode(),
                  'type'  => $type,
                  'filter' => (bool) $isFilterable
                ]
              ]
            ];
            $client->collections[Mage::helper('lcb_typesense')->getCollectionName()]->update($payload);
        }

        if ($fieldExists && !$isSearchable) {
            $payload = [
              'fields'    => [
                [
                  'name'  => $attribute->getAttributeCode(),
                  'drop'  => true
                ]
              ]
            ];
            $client->collections[Mage::helper('lcb_typesense')->getCollectionName()]->update($payload);
        }
    }
}
