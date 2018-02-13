<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\Date;

/**
 * @group GatherContentClient
 */
class DateTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = Date::class;

    /**
     * {@inheritdoc}
     */
    public function casesConstructor()
    {
        return [
            'empty' => [
                [
                    'date' => '',
                    'timezoneType' => 0,
                    'timezone' => '',
                ],
                [],
            ],
            'basic' => [
                [
                    'date' => '2016-09-29 11:35:06',
                    'timezoneType' => 3,
                    'timezone' => 'UTC',
                ],
                [
                    'date' => '2016-09-29 11:35:06',
                    'timezone_type' => 3,
                    'timezone' => 'UTC',
                ],
            ],
        ];
    }
}
