<?php

namespace Cheppers\GatherContent\DataTypes;

class ElementBase extends Base
{
    /**
     * @return mixed
     */
    public function getValue()
    {
        return null;
    }

    /**
     * @param  mixed  $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        return $this;
    }
}
