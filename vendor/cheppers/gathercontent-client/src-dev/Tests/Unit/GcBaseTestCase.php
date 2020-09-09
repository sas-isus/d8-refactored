<?php

namespace Cheppers\GatherContent\Tests\Unit;

use Cheppers\GatherContent\GatherContentClientException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

class GcBaseTestCase extends TestCase
{
    /**
     * @var array
     */
    protected $gcClientOptions = [
      'baseUri' => 'https://api.example.com',
      'email' => 'a@b.com',
      'apiKey' => 'a-b-c-d',
    ];

    protected static $uniqueNumber = 1;

    public static function getUniqueInt()
    {
        return static::$uniqueNumber++;
    }

    public static function getUniqueFloat()
    {
        return static::$uniqueNumber++ + (rand(1, 9) / 10);
    }

    public static function getUniqueString($prefix)
    {
        return "$prefix-" . static::$uniqueNumber++;
    }

    public static function getUniqueEmail($prefix)
    {
        return sprintf(
            '%s@%s.com',
            static::getUniqueString($prefix),
            static::getUniqueString($prefix)
        );
    }

    public static function getUniqueDate()
    {
        return date('Y-m-d H:i:s', rand(0, time()));
    }

    public static function getUniqueResponseAnnouncement()
    {
        return [
            'id' => static::getUniqueInt(),
            'name' => static::getUniqueString('name'),
            'acknowledged' => static::getUniqueString('acknowledged'),
        ];
    }

    public static function getUniqueResponseFile()
    {
        return [
            'id' => static::getUniqueInt(),
            'user_id' => static::getUniqueInt(),
            'item_id' => static::getUniqueInt(),
            'field' => 'field',
            'type' => 'type',
            'url' => static::getUniqueString('http://'),
            'filename' => static::getUniqueString('fileName'),
            'size' => static::getUniqueInt(),
            'created_at' => static::getUniqueDate(),
            'updated_at' => static::getUniqueDate(),
        ];
    }

    public static function getUniqueResponseUser()
    {
        return [
            'email' => 'email',
            'first_name' => 'firstName',
            'last_name' => 'lastName',
            'language' => 'language',
            'gender' => 'gender',
            'avatar' => 'avatar',
            'announcements' => [
                static::getUniqueResponseAnnouncement(),
                static::getUniqueResponseAnnouncement(),
                static::getUniqueResponseAnnouncement(),
            ],
        ];
    }

    public static function getUniqueResponseAccount()
    {
        return [
            'id' => static::getUniqueInt(),
            'name' => static::getUniqueString('name'),
            'slug' => static::getUniqueString('slug'),
            'timezone' => static::getUniqueString('timezone'),
        ];
    }

    public static function getUniqueResponseProject()
    {
        $allowedTags = [
            'a' => ['class' => '*'],
        ];

        return [
            'id' => static::getUniqueInt(),
            'name' => static::getUniqueString('name'),
            'type' => static::getUniqueString('type'),
            'example' => true,
            'account_id' => static::getUniqueInt(),
            'active' => true,
            'text_direction' => static::getUniqueString('text_direction'),
            'allowed_tags' => json_encode($allowedTags, JSON_PRETTY_PRINT),
            'created_at' => static::getUniqueInt(),
            'updated_at' => static::getUniqueInt(),
            'overdue' => true,
            'statuses' => [
                'data' => [
                    static::getUniqueResponseStatus(),
                    static::getUniqueResponseStatus(),
                    static::getUniqueResponseStatus(),
                ],
            ],
        ];
    }

    public static function getUniqueResponseDate()
    {
        return static::getUniqueDate();
    }

    public static function getUniqueResponseStatus()
    {
        return [
            'id' => static::getUniqueInt(),
            'is_default' => false,
            'position' => static::getUniqueString('position'),
            'color' => static::getUniqueString('color'),
            'name' => static::getUniqueString('name'),
            'description' => static::getUniqueString('description'),
            'can_edit' => true,
        ];
    }

    public static function getUniqueResponseElements(array $elementTypes)
    {
        $elements = [];

        foreach ($elementTypes as $elementType) {
            switch ($elementType) {
                case 'text':
                case 'guideline':
                    $elements[static::getUniqueString('uuid')] = static::getUniqueResponseElementText();
                    break;

                case 'files':
                case 'choice_radio':
                case 'choice_checkbox':
                    $elements[static::getUniqueString('uuid')] = static::getUniqueResponseElementArray($elementType);
                    break;
            }
        }

        return $elements;
    }

