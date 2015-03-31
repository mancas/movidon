<?php

namespace Movidon\FrontendBundle\Util;

class ArrayHelper
{
    public static function multiLevelArrayToSingleLevel($multiArray)
    {
        $singleArray = array();
        foreach ($multiArray as $key => $value) {
            if (!is_array($value)) {
                $singleArray[$key] = $value;
            } else {
                $singleArray = array_merge($singleArray, self::multiLevelArrayToSingleLevel($value, $singleArray));
            }
        }

        return $singleArray;
    }

    public static function removeObjectFromArray($objects, $objeto)
    {
        foreach ($objects as $k => $object) {
            if ($objeto->getId() == $object['id']) {
                unset($objects[$k]);
                break;
            }
        }

        return $objects;
    }
}