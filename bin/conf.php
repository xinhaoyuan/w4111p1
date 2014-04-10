<?php

class Config {
    public function instance() {
        static $instance;
        if (!($instance instanceof self))
            $instance = new self();
        return $instance;
    }
    
    public $oci_username;
    public $oci_password;
    public $oci_connstr;
    
    protected function __construct() {
        $this->oci_username = "xx2153";
        $this->oci_password = "xinan";
        $this->oci_connstr  = "w4111g.cs.columbia.edu:1521/adb";
    }
    
     public function get_db_conn() {
         $conn = oci_connect($this->oci_username,
                             $this->oci_password,
                             $this->oci_connstr);
         if (!$conn) {
             throw new Exception(oci_error()["message"]);
         }

         return $conn;
     }
};

?>