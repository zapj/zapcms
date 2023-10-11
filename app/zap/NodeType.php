<?php

namespace zap;

use zap\DB;
use zap\node\AbstractNodeType;

/**
 * 内容类型
 */
class NodeType
{
    protected static $nodeTypes;


    const NEWS = 1;
    const PRODUCT = 2;
    const PAGE = 3;
    const FAQ = 4;
    const LINK_URL = 5;
    const COMMENTS = 6;


    public static function getNodeTypes()
    {
        if(is_null(static::$nodeTypes)){
            $resultSet = DB::table('node_types')->where('status',1)
                ->orderBy('sort_order ASC')
                ->get(FETCH_ASSOC);
            foreach ($resultSet as $row){
                static::$nodeTypes[$row['id']] = $row;
                static::$nodeTypes[$row['name']] = $row;
            }
        }
        return static::$nodeTypes;
    }


    public static function getNodeType($id)
    {
        if(is_null(static::$nodeTypes)){
            static::getNodeTypes();
        }
        return self::$nodeTypes[$id];
    }


    public static function getTitle($name)
    {
        if(is_null(static::$nodeTypes)){
            static::getNodeTypes();
        }
        return self::$nodeTypes[$name]['title'] ?? '';
    }

    public static function getClass($name)
    {
        if(is_null(static::$nodeTypes)){
            static::getNodeTypes();
        }
        return self::$nodeTypes[$name]['node_type'] ?? AbstractNodeType::class;
    }

    public static function getName($name)
    {
        if(is_null(static::$nodeTypes)){
            static::getNodeTypes();
        }
        return self::$nodeTypes[$name]['name'];
    }

    public static function getID($name)
    {
        if(is_null(static::$nodeTypes)){
            static::getNodeTypes();
        }
        return self::$nodeTypes[$name]['id'];
    }


}