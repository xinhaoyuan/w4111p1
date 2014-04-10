<?php

require_once "backend.php";

class TransactionManager extends DefaultIRest {

    public static function instance() {
        static $instance;
        if (!($instance instanceof self))
            $instance = new self();
        return $instance; 
    }

    public function __construct() {        
    }

    public function post($args) {
        /* create transaction */
        if (!SessionManager::instance()->authenticate_session($args))
            return array("result" => "failed",
                         "reason" => "authentication failed");
        
        $item_id = $args["item_id"];
        $email   = $args["email"];

        try {
            $conn = Backend::instance()->get_db_conn();
            $r = Backend::instance()->sql_for_result(
                $conn,
                "INSERT INTO tbl_transaction");
        } catch (Exception $e) {
            return array("result" => "failed",
                         "reason" => "sql error");
        }

        return array("result" => "success");
    }

}

?>
