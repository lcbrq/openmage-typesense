<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2023, LeftCurlyBracket
 */
use Symfony\Component\HttpClient\HttplugClient;
use Typesense\Client;

class LCB_TypeSense_Model_Api
{
    /**
     * Get TypeSense admin client instance
     *
     * @return Client
     */
    public function getAdminClient(): Client
    {
        $apiHost = Mage::helper('lcb_typesense')->getHost();
        $apiKey = Mage::helper('lcb_typesense')->getAdminApiKey();

        return new Client(
            [
                'api_key' => $apiKey,
                'nodes' => [
                    [
                        'host' => $apiHost,
                        'port' => '443',
                        'protocol' => 'https',
                    ],
                ],
                'client' => new HttplugClient(),
            ]
        );
    }

    /**
     * Get TypeSense search client instance
     *
     * @return Client
     */
    public function getSearchClient(): Client
    {
        $apiHost = Mage::helper('lcb_typesense')->getHost();
        $apiKey = Mage::helper('lcb_typesense')->getSearchOnlyApiKey();

        return new Client(
            [
                'api_key' => $apiKey,
                'nodes' => [
                    [
                        'host' => $apiHost,
                        'port' => '443',
                        'protocol' => 'https',
                    ],
                ],
                'client' => new HttplugClient(),
            ]
        );
    }

    /**
     * @return void
     */
    public function createCollection()
    {
        $attributes = Mage::getResourceModel('lcb_typesense/catalog_product_attribute_collection')->addSearchableAttributeFilter();
        $fields = [
            [
                'name' => 'sku',
                'type' => 'string',
            ],
            [
                'name' => 'status',
                'type' => 'int32',
            ],
            [
                'name' => 'visibility',
                'type' => 'int32',
            ],
        ];
        foreach ($attributes as $attribute) {
            if (!in_array($attribute->getAttributeCode(), ['sku', 'status', 'visibility'])) {
                $fields[] = [
                    'name' => $attribute->getAttributeCode(),
                    'type' => $attribute->getTypeSenseType(),
                ];
            }
        }
        $payload = [
            'name' => Mage::helper('lcb_typesense')->getCollectionName(),
            'fields' => $fields,
        ];

        $this->getAdminClient()->collections->create($payload);
    }

    /**
     * @param  string $query
     * @return array
     */
    public function searchIds($query): array
    {
        try {
            $client = $this->getSearchClient();

            $result = [
                'count' => 0,
                'ids' => [],
            ];

            $queryBy = ['name'];
            $attributes = Mage::getResourceModel('lcb_typesense/catalog_product_attribute_collection')->addSearchableAttributeFilter();
            foreach ($attributes as $attribute) {
                if (!in_array($attribute->getAttributeCode(), ['status', 'visibility'])) {
                    $queryBy[] = $attribute->getAttributeCode();
                }
            }
            $payload = [
                'q' => $query,
                'query_by' => implode(',', $queryBy),
            ];
            $results = $client->collections[Mage::helper('lcb_typesense')->getCollectionName()]->documents->search($payload);

            if (!empty($results['found'])) {
                $result['count'] = $results['found'];
                foreach ($results['hits'] as $hit) {
                    $document = $hit['document'];
                    $result['ids'][] = $document['id'];
                }
            }
        } catch (Exception $e) {
            Mage::helper('lcb_typesense')->log($e->getMessage());
        } catch (Exception $e) {
            Mage::helper('lcb_typesense')->log($e->getMessage());
        }

        return $result;
    }
}
