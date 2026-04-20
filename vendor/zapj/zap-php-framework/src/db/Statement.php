<?php

namespace zap\db;

use PDO;

class Statement extends \PDOStatement
{
    public function bindIn(){

    }

    public function fetchNum(){
        return $this->fetch(PDO::FETCH_NUM);
    }

    public function fetchAssoc(){
        return $this->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchKeyPair(){
        return $this->fetchAll(PDO::FETCH_KEY_PAIR);
    }


}