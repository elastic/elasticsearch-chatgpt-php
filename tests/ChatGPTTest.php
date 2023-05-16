<?php
/**
 * Elasticsearch ChatGPT PHP
 *
 * @link      https://github.com/elastic/elasticsearch-chatgpt-php
 * @copyright Copyright (c) Elasticsearch B.V (https://www.elastic.co)
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 License
 *
 * Licensed to Elasticsearch B.V under one or more agreements.
 * Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
 * See the LICENSE file in the project root for more information.
 */
declare(strict_types=1);

namespace Elastic\Elasticsearch\ChatGPT\Tests;

use Elastic\Elasticsearch\ChatGPT\ChatGPT;
use Elastic\Elasticsearch\ChatGPT\Exception\MissingDirectoryException;
use Elastic\Elasticsearch\Client as EsClient;
use Elastic\Elasticsearch\Endpoints\Indices;
use Elastic\Elasticsearch\Response\Elasticsearch as EsResponse;
use PHPUnit\Framework\TestCase;
use OpenAI\Testing\ClientFake;
use OpenAI\Responses\Chat\CreateResponse;

class ChatGPTTest extends TestCase
{
    protected EsClient $elasticsearch;
    protected ClientFake $openai;
    protected EsResponse $esResponse;
    protected Indices $indices;
    protected ChatGPT $chatgpt;
    protected string $tmpFolder;

    public function setUp(): void
    {
        $this->elasticsearch = $this->createStub(EsClient::class);
        $this->esResponse = $this->createStub(EsResponse::class);
        $this->indices = $this->createStub(Indices::class);

        $this->openai = new ClientFake([
            CreateResponse::fake(),
        ]);

        $this->chatgpt = new ChatGPT($this->elasticsearch, $this->openai);

        // Set a temporary cache folder
        $this->tmpFolder = sprintf("%s/test-%s", sys_get_temp_dir(), str_replace('\\','-', __CLASS__));
        if (!is_dir($this->tmpFolder)) {
            mkdir($this->tmpFolder);
        }
        $this->chatgpt->setCacheFolder($this->tmpFolder);
    }

    public function testSearchWithoutCache(): void
    {
        $this->indices->method('getMapping')
            ->willReturn(new class {
                public function asString()
                {
                    return '{mapping}';
                }
            }
        );
       
        $this->esResponse->method('asString')
            ->willReturn('{ok}');

        $this->elasticsearch->method('indices')
            ->willReturn($this->indices);
        $this->elasticsearch->method('search')
            ->willReturn($this->esResponse);

        $result = $this->chatgpt->search('test', 'Find all the documents', false);
        $this->assertEquals('{ok}', $result->asString());
    }

    public function testSearchWithCache(): void
    {
        $this->indices->method('getMapping')
            ->willReturn(new class {
                public function asString()
                {
                    return '{mapping}';
                }
            }
        );
       
        $this->esResponse->method('asString')
            ->willReturn('{ok}');

        $this->elasticsearch->method('indices')
            ->willReturn($this->indices);
        $this->elasticsearch->method('search')
            ->willReturn($this->esResponse);

        $result = $this->chatgpt->search('test', 'Find all the documents');
        $this->assertEquals('{ok}', $result->asString());
    }

    public function testGetLastQuery(): void
    {
        $this->indices->method('getMapping')
            ->willReturn(new class {
                public function asString()
                {
                    return '{mapping}';
                }
            }
        );
       
        $this->esResponse->method('asString')
            ->willReturn('{ok}');

        $this->elasticsearch->method('indices')
            ->willReturn($this->indices);
        $this->elasticsearch->method('search')
            ->willReturn($this->esResponse);

        $result = $this->chatgpt->search('test', 'Find all the documents', false);
        $this->assertEquals("\n\nHello there, this is a fake chat response.", $this->chatgpt->getLastQuery());
    }

    public function testSetCacheFolderThrowsException()
    {
        $this->expectException(MissingDirectoryException::class);
        $this->chatgpt->setCacheFolder(__DIR__ . '/foo');
    }
}