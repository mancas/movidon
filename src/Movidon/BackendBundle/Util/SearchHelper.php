<?php

namespace Movidon\BackendBundle\Util;

class SearchHelper
{
    private static $limitsPerPage = array('too_less'=> 3, 'less_than_few' => 10, 'few' => 20, 'medium' => 30, 'many' => 50, 'too_many' => 100);

    public static function getLimitPerPage($limit = 'few')
    {
        return self::$limitsPerPage[$limit];
    }
}