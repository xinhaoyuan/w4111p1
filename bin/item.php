<?php

require_once "backend.php";

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
        return (new ItemProxy($item_id))->dispatch($remain);
    }

    public function post($args) {
        /* post new items */
        if (!SessionManager::instance()->authenticate_session($args))
            return array("result" => "failed",
                         "reason" => "authentication failed");
        
        $iname = $args["iname"];
        $idesc = $args["idesc"];
        $price = $args["price"];
        if (isset($args["cname"])) {
            $cname = $args["cname"];
            $cname = "'$cname'";
        } else $cname = "NULL";
        $email = $args["email"];

        try {
            $conn = Backend::instance()->get_db_conn();
            $st = oci_parse($conn,
                            "INSERT INTO tbl_item (iname, idesc, price, cname, email, post_date) " .
                            "VALUES ('$iname', '$idesc', $price, $cname, '$email', SYSDATE) " .
                            "RETURNING item_id INTO :item_id");
            oci_bind_by_name($st, ":item_id", $id, 32);
            oci_execute($st);
            oci_free_statement($st);
        } catch (Exception $e) {
            return array("result" => "failed",
                         "reason" => "sql error");
        }

        /* creation success */
        return array("result" => "success",
                     "item_id" => "$id");
    }

    public function get($args) {
        /* list all items */
        if (!SessionManager::instance()->authenticate_session($args))
            return array("result" => "failed",
                         "reason" => "authentication failed");

        $email = $args["email"];
        $extra_conditions = "";
        if (isset($args["owner_email"])) {
            $owner_email = $args["owner_email"];
            $extra_conditions .= " AND i.email = '$owner_email'";
        }

        try {
            $conn = Backend::instance()->get_db_conn();
            $r = Backend::instance()->sql_for_result(
                $conn,
                "SELECT DISTINCT RAWTOHEX(i.item_id) as item_id, i.iname, i.idesc, i.price, i.cname, i.email, i.post_date " .
                "FROM tbl_item i, tbl_user_group ug2, tbl_user_group ug " .
                "WHERE ug.email = '$email' AND ug2.gname = ug.gname AND ug2.email = i.email" .
                $extra_conditions .
                " ORDER BY i.post_date DESC");
        } catch (Exception $e) {
            $r = NULL;
        }

        $item_arr = [];
        if ($r) {
            while ($item = sql_extract_assoc($r)) {
                array_push($item_arr,
                           array(
                               "item_id" => $item["ITEM_ID"],
                               "iname"   => $item["INAME"],
                               "idesc"   => $item["IDESC"],
                               "cname"   => $item["CNAME"],
                               "email"   => $item["EMAIL"],
                               "price"   => $item["PRICE"],
                               "post_date" => $item["POST_DATE"]));
            }
            Backend::instance()->sql_close_result($r);
        }
        
        return array("result" => "success",
                     "items" => $item_arr);
    }
};

class ItemProxy extends DefaultIRest {
    private $_item_id;
    
    public function __construct($item_id) {
        $this->_item_id = $item_id;
    }

    public function dispatch($path) {
        if ($this->_parse_path($path, $name, $remain) === FALSE)
            return NULL;

        if ($name === "photo")
            return new ItemPhotoManager($this->_item_id);
        else return $this;
    }

    public function get($args) {
        /* get single item */
        if (!SessionManager::instance()->authenticate_session($args))
            return array("result" => "failed",
                         "reason" => "authentication failed");

        $email = $args["email"];
        
        $conn = Backend::instance()->get_db_conn();
        $r = Backend::instance()->sql_for_result(
            $conn,
            "SELECT i.iname, i.idesc, i.price, i.cname, i.email, i.post_date " .
            "FROM tbl_item i WHERE i.item_id = HEXTORAW('$this->_item_id')");
        $info = sql_extract_assoc($r);
        Backend::instance()->sql_close_result($r);

        if (!$info)
            return array("result" => "failed",
                         "reason" => "item not found");

        $r = Backend::instance()->sql_for_result(
            $conn,
            "SELECT RAWTOHEX(p.image_id) AS photo_id, p.image_url FROM tbl_photo p WHERE p.item_id = '$this->_item_id'");

        $photo_arr = [];
        while ($photo = sql_extract_assoc($r)) {
            array_push($photo_arr,
                       array("photo_id" => $photo["PHOTO_ID"],
                             "image_url" => $photo["IMAGE_URL"])
                );
        }
        Backend::instance()->sql_close_result($r);

        if ($email === $info["EMAIL"]) {
            /* owner */
            $r = Backend::instance()->sql_for_result(
                $conn,
                "SELECT RAWTOHEX(t.trans_id) AS trans_id, t.email, t.last_date FROM tbl_transaction t WHERE t.item_id = '$this->_item_id' ORDER BY t.last_date DESC");
        } else {
            /* guest */
            $r = Backend::instance()->sql_for_result(
                $conn,
                "SELECT RAWTOHEX(t.trans_id) AS trans_id, t.email, t.last_date FROM tbl_transaction t " .
                "WHERE t.item_id = '$this->_item_id' AND t.email = '$email' ORDER BY t.last_date DESC");
        }

        $trans_arr = [];
        while ($trans = sql_extract_assoc($r)) {
            array_push($trans_arr,
                       array("trans_id"  => $trans["TRANS_ID"],
                             "email"     => $trans["EMAIL"],
                             "last_date" => $trans["LAST_DATE"])
                );
        }
        Backend::instance()->sql_close_result($r);

        return array("result" => "success",
                     "item_id"=> $this->_item_id,
                     "iname"  => $info["INAME"],
                     "idesc"  => $info["IDESC"],
                     "cname"  => $info["CNAME"],
                     "price"  => $info["PRICE"],
                     "email"  => $info["EMAIL"],
                     "post_date" => $info["POST_DATE"],
                     "photos" => $photo_arr,
                     "transactions" => $trans_arr);
    }
};

class ItemPhotoManager extends DefaultIRest {
    private $_item_id;
    
    public function __construct($item_id) {
        $this->_item_id = $item_id;
    }

    public function post($args) {
        $conn = Backend::instance()->get_db_conn();
        $url = $args["image_url"];
        $st = oci_parse($conn,
                        "INSERT INTO tbl_photo (item_id, image_url) VALUES " .
                        "('$this->_item_id', '$url') " .
                        "RETURNING image_id INTO :image_id");
        oci_bind_by_name($st, ":image_id", $id, 32);
        oci_execute($st);
        oci_free_statement($st);

        return array("result" => "success",
                     "photo_id" => $id);
    }
};

?>
