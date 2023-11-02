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
        $payload = [
            'name' => Mage::helper('lcb_typesense')->getCollectionName(),
            'fields' => [
                [
                    'name' => 'name',
                    'type' => 'string',
                ],
                [
                    'name' => 'sku',
                    'type' => 'string',
                ],
                [
                    'name' => 'short_description',
                    'type' => 'string',
                ],
                [
                    'name' => 'description',
                    'type' => 'string',
                ],
            ],
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

            $results = $client->collections[Mage::helper('lcb_typesense')->getCollectionName()]->documents->search(
                [
                    'q' => $query,
                    'query_by' => 'name',
                ]
            );

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
