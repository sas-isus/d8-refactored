<?php

namespace Cheppers\GatherContent\DataTypes;

class ElementChoice extends Element
{
    /**
     * @var array
     */
    public $options = [];

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        $value = [];
        foreach ($this->options as $option) {
            $value[$option['name']] = $option['selected'];
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        foreach ($value as $name => $selected) {
            foreach ($this->options as $key => $option) {
                if ($option['name'] === $name) {
                    $this->options[$key]['selected'] = $selected;

                    break;
                }
            }
        }

        return $this;
    }

    public function getOptions()
    {
        $options = [];
        foreach ($this->options as $option) {
            $options[$option['name']] = $option['label'];
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'options' => 'options',
            ]
        );

        return $this;
    }
}
