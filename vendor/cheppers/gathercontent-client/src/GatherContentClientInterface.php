<?php

namespace Cheppers\GatherContent;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

interface GatherContentClientInterface
{
    const PROJECT_TYPE_WEBSITE_BUILDING = 'website-build';

    const PROJECT_TYPE_ONGOING_WEBSITE_CONTENT = 'ongoing-website-content';

    const PROJECT_TYPE_MARKETING_EDITORIAL_CONTENT = 'marketing-editorial-content';

    const PROJECT_TYPE_EMAIL_MARKETING_CONTENT = 'email-marketing-content';

    const PROJECT_TYPE_OTHER = 'other';

    const INTEGRATION_VERSION = '1.0';

    public function getResponse();

    public function getEmail();

    /**
     * @return $this
     */
    public function setEmail($value);

    public function getApiKey();

    /**
     * @return $this
     */
    public function setApiKey($apiKey);

    public function getBaseUri();

    /**
     * @return $this
     */
    public function setBaseUri($value);

    /**
     * @return string[]
     */
    public function getIntegrationVersion();

    /**
     * @return $this
     */
    public function setFrameworkVersion($value);

    /**
     * @return string[]
     */
    public function getFrameworkVersion();

    /**
     * @return $this
     */
    public function setFrameworkName($value);

    /**
     * @return string[]
     */
    public function getFrameworkName();

    /**
     * @return string[]
     */
    public function getVersionString();

    /**
     * GatherContentClientInterface constructor.
     */
    public function __construct(ClientInterface $client);

    /**
     * @return string[]
     */
    public function projectTypes();

    /**
     * @see https://docs.gathercontent.com/reference#get-me
     */
    public function meGet();

    /**
     * @see https://docs.gathercontent.com/reference#get-accounts
     *
     * @return \Cheppers\GatherContent\DataTypes\Account[]
     */
    public function accountsGet();

    /**
     * @see https://docs.gathercontent.com/reference#get-accounts
     */
    public function accountGet($accountId);

    /**
     * @see https://docs.gathercontent.com/reference#get-projects
     *
     * @return \Cheppers\GatherContent\DataTypes\Project[]
     */
    public function projectsGet($accountId);

    /**
     * @see https://docs.gathercontent.com/reference#get-projects
     */
    public function projectGet($projectId);

    /**
     * @see https://docs.gathercontent.com/reference#post-projects
     *
     * @return int
     *   Id of the newly created project.
     */
    public function projectsPost($accountId, $projectName, $projectType);

    /**
     * @see https://docs.gathercontent.com/reference#get-project-statuses
     *
     * @return \Cheppers\GatherContent\DataTypes\Status[]
     */
    public function projectStatusesGet($projectId);

    /**
     * @see https://docs.gathercontent.com/reference#get-project-statuses-by-id
     */
    public function projectStatusGet($projectId, $statusId);

    /**
     * @see https://docs.gathercontent.com/reference#get-items
     *
     * @return \Cheppers\GatherContent\DataTypes\Item[]
     */
    public function itemsGet($projectId);

    /**
     * @see https://docs.gathercontent.com/reference#get-items-by-id
     */
    public function itemGet($itemId);

    /**
     * @see https://docs.gathercontent.com/reference#post-items
     */
    public function itemsPost(
        $projectId,
        $name,
        $parentId = 0,
        $templateId = 0,
        array $config = []
    );

    /**
     * @see https://docs.gathercontent.com/reference#post-item-save
     */
    public function itemSavePost($itemId, array $config);

    /**
     * @return \Cheppers\GatherContent\DataTypes\File[]
     */
    public function itemFilesGet($itemId);

    /**
     * @see https://docs.gathercontent.com/reference#post-item-apply_template
     */
    public function itemApplyTemplatePost($itemId, $templateId);

    /**
     * @see https://docs.gathercontent.com/reference#post-item-choose_status
     */
    public function itemChooseStatusPost($itemId, $statusId);

    /**
     * @see https://docs.gathercontent.com/reference#get-templates
     *
     * @return \Cheppers\GatherContent\DataTypes\Template[]
     */
    public function templatesGet($projectId);

    /**
     * @see https://docs.gathercontent.com/reference#get-template-by-id
     */
    public function templateGet($templateId);

    /**
     * @see https://docs.gathercontent.com/reference#get-folders
     *
     * @return \Cheppers\GatherContent\DataTypes\Folder[]
     */
    public function foldersGet($projectId);
}
