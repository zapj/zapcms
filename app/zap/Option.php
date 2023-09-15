<?php

namespace zap;


class Option
{
    public static function update($option_name,$option_value,$sort_order = null,$autoload = null){
        $data = ['option_value'=>$option_value];
        is_null($sort_order) ?: $data['sort_order'] = $sort_order;
        is_null($autoload) ?: $data['autoload'] = $autoload;
        return DB::update('options',$data,['option_name'=>$option_name]);
    }

    public static function replace($option_name,$option_value,$sort_order = null,$autoload = null){
        $data = ['option_name'=>$option_name,'option_value'=>$option_value];
        is_null($sort_order) ?: $data['sort_order'] = $sort_order;
        is_null($autoload) ?: $data['autoload'] = $autoload;
        return DB::replace('options',$data);
    }

    public static function add($option_name,$option_value,$sort_order = null,$autoload = null){
        $data = ['option_name'=>$option_name,'option_value'=>$option_value];
        is_null($sort_order) ?: $data['sort_order'] = $sort_order;
        is_null($autoload) ?: $data['autoload'] = $autoload;
        try {
            return DB::insert('options',$data);
        }catch (\PDOException $e){
            return DB::update('options',$data);
        }
    }

    public static function addArray($options,$sort_order = null,$autoload = null){
        $data = [];
        is_null($sort_order) ?: $data['sort_order'] = $sort_order;
        is_null($autoload) ?: $data['autoload'] = $autoload;
        foreach ($options as $option_name=>$option_value){
            $data['option_name'] = $option_name;
            $data['option_value'] = $option_value;
            DB::insert('options',$data);
        }
    }

    public static function remove($option_name){
        return DB::delete('options','option_name=:name',['name'=>$option_name]);
    }

    /**
     * @throws \Exception
     */
    public static function get($option_name, $default = null){
        $option_value = DB::scalar('select option_value from {options} where option_name=:option_name',
            ['option_name'=>$option_name]);
        if(empty($option_value)){
            return $default;
        }
        return $option_value;
    }

    public static function getArray($option_name,$type): array
    {
        $query = DB::table('options')->select('option_name,option_value')
            ->orderBy('sort_order desc');
        $query->where('option_name',$type,$option_name);
        $options = $query->fetchAll(FETCH_KEY_PAIR);
        if(empty($options)){
            return [];
        }
        return $options;
    }

    public static function getKeys($option_name,$type = '='): array
    {
        $query = DB::table('options')->select('option_name')
            ->orderBy('sort_order desc');

        $query->where('option_name',$type,$option_name);
        $options = $query->fetchAll(FETCH_COLUMN);
        if(empty($options)){
            return [];
        }
        return $options;
    }
}