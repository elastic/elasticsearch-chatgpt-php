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

$result = $chatGPT->search('stocks', 'Return the first 10 documents of 2017');
//$result = $chatGPT->search('stocks', 'Return the first 30 names of all the different stock names'); 
//$result = $chatGPT->search('stocks', 'Return the max value of the field "high" for each stock in 2015');
//$result = $chatGPT->search('stocks', 'Return the average value of the field "high" for each stock in 2015');
//$result = $chatGPT->search('stocks', 'Return the max value of the field "high" for all the documents with name MON in 2014');
//$result = $chatGPT->search('stocks', 'Return the documents that have the difference between close and open fields > 20');

print_r($result->asArray());
printf("--- Last query:\n%s\n", $chatGPT->getLastQuery());