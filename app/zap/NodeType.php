<?php

namespace zap;

use zap\DB;
use zap\node\AbstractNodeType;

/**
 * 内容类型
 */
class NodeType
{
    protected static $nodeTypeNames;
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
                static::$nodeTypeNames[$row['id']] = $row['title'];
                static::$nodeTypeNames[$row['name']] = $row['title'];
                static::$nodeTypeNames[$row['name'].'_ID'] = $row['id'];
                static::$nodeTypeNames[$row['name'].'_ClassName'] = $row['node_type'];
                static::$nodeTypes[$row['id']] = $row;
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


    public static function getNodeTypeTitle($id)
    {
        if(is_null(static::$nodeTypes)){
            static::getNodeTypes();
        }
        return self::$nodeTypeNames[$id];
    }

    public static function getNodeTypeClass($name)
    {
        if(is_null(static::$nodeTypes)){
            static::getNodeTypes();
        }
        return self::$nodeTypeNames[$name.'_ClassName'] ?? AbstractNodeType::class;
    }

    public static function getNodeTypeID($name)
    {
        if(is_null(static::$nodeTypes)){
            static::getNodeTypes();
        }
        return self::$nodeTypeNames[$name.'_ID'];
    }


}