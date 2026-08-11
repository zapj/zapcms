<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:27
 * @lastModified 2023/10/28 下午1:54
 *
 */

namespace zapcms\services;


use zap\DB;
use zapcms\node\AbstractNodeType;

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

    public static function getKeyPair(string $key = 'type_name', string $value = 'title')
    {
        return DB::table('node_types')->select([$key,$value])
            ->where('status',1)
            ->orderBy('sort_order DESC')
            ->get(FETCH_KEY_PAIR);
    }


    public static function getNodeType(string $type_name, ?string $key = null)
    {
        if(is_null(static::$nodeTypes)){
            static::getNodeTypes();
        }
        return is_null($key) ? self::$nodeTypes[$type_name] : self::$nodeTypes[$type_name][$key];
    }


    public static function getTitle(string $type_name)
    {
       return static::getNodeType($type_name,'title');
    }

    public static function getClass(string $type_name)
    {
        $node_type = static::getNodeType($type_name,'node_type');
        return $node_type ?? AbstractNodeType::class;
    }

    public static function getID(string $type_name)
    {
        return static::getNodeType($type_name,'type_id');
    }


}