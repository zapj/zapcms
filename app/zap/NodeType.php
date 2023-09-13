<?php

namespace zap;

use zap\DB;

/**
 * 内容类型
 */
class NodeType
{
    protected static $nodeTypeNames;
    protected static $nodeTypes;


    /**
     * @return array
     */
    public static function getNodeTypes()
    {
        if(is_null(static::$nodeTypes)){
            $resultSet = DB::table('node_types')->orderBy('sort_order ASC')->get(FETCH_ASSOC);
            foreach ($resultSet as $row){
                static::$nodeTypeNames[$row['id']] = $row['name'];
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

    /**
     * 获取ContentType 名称
     * @param $id
     * @return mixed
     */
    public static function getNodeTypeName($id)
    {
        if(is_null(static::$nodeTypes)){
            static::getNodeTypes();
        }
        return self::$nodeTypeNames[$id];
    }


}