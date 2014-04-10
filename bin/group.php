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
                                     'INSERT INTO tbl_group (gname, gdesc) VALUES ' .
                                     '("' . $gname . '", "' . $gdesc . '");');
        } catch (Exception $e) {
            try {
                $r = Backend::instance()->sql($conn,
                                              'SELECT COUNT(*) FROM tbl_group WHERE gname = "' . $gname . '";');
                $count = (int)$r->fetch_row()[0];
            } catch (Exception $e) {
                $count = 0;
            }
            
            if ($count === 1) {
                /* user already exists */
                return ["result" => "failed",
                        "reason" => "group already exists"];
            } else {
                /* other reason */
                return ["result" => "failed",
                        "reason" => "sql error"];
            }
        }

        /* creation success */
        return ["result" => "success"];
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
            $r = Backend::instance()->sql(
                $conn, "SELECT * FROM tbl_group WHERE gname = \"" . $this->_gname . "\";");
            $r = $r->fetch_row()[0];
        } catch (Exception $e) {
            return ["result" => "failed",
                    "reason" => "sql error"];
        }

        return ["result" => "success",
                "gname"  => $r["gname"],
                "gdesc"  => $r["gdesc"]];
    }

    public function post($args) {
        if (!SessionManager::instance()->authenticate_session($args))
            return ["result" => "failed",
                    "reason" => "authentication failed"];
        
        try {
            $conn = Backend::instance()->get_db_conn();
            $r = Backend::instance()->sql(
                $conn, "INSERT IGNORE INTO tbl_user_group (email, gname) VALUES (\"" .
                $email . "\", \"" . $gname . "\");");

            return ["result" => "success"];
            
        } catch (Exception $e) {
            return ["result" => "failed",
                    "reason" => "sql error"];
        }
    }
};

?>