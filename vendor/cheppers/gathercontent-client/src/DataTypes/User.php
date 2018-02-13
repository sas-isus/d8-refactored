<?php

namespace Cheppers\GatherContent\DataTypes;

class User extends Base
{
    /**
     * @var string
     */
    public $email = '';

    /**
     * @var string
     */
    public $firstName = '';

    /**
     * @var string
     */
    public $lastName = '';

    /**
     * @var null|string
     */
    public $language = null;

    /**
     * @var null|string
     */
    public $gender = null;

    /**
     * @var string
     */
    public $avatar = '';

    /**
     * @var \Cheppers\GatherContent\DataTypes\Announcement[]
     */
    public $announcements = [];

    /**
     * {@inheritdoc}
     */
    protected $unusedProperties = ['id'];

    /**
     * {@inheritdoc}
     */
    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'email' => 'email',
                'first_name' => 'firstName',
                'last_name' => 'lastName',
                'language' => 'language',
                'gender' => 'gender',
                'avatar' => 'avatar',
                'announcements' => [
                    'type' => 'subConfigs',
                    'class' => Announcement::class,
                ],
            ]
        );

        return $this;
    }
}
