<?php

namespace zap;


use zap\node\AbstractNodeType;

/**
 * 内容类型
 */
class NodeType
{
    protected static ?array $nodeTypes = null;

    public static function getNodeTypes(): array
    {
        if(is_null(static::$nodeTypes)){
            $resultSet = DB::table('node_types')->where('status',1)
                ->orderBy('sort_order DESC')
                ->get(FETCH_ASSOC);
            foreach ($resultSet as $row){
                static::$nodeTypes[$row['type_name']] = $row;
            }
        }
        return static::$nodeTypes;
    }

    public static function getKeyPair($key = 'type_name',$value = 'title')
    {
        return DB::table('node_types')->select([$key,$value])
            ->where('status',1)
            ->orderBy('sort_order DESC')
            ->get(FETCH_KEY_PAIR);
    }


    public static function getNodeType($type_name,$key = null)
    {
        if(is_null(static::$nodeTypes)){
            static::getNodeTypes();
        }
        return is_null($key) ? self::$nodeTypes[$type_name] : self::$nodeTypes[$type_name][$key];
    }


    public static function getTitle($type_name)
    {
       return static::getNodeType($type_name,'title');
    }

    public static function getClass($type_name)
    {
        $node_type = static::getNodeType($type_name,'node_type');
        return $node_type ?? AbstractNodeType::class;
    }

    public static function getID($type_name)
    {
        return static::getNodeType($type_name,'type_id');
    }


}