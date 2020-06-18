<?php

namespace Cheppers\GatherContent\Tests\Unit;

use Cheppers\GatherContent\DataTypes\Account;
use Cheppers\GatherContent\GatherContentClient;
use Cheppers\GatherContent\GatherContentClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class GatherContentClientAccountTest extends GcBaseTestCase
{
    public function casesAccountsGet()
    {
        $data = [
            static::getUniqueResponseAccount(),
            static::getUniqueResponseAccount(),
            static::getUniqueResponseAccount(),
        ];

        return [
            'basic' => [
                $data,
                ['data' => $data],
            ],
        ];
    }

    /**
     * @dataProvider casesAccountsGet
     */
    public function testAccountsGet(array $expected, array $responseBody)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($responseBody)
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $accounts = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->accountsGet();

        static::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($accounts['data'], JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/accounts",
            (string) $request->getUri()
        );
    }

    public function casesAccountsGetFail()
    {
        return static::basicFailCasesGet();
    }

    /**
     * @dataProvider casesAccountsGetFail
     */
    public function testAccountsGetFail(array $expected, array $response)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                $response['code'],
                $response['headers'],
                \GuzzleHttp\json_encode($response['body'])
            ),
        ]);
        $client = $tester['client'];

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->accountsGet();
    }

    public function casesAccountGet()
    {
        $data = static::getUniqueResponseAccount();

        return [
            'basic' => [
                $data,
                ['data' => $data],
            ],
        ];
    }

    /**
     * @dataProvider casesAccountGet
     */
    public function testAccountGet(array $expected, array $responseBody)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($responseBody)
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->accountGet($responseBody['data']['id']);

        static::assertTrue($actual instanceof Account, 'Data type of the return is Account');
        static::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/accounts/" . (int) $responseBody['data']['id'],
            (string) $request->getUri()
        );
    }

    public function casesAccountGetFail()
    {
        $data = static::getUniqueResponseAccount();
        $cases = static::basicFailCasesGet($data);

        $cases['not_found'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Account not found"',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'data' => [
                        'message' => 'Account not found'
                    ]
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesAccountGetFail
     */
    public function testAccountGetFail(array $expected, array $response, $account_id)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                $response['code'],
                $response['headers'],
                \GuzzleHttp\json_encode($response['body'])
            ),
        ]);
        $client = $tester['client'];

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->accountGet($account_id);
    }
}
