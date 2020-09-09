<?php

namespace Cheppers\GatherContent\Tests\Unit;

use Cheppers\GatherContent\DataTypes\Item;
use Cheppers\GatherContent\GatherContentClient;
use Cheppers\GatherContent\GatherContentClientException;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * Class GatherContentClientItemTest.
 *
 * @package Cheppers\GatherContent\Tests\Unit
 */
class GatherContentClientItemTest extends GcBaseTestCase
{
    public function casesItemsGet()
    {
        $data = [
            static::getUniqueResponseItem(),
            static::getUniqueResponseItem(),
            static::getUniqueResponseItem(),
        ];

        $items = [];
        foreach ($data as $item) {
            $items[] = new Item($item);
        }

        return [
            'empty' => [
                [],
                ['data' => []],
                42,
            ],
            'basic' => [
                $items,
                ['data' => $data],
                42,
            ],
        ];
    }

    /**
     * @dataProvider casesItemsGet
     */
    public function testItemsGet(array $expected, array $responseBody, $projectId)
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
            ->itemsGet($projectId);

        static::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT),
            json_encode($actual['data'], JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/projects/$projectId/items",
            (string) $request->getUri()
        );
    }

    public function casesItemsGetFail()
    {
        $cases = static::basicFailCasesGet();

        $cases['not_found'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Project Not Found", Code: 404',
            ],
            [
                'code' => 200,
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
     * @dataProvider casesItemsGetFail
     */
    public function testItemsGetFail(array $expected, array $response, $projectId)
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

        $gc->itemsGet($projectId);
    }

    public function casesItemGet()
    {
        $itemArray = static::getUniqueResponseItem([
            'text', 'choice_checkbox'
        ]);

        $item = new Item($itemArray);

        return [
            'empty' => [
                [],
                ['data' => []],
                42,
            ],
            'basic' => [
                $item,
                ['data' => $item],
                $item ->id
            ],
        ];
    }

    /**
     * @dataProvider casesItemGet
     */
    public function testItemGet($expected, array $responseBody, $itemId)
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
            ->itemGet($itemId);

        if ($expected) {
            static::assertTrue($actual instanceof Item, 'Data type of the return is Item');
            static::assertEquals(
                \GuzzleHttp\json_encode($expected, JSON_PRETTY_PRINT),
                \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
            );
        } else {
            static::assertNull($actual);
        }

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/items/$itemId",
            (string) $request->getUri()
        );
    }

    public function casesItemGetFail()
    {
        $cases = static::basicFailCasesGet();

        $cases['not_found'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Item Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Item Not Found',
                    'code' => 404
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesItemGetFail
     */
    public function testItemGetFail(array $expected, array $response, $itemId)
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

        $gc->itemGet($itemId);
    }

    public function casesItemChooseStatusPost()
    {
        return [
            'basic' => [
                [
                    'code' => 202,
                ],
                [
                    'code' => 202,
                    'body' => [],
                ],
                42,
                423
            ],
        ];
    }

    /**
     * @dataProvider casesItemChooseStatusPost
     */
    public function testItemChooseStatusPost(array $expected, array $response, $itemId, $statusId)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                $response['code'],
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($response['body'])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $client = (new GatherContentClient($client));
        $client->setOptions($this->gcClientOptions)
            ->itemChooseStatusPost($itemId, $statusId);


        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals($expected['code'], $client->getResponse()->getStatusCode());
        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/items/{$itemId}/choose_status",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);

        if (!empty($statusId)) {
            static::assertArrayHasKey('status_id', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['status_id'], $statusId);
        } else {
            static::assertArrayNotHasKey('status_id', $sentQueryVariables);
        }
    }

    public function casesItemChooseStatusPostFail()
    {
        $cases['empty'] = [
            [
                'class' => \Exception::class,
                'code' => 400,
                'msg' => '{"error":"Missing status_id","code":400}',
            ],
            [
                'code' => 400,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Missing status_id',
                    'code' => 400
                ],
            ],
            42,
            0
        ];

        return $cases;
    }

    /**
     * @dataProvider casesItemChooseStatusPostFail
     */
    public function testItemChooseStatusPostFail(array $expected, array $response, $itemId, $statusId)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                $response['code'],
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode($response['body'])
            ),
        ]);
        $client = $tester['client'];

        $gc = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions);

        static::expectException($expected['class']);
        static::expectExceptionCode($expected['code']);
        static::expectExceptionMessage($expected['msg']);

        $gc->itemChooseStatusPost($itemId, $statusId);
    }

    public function casesItemPost()
    {
        $itemArray = static::getUniqueResponseItem();
        $itemEmpty = new Item($itemArray);
        $itemEmpty->folderUuid = null;
        $itemEmpty->templateId = 234;

        $itemArray = static::getUniqueResponseItem([
            'text'
        ], static::getUniqueResponseStructure([
            ['text'],
        ]));
        $itemCustom = new Item($itemArray);
        $itemCustom->folderUuid = '500';
        $itemCustom->templateId = null;

        $itemArray = static::getUniqueResponseItem([
            'text', 'files', 'choice_radio', 'choice_checkbox'
        ]);
        $itemMultipleElements = new Item($itemArray);

        $itemArray = static::getUniqueResponseItem([
            'text', 'files', 'choice_radio', 'choice_checkbox'
        ]);
        $itemAssets = new Item($itemArray);
        $sentItem = new Item($itemArray);
        $sentItem->assets = [
            'field-uuid' => [__DIR__.'/files/test.txt'],
        ];

        return [
            'empty' => [
                $itemEmpty,
                $itemEmpty,
                131313,
                $itemEmpty->id,
                [],
            ],
            'custom' => [
                $itemCustom,
                $itemCustom,
                131313,
                $itemCustom->id,
                [],
            ],
            'multiple-elements' => [
                $itemMultipleElements,
                $itemMultipleElements,
                131313,
                $itemMultipleElements->id,
                [],
            ],
            'with-assets' => [
                $itemAssets,
                $sentItem,
                131313,
                $itemAssets->id,
                static::getUniqueResponseMeta(),
            ],
        ];
    }

    /**
     * @dataProvider casesItemPost
     */
    public function testItemPost(Item $expected, Item $item, $projectId, $resultItemId, $meta)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                201,
                [
                    'Content-Type' => 'application/json',
                ],
                \GuzzleHttp\json_encode(['data' => $expected, 'meta' => $meta])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->itemPost($projectId, $item);

        $actual['data']->setSkipEmptyProperties(true);
        $expected->setSkipEmptyProperties(true);

        static::assertEquals($resultItemId, $actual['data']->id);

        if (!empty($meta)) {
            static::assertEquals($actual['meta']->assets, $meta['assets']);
        }

        static::assertTrue($actual['data'] instanceof Item, 'Data type of the return is Item');
        static::assertEquals(
            \GuzzleHttp\json_encode($expected, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual['data'], JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/projects/$projectId/items",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        if (!empty($item->assets)) {
            static::assertInstanceOf(MultipartStream::class, $requestBody);
            return;
        }

        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);
        if (!empty($item->folderUuid)) {
            static::assertArrayHasKey('folder_uuid', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['folder_uuid'], $expected->folderUuid);
        } else {
            static::assertArrayNotHasKey('folder_uuid', $sentQueryVariables);
        }

        if (!empty($item->templateId)) {
            static::assertArrayHasKey('template_id', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['template_id'], $expected->templateId);
        } else {
            static::assertArrayNotHasKey('template_id', $sentQueryVariables);
        }

        if (!empty($item->content)) {
            static::assertArrayHasKey('content', $sentQueryVariables);
            // We need to do this because the 'value' parameter on text types
            // will be converted to simple string instead of array.
            $preparedContent = \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($expected->content), true);
            static::assertEquals($sentQueryVariables['content'], $preparedContent);
        } else {
            static::assertArrayNotHasKey('content', $sentQueryVariables);
        }
    }

    public function testItemsPostNoPath()
    {
        $item = new Item();
        $tester = $this->getBasicHttpClientTester([
            new Response(
                201,
                [
                    'Content-Type' => 'application/json',
                    'Location' => $this->gcClientOptions['baseUri'],
                ]
            ),
        ]);
        $client = $tester['client'];

        static::expectException(\Exception::class);
        (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->itemPost(0, $item);
    }

    public function testItemsPostUnexpectedStatusCode()
    {
        $item = new Item();
        $tester = $this->getBasicHttpClientTester([
            new Response(200, []),
        ]);
        $client = $tester['client'];

        static::expectException(\Exception::class);
        (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->itemPost(0, $item);
    }

    public function casesItemUpdatePost()
    {
        $itemArray = static::getUniqueResponseItem();
        $itemEmpty = new Item($itemArray);
        $itemEmpty->folderUuid = null;
        $itemEmpty->templateId = 234;

        $itemArray = static::getUniqueResponseItem([
            'text'
        ]);
        $itemCustom = new Item($itemArray);
        $itemCustom->folderUuid = '500';
        $itemCustom->templateId = null;

        $itemArray = static::getUniqueResponseItem([
            'text', 'files', 'choice_radio', 'choice_checkbox'
        ]);
        $itemMultipleElements = new Item($itemArray);

        $itemArray = static::getUniqueResponseItem([
            'text', 'files', 'choice_radio', 'choice_checkbox'
        ]);
        $itemAssets = new Item($itemArray);

        return [
            'empty' => [
                13,
                $itemEmpty->content,
                [],
                [],
            ],
            'custom' => [
                13,
                $itemCustom->content,
                [],
                [],
            ],
            'multiple-elements' => [
                13,
                $itemMultipleElements->content,
                [],
                [],
            ],
            'with-assets' => [
                13,
                $itemAssets->content,
                ['field-uuid' => [__DIR__.'/files/test.txt']],
                static::getUniqueResponseMeta(),
            ],
        ];
    }

    /**
     * @dataProvider casesItemUpdatePost
     */
    public function testItemUpdatePost($itemId, array $content, array $assets, array $meta)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                202,
                [
                    'Content-Type' => 'application/json',
                ],
                \GuzzleHttp\json_encode(['meta' => $meta])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $item = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->itemUpdatePost($itemId, $content, $assets);

        /** @var Request $request */
        $request = $container[0]['request'];

        if (!empty($meta)) {
            static::assertEquals($item->assets, $meta['assets']);
        }
        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/items/$itemId/content",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        if (!empty($assets)) {
            static::assertInstanceOf(MultipartStream::class, $requestBody);
            return;
        }

        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);
        static::assertArrayHasKey('content', $sentQueryVariables);
        // We need to do this because the 'value' parameter on text types
        // will be converted to simple string instead of array.
        $preparedContent = \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($content), true);
        static::assertEquals($sentQueryVariables['content'], $preparedContent);
    }

    public function casesItemRenamePost()
    {
        $itemArray = static::getUniqueResponseItem();
        $item = new Item($itemArray);

        return [
            'basic' => [
                $item,
                13,
                $item->name,
            ],
        ];
    }

    /**
     * @dataProvider casesItemRenamePost
     */
    public function testItemRenamePost(Item $item, $itemId, $name)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                200,
                [
                    'Content-Type' => 'application/json',
                ],
                \GuzzleHttp\json_encode(['data' => $item])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->itemRenamePost($itemId, $name);

        static::assertTrue($actual instanceof Item, 'Data type of the return is Item');
        static::assertEquals(
            \GuzzleHttp\json_encode($item, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/items/$itemId/rename",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);

        static::assertArrayHasKey('name', $sentQueryVariables);
        static::assertArrayNotHasKey('content', $sentQueryVariables);
        static::assertEquals($sentQueryVariables['name'], $name);
    }

    public function casesItemRenamePostFail()
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
                'msg' => 'API Error: "Item Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Item Not Found',
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
                'msg' => '{"error":"Missing template_id","code":400}',
            ],
            [
                'code' => 400,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Missing template_id',
                    'code' => 400
                ],
            ],
            1,
            ''
        ];

        return $cases;
    }

    /**
     * @dataProvider casesItemRenamePostFail
     */
    public function testItemRenamePostFail(array $expected, array $response, $itemId, $name)
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

        $gc->itemRenamePost($itemId, $name);
    }

    public function casesItemMovePost()
    {
        $itemArray = static::getUniqueResponseItem();
        $item = new Item($itemArray);

        return [
            'basic' => [
                $item,
                13,
                $item->position,
                $item->folderUuid,
            ],
        ];
    }

    /**
     * @dataProvider casesItemMovePost
     */
    public function testItemMovePost(Item $item, $itemId, $position, $folderUuid)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                200,
                [
                    'Content-Type' => 'application/json',
                ],
                \GuzzleHttp\json_encode(['data' => $item])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->itemMovePost($itemId, $position, $folderUuid);

        static::assertTrue($actual instanceof Item, 'Data type of the return is Item');
        static::assertEquals(
            \GuzzleHttp\json_encode($item, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/items/$itemId/move",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);

        static::assertArrayHasKey('position', $sentQueryVariables);
        static::assertArrayHasKey('folder_uuid', $sentQueryVariables);
        static::assertArrayNotHasKey('content', $sentQueryVariables);
        static::assertEquals($sentQueryVariables['position'], $position);
        static::assertEquals($sentQueryVariables['folder_uuid'], $folderUuid);
    }

    public function casesItemMovePostFail()
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
                'msg' => 'API Error: "Item Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Item Not Found',
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
                'msg' => '{"error":"Missing template_id","code":400}',
            ],
            [
                'code' => 400,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Missing template_id',
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
     * @dataProvider casesItemMovePostFail
     */
    public function testItemMovePostFail(array $expected, array $response, $itemId, $position, $folderUuid)
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

        $gc->itemMovePost($itemId, $position, $folderUuid);
    }

    public function casesItemApplyTemplatePost()
    {
        $itemArray = static::getUniqueResponseItem();
        $item = new Item($itemArray);

        return [
            'basic' => [
                $item,
                $item->id,
                $item->templateId
            ],
        ];
    }

    /**
     * @dataProvider casesItemApplyTemplatePost
     */
    public function testItemApplyTemplatePost(Item $item, $itemId, $templateId)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode(['data' => $item])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $client = (new GatherContentClient($client));
        $actual = $client->setOptions($this->gcClientOptions)
            ->itemApplyTemplatePost($itemId, $templateId);

        static::assertTrue($actual instanceof Item, 'Data type of the return is Item');
        static::assertEquals(
            \GuzzleHttp\json_encode($item, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/items/$itemId/apply_template",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);

        if ($templateId) {
            static::assertArrayHasKey('template_id', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['template_id'], $templateId);
        } else {
            static::assertArrayNotHasKey('template_id', $sentQueryVariables);
        }
    }

    public function casesItemApplyTemplatePostFail()
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
            0
        ];
        $cases['missing_item'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Item Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Item Not Found',
                    'code' => 404
                ],
            ],
            0,
            423
        ];
        $cases['empty'] = [
            [
                'class' => \Exception::class,
                'code' => 400,
                'msg' => '{"error":"Missing template_id","code":400}',
            ],
            [
                'code' => 400,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Missing template_id',
                    'code' => 400
                ],
            ],
            42,
            0
        ];

        return $cases;
    }

    /**
     * @dataProvider casesItemApplyTemplatePostFail
     */
    public function testItemApplyTemplatePostFail(array $expected, array $response, $itemId, $templateId)
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

        $gc->itemApplyTemplatePost($itemId, $templateId);
    }

    public function casesItemDisconnectTemplatePost()
    {
        $itemArray = static::getUniqueResponseItem();
        $item = new Item($itemArray);

        return [
            'basic' => [
                $item,
                $item->id
            ],
        ];
    }

    /**
     * @dataProvider casesItemDisconnectTemplatePost
     */
    public function testItemDisconnectTemplatePost(Item $item, $itemId)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode(['data' => $item])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $client = (new GatherContentClient($client));
        $actual = $client->setOptions($this->gcClientOptions)
            ->itemDisconnectTemplatePost($itemId);

        static::assertTrue($actual instanceof Item, 'Data type of the return is Item');
        static::assertEquals(
            \GuzzleHttp\json_encode($item, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/items/$itemId/disconnect_template",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        static::assertEmpty($requestBody->getContents());
    }

    public function casesItemDisconnectTemplatePostFail()
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
            1
        ];
        $cases['missing_item'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Item Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Item Not Found',
                    'code' => 404
                ],
            ],
            0
        ];
        $cases['empty'] = [
            [
                'class' => \Exception::class,
                'code' => 400,
                'msg' => '{"error":"Missing template_id","code":400}',
            ],
            [
                'code' => 400,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Missing template_id',
                    'code' => 400
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesItemDisconnectTemplatePostFail
     */
    public function testItemDisconnectTemplatePostFail(array $expected, array $response, $itemId)
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

        $gc->itemDisconnectTemplatePost($itemId);
    }

    public function casesItemDuplicatePost()
    {
        $itemArray = static::getUniqueResponseItem();
        $item = new Item($itemArray);

        return [
            'basic' => [
                $item,
                $item->id
            ],
        ];
    }

    /**
     * @dataProvider casesItemDuplicatePost
     */
    public function testItemDuplicateTemplatePost(Item $item, $itemId)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode(['data' => $item])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $client = (new GatherContentClient($client));
        $actual = $client->setOptions($this->gcClientOptions)
            ->itemDuplicatePost($itemId);

        static::assertTrue($actual instanceof Item, 'Data type of the return is Item');
        static::assertEquals(
            \GuzzleHttp\json_encode($item, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/items/$itemId/duplicate",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        static::assertEmpty($requestBody->getContents());
    }

    public function casesItemDuplicatePostFail()
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
            1
        ];
        $cases['missing_item'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Item Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Item Not Found',
                    'code' => 404
                ],
            ],
            0
        ];
        $cases['empty'] = [
            [
                'class' => \Exception::class,
                'code' => 400,
                'msg' => '{"error":"Missing template_id","code":400}',
            ],
            [
                'code' => 400,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Missing template_id',
                    'code' => 400
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesItemDuplicatePostFail
     */
    public function testItemDuplicatePostFail(array $expected, array $response, $itemId)
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

        $gc->itemDuplicatePost($itemId);
    }
}
