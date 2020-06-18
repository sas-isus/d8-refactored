<?php

namespace Cheppers\GatherContent\Tests\Unit;

use Cheppers\GatherContent\DataTypes\Structure;
use Cheppers\GatherContent\DataTypes\Template;
use Cheppers\GatherContent\GatherContentClient;
use Cheppers\GatherContent\GatherContentClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class GatherContentClientStructureTest extends GcBaseTestCase
{
    public function casesStructureGet()
    {
        $structure = static::getUniqueResponseStructure([
            ['text', 'files', 'choice_radio', 'choice_checkbox'],
        ]);
        $structureNoFields = static::getUniqueResponseStructure();

        return [
            'empty' => [
                [],
                ['data' => []],
                42,
            ],
            'basic' => [
                $structure,
                ['data' => $structure],
                42,
            ],
            'no_fields' => [
                $structureNoFields,
                ['data' => $structureNoFields],
                42,
            ],
        ];
    }

    /**
     * @dataProvider casesStructureGet
     */
    public function testStructureGet($expected, array $responseBody, $structureUuid)
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

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->structureGet($structureUuid);

        if (!empty($expected)) {
            static::assertTrue($actual instanceof Structure, 'Data type of the return is Structure');
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
            "{$this->gcClientOptions['baseUri']}/structures/$structureUuid",
            (string) $request->getUri()
        );
    }

    public function casesStructureGetFail()
    {
        $cases = static::basicFailCasesGet();

        $cases['not_found'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Structure Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Structure Not Found',
                    'code' => 404
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesStructureGetFail
     */
    public function testStructureGetFail(array $expected, array $response, $templateId)
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

        $gc->templateGet($templateId);
    }

    public function casesStructureAlterPut()
    {
        $structureArray = static::getUniqueResponseStructure([
            ['text', 'files', 'choice_radio', 'choice_checkbox'],
        ]);
        $structureNoFieldsArray = static::getUniqueResponseStructure();

        foreach (array_keys($structureArray['groups']) as $groupId) {
            $structureArray['groups'][$groupId]['fields'] = static::reKeyArray(
                $structureArray['groups'][$groupId]['fields'],
                'uuid'
            );
        }

        $structure = new Structure($structureArray);
        $structureNoFields = new Structure($structureNoFieldsArray);

        return [
            'basic' => [
                $structure,
                $structure->id,
                $structure,
                1,
            ],
            'empty' => [
                $structureNoFields,
                $structureNoFields->id,
                $structureNoFields,
                null,
            ],
        ];
    }

    /**
     * @dataProvider casesStructureAlterPut
     */
    public function testStructureAlterPut(Structure $expected, $structureUuid, Structure $structure, $priorityItemId)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                200,
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
            ->structureAlterPut($structureUuid, $structure, $priorityItemId);

        $actual->setSkipEmptyProperties(true);

        static::assertTrue($actual instanceof Structure, 'Data type of the return is Structure');
        static::assertEquals(
            \GuzzleHttp\json_encode($expected, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('PUT', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/structures/$structureUuid",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);

        if (!empty($priorityItemId)) {
            static::assertArrayHasKey('priority_item_id', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['priority_item_id'], $priorityItemId);
        } else {
            static::assertArrayNotHasKey('priority_item_id', $sentQueryVariables);
        }
    }

    public function casesStructureAlterPutFail()
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
            1
        ];
        $cases['missing_item'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Structure Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Structure Not Found',
                    'code' => 404
                ],
            ],
            1,
            1
        ];
        $cases['empty'] = [
            [
                'class' => \Exception::class,
                'code' => 400,
                'msg' => '{"error":"Missing structure_uuid","code":400}',
            ],
            [
                'code' => 400,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Missing structure_uuid',
                    'code' => 400
                ],
            ],
            1,
            1
        ];

        return $cases;
    }

    /**
     * @dataProvider casesStructureAlterPutFail
     */
    public function testStructureAlterPutFail(array $expected, array $response, $structureUuid, $priorityItemId)
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

        $gc->structureAlterPut($structureUuid, new Structure(), $priorityItemId);
    }

    public function casesStructureSaveAsTemplatePost()
    {
        $templateArray = static::getUniqueResponseTemplate();
        $template = new Template($templateArray);

        return [
            'basic' => [
                $template,
                13,
                $template->name,
            ],
        ];
    }

    /**
     * @dataProvider casesStructureSaveAsTemplatePost
     */
    public function testStructureSaveAsTemplatePost(Template $expected, $structureUuid, $name)
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
            ->structureSaveAsTemplatePost($structureUuid, $name);

        static::assertTrue($actual instanceof Template, 'Data type of the return is Template');
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
            "{$this->gcClientOptions['baseUri']}/structures/$structureUuid/save_as_template",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);

        static::assertArrayHasKey('name', $sentQueryVariables);
        static::assertEquals($sentQueryVariables['name'], $name);
    }

    public function casesStructureSaveAsTemplatePostFail()
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
        $cases['missing_item'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Structure Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Structure Not Found',
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
                'msg' => '{"error":"Missing structure_uuid","code":400}',
            ],
            [
                'code' => 400,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Missing structure_uuid',
                    'code' => 400
                ],
            ],
            1,
            ''
        ];

        return $cases;
    }

    /**
     * @dataProvider casesStructureSaveAsTemplatePostFail
     */
    public function testStructureSaveAsTemplatePostFail(array $expected, array $response, $structureUuid, $name)
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

        $gc->structureSaveAsTemplatePost($structureUuid, $name);
    }
}