    public static function getUniqueResponseGroup(array $elementTypes)
    {
        $group = [
            'uuid' => static::getUniqueString('uuid'),
            'name' => static::getUniqueString('name'),
            'fields' => [],
        ];

        foreach ($elementTypes as $elementType) {
            switch ($elementType) {
                case 'text':
                    $group['fields'][] = static::getUniqueResponseElementTemplateText();
                    break;

                case 'files':
                    $group['fields'][] = static::getUniqueResponseElementTemplateFiles();
                    break;

                case 'guideline':
                    $group['fields'][] = static::getUniqueResponseElementTemplateGuideline();
                    break;

                case 'choice_radio':
                    $group['fields'][] = static::getUniqueResponseElementTemplateChoiceRadio();
                    break;

                case 'choice_checkbox':
                    $group['fields'][] = static::getUniqueResponseElementTemplateChoiceCheckbox();
                    break;
            }
        }

        return $group;
    }

    public static function getUniqueResponseElementText()
    {
        return static::getUniqueString('value');
    }

    public static function getUniqueResponseElementArray($elementType, $amount = null)
    {
        $elements = [];
        if (!$amount) {
            $amount = rand(1, 5);
        }

        for ($i = 0; $i < $amount; $i++) {
            switch ($elementType) {
                case 'files':
                    $elements[] = static::getUniqueResponseElementFile();
                    break;

                case 'choice_radio':
                case 'choice_checkbox':
                    $elements[] = static::getUniqueResponseElementChoice();
                    break;
            }
        }

        return $elements;
    }

    public static function getUniqueResponseElementFile()
    {
        return [
            'file_id' => static::getUniqueInt(),
            'filename' => static::getUniqueString('file'),
            'mime_type' => static::getUniqueString('mime_type'),
            'url' => static::getUniqueString('url'),
            'optimised_image_url' => self::getUniqueString('optimised_image_url'),
            'size' => static::getUniqueInt(),
        ];
    }

    public static function getUniqueResponseElementChoice()
    {
        return [
            'id' => static::getUniqueInt(),
            'label' => static::getUniqueString('label'),
        ];
    }

    public static function getUniqueResponseElement()
    {
        return [
            'uuid' => static::getUniqueString('uuid'),
            'field_type' => '',
            'label' => static::getUniqueString('label'),
            'instructions' => static::getUniqueString('instructions'),
            'metadata' => [],
        ];
    }

    public static function getUniqueResponseElementTemplateFiles()
    {
        $element = static::getUniqueResponseElement();
        $element['field_type'] = 'attachment';
        return $element;
    }

    public static function getUniqueResponseElementTemplateText()
    {
        $element = static::getUniqueResponseElement();
        $element['field_type'] = 'attachment';
        $element['metadata'] = [
            'is_plain' => true,
            'validation' => [
                'rule' => static::getUniqueString('rule'),
                'limit' => static::getUniqueInt(),
            ],
        ];
        return $element;
    }

    public static function getUniqueResponseElementTemplateGuideline()
    {
        $element = static::getUniqueResponseElement();
        $element['field_type'] = 'guidelines';
        return $element;
    }

    public static function getUniqueResponseElementTemplateChoiceRadio()
    {
        $element = static::getUniqueResponseElement();
        $element['field_type'] = 'choice_radio';
        $element['metadata'] = [
            'choice_fields' => [
                'options' => static::getUniqueResponseElementChoiceOptions(),
            ],
        ];
        return $element;
    }

    public static function getUniqueResponseElementTemplateChoiceCheckbox()
    {
        $element = static::getUniqueResponseElement();
        $element['field_type'] = 'choice_checkbox';
        $element['metadata'] = [
            'choice_fields' => [
                'options' => static::getUniqueResponseElementChoiceOptions(),
            ],
        ];
        return $element;
    }

    public static function getUniqueResponseElementChoiceOptions()
    {
        $amount = rand(1, 5);
        $keys = range(1, $amount);
        shuffle($keys);
        $options = [];
        for ($i = 0; $i < $amount; $i++) {
            $options[] = [
                'optionId' => static::getUniqueString('optionId'),
                'label' => static::getUniqueString('label'),
            ];
        }

        return $options;
    }

    public static function getUniqueResponseAssignedUsers()
    {
        $amount = rand(1, 5);
        $userIds = [];
        for ($i = 0; $i < $amount; $i++) {
            $userIds[] = static::getUniqueInt();
        }

        return $userIds;
    }

    public static function getUniqueResponseItem(array $elementTypes = [], array $structure = null)
    {
        $item = [
            'id' => static::getUniqueInt(),
            'project_id' => static::getUniqueInt(),
            'folder_uuid' => static::getUniqueString('folder_uuid'),
            'template_id' => static::getUniqueInt(),
            'structure' => $structure,
            'structure_uuid' => static::getUniqueString('structure_uuid'),
            'position' => static::getUniqueString('position'),
            'name' => static::getUniqueString('name'),
            'archived_by' => static::getUniqueInt(),
            'archived_at' => static::getUniqueInt(),
            'created_at' => static::getUniqueDate(),
            'updated_at' => static::getUniqueDate(),
            'next_due_at' => static::getUniqueDate(),
            'completed_at' => static::getUniqueDate(),
            'status_id' => static::getUniqueInt(),
            'assigned_user_ids' => static::getUniqueResponseAssignedUsers(),
            'assignee_count' => static::getUniqueInt(),
            'approval_count' => static::getUniqueInt(),
        ];

        if (!empty($elementTypes)) {
            $item['content'] = static::getUniqueResponseElements($elementTypes);
        }

        return $item;
    }

