<?php
/**
 * Test for search in stocks index
 * 
 * @see https://github.com/elastic/elasticsearch-php-examples
 * 
 * You can fill in the "stocks" index using this script, from the previous repository:
 * https://github.com/elastic/elasticsearch-php-examples/blob/main/src/bulk.php
 */
use Elastic\Elasticsearch\ChatGPT\ChatGPT;
use Elastic\Elasticsearch\ClientBuilder;

require __DIR__ . '/../vendor/autoload.php';

$openAI = OpenAI::client(getenv("OPENAI_API_KEY"));

$elasticsearch = ClientBuilder::create()
    ->setHosts([getenv("ELASTIC_CLOUD_ENDPOINT")])
    ->setApiKey(getenv("ELASTIC_CLOUD_API_KEY"))
    ->build();

$chatGPT = new ChatGPT($elasticsearch, $openAI);

$queries = [
    'eng' => 'Return the first 10 documents of 2017',
    'ita' => 'Restituisci i primi 10 documenti del 2017',
    'esp' => 'Devuelve los 10 primeros documentos de 2017',
    'fra' => 'Retourner les 10 premiers documents de 2017',
    'deu' => 'Senden Sie die ersten 10 Dokumente des Jahres 2017 zurück'
];

$results = [];
foreach ($queries as $lang => $query) {
    $results[$lang] = $chatGPT->search('stocks', $query);
}

# We assume correct the english results
foreach (array_keys($results) as $lang) {
    if ($lang === 'eng') {
        continue;
    }
    printf("%s : %s\n", $lang, $results['eng']['hits']['hits'] === $results[$lang]['hits']['hits'] ? 'OK' : 'FAILED');
}