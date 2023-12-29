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
     * @var array
     */
    private $results = [];

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
            [
                'name' => 'category_ids',
                'type' => 'string[]',
                'optional' => true,
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
     * @param  array  $filters
     * @return array
     */
    public function searchIds($query, $filters = []): array
    {
        try {
            $result = [
                'count' => 0,
                'ids' => [],
            ];

            $results = $this->search($query, $filters);

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

    /**
     * @param  string $query
     * @return array
     */
    public function getFacets($query): array
    {
        try {
            $facets = [];

            $results = $this->search($query);

            if (!empty($results['found'])) {
                $filterableAttributes = Mage::getResourceModel('lcb_typesense/catalog_product_attribute_collection')->addIsFilterableFilter();
                foreach ($results['hits'] as $hit) {
                    $document = $hit['document'];
                    foreach ($filterableAttributes as $attribute) {
                        $code = $attribute->getAttributeCode();
                        if (!empty($document[$code])) {
                            $value = $document[$code];
                            if ($value) {
                                if (!isset($facets[$code][$value])) {
                                    $facets[$code]['label'] = $attribute->getFrontendLabel();
                                    $facets[$code][$value] = [
                                        'label' => $value,
                                        'value' => $value,
                                        'count' => 1,
                                    ];
                                } else {
                                    $facets[$code][$value]['count']++;
                                }
                            }
                        }
                    }
                    if (!empty($document['category_ids'])) {
                        foreach ($document['category_ids'] as $categoryId) {
                            if (!isset($facets['category_ids'][$categoryId])) {
                                if ($category = Mage::getModel('catalog/category')->load($categoryId)) {
                                    $facets['category_ids'][$categoryId] = [
                                        'label' => $category->getName(),
                                        'value' => $category->getId(),
                                        'count' => 1,
                                    ];
                                } else {
                                    $facets['category_ids'][$categoryId]['count']++;
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Mage::helper('lcb_typesense')->log($e->getMessage());
        } catch (Exception $e) {
            Mage::helper('lcb_typesense')->log($e->getMessage());
        }

        return $facets;
    }

    /**
     * @param  string $query
     * @param  array  $filters
     * @return array
     */
    public function search($query, $filters = []): array
    {
        $hash = md5(json_encode($filters));
        if (!$this->results[$hash]) {
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

            if ($filters) {
                foreach ($filters as $code => $value) {
                    $filterString = "$code:$value";
                }
                $payload['filter_by'] = $filterString;
            }

            $client = $this->getSearchClient();
            $this->results[$hash] = $client->collections[Mage::helper('lcb_typesense')->getCollectionName()]->documents->search($payload);
        }

        return $this->results[$hash];
    }
}
