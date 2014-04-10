<?php

class ItemManager extends DefaultIRest {

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

        $item_id = $name;
        return new ItemProxy($item_id);
    }

    public function post($args) {
        /* post new items */
        if (!SessionManager::instance()->authenticate_session($args))
            return ["result" => "failed",
                    "reason" => "authentication failed"];
        
        $iname = $args["iname"];
        $idesc = $args["idesc"];
        $price = $args["price"];
        $cname = $args["cname"];
        $email = $args["email"];

        $conn = Backend::instance()->get_db_conn();

        try {
            Backend::instance()->sql($conn,
                                     "INSERT INTO tbl_item (iname, idesc, price, cname, email, post_date) " .
                                     "VALUES ('$iname', '$idesc', $price, '$cname', '$email', SYSDATE);");
        } catch (Exception $e) {
            return array("result" => "failed",
                         "reason" => "sql error");
        }

        /* creation success */
        return array("result" => "success");
    }

    public function get($args) {
        /* list all items */
        if (!SessionManager::instance()->authenticate_session($args))
            return array("result" => "failed",
                         "reason" => "authentication failed");

        $email = $args["email"];

        $conn = Backend::instance()->get_db_conn();
        $r = Backend::instance()->sql($conn,
                                      "SELECT i.item_id FORM tbl_item i, tbl_user_group ug2, tbl_user_group ug " .
                                      "WHERE ug.email = '$email' AND ug2.gname = ug.gname AND ug.email = i.email;");
        return $r->fetch_row();
    }
};

?>