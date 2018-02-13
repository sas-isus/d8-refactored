<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\Project;

/**
 * @group GatherContentClient
 */
class ProjectTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = Project::class;

    /**
     * {@inheritdoc}
     */
    public function casesConstructor()
    {
        $cases = parent::casesConstructor();

        $cases['empty'][0] += [
            'name' => '',
            'type' => '',
            'example' => false,
            'accountId' => 0,
            'active' => true,
            'textDirection' => '',
            'allowedTags' => [],
            'createdAt' => 0,
            'updatedAt' => 0,
            'overdue' => false,
            'statuses' => [],
            'meta' => [],
        ];

        $allowedTags = [
            'a' => [],
            'p' => ['class' => '*'],
        ];

        $cases['basic'][0] += [
            'name' => 'project-name',
            'type' => 'project-type',
            'example' => false,
            'accountId' => 42,
            'active' => true,
            'textDirection' => 'ltr',
            'allowedTags' => $allowedTags,
            'createdAt' => 43,
            'updatedAt' => 44,
            'overdue' => true,
            'meta' => [],
        ];
        $cases['basic'][1] += [
            'name' => 'project-name',
            'type' => 'project-type',
            'example' => false,
            'account_id' => 42,
            'active' => true,
            'text_direction' => 'ltr',
            'allowed_tags' => json_encode($allowedTags),
            'created_at' => 43,
            'updated_at' => 44,
            'overdue' => true,
            'meta' => [],
        ];

        return $cases;
    }

    public function testJsonSerialize()
    {
        $projectArray = static::getUniqueResponseProject();

        /** @var \Cheppers\GatherContent\DataTypes\Project $project1 */
        $project1 = new $this->className($projectArray);

        $project1->name .= '-MODIFIED';
        $projectArray['name'] .= '-MODIFIED';

        $project1->textDirection .= '-MODIFIED';
        $projectArray['text_direction'] .= '-MODIFIED';

        $statusId = key($project1->statuses);
        $project1->statuses[$statusId]->color .= '-MODIFIED';
        $projectArray['statuses']['data'][0]['color'] .= '-MODIFIED';

        $json1 = json_encode($project1);
        $actual1 = json_decode($json1, true);
        foreach ($projectArray as $key => $value) {
            static::assertEquals($value, $actual1[$key], "JSON encode.decode - $key");
        }

        /** @var \Cheppers\GatherContent\DataTypes\Project $project2 */
        $project2 = new $this->className($actual1);
        $json2 = json_encode($project2);
        $actual2 = json_decode($json2, true);
        static::assertEquals($actual1, $actual2);
    }
}
