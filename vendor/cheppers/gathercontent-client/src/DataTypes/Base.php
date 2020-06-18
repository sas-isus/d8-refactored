<?php

namespace Cheppers\GatherContent\DataTypes;

use Cheppers\GatherContent\Utils\NestedArray;
use JsonSerializable;
use ReflectionObject;
use ReflectionProperty;

class Base implements JsonSerializable, \Serializable
{
    /**
     * @var string
     */
    public $id = '';

    /**
     * @var array
     */
    protected $propertyMapping = [];

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $dataDefaultValues = [];

    /**
     * @var array
     */
    protected $unusedProperties = [];

    /**
     * @var bool
     */
    protected $skipEmptyProperties = false;

    public function __construct(array $data = [])
    {
        $this->data = $data;
        $this
            ->initPropertyMapping()
            ->expandPropertyMappingShortCuts()
            ->populateProperties();
    }

    /**
     * @param $value
     * @return $this
     */
    public function setSkipEmptyProperties($value)
    {
        $this->skipEmptyProperties = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSkipEmptyProperties()
    {
        return $this->skipEmptyProperties;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $export = [];

        foreach ($this->propertyMapping as $src => $handler) {
            if (in_array($src, $this->unusedProperties)) {
                continue;
            }

            $value = $this->{$handler['destination']};

            if ($handler['type'] === 'subConfigs') {
                $value = array_values($value);
            }

            if ($handler['type'] === 'setJsonDecode') {
                $value = json_encode($value, JSON_PRETTY_PRINT);
            }

            if (!empty($handler['parents'])) {
                while ($handler['parents']) {
                    $value = [
                        array_pop($handler['parents']) => $value,
                    ];
                }
            }

            if ($value instanceof Base) {
                $value->setSkipEmptyProperties($this->getSkipEmptyProperties());
            }

            if (is_array($value)) {
                foreach ($value as $object) {
                    if ($object instanceof Base) {
                        $object->setSkipEmptyProperties($this->getSkipEmptyProperties());
                    }
                }
            }

            if ($this->getSkipEmptyProperties()
                && empty($value)
            ) {
                continue;
            }

            $export[$src] = $value;
        }

        return $export;
    }

    /**
     * @return $this
     */
    protected function initPropertyMapping()
    {
        $this->propertyMapping += [
            'id' => 'id',
        ];

        return $this;
    }

    /**
     * @return $this
     */
    protected function expandPropertyMappingShortCuts()
    {
        foreach ($this->propertyMapping as $src => $handler) {
            if (is_string($handler)) {
                $handler = [
                    'type' => 'set',
                    'destination' => $handler,
                ];
            }

            if ($handler['type'] === 'subConfig' || $handler['type'] === 'subConfigs') {
                $handler += ['parents' => []];
            }

            $this->propertyMapping[$src] = $handler + ['destination' => $src];
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function populateProperties()
    {
        $data = $this->setDataDefaultValues($this->data);
        foreach ($this->propertyMapping as $src => $handler) {
            if (!array_key_exists($src, $data)) {
                continue;
            }

            switch ($handler['type']) {
                case 'set':
                    $this->{$handler['destination']} = $data[$src];
                    break;
                case 'setJsonDecode':
                    $this->{$handler['destination']} = json_decode($data[$src], true);
                    break;

                case 'closure':
                    $this->{$handler['destination']} = $handler['closure']($data[$src], $src);
                    break;

                case 'subConfig':
                    /** @var \Cheppers\GatherContent\DataTypes\Base $subConfig */
                    if (empty($handler['parents'])) {
                        $subConfig = new $handler['class']((array) $data[$src]);
                    } else {
                        $subConfigData = NestedArray::getValue($data[$src], $handler['parents']);
                        $subConfig = new $handler['class']((array) $subConfigData);
                    }

                    $this->{$handler['destination']} = $subConfig;
                    break;

                case 'subConfigs':
                    if (empty($handler['parents'])) {
                        $subConfigs = (array) $data[$src];
                    } else {
                        $subConfigs = (array) NestedArray::getValue($data[$src], $handler['parents']);
                    }

                    foreach ($subConfigs as $subConfigId => $subConfigData) {
                        $subConfig = new $handler['class']($subConfigData);
                        $id = $subConfig->id ?: $subConfigId;
                        $this->{$handler['destination']}[$id] = $subConfig;
                    }
                    break;
            }
        }

        return $this;
    }

    protected function setDataDefaultValues(array $data)
    {
        return array_replace_recursive($this->dataDefaultValues, $data);
    }

    /**
    * Serialize only public non-static properties.
    */
    public function serialize()
    {
        $toSerialize = [];
        $reflection = new ReflectionObject($this);
        $publicProperties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        $staticProperties = $reflection->getProperties(ReflectionProperty::IS_STATIC);
        $properties = array_diff($publicProperties, $staticProperties);

        foreach ($properties as $property) {
            $name = $property->name;
            $toSerialize[$name] = $this->{$name};
        }

        return serialize($toSerialize);
    }

    /**
    * {@inheritdoc}
    */
    public function unserialize($serialized)
    {
        foreach (unserialize($serialized) as $propertyName => $propertyValue) {
            $this->{$propertyName} = $propertyValue;
        }
    }
}
