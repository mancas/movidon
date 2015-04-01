<?php

namespace Movidon\BackendBundle\Util;

class FunctionsHelper
{
    public static function isClass($object, $className)
    {
        if (strpos(get_class($object), ucfirst($className)) !== false) {
            return true;
        }

        return false;
    }

    public static function uniqueRandString()
    {
        return substr(md5(uniqid(rand(), true)), 0, 11);
    }

}
