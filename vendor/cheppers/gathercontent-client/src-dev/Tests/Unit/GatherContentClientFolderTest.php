<?php

namespace Cheppers\GatherContent\Tests\Unit;

use Cheppers\GatherContent\DataTypes\Project;
use Cheppers\GatherContent\DataTypes\Status;
use Cheppers\GatherContent\GatherContentClient;
use Cheppers\GatherContent\GatherContentClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class GatherContentClientFolderTest extends GcBaseTestCase
{
    public function casesFoldersGet(): array
    {
        $data = [
            static::getUniqueResponseFolder(),
            static::getUniqueResponseFolder(),
            static::getUniqueResponseFolder(),
        ];

        $expected = static::reKeyArray($data, 'id');

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
     * @dataProvider casesFoldersGet
     */
    public function testFoldersGet(array $expected, array $responseBody, int $projectId): void
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

        $folders = (new GatherContentClient($client))
          ->setOptions($this->gcClientOptions)
          ->foldersGet($projectId);

        static::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($folders, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/folders?project_id=$projectId",
            (string) $request->getUri()
        );
    }

    public function casesFoldersGetFail(): array
    {
        $cases = static::basicFailCasesGet();

        $cases['not_found'] = [
          [
            'class' => GatherContentClientException::class,
            'code' => GatherContentClientException::API_ERROR,
            'msg' => 'API Error: "Folders not found"',
          ],
          [
            'code' => 200,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => [
              'data' => [
                'message' => 'Folders not found'
              ]
            ],
          ],
          42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesFoldersGetFail
     */
    public function testFoldersGetFail(array $expected, array $response, int $projectId): void
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

        $gc->foldersGet($projectId);
    }
}
