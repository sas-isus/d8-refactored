<?php

namespace Cheppers\GatherContent;

use Cheppers\GatherContent\DataTypes\Folder;
use Cheppers\GatherContent\DataTypes\Item;
use Cheppers\GatherContent\DataTypes\Pagination;
use Cheppers\GatherContent\DataTypes\Structure;
use GuzzleHttp\ClientInterface;

class GatherContentClient implements GatherContentClientInterface
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * {@inheritdoc}
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @var string
     */
    protected $email = '';

    public function getEmail()
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($value)
    {
        $this->email = $value;

        return $this;
    }

    /**
     * @var string
     */
    protected $apiKey = '';

    /**
     * {@inheritdoc}
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * {@inheritdoc}
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @var string
     */
    protected $baseUri = 'https://api.gathercontent.com';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseUri($value)
    {
        $this->baseUri = $value;

        return $this;
    }

    protected function getUri($path)
    {
        return $this->getBaseUri()."/$path";
    }

    /**
     * @var bool
     */
    protected $useLegacy = false;

    /**
     * {@inheritdoc}
     */
    public function getUseLegacy()
    {
        return $this->useLegacy;
    }

    /**
     * {@inheritdoc}
     */
    public function setUseLegacy($value)
    {
        $this->useLegacy = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @return $this
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'baseUri':
                    $this->setBaseUri($value);
                    break;

                case 'email':
                    $this->setEmail($value);
                    break;

                case 'apiKey':
                    $this->setApiKey($value);
                    break;

                case 'frameworkVersion':
                    $this->setFrameworkVersion($value);
                    break;

                case 'frameworkName':
                    $this->setFrameworkName($value);
                    break;
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function projectTypes()
    {
        return [
            static::PROJECT_TYPE_WEBSITE_BUILDING,
            static::PROJECT_TYPE_ONGOING_WEBSITE_CONTENT,
            static::PROJECT_TYPE_MARKETING_EDITORIAL_CONTENT,
            static::PROJECT_TYPE_EMAIL_MARKETING_CONTENT,
            static::PROJECT_TYPE_OTHER,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function meGet()
    {
        $this->setUseLegacy(true);
        $this->sendGet('me');

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\User::class);
    }

    /**
     * {@inheritdoc}
     */
    public function accountsGet()
    {
        $this->setUseLegacy(true);
        $this->sendGet('accounts');

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseItems($body, DataTypes\Account::class);
    }

    /**
     * {@inheritdoc}
     */
    public function accountGet($accountId)
    {
        $this->setUseLegacy(true);
        $this->sendGet("accounts/$accountId");

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Account::class);
    }

    /**
     * {@inheritdoc}
     */
    public function projectsGet($accountId)
    {
        $this->setUseLegacy(true);
        $this->sendGet('projects', ['query' => ['account_id' => $accountId]]);

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseItems($body, DataTypes\Project::class);
    }

    /**
     * {@inheritdoc}
     */
    public function projectGet($projectId)
    {
        $this->setUseLegacy(true);
        $this->sendGet("projects/$projectId");

        $this->validateResponse();
        $body = $this->parseResponse();
        $body += ['meta' => []];

        return empty($body['data'])
            ? null
            : $this->parseResponseDataItem(
                $body['data'] + ['meta' => $body['meta']],
                DataTypes\Project::class
            );
    }

    /**
     * {@inheritdoc}
     */
    public function projectsPost($accountId, $projectName, $projectType)
    {
        $this->setUseLegacy(true);
        $this->sendPost('projects', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [
                'account_id' => $accountId,
                'name' => $projectName,
                'type' => $projectType,
            ],
        ]);

        $this->validatePostResponse(202);

        $locations = $this->response->getHeader('Location');
        $locationPath = parse_url(reset($locations), PHP_URL_PATH);
        $matches = [];
        if (!preg_match('@/projects/(?P<projectId>\d+)$@', $locationPath, $matches)) {
            throw new GatherContentClientException(
                'Invalid response header the project ID is missing',
                GatherContentClientException::INVALID_RESPONSE_HEADER
            );
        }

        return $matches['projectId'];
    }

    /**
     * {@inheritdoc}
     */
    public function projectStatusesGet($projectId)
    {
        $this->setUseLegacy(true);
        $this->sendGet("projects/$projectId/statuses");

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseItems($body, DataTypes\Status::class);
    }

    /**
     * {@inheritdoc}
     */
    public function projectStatusGet($projectId, $statusId)
    {
        $this->setUseLegacy(true);
        $this->sendGet("projects/$projectId/statuses/$statusId");

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Status::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemsGet($projectId, $query = [])
    {
        $this->setUseLegacy(false);
        $this->sendGet("projects/$projectId/items", ['query' => $query]);

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseItems($body, DataTypes\Item::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemGet($itemId)
    {
        $this->setUseLegacy(false);
        $this->sendGet("items/$itemId");

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Item::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemPost($projectId, Item $item)
    {
        $this->setUseLegacy(false);
        $item->setSkipEmptyProperties(true);
        $request = [
            'json' => $item,
        ];

        if (!empty($item->assets)) {
            $item->content = \GuzzleHttp\json_encode($item->content);

            // Convert data to array so we can format it to multipart type.
            $itemArray = \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($item), true);

            $request = [
                'multipart' => $this->formatRequestMultipart($itemArray),
            ];
        }

        $this->sendPost("projects/$projectId/items", $request);

        $this->validatePostResponse(201);
        $body = $this->parseResponse();

        $response['data'] = empty($body['data'])
            ? null
            : $this->parseResponseDataItem($body['data'], DataTypes\Item::class);
        $response['meta'] = empty($body['meta'])
            ? null
            : $this->parseResponseDataItem($body['meta'], DataTypes\Meta::class);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function itemUpdatePost($itemId, array $content = [], array $assets = [])
    {
        $this->setUseLegacy(false);
        $request = [
            'json' => ['content' => $content],
        ];

        if (!empty($assets)) {
            $request = [
                'multipart' => $this->formatRequestMultipart([
                    'content' => \GuzzleHttp\json_encode($content),
                    'assets' => $assets,
                ]),
            ];
        }

        $this->sendPost("items/$itemId/content", $request);

        $this->validatePostResponse(202);
        $body = $this->parseResponse();

        return empty($body['meta']) ? null : $this->parseResponseDataItem($body['meta'], DataTypes\Meta::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemRenamePost($itemId, $name)
    {
        $this->setUseLegacy(false);
        $this->sendPost("items/$itemId/rename", [
            'json' => ['name' => $name],
        ]);

        $this->validatePostResponse(200);
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Item::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemMovePost($itemId, $position = null, $folderUuid = '')
    {
        $this->setUseLegacy(false);
        $request = [];

        if ($position !== null) {
            $request['position'] = $position;
        }

        if (!empty($folderUuid)) {
            $request['folder_uuid'] = $folderUuid;
        }

        $this->sendPost("items/$itemId/move", [
            'json' => $request,
        ]);

        $this->validatePostResponse(200);
        $body = $this->parseResponse();

        // TODO: change later, because now the data is not returned even though the documentation says so.
        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Item::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemApplyTemplatePost($itemId, $templateId)
    {
        $this->setUseLegacy(false);
        $this->sendPost("items/$itemId/apply_template", [
            'json' => [
                'template_id' => $templateId
            ],
        ]);

        $this->validatePostResponse(200);
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Item::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemDisconnectTemplatePost($itemId)
    {
        $this->setUseLegacy(false);
        $this->sendPost("items/$itemId/disconnect_template");

        $this->validatePostResponse(200);
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Item::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemDuplicatePost($itemId)
    {
        $this->setUseLegacy(false);
        $this->sendPost("items/$itemId/duplicate");

        $this->validatePostResponse(200);
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Item::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemChooseStatusPost($itemId, $statusId)
    {
        $this->setUseLegacy(true);
        $this->sendPost("items/$itemId/choose_status", [
            'json' => [
                'status_id' => $statusId,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function templatesGet($projectId)
    {
        $this->setUseLegacy(false);
        $this->sendGet("projects/$projectId/templates");

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseItems($body, DataTypes\Template::class);
    }

    /**
     * {@inheritdoc}
     */
    public function templateGet($templateId)
    {
        $this->setUseLegacy(false);
        $this->sendGet("templates/$templateId");

        $this->validateResponse();
        $body = $this->parseResponse();

        $response['data'] = empty($body['data'])
            ? null
            : $this->parseResponseDataItem($body['data'], DataTypes\Template::class);
        $response['related'] = empty($body['related'])
            ? null
            : $this->parseResponseDataItem($body['related'], DataTypes\Related::class);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function templatePost($projectId, $name, Structure $structure)
    {
        $this->setUseLegacy(false);
        $structure->setSkipEmptyProperties(true);
        $this->sendPost("projects/$projectId/templates", [
            'json' => [
                'name' => $name,
                'structure' => $structure,
            ],
        ]);

        $this->validatePostResponse(201);
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Template::class);
    }

    /**
     * {@inheritdoc}
     */
    public function templateRenamePost($templateId, $name)
    {
        $this->setUseLegacy(false);
        $this->sendPost("templates/$templateId/rename", [
            'json' => ['name' => $name],
        ]);

        $this->validatePostResponse(200);
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Template::class);
    }

    /**
     * {@inheritdoc}
     */
    public function templateDuplicatePost($templateId, $projectId = null)
    {
        $this->setUseLegacy(false);
        $request = [];

        if ($projectId !== null) {
            $request['project_id'] = $projectId;
        }

        $this->sendPost("templates/$templateId/duplicate", [
            'json' => $request,
        ]);

        $this->validatePostResponse(201);
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Template::class);
    }

    /**
     * {@inheritdoc}
     */
    public function templateDelete($templateId)
    {
        $this->setUseLegacy(false);
        $this->sendDelete("templates/$templateId");
    }

    /**
     * {@inheritdoc}
     */
    public function structureGet($structureUuid)
    {
        $this->setUseLegacy(false);
        $this->sendGet("structures/$structureUuid");

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Structure::class);
    }

    /**
     * {@inheritdoc}
     */
    public function structureAlterPut($structureUuid, Structure $structure, $priorityItemId = null)
    {
        $this->setUseLegacy(false);
        $structure->setSkipEmptyProperties(true);
        $request = [
            'structure' => $structure,
        ];

        if ($priorityItemId !== null) {
            $request['priority_item_id'] = $priorityItemId;
        }

        $this->sendPut("structures/$structureUuid", [
            'json' => $request,
        ]);

        $this->validatePutResponse(200);
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Structure::class);
    }

    /**
     * {@inheritdoc}
     */
    public function structureSaveAsTemplatePost($structureUuid, $name)
    {
        $this->setUseLegacy(false);
        $this->sendPost("structures/$structureUuid/save_as_template", [
            'json' => [
                'name' => $name,
            ],
        ]);

        $this->validatePutResponse(201);
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Template::class);
    }

    /**
     * {@inheritdoc}
     */
    public function foldersGet($projectId, $includeTrashed = false)
    {
        $this->setUseLegacy(false);
        $this->sendGet("projects/$projectId/folders", ['query' => ['include_trashed' => $includeTrashed]]);

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseItems($body, DataTypes\Folder::class);
    }

    /**
     * {@inheritdoc}
     */
    public function folderPost($parentFolderUuid, Folder $folder)
    {
        $this->setUseLegacy(false);
        $folder->setSkipEmptyProperties(true);
        $this->sendPost("folders/$parentFolderUuid/folders", [
            'json' => $folder,
        ]);

        $this->validatePostResponse(201);
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Folder::class);
    }

    /**
     * {@inheritdoc}
     */
    public function folderRenamePost($folderUuid, $name)
    {
        $this->setUseLegacy(false);
        $this->sendPost("folders/$folderUuid/rename", [
            'json' => ['name' => $name],
        ]);

        $this->validatePostResponse(200);
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Folder::class);
    }

    /**
     * {@inheritdoc}
     */
    public function folderMovePost($folderUuid, $parentFolderUuid, $position = null)
    {
        $this->setUseLegacy(false);
        $request = [
            'parent_uuid' => $parentFolderUuid,
        ];

        if ($position !== null) {
            $request['position'] = $position;
        }

        $this->sendPost("folders/$folderUuid/move", [
            'json' => $request,
        ]);

        $this->validatePostResponse(200);
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Folder::class);
    }

    /**
     * {@inheritdoc}
     */
    public function folderDelete($folderUuid)
    {
        $this->setUseLegacy(false);
        $this->sendDelete("folders/$folderUuid");

        if ($this->response->getStatusCode() === 200) {
            $body = $this->parseResponse();

            return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Folder::class);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function folderRestorePost($folderUuid)
    {
        $this->setUseLegacy(false);
        $this->sendPost("folders/$folderUuid/restore");

        $this->validatePostResponse(200);
        $body = $this->parseResponse();

        return empty($body['data']) ? null : $this->parseResponseDataItem($body['data'], DataTypes\Folder::class);
    }

    protected function getRequestAuth()
    {
        return [
            $this->getEmail(),
            $this->getApiKey(),
        ];
    }

    protected function getRequestHeaders(array $base = [])
    {
        $accept = 'application/vnd.gathercontent.v2+json';
        if ($this->useLegacy) {
            $accept = 'application/vnd.gathercontent.v0.5+json';
        }

        return $base + [
            'Accept' => $accept,
            'User-Agent' => $this->getVersionString(),
        ];
    }

    /**
     * @return string[]
     */
    public function getFrameworkName()
    {
        return $this->framework['name'];
    }

    protected $framework = ['name' => 'PHP', 'version' => 'UKNOWN'];

    /**
     * {@inheritdoc}
     */
    public function setFrameworkName($value)
    {
        $this->framework['name'] = $value;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getFrameworkVersion()
    {
        return $this->framework['version'];
    }

    /**
     * {@inheritdoc}
     */
    public function setFrameworkVersion($version)
    {
        $this->framework['version'] = $version;
    }

    /**
     * {@inheritdoc}
     */
    public function getIntegrationVersion()
    {
        return static::INTEGRATION_VERSION;
    }

    /**
     * @return string[]
     */
    public function getVersionString()
    {
        $frameworkName = $this->getFrameworkName();
        $frameworkVersion = $this->getFrameworkVersion();
        $integrationVersion = $this->getIntegrationVersion();
        return sprintf('Integration-%s-%s/%s', $frameworkName, $frameworkVersion, $integrationVersion);
    }

    /**
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendGet($path, array $options = [])
    {
        return $this->sendRequest('GET', $path, $options);
    }

    /**
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendPost($path, array $options = [])
    {
        return $this->sendRequest('POST', $path, $options);
    }

    /**
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendPut($path, array $options = [])
    {
        return $this->sendRequest('PUT', $path, $options);
    }

    /**
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendDelete($path, array $options = [])
    {
        return $this->sendRequest('DELETE', $path, $options);
    }

    /**
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendRequest($method, $path, array $options = [])
    {
        $options += [
            'auth' => $this->getRequestAuth(),
            'headers' => [],
        ];

        $options['headers'] += $this->getRequestHeaders();

        $uri = $this->getUri($path);
        $this->response = $this->client->request($method, $uri, $options);

        return $this;
    }

    protected function parseResponse()
    {
        if ($this->getUseLegacy()) {
            return $this->parseLegacyResponse();
        }

        $body = \GuzzleHttp\json_decode($this->response->getBody(), true);
        if (!empty($body['error'])) {
            throw new GatherContentClientException(
                'API Error: "'.$body['error'].'", Code: '.$body['code'],
                GatherContentClientException::API_ERROR
            );
        }

        return $body;
    }

    /**
     * @deprecated Will be removed when v2 API fully developed.
     */
    protected function parseLegacyResponse()
    {
        $body = \GuzzleHttp\json_decode($this->response->getBody(), true);
        if (!empty($body['data']['message'])) {
            throw new GatherContentClientException(
                'API Error: "'.$body['data']['message'].'"',
                GatherContentClientException::API_ERROR
            );
        }

        return $body;
    }

    /**
     * @return array
     */
    protected function parseResponseItems(array $data, $class)
    {
        $items = ['data' => []];

        foreach ($data['data'] as $itemData) {
            $item = $this->parseResponseDataItem($itemData, $class);
            $items['data'][] = $item;
        }

        if (!empty($data['pagination'])) {
            $items['pagination'] = $this->parsePagination($data['pagination']);
        }

        return $items;
    }

    protected function parseResponseDataItem(array $data, $class)
    {
        return new $class($data);
    }

    protected function parsePagination(array $data)
    {
        return new Pagination($data);
    }

    protected function validateResponse()
    {
        $responseContentType = $this->response->getHeader('Content-Type');
        $responseContentType = end($responseContentType);
        if ($responseContentType !== 'application/json') {
            throw new GatherContentClientException(
                "Unexpected Content-Type: '$responseContentType'",
                GatherContentClientException::UNEXPECTED_CONTENT_TYPE
            );
        }
    }

    protected function validatePostResponse($code)
    {
        if ($this->response->getStatusCode() !== $code) {
            $responseContentType = $this->response->getHeader('Content-Type');
            $responseContentType = end($responseContentType);

            if ($responseContentType === 'application/json') {
                $this->parseResponse();
            }

            throw new GatherContentClientException(
                'Unexpected answer',
                GatherContentClientException::UNEXPECTED_ANSWER
            );
        }

        $this->validateResponse();
    }

    protected function validatePutResponse($code)
    {
        return $this->validatePostResponse($code);
    }

    protected function formatRequestMultipart(array $data, string $parentKey = '')
    {
        $formatted = [];

        foreach ($data as $key => $value) {
            $formattedKey = $key;
            if (!empty($parentKey)) {
                $formattedKey = $parentKey.'['.$key.']';
            }

            if (is_array($value)) {
                $formatted = array_merge($formatted, $this->formatRequestMultipart($value, $formattedKey));
                continue;
            }

            $headers = [];
            $filename = '';

            // Check if the value is a string and a json and set the type.
            $jsonValue = json_decode($value);
            if (is_string($value)
                && $jsonValue !== null
                && (is_array($jsonValue) || $jsonValue instanceof \stdClass)
            ) {
                $headers = [
                    'Content-Type' => 'application/json',
                ];
            }

            // Check if the value is a file path and load the file data and mime.
            if (is_file($value)) {
                $headers = [
                    'Content-Type' => mime_content_type($value),
                ];
                $filename = basename($value);
                $value = fopen($value, 'r');
            }

            $formatted[] = [
                'name' => $formattedKey,
                'contents' => $value,
                'headers' => $headers,
                'filename' => $filename,
            ];
        }

        return $formatted;
    }
}
