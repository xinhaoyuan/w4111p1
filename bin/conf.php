<?php

class Config {
    public function instance() {
        static $instance;
        if (!($instance instanceof self))
            $instance = new self();
        return $instance;
    }

     public $mysql_host;
     public $mysql_username;
     public $mysql_password;
     public $mysql_database;
     
     protected function __construct() {
         $this->mysql_host     = "www.xinhaoyuan.net";
         $this->mysql_username = "w4111p1";
         $this->mysql_password = "EGqy2VDest8m7m5a";
         $this->mysql_database = "w4111p1";
     }

     public function get_db_conn() {
         return new mysqli($this->mysql_host,
                           $this->mysql_username,
                           $this->mysql_password,
                           $this->mysql_database,
                           3306);
     }
};

?>