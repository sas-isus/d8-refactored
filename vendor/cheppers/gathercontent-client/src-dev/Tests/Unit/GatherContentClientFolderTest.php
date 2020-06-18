<?php

namespace Cheppers\GatherContent\Tests\Unit;

use Cheppers\GatherContent\DataTypes\Folder;
use Cheppers\GatherContent\GatherContentClient;
use Cheppers\GatherContent\GatherContentClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class GatherContentClientFolderTest extends GcBaseTestCase
{
    public function casesFoldersGet()
    {
        $data = [
            static::getUniqueResponseFolder(),
            static::getUniqueResponseFolder(),
            static::getUniqueResponseFolder(),
        ];

        $folders = [];
        foreach ($data as $folder) {
            $folders[] = new Folder($folder);
        }

        return [
            'empty' => [
                [],
                ['data' => []],
                42,
                1,
            ],
            'basic' => [
                $folders,
                ['data' => $data],
                42,
                0,
            ],
        ];
    }

    /**
     * @dataProvider casesFoldersGet
     */
    public function testFoldersGet(array $expected, array $responseBody, int $projectId, $includeTrashed)
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
            ->foldersGet($projectId, $includeTrashed);

        static::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($folders['data'], JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/projects/$projectId/folders?include_trashed=$includeTrashed",
            (string) $request->getUri()
        );
    }

    public function casesFoldersGetFail()
    {
        $cases = static::basicFailCasesGet();

        $cases['not_found'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Folders not found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Folders not found',
                    'code' => 404
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesFoldersGetFail
     */
    public function testFoldersGetFail(array $expected, array $response, int $projectId)
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

    public function casesFolderPost()
    {
        $folderArray = static::getUniqueResponseFolder();
        $folder = new Folder($folderArray);

        return [
            'basic' => [
                $folder,
                $folder,
                131313,
                $folder->id,
            ],
        ];
    }

    /**
     * @dataProvider casesFolderPost
     */
    public function testFolderPost(Folder $expected, Folder $folder, $parentFolderUuid, $resultFolderId)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                201,
                [
                    'Content-Type' => 'application/json',
                ],
                \GuzzleHttp\json_encode(['data' => $expected])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->folderPost($parentFolderUuid, $folder);

        $actual->setSkipEmptyProperties(true);

        static::assertEquals($resultFolderId, $actual->id);

        static::assertTrue($actual instanceof Folder, 'Data type of the return is Folder');
        static::assertEquals(
            \GuzzleHttp\json_encode($expected, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/folders/$parentFolderUuid/folders",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);

        if (!empty($folder->id)) {
            static::assertArrayHasKey('uuid', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['uuid'], $expected->id);
        } else {
            static::assertArrayNotHasKey('uuid', $sentQueryVariables);
        }

        if (!empty($folder->name)) {
            static::assertArrayHasKey('name', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['name'], $expected->name);
        } else {
            static::assertArrayNotHasKey('name', $sentQueryVariables);
        }

        if (!empty($folder->position)) {
            static::assertArrayHasKey('position', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['position'], $expected->position);
        } else {
            static::assertArrayNotHasKey('position', $sentQueryVariables);
        }
    }

    public function casesFolderPostFail()
    {
        $cases['wrong_type'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::UNEXPECTED_CONTENT_TYPE,
                'msg' => 'Unexpected Content-Type',
            ],
            [
                'code' => 201,
                'headers' => ['Content-Type' => 'image/jpeg'],
                'body' => [],
            ],
            1,
            ''
        ];
        $cases['not_found'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Folder Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Folder Not Found',
                    'code' => 404
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesFolderPostFail
     */
    public function testFolderPostFail(array $expected, array $response, $parentFolderUuid)
    {
        $tester = $this->getBasicHttpClientTester(
            [
                new Response(
                    $response['code'],
                    $response['headers'],
                    \GuzzleHttp\json_encode($response['body'])
                ),
            ]
        );
        $client = $tester['client'];

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->folderPost($parentFolderUuid, new Folder());
    }

    public function casesFolderRenamePost()
    {
        $folderArray = static::getUniqueResponseFolder();
        $folder = new Folder($folderArray);

        return [
            'basic' => [
                $folder,
                13,
                $folder->name,
            ],
        ];
    }

    /**
     * @dataProvider casesFolderRenamePost
     */
    public function testFolderRenamePost(Folder $folder, $folderUuid, $name)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                200,
                [
                    'Content-Type' => 'application/json',
                ],
                \GuzzleHttp\json_encode(['data' => $folder])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->folderRenamePost($folderUuid, $name);

        static::assertTrue($actual instanceof Folder, 'Data type of the return is Folder');
        static::assertEquals(
            \GuzzleHttp\json_encode($folder, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/folders/$folderUuid/rename",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);

        static::assertArrayHasKey('name', $sentQueryVariables);
        static::assertEquals($sentQueryVariables['name'], $name);
    }

    public function casesFolderRenamePostFail()
    {
        $cases['wrong_type'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::UNEXPECTED_CONTENT_TYPE,
                'msg' => 'Unexpected Content-Type',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'image/jpeg'],
                'body' => [],
            ],
            1,
            ''
        ];
        $cases['missing_item'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Folder Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Folder Not Found',
                    'code' => 404
                ],
            ],
            1,
            ''
        ];
        $cases['empty'] = [
            [
                'class' => \Exception::class,
                'code' => 400,
                'msg' => '{"error":"Missing folder_uuid","code":400}',
            ],
            [
                'code' => 400,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Missing folder_uuid',
                    'code' => 400
                ],
            ],
            1,
            ''
        ];

        return $cases;
    }

    /**
     * @dataProvider casesFolderRenamePostFail
     */
    public function testFolderRenamePostFail(array $expected, array $response, $folderUuid, $name)
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

        $gc->folderRenamePost($folderUuid, $name);
    }

    public function casesFolderMovePost()
    {
        $folderArray = static::getUniqueResponseFolder();
        $folder = new Folder($folderArray);

        return [
            'basic' => [
                $folder,
                13,
                $folder->position,
                $folder->parentUuid,
            ],
        ];
    }

    /**
     * @dataProvider casesFolderMovePost
     */
    public function testFolderMovePost(Folder $folder, $folderUuid, $position, $parentUuid)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                200,
                [
                    'Content-Type' => 'application/json',
                ],
                \GuzzleHttp\json_encode(['data' => $folder])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->folderMovePost($folderUuid, $parentUuid, $position);

        static::assertTrue($actual instanceof Folder, 'Data type of the return is Folder');
        static::assertEquals(
            \GuzzleHttp\json_encode($folder, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/folders/$folderUuid/move",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);

        static::assertArrayHasKey('position', $sentQueryVariables);
        static::assertArrayHasKey('parent_uuid', $sentQueryVariables);
        static::assertEquals($sentQueryVariables['position'], $position);
        static::assertEquals($sentQueryVariables['parent_uuid'], $parentUuid);
    }

    public function casesFolderMovePostFail()
    {
        $cases['wrong_type'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::UNEXPECTED_CONTENT_TYPE,
                'msg' => 'Unexpected Content-Type',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'image/jpeg'],
                'body' => [],
            ],
            1,
            0,
            ''
        ];
        $cases['missing_item'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Folder Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Folder Not Found',
                    'code' => 404
                ],
            ],
            1,
            0,
            ''
        ];
        $cases['empty'] = [
            [
                'class' => \Exception::class,
                'code' => 400,
                'msg' => '{"error":"Missing folder_uuid","code":400}',
            ],
            [
                'code' => 400,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Missing folder_uuid',
                    'code' => 400
                ],
            ],
            1,
            0,
            ''
        ];

        return $cases;
    }

    /**
     * @dataProvider casesFolderMovePostFail
     */
    public function testFolderMovePostFail(array $expected, array $response, $folderUuid, $position, $parentUuid)
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

        $gc->folderMovePost($folderUuid, $parentUuid, $position);
    }

    public function casesFolderDelete()
    {
        $folderArray = static::getUniqueResponseFolder();
        $folder = new Folder($folderArray);

        return [
            'trash' => [
                $folder,
                13,
                200,
            ],
            'delete' => [
                $folder,
                13,
                204,
            ],
        ];
    }

    /**
     * @dataProvider casesFolderDelete
     */
    public function testFolderDelete(Folder $folder, $folderUuid, $returnStatus)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                $returnStatus,
                [
                    'Content-Type' => 'application/json',
                ],
                \GuzzleHttp\json_encode(['data' => $folder])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->folderDelete($folderUuid);

        if ($returnStatus === 200) {
            static::assertTrue($actual instanceof Folder, 'Data type of the return is Folder');
            static::assertEquals(
                \GuzzleHttp\json_encode($folder, JSON_PRETTY_PRINT),
                \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
            );
        } else {
            static::assertNull($actual);
        }

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('DELETE', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/folders/$folderUuid",
            (string) $request->getUri()
        );
    }

    public function casesFolderDeleteFail()
    {
        $cases['missing_item'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Folder Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Folder Not Found',
                    'code' => 404
                ],
            ],
            1,
        ];
        $cases['empty'] = [
            [
                'class' => \Exception::class,
                'code' => 400,
                'msg' => '{"error":"Missing folder_uuid","code":400}',
            ],
            [
                'code' => 400,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Missing folder_uuid',
                    'code' => 400
                ],
            ],
            1,
        ];

        return $cases;
    }

    /**
     * @dataProvider casesFolderDeleteFail
     */
    public function testFolderDeleteFail(array $expected, array $response, $folderUuid)
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

        $gc->folderDelete($folderUuid);
    }

    public function casesFolderRestorePost()
    {
        $folderArray = static::getUniqueResponseFolder();
        $folder = new Folder($folderArray);

        return [
            'basic' => [
                $folder,
                13,
            ],
        ];
    }

    /**
     * @dataProvider casesFolderRestorePost
     */
    public function testFolderRestorePost(Folder $folder, $folderUuid)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                200,
                [
                    'Content-Type' => 'application/json',
                ],
                \GuzzleHttp\json_encode(['data' => $folder])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->folderRestorePost($folderUuid);

        static::assertTrue($actual instanceof Folder, 'Data type of the return is Folder');
        static::assertEquals(
            \GuzzleHttp\json_encode($folder, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/folders/$folderUuid/restore",
            (string) $request->getUri()
        );
    }

    public function casesFolderRestorePostFail()
    {
        $cases['wrong_type'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::UNEXPECTED_CONTENT_TYPE,
                'msg' => 'Unexpected Content-Type',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'image/jpeg'],
                'body' => [],
            ],
            1,
        ];
        $cases['missing_item'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Folder Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Folder Not Found',
                    'code' => 404
                ],
            ],
            1,
        ];
        $cases['empty'] = [
            [
                'class' => \Exception::class,
                'code' => 400,
                'msg' => '{"error":"Missing folder_uuid","code":400}',
            ],
            [
                'code' => 400,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Missing folder_uuid',
                    'code' => 400
                ],
            ],
            1,
        ];

        return $cases;
    }

    /**
     * @dataProvider casesFolderRestorePostFail
     */
    public function testFolderRestorePostFail(array $expected, array $response, $folderUuid)
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

        $gc->folderRestorePost($folderUuid);
    }
}
