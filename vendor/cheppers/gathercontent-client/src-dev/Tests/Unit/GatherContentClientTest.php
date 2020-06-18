<?php

namespace Cheppers\GatherContent\Tests\Unit;

use Cheppers\GatherContent\DataTypes\Pagination;
use Cheppers\GatherContent\DataTypes\User;
use Cheppers\GatherContent\GatherContentClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * @group GatherContentClient
 *
 * @covers \Cheppers\GatherContent\GatherContentClient
 */
class GatherContentClientTest extends GcBaseTestCase
{
    public function testVersionString()
    {
        $client = new Client();
        $gc = new GatherContentClient($client);
        $gc->setOptions([
            'frameworkName' => 'Drupal',
            'frameworkVersion' => '8.3.4',
        ]);
        static::assertEquals('Integration-Drupal-8.3.4/1.0', $gc->getVersionString());
    }

    public function testGetSetEmail()
    {
        $client = new Client();
        $gc = new GatherContentClient($client);

        static::assertEquals('', $gc->getEmail());
        $gc->setEmail('a@b.c');
        static::assertEquals('a@b.c', $gc->getEmail());
    }

    public function testGetSetApiKey()
    {
        $client = new Client();
        $gc = new GatherContentClient($client);

        static::assertEquals('', $gc->getApiKey());
        $gc->setApiKey('a-b-c-d');
        static::assertEquals('a-b-c-d', $gc->getApiKey());
    }

    public function testGetSetBaseUri()
    {
        $client = new Client();
        $gc = new GatherContentClient($client);

        static::assertEquals('https://api.gathercontent.com', $gc->getBaseUri());
        $gc->setBaseUri('https://example.com');
        static::assertEquals('https://example.com', $gc->getBaseUri());
    }

    public function testSetOptions()
    {
        $client = new Client();
        $gc = new GatherContentClient($client);
        $gc->setOptions([
            'email' => 'a@b.c',
            'apiKey' => 'a-b-c-d',
            'baseUri' => 'https://example.com',
        ]);
        static::assertEquals('a@b.c', $gc->getEmail());
        static::assertEquals('a-b-c-d', $gc->getApiKey());
        static::assertEquals('https://example.com', $gc->getBaseUri());
    }

    public function testProjectTypes()
    {
        $client = new Client();
        $gc = new GatherContentClient($client);
        static::assertEquals(
            [
                'website-build',
                'ongoing-website-content',
                'marketing-editorial-content',
                'email-marketing-content',
                'other',
            ],
            $gc->projectTypes()
        );
    }

    public function casesMeGet()
    {
        $userData = static::getUniqueResponseUser();

        return [
            'empty' => [
                [],
                ['data' => []],
            ],
            'basic' => [
                $userData,
                ['data' => $userData],
            ],
        ];
    }

    /**
     * @dataProvider casesMeGet
     */
    public function testMeGet(array $expected, array $responseBody)
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($responseBody)
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'me'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $user = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->meGet();

        if ($expected) {
            static::assertTrue($user instanceof User, 'Return data type is User');
            static::assertEquals(
                json_encode($expected, JSON_PRETTY_PRINT),
                json_encode($user, JSON_PRETTY_PRINT)
            );
        } else {
            static::assertNull($user);
        }

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/me",
            (string) $request->getUri()
        );
    }

    public function casesMeGetFail()
    {
        return static::basicFailCasesGet();
    }

    /**
     * @dataProvider casesMeGetFail
     */
    public function testMeGetFail(array $expected, array $response)
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(
                $response['code'],
                $response['headers'],
                \GuzzleHttp\json_encode($response['body'])
            ),
            new RequestException('Error Communicating with Server', new Request('GET', 'me'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client([
            'handler' => $handlerStack,
        ]);

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->meGet();
    }

    public function casesParsePagination()
    {
        $pagination = new Pagination([
            'total' => 10,
            'count' => 100,
            'per_page' => 10,
            'current_page' => 1,
            'total_pages' => 10,
            'links' => [
                'next' => 'https://api.gathercontent.com/projects/876243786/items?&page=2',
            ],
        ]);
        return [
            'basic' => [
                $pagination,
                [
                    'data' => [],
                    'pagination' => $pagination,
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesParsePagination
     */
    public function testParsePagination($expected, $responseBody)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($responseBody)
            ),
        ]);
        $client = $tester['client'];
        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->itemsGet(123);

        static::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($actual['pagination'], JSON_PRETTY_PRINT)
        );
    }
}
