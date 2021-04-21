<?php

namespace Cheppers\GatherContent\Utils;

class NestedArray
{
    /**
     * @return array|mixed|null
     */
    public static function &getValue(array &$array, array $parents, &$key_exists = null)
    {
        $ref = &$array;
        foreach ($parents as $parent) {
            if (is_array($ref) && array_key_exists($parent, $ref)) {
                $ref = &$ref[$parent];
            } else {
                $key_exists = false;
                $null = null;

                return $null;
            }
        }
        $key_exists = true;

        return $ref;
    }
}
