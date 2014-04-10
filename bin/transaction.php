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

     public function dispatch($path) {
        if ($this->_parse_path($path, $name, $remain) === FALSE)
            return NULL;

        if ($name === "") {
            return $this;
        }

        $trans_id = $name;
        return new TransactionProxy($trans_id);
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
                "SELECT COUNT(*) FROM tbl_item WHERE item_id = '$item_id' AND email != '$email'");
            $count = (int)sql_extract_row($r)[0];
            Backend::instance()->sql_close_result($r);
            if ($count != 1) {
                return array("result" => "failed",
                             "reason" => "item not found or you are the owner");
            }
        } catch (Exception $e) {
            return array("result" => "failed",
                         "reason" => "sql error");
        }

        try {
            $conn = Backend::instance()->get_db_conn();
            $st = oci_parse(
                $conn,
                "INSERT INTO tbl_transaction (last_date, price, email, item_id) VALUES " .
                "(SYSDATE, 0.00, '$email', '$item_id') RETURNING trans_id INTO :id");
            oci_bind_by_name($st, ":id", $id, 32);
            oci_execute($st);
            oci_free_statement($st);
        } catch (Exception $e) {
            return array("result" => "failed",
                         "reason" => "sql error");
        }

        return array("result" => "success",
                     "trans_id" => $id);
    }

    public function get($args) {
        if (!SessionManager::instance()->authenticate_session($args))
            return array("result" => "failed",
                         "reason" => "authentication failed");
        $email = $args["email"];

        try {
            $conn = Backend::instance()->get_db_conn();

            $r = Backend::instance()->sql_for_result(
                $conn,
                "SELECT RAWTOHEX(tx.trans_id) as trans_id, RAWTOHEX(tx.item_id) as item_id, tx.last_date ".
                "FROM tbl_transaction tx WHERE " .
                "tx.email = '$email'");
            $gtx = [];
            while ($tx = sql_extract_assoc($r)) {
                array_push($gtx, array(
                               "trans_id"  => $tx["TRANS_ID"],
                               "item_id"   => $tx["ITEM_ID"],
                               "last_date" => $tx["LAST_DATE"]));
            }
            Backend::instance()->sql_close_result($r);

            $r = Backend::instance()->sql_for_result(
                $conn,
                "SELECT RAWTOHEX(tx.trans_id) as trans_id, RAWTOHEX(tx.item_id) as item_id, tx.last_date " .
                "FROM tbl_transaction tx, tbl_item i WHERE " .
                "tx.item_id = i.item_id AND i.email = '$email'");
            $otx = [];
            while ($tx = sql_extract_assoc($r)) {
                array_push($otx, array(
                               "trans_id"  => $tx["TRANS_ID"],
                               "item_id"   => $tx["ITEM_ID"],
                               "last_date" => $tx["LAST_DATE"]));
            }
            Backend::instance()->sql_close_result($r);

            return array("result" => "success",
                         "guest_tx" => $gtx,
                         "owner_tx" => $otx);
        } catch (Exception $e) {
            return array("result" => "failed",
                         "reason" => "sql error");
        }
    }

};

class TransactionProxy extends DefaultIRest {
    private $_trans_id;
    
    public function __construct($trans_id) {
        $this->_trans_id = $trans_id;
    }
};

?>
