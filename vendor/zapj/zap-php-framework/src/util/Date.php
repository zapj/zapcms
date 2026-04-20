<?php

namespace zap\util;

class Date {

    protected $timezone = null;

    public function setTimeZone($timezone = 'UTC'){
        $this->timezone = new \DateTimeZone($timezone);
    }

    /**
     * @param $datetime
     * @param $timezone
     *
     * @return \DateTime
     * @throws \Exception
     */
    public function create($datetime,$timezone = null){
        if(!is_null($timezone)){
            $timezone = new \DateTimeZone($timezone);
        }
        return new \DateTime($datetime,$timezone);
    }

    /**
     * @param $timezone
     *
     * @return \DateTime
     * @throws \Exception
     */
    public function now($timezone = null){
        if(!is_null($timezone)){
            $timezone = new \DateTimeZone($timezone);
        }
        return new \DateTime("now",$timezone);
    }

    public function format($format,$datetime,$timezone = null){
        if(!is_null($timezone)){
            $timezone = new \DateTimeZone($timezone);
        }
        $date = new \DateTime($datetime,$timezone);
        return $date->format($format);
    }

    /**
     * $ret = $d->diff()
     * $ret->days
     *
     * @param $datetime1
     * @param $datetime2
     *
     * @return \DateInterval
     * @throws \Exception
     */
    public function diff($datetime1,$datetime2){
        $date1 = new \DateTime($datetime1,$this->timezone);
        $date2 = new \DateTime($datetime2,$this->timezone);
        return $date1->diff($date2);
    }
}