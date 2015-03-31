<?php

namespace Movidon\FrontendBundle\Util;

class StringHelper
{
    public static function getUniqueIdentifier($length = 8)
    {
        return substr(md5(uniqid(rand(), true)), 0, $length);
    }
}