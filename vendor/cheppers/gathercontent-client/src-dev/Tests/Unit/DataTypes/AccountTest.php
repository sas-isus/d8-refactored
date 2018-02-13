<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\Account;

/**
 * @group GatherContentClient
 */
class AccountTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = Account::class;

    /**
     * {@inheritdoc}
     */
    public function casesConstructor()
    {
        $cases = parent::casesConstructor();
        $cases['basic'][0] += [
            'name' => 'a',
            'slug' => 'b',
            'timezone' => 'v',
        ];
        $cases['basic'][1] += [
            'name' => 'a',
            'slug' => 'b',
            'timezone' => 'v',
        ];

        return $cases;
    }
}
