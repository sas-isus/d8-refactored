<?php

namespace Cheppers\GatherContent\DataTypes;

class Pagination extends Base
{
    /**
     * {@inheritdoc}
     */
    protected $unusedProperties = [
        'id',
    ];

    /**
     * @var int
     */
    public $total = 0;

    /**
     * @var int
     */
    public $count = 0;

    /**
     * @var int
     */
    public $perPage = 0;

    /**
     * @var int
     */
    public $currentPage = 0;

    /**
     * @var int
     */
    public $totalPages = 0;

    /**
     * @var array
     */
    public $links = [];

    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'total' => 'total',
                'count' => 'count',
                'per_page' => 'perPage',
                'current_page' => 'currentPage',
                'total_pages' => 'totalPages',
                'links' => 'links',
            ]
        );

        return $this;
    }
}
