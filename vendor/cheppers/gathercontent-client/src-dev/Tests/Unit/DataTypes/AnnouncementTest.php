<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\Announcement;

/**
 * @group GatherContentClient
 */
class AnnouncementTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = Announcement::class;

    /**
     * {@inheritdoc}
     */
    public function casesConstructor()
    {
        $cases = parent::casesConstructor();
        $cases['basic'][0] += [
            'name' => 'a',
            'acknowledged' => 'b',
        ];
        $cases['basic'][1] += [
            'name' => 'a',
            'acknowledged' => 'b',
        ];

        return $cases;
    }
}
