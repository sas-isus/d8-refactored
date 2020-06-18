<?php

namespace Cheppers\GatherContent\Tests\Unit;

use Cheppers\GatherContent\DataTypes\Related;
use Cheppers\GatherContent\DataTypes\Structure;
use Cheppers\GatherContent\DataTypes\Template;
use Cheppers\GatherContent\GatherContentClient;
use Cheppers\GatherContent\GatherContentClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class GatherContentClientTemplateTest extends GcBaseTestCase
{
    public function casesTemplatesGet()
    {
        $data = [
            static::getUniqueResponseTemplate(),
            static::getUniqueResponseTemplate()
        ];

        $templates = [];
        foreach ($data as $template) {
            $templates[] = new Template($template);
        }

        return [
            'empty' => [
                [],
                ['data' => []],
                42,
            ],
            'basic' => [
                $templates,
                ['data' => $templates],
                42,
            ],
        ];
    }

    /**
     * @dataProvider casesTemplatesGet
     */
    public function testTemplatesGet(array $expected, array $responseBody, $projectId)
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
            ->templatesGet($projectId);

        static::assertEquals(
            \GuzzleHttp\json_encode($expected, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual['data'], JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/projects/$projectId/templates",
            (string) $request->getUri()
        );
    }

    public function casesTemplatesGetFail()
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
     * @dataProvider casesTemplatesGetFail
     */
    public function testTemplatesGetFail(array $expected, array $response, $projectId)
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

        $gc->templatesGet($projectId);
    }

    public function casesTemplateGet()
    {
        $data = static::getUniqueResponseTemplate();
        $structure = static::getUniqueResponseRelated([
            ['text', 'files', 'choice_radio', 'choice_checkbox'],
            ['text', 'choice_checkbox'],
        ]);

        return [
            'empty' => [
                ['data' => [], 'related' => []],
                ['data' => [], 'related' => []],
                42,
            ],
            'basic' => [
                ['data' => $data, 'related' => $structure],
                ['data' => $data, 'related' => $structure],
                42,
            ],
        ];
    }

    /**
     * @dataProvider casesTemplateGet
     */
    public function testTemplateGet(array $expected, array $responseBody, $templateId)
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
            ->templateGet($templateId);

        if (!empty($expected['data'])) {
            static::assertTrue($actual['data'] instanceof Template, 'Data type of the return is Template');
            static::assertEquals(
                \GuzzleHttp\json_encode($expected['data'], JSON_PRETTY_PRINT),
                \GuzzleHttp\json_encode($actual['data'], JSON_PRETTY_PRINT)
            );
        } else {
            static::assertNull($actual['data']);
        }

        if (!empty($expected['related'])) {
            static::assertTrue($actual['related'] instanceof Related, 'Data type of the return is Related');
            static::assertTrue(
                $actual['related']->structure instanceof Structure,
                'Data type of the return is Structure'
            );
            static::assertEquals(
                \GuzzleHttp\json_encode($expected['related'], JSON_PRETTY_PRINT),
                \GuzzleHttp\json_encode($actual['related'], JSON_PRETTY_PRINT)
            );
        } else {
            static::assertNull($actual['related']);
        }

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/templates/$templateId",
            (string) $request->getUri()
        );
    }

    public function casesTemplateGetFail()
    {
        $cases = static::basicFailCasesGet();

        $cases['not_found'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Template Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Template Not Found',
                    'code' => 404
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesTemplateGetFail
     */
    public function testTemplateGetFail(array $expected, array $response, $templateId)
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

    public function casesTemplatePost()
    {
        $templateArray = static::getUniqueResponseTemplate();
        $template = new Template($templateArray);

        $structureArray = static::getUniqueResponseStructure([
            ['text', 'files', 'choice_radio', 'choice_checkbox']
        ]);
        $structure = new Structure($structureArray);

        $emptyStructure = new Structure();

        return [
            'basic' => [
                $template,
                $template->name,
                $structure,
                131313,
                $template->id,
            ],
            'empty' => [
                $template,
                $template->name,
                $emptyStructure,
                131313,
                $template->id,
            ],
        ];
    }

    /**
     * @dataProvider casesTemplatePost
     */
    public function testTemplatePost(Template $expected, $name, $structure, $projectId, $resultItemId)
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
            ->templatePost($projectId, $name, $structure);

        $actual->setSkipEmptyProperties(true);

        static::assertEquals($resultItemId, $actual->id);

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
            "{$this->gcClientOptions['baseUri']}/projects/$projectId/templates",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);

        static::assertArrayHasKey('name', $sentQueryVariables);
        static::assertEquals($sentQueryVariables['name'], $expected->name);
    }

    public function casesTemplatePostFail()
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
                'msg' => 'API Error: "Template Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Template Not Found',
                    'code' => 404
                ],
            ],
            42
        ];

        return $cases;
    }

    /**
     * @dataProvider casesTemplatePostFail
     */
    public function testTemplatePostFail(array $expected, array $response, $projectId)
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

        $gc->templatePost($projectId, 'name', new Structure());
    }

    public function casesTemplateRenamePost()
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
     * @dataProvider casesTemplateRenamePost
     */
    public function testTemplateRenamePost(Template $template, $templateId, $name)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                200,
                [
                    'Content-Type' => 'application/json',
                ],
                \GuzzleHttp\json_encode(['data' => $template])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $actual = (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->templateRenamePost($templateId, $name);

        static::assertTrue($actual instanceof Template, 'Data type of the return is Template');
        static::assertEquals(
            \GuzzleHttp\json_encode($template, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/templates/$templateId/rename",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);

        static::assertArrayHasKey('name', $sentQueryVariables);
        static::assertArrayNotHasKey('content', $sentQueryVariables);
        static::assertEquals($sentQueryVariables['name'], $name);
    }

    public function casesTemplateRenamePostFail()
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
                'msg' => 'API Error: "Template Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Template Not Found',
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
     * @dataProvider casesTemplateRenamePostFail
     */
    public function testTemplateRenamePostFail(array $expected, array $response, $templateId, $name)
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

        $gc->templateRenamePost($templateId, $name);
    }

    public function casesTemplateDuplicatePost()
    {
        $templateArray = static::getUniqueResponseItem();
        $template = new Template($templateArray);

        return [
            'basic' => [
                $template,
                $template->id,
                $template->projectId
            ],
            'empty_project' => [
                $template,
                $template->id,
                null
            ],
        ];
    }

    /**
     * @dataProvider casesTemplateDuplicatePost
     */
    public function testTemplateDuplicateTemplatePost(Template $template, $templateId, $projectId = null)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                201,
                ['Content-Type' => 'application/json'],
                \GuzzleHttp\json_encode(['data' => $template])
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        $client = (new GatherContentClient($client));
        $actual = $client->setOptions($this->gcClientOptions)
            ->templateDuplicatePost($templateId, $projectId);

        static::assertTrue($actual instanceof Template, 'Data type of the return is Template');
        static::assertEquals(
            \GuzzleHttp\json_encode($template, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual, JSON_PRETTY_PRINT)
        );

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('POST', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/templates/$templateId/duplicate",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        $sentQueryVariables = \GuzzleHttp\json_decode($requestBody, true);

        if (!empty($projectId)) {
            static::assertArrayHasKey('project_id', $sentQueryVariables);
            static::assertEquals($sentQueryVariables['project_id'], $projectId);
        } else {
            static::assertArrayNotHasKey('project_id', $sentQueryVariables);
        }
    }

    public function casesTemplateDuplicatePostFail()
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
            1
        ];
        $cases['missing_item'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::API_ERROR,
                'msg' => 'API Error: "Template Not Found", Code: 404',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'error' => 'Template Not Found',
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
     * @dataProvider casesTemplateDuplicatePostFail
     */
    public function testTemplateDuplicatePostFail(array $expected, array $response, $templateId)
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

        $gc->templateDuplicatePost($templateId);
    }

    public function casesTemplateDelete()
    {
        return [
            'basic' => [
                13
            ],
        ];
    }

    /**
     * @dataProvider casesTemplateDelete
     */
    public function testTemplateDelete($templateId)
    {
        $tester = $this->getBasicHttpClientTester([
            new Response(
                204,
                [
                    'Content-Type' => 'application/json',
                ]
            ),
        ]);
        $client = $tester['client'];
        $container = &$tester['container'];

        (new GatherContentClient($client))
            ->setOptions($this->gcClientOptions)
            ->templateDelete($templateId);

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('DELETE', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v2+json'], $request->getHeader('Accept'));
        static::assertEquals(['api.example.com'], $request->getHeader('Host'));
        static::assertEquals(
            "{$this->gcClientOptions['baseUri']}/templates/$templateId",
            (string) $request->getUri()
        );

        $requestBody = $request->getBody();
        static::assertEmpty($requestBody->getContents());
    }
}
