# Elasticsearch ChatGPT for PHP

This is an **experimental library** for using [ChatGPT](https://openai.com/blog/chatgpt) for searching in [Elasticsearch](https://github.com/elastic/elasticsearch).

This library exposes a `search(string $index, string $prompt)` function for searching in Elasticsearch using natural language.
The parameters are: `$index` where you specify the name of the index to search in and `$prompt` that is the query specified in natural language.

For instance, a prompt can be `Give me all the first 10 documents` or `Return all the different values of the field name`.

## How this library works

This library uses ChatGPT to translate query expressed in natural language to [Elasticsearch DSL](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl.html) query.

Then, the translated query is executed in Elasticsearch using [elasticsearch-php](https://github.com/elastic/elasticsearch-php) library.

## Using ChatGPT

For using ChatGPT you need an API key from [OpenAI](https://openai.com/blog/openai-api).
This API key is available only for paying users.
You can read more about the procedure to activate ChatGPT Plus account [here](https://openai.com/blog/chatgpt-plus).

## Using Elasticsearch

You need to have an Elasticsearch server running or use the [Elastic Cloud](https://www.elastic.co/cloud/).

You can read [here](https://github.com/elastic/elasticsearch-php#configuration) how to start a server using Docker or using the Elastic Cloud environment.

## Example of using the library

Here we provided an example that is also available in the [examples/test.php](examples/test.php) script.

In order to execute the `test.php` script you need to install the dependencies
using [composer](https://getcomposer.org/) with the following command:

```bash
composer install
```

Then, you need to set the environmental variables for using OpenAI and Elasticsearch.
In the following example we used the following env:

- `OPENAI_API_KEY`, for the OpenAI API key;
- `ELASTIC_CLOUD_ENDPOINT`, containing the URL endpoint for Elastic Cloud;
- `ELASTIC_CLOUD_API_KEY`, containing the API key for Elastic Cloud.

If you want to connect using a different Elasticsearch server, you can read the
[connecting section](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/connecting.html)
of the `elasticsearch-php` documentation.

The `test.php` script contains the following code:

```php
use Elastic\Elasticsearch\ChatGPT\ChatGPT;
use Elastic\Elasticsearch\ClientBuilder;

// Openai-php/client library 
$openAI = OpenAI::client(getenv("OPENAI_API_KEY"));

// Connecting to Elasticsearch using Elastic Cloud
$elasticsearch = ClientBuilder::create()
    ->setHosts([getenv("ELASTIC_CLOUD_ENDPOINT")])
    ->setApiKey(getenv("ELASTIC_CLOUD_API_KEY"))
    ->build();

$chatGPT = new ChatGPT($elasticsearch, $openAI);
$result = $chatGPT->search('stocks', 'Return the first 10 documents of 2017');

// Print the Elasticsearch result
print_r($result->asArray());

// Print the Elasticsearch DSL used for the query
print($chatGPT->getLastQuery());
```

The search function is the key point of this code. In this example we
used a dataset of [stock prices](https://github.com/elastic/elasticsearch-php-examples/blob/main/data/all_stocks_5yr.csv)
containing 5 years of stock of 500 Fortune companies, starting from February 2013.

The dataset ihas the following mapping:

```json
{
    "stocks": {
        "mappings": {
            "properties": {
                "close": {"type":"float"},
                "date" : {"type":"date"},
                "high" : {"type":"float"},
                "low"  : {"type":"float"},
                "name" : {
                    "type": "text",
                    "fields": {
                        "keyword":{"type":"keyword", "ignore_above":256}
                    }
                },
                "open"  : {"type":"float"},
                "volume": {"type":"long"}
            }
        }
    }
}
```

## Using the cache

By default this library uses a cache system, based on files stored in `cache` folder.
Each time the library ask for a mapping to Elasticsearch or send a prompt to ChatGPT
it store the results in files. For the mapping, the library uses the name of the index.
For the Elasticsearch DSL produced by ChatGPT we use the MD5 of the prompt request as file name.

If you want to disable the cache, e.g. you changed the mapping of the index, you
can pass `$cache = false` in the third optional parameter of the `search()` function.

## Multilanguage support

A very nice feature of ChatGPT is the ability to specify questions in different languages.
That means, you can use this library and specify the query in different natural languges, like
Italian, Spanish, French, German, etc.

Here an example:

```php
# English
$result = $chatGPT->search('stocks', 'Return the first 10 documents of 2017');
# Italian
$result = $chatGPT->search('stocks', 'Restituisci i primi 10 documenti del 2017');
# Spanish
$result = $chatGPT->search('stocks', 'Devuelve los 10 primeros documentos de 2017');
# French
$result = $chatGPT->search('stocks', 'Retourner les 10 premiers documents de 2017');
# German
$result = $chatGPT->search('stocks', 'Senden Sie die ersten 10 Dokumente des Jahres 2017 zurück');
```

All the previous search have the same results producing the following Elasticsearch query (more or less):

```json
{"size":10,"query":{"range":{"date":{"gte":"2017-01-01","lt":"2018-01-01"}}}}
```

You can see the [examples/multilanguage.php](examples/multilanguage.php) script for more information.

**NOTE**: It is important to know that ChatGPT is an LLM that has been optimized for English.
That means, the best results are obtained using queries entered in English.


## Limitations

Since ChatGPT is a LLM model **it does not guarantee** that the answer are correct from a semantic point of view.
That means you can expect invalid results using this library, that means not valid translation in 
Elasticsearch DSL query. You should always check the DSL query translated by ChatGPT using the `getLastQuery()`
function. For these reasons, we suggest to **do not use this library in production**.

## Disclaimer

See the full documentation online in this [Elastic Blog Post](https://www.elastic.co/blog/elasticsearch-prompt-chatgpt-natural-language).
**elastic/elasticsearch-chatgt-php** is an Elastic Labs project. Elastic Labs projects are for illustrative and experimental purposes only. This Elastic Labs project is not part of any product or services offering provided or supported under a commercial license or subscription. This project is made available as-is under the terms of the license associated with this project.
The release and timing of any features or functionality described in this project remain at Elastic's sole discretion.
Any features or functionality not currently available may not be delivered on time or at all.

## License

`elasticsearch-chatgpt-php` is available under the Apache 2.0 license.
For more details see [LICENSE](LICENSE).