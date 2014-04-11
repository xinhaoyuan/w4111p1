<?php

require_once "backend.php";

class GroupManager extends DefaultIRest {

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
        } else if ($name === "list") {
            return new GroupEnumerator();
        }

        $gname = $name;
        return new GroupProxy($gname);
    }

    public function post($args) {
        /* create new groups */

        $gname = $args["gname"];
        $gdesc  = $args["gdesc"];

        $conn = Backend::instance()->get_db_conn();

        try {
            Backend::instance()->sql($conn,
                                     "INSERT INTO tbl_group (gname, gdesc) VALUES " .
                                     "('$gname', '$gdesc')");
        } catch (Exception $e) {
            try {
                $r = Backend::instance()->sql_for_result($conn,
                                              "SELECT COUNT(*) FROM tbl_group WHERE gname = '$gname'");
                $count = (int)sql_extract_row($r);
                Backend::instance()->sql_close_result($r);
            } catch (Exception $e) {
                $count = 0;
            }
            
            if ($count === 1) {
                /* user already exists */
                return array("result" => "failed",
                             "reason" => "group already exists");
            } else {
                /* other reason */
                return array("result" => "failed",
                             "reason" => "sql error");
            }
        }

        /* creation success */
        return array("result" => "success");
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
                "SELECT ug.gname FROM tbl_user_group ug " .
                "WHERE ug.email = '$email'");
            $groups = [];
            while ($ug = sql_extract_assoc($r)) {
                array_push($groups, $ug["GNAME"]);
            }
            Backend::instance()->sql_close_result($r);
        } catch (Exception $e) {
            return array("result" => "failed",
                         "reason" => "sql error");
        }

        return array("result" => "success",
                     "groups" => $groups);
                                             
    }
};

class GroupEnumerator extends DefaultIRest {
    public function get($args) {
        $conditions = "";
        if (isset($args["keyword"])) {
            $keyword = $args["keyword"];
            $conditions .= " WHERE (g.gname LIKE '%$keyword%' OR g.gdesc LIKE '%$keyword%')";
        }
        
        try {
            $conn = Backend::instance()->get_db_conn();
            $r = Backend::instance()->sql_for_result(
                $conn,
                "SELECT g.gname, g.gdesc FROM tbl_group g" . $conditions);
            $group_arr = [];
            while ($group = sql_extract_assoc($r)) {
                array_push($group_arr, array(
                               "gname" => $group["GNAME"],
                               "gdesc" => $group["GDESC"]));
            }
            Backend::instance()->sql_close_result($r);
        } catch (Exception $e) {
            return array("result" => "failed",
                         "reason" => "sql error");
        }

        return array("result" => "success",
                     "groups" => $group_arr);
    }
};

class GroupProxy extends DefaultIRest {
    private $_gname;
    
    public function __construct($gname) {
        $this->_gname = $gname;
    }

    public function get($args) {
        try {
            $conn = Backend::instance()->get_db_conn();
            $result = Backend::instance()->sql_for_result(
                $conn, "SELECT * FROM tbl_group WHERE gname = '$this->_gname'");
            $info = sql_extract_assoc($result);
            Backend::instance()->sql_close_result($result);
            $result = Backend::instance()->sql_for_result(
                $conn,
                "SELECT email FROM tbl_user_group WHERE " .
                "gname = '$this->_gname'");
            $a = [];
            while ($e = sql_extract_assoc($result)) {
                array_push($a, $e["EMAIL"]);
            }
            Backend::instance()->sql_close_result($result);
        } catch (Exception $e) {
            return array("result" => "failed",
                         "reason" => "sql error");
        }

        return array("result" => "success",
                     "gname"  => $info["GNAME"],
                     "gdesc"  => $info["GDESC"],
                     "members" => $a);
    }

    public function post($args) {
        if (!SessionManager::instance()->authenticate_session($args))
            return array("result" => "failed",
                         "reason" => "authentication failed");

        $email = $args["email"];
        $gname = $this->_gname;
        
        try {
            $conn = Backend::instance()->get_db_conn();
            try {
                $r = Backend::instance()->sql(
                    $conn, "INSERT INTO tbl_user_group (email, gname) VALUES ".
                    "('$email', '$gname')");
            } catch (Exception $e) { }

            return array("result" => "success");
            
        } catch (Exception $e) {
            return array("result" => "failed",
                         "reason" => "sql error");
        }
    }
};

?>