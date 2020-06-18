<?php

namespace Cheppers\GatherContent\Tests\Unit;

use Cheppers\GatherContent\DataTypes\Project;
use Cheppers\GatherContent\DataTypes\Status;
use Cheppers\GatherContent\GatherContentClient;
use Cheppers\GatherContent\GatherContentClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class GatherContentClientProjectTest extends GcBaseTestCase
{
    public function casesProjectsGet()
    {
        $data = [
            static::getUniqueResponseProject(),
            static::getUniqueResponseProject(),
            static::getUniqueResponseProject(),
        ];

        $expected = [];
        foreach ($data as $project) {
            $project = $project + ['meta' => []];
            $expected[] = $project;
        }

        return [
            'empty' => [
                [],
                ['data' => []],
                42,
            ],
            'basic' => [
                $expected,
                ['data' => $data],
                42,
            ],
        ];
    }

    /**
     * @dataProvider casesProjectsGet
     */
    public function testProjectsGet(array $expected, array $responseBody, $accountId)
    {
        $tester = $this->getBasicHttpClientTester(
            [
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    \GuzzleHttp\json_encode($responseBody)
                ),
            ]
        );
        $client = $tester['client'];
        $container = &$tester['container'];

        $projects = (new GatherContentClient($client))
          ->setOptions($this->gcClientOptions)
          ->projectsGet($accountId);

        static::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($projects['data'], JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/projects?account_id=$accountId",
            (string) $request->getUri()
        );
    }

    public function casesProjectsGetFail()
    {
        $cases = static::basicFailCasesGet();

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
     * @dataProvider casesProjectsGetFail
     */
    public function testProjectsGetFail(array $expected, array $response, $accountId)
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

        $gc->projectsGet($accountId);
    }

    public function casesProjectGet()
    {
        $data = static::getUniqueResponseProject();

        $expected = $data;
        $expected['meta'] = [];

        return [
            'basic' => [
                $expected,
                ['data' => $data],
                42,
            ],
            'empty' => [
                null,
                ['data' => []],
                42,
            ],
        ];
    }

    /**
     * @dataProvider casesProjectGet
     */
    public function testProjectGet($expected, array $responseBody, $projectId)
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
          ->projectGet($projectId);

        if (!$expected) {
            static::assertNull($actual);
        } else {
            static::assertTrue($actual instanceof Project, 'Data type of the return is Project');
            static::assertEquals(
                json_encode($expected, JSON_PRETTY_PRINT),
                json_encode($actual, JSON_PRETTY_PRINT)
            );
        }

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/projects/$projectId",
            (string) $request->getUri()
        );
    }

    public function casesProjectGetFail()
    {
        $cases = static::basicFailCasesGet();

        $cases['not_found'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Project Not Found"',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'data' => [
                        'message' => 'Project Not Found'
                    ]
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesProjectGetFail
     */
    public function testProjectGetFail(array $expected, array $response, $projectId)
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

        $gc->projectGet($projectId);
    }

    public function casesProjectStatusesGet()
    {
        $data = [
            static::getUniqueResponseStatus(),
            static::getUniqueResponseStatus(),
            static::getUniqueResponseStatus(),
        ];

        return [
            'empty' => [
                [],
                ['data' => []],
                42,
            ],
            'basic' => [
                $data,
                ['data' => $data],
                42,
            ],
        ];
    }

    public function casesProjectsPost()
    {
        return [
            'basic' => [
                [
                    'code' => 202,
                    'id' => 42,
                ],
                [
                    'code' => 202,
                    'body' => [],
                    'id' => 42,
                ],
                42,
                'Project name',
                'Project type'
            ],
        ];
    }

    /**
     * @dataProvider casesProjectsPost
     */
    public function testProjectsPost(
        array $expected,
        array $response,
        $accountId,
        $projectName,
        $projectType
    ) {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                $response['code'],
                [
                    'Content-Type' => 'application/json',
                    'Location' => "{$this->gcClientOptions['baseUri']}/projects/{$response['id']}"
                ],
                \GuzzleHttp\json_encode($response['body'])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $client = (new GatherContentClient($client))
          ->setOptions($this->gcClientOptions);
        $actual = $client->projectsPost($accountId, $projectName, $projectType);

        static::assertEquals($expected['id'], $actual);


        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals($expected['code'], $client->getResponse()->getStatusCode());
        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['application/x-www-form-urlencoded'], $request->getHeader('Content-Type'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/projects",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $queryString = $requestBody->getContents();
        $sentQueryVariables = [];
        parse_str($queryString, $sentQueryVariables);

        if ($accountId) {
            static::assertArrayHasKey('account_id', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['account_id'], $accountId);
        } else {
            static::assertArrayNotHasKey('account_id', $sentQueryVariables);
        }

        if ($projectName) {
            static::assertArrayHasKey('name', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['name'], $projectName);
        } else {
            static::assertArrayNotHasKey('name', $sentQueryVariables);
        }

        if ($projectType) {
            static::assertArrayHasKey('type', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['type'], $projectType);
        } else {
            static::assertArrayNotHasKey('type', $sentQueryVariables);
        }
    }

    public function casesProjectsPostFail()
    {
        $cases = static::basicFailCasesPost(['name' => 'Project name', 'type' => 'Project type']);
        $cases['missing_item'] = [
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
          0,
          'Project name',
          'Project type'
        ];
        $cases['empty_name'] = [
          [
            'class' => \Exception::class,
            'code' => 400,
            'msg' => '{"error":"Missing name","code":400}',
          ],
          [
            'code' => 400,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => [
              'error' => 'Missing name',
              'code' => 400
            ],
          ],
          42,
          '',
          'Project type'
        ];
        $cases['missing_project_id'] = [
          [
            'class' => GatherContentClientException::class,
            'code' => GatherContentClientException::INVALID_RESPONSE_HEADER,
            'msg' => 'Invalid response header the project ID is missing',
          ],
          [
            'code' => 202,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => [],
          ],
          42,
          '',
          'Project type'
        ];

        return $cases;
    }

    /**
     * @dataProvider casesProjectsPostFail
     */
    public function testProjectsPostFail(
        array $expected,
        array $response,
        $accountId,
        $projectName,
        $projectType
    ) {
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

        $gc->projectsPost($accountId, $projectName, $projectType);
    }

    /**
     * @dataProvider casesProjectStatusesGet
     */
    public function testProjectStatusesGet(array $expected, array $responseBody, $projectId)
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

        $statuses = (new GatherContentClient($client))
          ->setOptions($this->gcClientOptions)
          ->projectStatusesGet($projectId);

        static::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($statuses['data'], JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/projects/$projectId/statuses",
            (string) $request->getUri()
        );
    }

    public function casesProjectStatusesGetFail()
    {
        $cases = static::basicFailCasesGet();

        $cases['not_found'] = [
          [
            'class' => \Exception::class,
            'code' => 404,
            'msg' => '{"error":"Project Not Found","code":404}',
          ],
          [
            'code' => 404,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => [
              'error' => 'Project Not Found',
              'code' => 404
            ],
          ],
          42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesProjectStatusesGetFail
     */
    public function testProjectStatusesGetFail(array $expected, array $response, $projectId)
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

        $gc->projectStatusesGet($projectId);
    }

    public function casesProjectStatusGet()
    {
        $data = static::getUniqueResponseStatus();

        return [
          'basic' => [
            $data,
            ['data' => $data],
            42,
            $data['id']
          ],
        ];
    }

    /**
     * @dataProvider casesProjectStatusGet
     */
    public function testProjectStatusGet(array $expected, array $responseBody, $projectId, $statusId)
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
          ->projectStatusGet($projectId, $statusId);

        static::assertTrue($actual instanceof Status, 'Data type of the return is Status');
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
            "{$this->gcClientOptions['baseUri']}/projects/$projectId/statuses/$statusId",
            (string) $request->getUri()
        );
    }

    public function casesProjectStatusGetFail()
    {
        $data = static::getUniqueResponseStatus();
        return static::basicFailCasesGet($data);
    }

    /**
     * @dataProvider casesProjectStatusGetFail
     */
    public function testProjectStatusGetFail(
        array $expected,
        array $response,
        $projectId,
        $statusId
    ) {
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

        $gc->projectStatusGet($projectId, $statusId);
    }
}
