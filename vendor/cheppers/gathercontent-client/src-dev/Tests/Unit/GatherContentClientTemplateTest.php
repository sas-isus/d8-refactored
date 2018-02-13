<?php

namespace Cheppers\GatherContent\Tests\Unit;

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
            static::getUniqueResponseTemplate([
                ['text', 'files', 'choice_radio', 'choice_checkbox'],
            ]),
            static::getUniqueResponseTemplate([
                ['text', 'choice_radio', 'choice_checkbox'],
                ['text', 'choice_radio'],
            ]),
            static::getUniqueResponseTemplate([
                ['choice_radio', 'choice_checkbox'],
            ]),
        ];

        $templates = static::reKeyArray($data, 'id');
        foreach (array_keys($templates) as $templateId) {
            foreach (array_keys($templates[$templateId]['config']) as $tabId) {
                $templates[$templateId]['config'][$tabId]['elements'] = static::reKeyArray(
                    $templates[$templateId]['config'][$tabId]['elements'],
                    'name'
                );
            }
        }

        return [
            'empty' => [
                [],
                ['data' => []],
                42,
            ],
            'basic' => [
                $templates,
                ['data' => $data],
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
            "{$this->gcClientOptions['baseUri']}/templates?project_id=$projectId",
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
        $data = static::getUniqueResponseTemplate([
            ['text', 'files', 'section', 'choice_radio', 'choice_checkbox'],
        ]);

        $template = $data;
        foreach (array_keys($template['config']) as $tabId) {
            $template['config'][$tabId]['elements'] = static::reKeyArray(
                $template['config'][$tabId]['elements'],
                'name'
            );
        }

        return [
            'empty' => [
                [],
                ['data' => []],
                42,
            ],
            'basic' => [
                $template,
                ['data' => $data],
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

        if ($expected) {
            static::assertTrue($actual instanceof Template, 'Data type of the return is Template');
            static::assertEquals(
                json_encode($expected, JSON_PRETTY_PRINT),
                json_encode($actual, JSON_PRETTY_PRINT)
            );
        } else {
            static::assertNull($actual);
        }

        /** @var Request $request */
        $request = $container[0]['request'];

        static::assertEquals(1, count($container));
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals(['application/vnd.gathercontent.v0.5+json'], $request->getHeader('Accept'));
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
                'msg' => 'API Error: "Template Not Found"',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => [
                    'data' => [
                        'message' => 'Template Not Found'
                    ]
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
}
