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

# error failed to parse date field [2015-01-01] with format [yyyy]
# query DSL: {"from":0,"size":10,"query":{"bool":{"must":[{"range":{"date":{"gte":"2015-01-01","lte":"2015-12-31","format":"yyyy"}}},{"range":{"date":{"gte":"2017-01-01","lte":"2017-12-31","format":"yyyy"}}}]}}}
$result = $chatGPT->search('stocks', 'Return the first 10 documents of 2017 and 2015');

# correct sentence
//$result = $chatGPT->search('stocks', 'Return the first 10 documents with year 2017 and 2015 in "date" field');

print_r($result->asArray());
printf("--- Last query:\n%s\n", $chatGPT->getLastQuery());