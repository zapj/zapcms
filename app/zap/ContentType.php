<?php

namespace zap;

use zap\DB;

/**
 * 内容类型
 */
class ContentType
{
    protected static $contentTypeNames;
    protected static $contentTypes;


    /**
     * @return array
     */
    public static function getContentTypes()
    {
        if(is_null(static::$contentTypes)){
            $resultSet = DB::table('content_types')->orderBy('sort_order ASC')->get(FETCH_ASSOC);
            foreach ($resultSet as $row){
                static::$contentTypeNames[$row['id']] = $row['name'];
                static::$contentTypes[$row['id']] = $row;
            }
        }
        return static::$contentTypes;
    }


    public static function getContentType($id)
    {
        if(is_null(static::$contentTypes)){
            static::getContentTypes();
        }
        return self::$contentTypes[$id];
    }

    /**
     * 获取ContentType 名称
     * @param $id
     * @return mixed
     */
    public static function getContentTypeName($id)
    {
        if(is_null(static::$contentTypes)){
            static::getContentTypes();
        }
        return self::$contentTypeNames[$id];
    }


}