    public static function getUniqueResponseMeta()
    {
        $amount = rand(1, 5);
        $meta = [
            'assets' => [],
        ];
        for ($i = 0; $i < $amount; $i++) {
            $meta['assets'][] = static::getUniqueString('file');
        }

        return $meta;
    }

    public static function getUniqueResponseTemplate()
    {
        return [
            'id' => static::getUniqueInt(),
            'name' => static::getUniqueString('name'),
            'number_of_items_using' => static::getUniqueInt(),
            'structure_uuid' => static::getUniqueString('structure_uuid'),
            'project_id' => static::getUniqueInt(),
            'updated_at' => static::getUniqueResponseDate(),
            'updated_by' => static::getUniqueInt(),
        ];
    }

    public static function getUniqueResponseRelated(array $groups = [])
    {
        return ['structure' => static::getUniqueResponseStructure($groups)];
    }

    public static function getUniqueResponseStructure(array $groups = [])
    {
        $structure = [
            'uuid' => static::getUniqueString('uuid'),
            'groups' => [],
        ];

        foreach ($groups as $elementTypes) {
            $structure['groups'][] = static::getUniqueResponseGroup($elementTypes);
        }

        return $structure;
    }

    protected static function getUniqueResponseFolder()
    {
        return [
            'uuid' => static::getUniqueInt(),
            'name' => static::getUniqueString('name'),
            'position' => static::getUniqueString('position'),
            'parent_uuid' => static::getUniqueInt(),
            'project_id' => static::getUniqueInt(),
            'type' => static::getUniqueString('type'),
            'archived_at' => static::getUniqueInt(),
        ];
    }

    protected static function reKeyArray(array $array, $key)
    {
        $items = [];
        foreach ($array as $item) {
            $items[$item[$key]] = $item;
        }

        return $items;
    }

    protected static function basicFailCases($data = null)
    {
        return [
            'unauthorized' => [
                [
                    'class' => \Exception::class,
                    'code' => 401,
                    'msg' => '401 Unauthorized',
                ],
                [
                    'code' => 401,
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => '401 Unauthorized',
                ],
                42,
                (isset($data['id']) ? $data['id'] : (isset($data['name']) ? $data['name'] : null)),
                (isset($data['type']) ? $data['type'] : null)
            ],
            'internal-error' => [
                [
                    'class' => \Exception::class,
                    'code' => 500,
                    'msg' => '{"error":"unknown error"}',
                ],
                [
                    'code' => 500,
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => [
                        'error' => 'unknown error'
                    ],
                ],
                42,
                (isset($data['id']) ? $data['id'] : (isset($data['name']) ? $data['name'] : null)),
                (isset($data['type']) ? $data['type'] : null)
            ],
        ];
    }

    protected static function basicFailCasesGet($data = null)
    {
        $cases = self::basicFailCases($data);
        $cases['header-error'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::UNEXPECTED_CONTENT_TYPE,
                'msg' => 'Unexpected Content-Type: \'text/css\'',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'text/css'],
                'body' => [],
            ],
            42,
            (isset($data['id']) ? $data['id'] : (isset($data['name']) ? $data['name'] : null)),
            (isset($data['type']) ? $data['type'] : null)
        ];

        return $cases;
    }

    protected static function basicFailCasesPost($data = null)
    {
        $cases = self::basicFailCases($data);
        $cases['header-error'] = [
            [
                'class' => GatherContentClientException::class,
                'code' => GatherContentClientException::UNEXPECTED_ANSWER,
                'msg' => 'Unexpected answer',
            ],
            [
                'code' => 200,
                'headers' => ['Content-Type' => 'text/css'],
                'body' => [],
            ],
            42,
            (isset($data['id']) ? $data['id'] : (isset($data['name']) ? $data['name'] : null)),
            (isset($data['type']) ? $data['type'] : null)
        ];

        return $cases;
    }

    public function getBasicHttpClientTester(array $requests)
    {
        $requests[] = new RequestException(
            'Error Communicating with Server',
            new Request('GET', 'unexpected_request')
        );
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler($requests);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        return [
            'client' => $client,
            'container' => &$container,
            'history' => $history,
            'handlerStack' => $handlerStack,
            'mock' => $mock,
        ];
    }
}
