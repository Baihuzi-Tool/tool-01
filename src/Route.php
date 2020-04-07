<?php

namespace Tool01;

class Route
{
    protected static $map = [];
    
    public static function init()
    {
        self::$map['upload'] = 'Tool01\Controllers\Parse::upload';
        self::$map['get']    = 'Tool01\Controllers\Parse::get';
        
        $uri = strtolower(str_replace('/', '.', ltrim($_SERVER['REQUEST_URI'], '/')));
        if (isset(self::$map[$uri])) {
            $route = self::$map[$uri];
        }
        else {
            $route = 'Tool01\Controllers\Index::index';
        }
        
        [$controller, $action] = explode('::', $route);
        
        return (new $controller)->$action();
    }
    
}