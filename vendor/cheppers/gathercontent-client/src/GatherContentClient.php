<?php

namespace Cheppers\GatherContent;

use GuzzleHttp\ClientInterface;

class GatherContentClient implements GatherContentClientInterface
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    //region response
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
    //endregion

    // region Option - email.
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
    // endregion

    //region Option - apiKey
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
    //endregion

    // region Option - baseUri.
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
    // endregion

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
        $this->sendGet('me');

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : new DataTypes\User($body['data']);
    }

    /**
     * {@inheritdoc}
     */
    public function accountsGet()
    {
        $this->sendGet('accounts');

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseDataItems($body['data'], DataTypes\Account::class);
    }

    /**
     * {@inheritdoc}
     */
    public function accountGet($accountId)
    {
        $this->sendGet("accounts/$accountId");

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : new DataTypes\Account($body['data']);
    }

    /**
     * {@inheritdoc}
     */
    public function projectsGet($accountId)
    {
        $this->sendGet('projects', ['query' => ['account_id' => $accountId]]);

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseDataItems($body['data'], DataTypes\Project::class);
    }

    /**
     * {@inheritdoc}
     */
    public function projectGet($projectId)
    {
        $this->sendGet("projects/$projectId");

        $this->validateResponse();
        $body = $this->parseResponse();
        $body += ['meta' => []];

        return empty($body['data']) ? null : new DataTypes\Project($body['data'] + ['meta' => $body['meta']]);
    }

    /**
     * {@inheritdoc}
     */
    public function projectsPost($accountId, $projectName, $projectType)
    {
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

        if ($this->response->getStatusCode() !== 202) {
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
        $this->sendGet("projects/$projectId/statuses");

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseDataItems($body['data'], DataTypes\Status::class);
    }

    /**
     * {@inheritdoc}
     */
    public function projectStatusGet($projectId, $statusId)
    {
        $this->sendGet("projects/$projectId/statuses/$statusId");

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : new DataTypes\Status($body['data']);
    }

    /**
     * {@inheritdoc}
     */
    public function itemsGet($projectId)
    {
        $this->sendGet('items', ['query' => ['project_id' => $projectId]]);

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseDataItems($body['data'], DataTypes\Item::class);
    }

    /**
     * {@inheritdoc}
     */
    public function itemGet($itemId)
    {
        $this->sendGet("items/$itemId");

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : new DataTypes\Item($body['data']);
    }

    public function itemsPost($projectId, $name, $parentId = 0, $templateId = 0, array $config = [])
    {
        $form_params = [
            'project_id' => $projectId,
            'name' => $name,
        ];

        if ($parentId) {
            $form_params['parent_id'] = $parentId;
        }

        if ($templateId) {
            $form_params['template_id'] = $templateId;
        }

        if ($config) {
            $config = array_values($config);
            $form_params['config'] = base64_encode(\GuzzleHttp\json_encode($config));
        }

        $this->sendPost('items', [
            'form_params' => $form_params,
        ]);

        if ($this->response->getStatusCode() !== 202) {
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

        $locations = $this->response->getHeader('Location');
        $locationPath = parse_url(reset($locations), PHP_URL_PATH);
        $matches = [];
        if (!preg_match('@/items/(?P<itemId>\d+)$@', $locationPath, $matches)) {
            throw new GatherContentClientException(
                'Invalid response header the item ID is missing',
                GatherContentClientException::INVALID_RESPONSE_HEADER
            );
        }

        return $matches['itemId'];
    }

    public function itemSavePost($itemId, array $config)
    {
        $formParams = [];
        $config = array_values($config);
        $jsonConfig = \GuzzleHttp\json_encode($config);
        $encodedConfig = base64_encode($jsonConfig);
        $formParams['config'] = $encodedConfig;

        $this->sendPost("items/$itemId/save", [
            'form_params' => $formParams,
        ]);

        if ($this->response->getStatusCode() !== 202) {
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
    }

    /**
     * {@inheritdoc}
     */
    public function itemApplyTemplatePost($itemId, $templateId)
    {
        $this->sendPost("items/$itemId/apply_template", [
            'form_params' => [
                'template_id' => $templateId,
            ],
        ]);

        if ($this->response->getStatusCode() !== 202) {
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
    }

    /**
     * {@inheritdoc}
     */
    public function itemChooseStatusPost($itemId, $statusId)
    {
        $this->sendPost("items/$itemId/choose_status", [
            'form_params' => [
                'status_id' => $statusId,
            ],
        ]);

        if ($this->response->getStatusCode() !== 202) {
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
    }

    /**
     * {@inheritdoc}
     */
    public function itemFilesGet($itemId)
    {
        $this->sendGet("items/$itemId/files");

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseDataItems($body['data'], DataTypes\File::class);
    }

    public function templatesGet($projectId)
    {
        $this->sendGet('templates', ['query' => ['project_id' => $projectId]]);

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseDataItems($body['data'], DataTypes\Template::class);
    }

    public function templateGet($templateId)
    {
        $this->sendGet("templates/$templateId");

        $this->validateResponse();
        $body = $this->parseResponse();

        return empty($body['data']) ? null : new DataTypes\Template($body['data']);
    }

    protected function getUri($path)
    {
        return $this->getBaseUri() . "/$path";
    }

    public function foldersGet($projectId)
    {
        $this->sendGet('folders', ['query' => ['project_id' => $projectId]]);

        $this->validateResponse();
        $body = $this->parseResponse();

        return $this->parseResponseDataItems($body['data'], DataTypes\Folder::class);
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
        return $base + [
            'Accept' => 'application/vnd.gathercontent.v0.5+json',
        ];
    }

    /**
     * @return $this
     */
    protected function sendGet($path, array $options = [])
    {
        return $this->sendRequest('GET', $path, $options);
    }

    /**
     * @return $this
     */
    protected function sendPost($path, array $options = [])
    {
        return $this->sendRequest('POST', $path, $options);
    }

    /**
     * @return $this
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
        $body = \GuzzleHttp\json_decode($this->response->getBody(), true);
        if (!empty($body['data']['message'])) {
            throw new GatherContentClientException(
                'API Error: "' . $body['data']['message'] . '"',
                GatherContentClientException::API_ERROR
            );
        }

        return $body;
    }

    /**
     * @return \Cheppers\GatherContent\DataTypes\Base[]
     */
    protected function parseResponseDataItems(array $data, $class)
    {
        $items = [];
        foreach ($data as $itemData) {
            $item = $this->parseResponseDataItem($itemData, $class);
            $items[$item->id] = $item;
        }

        return $items;
    }

    protected function parseResponseDataItem(array $data, $class)
    {
        return $item = new $class($data);
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
}
