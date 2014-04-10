<?php
/* dummy root object */
require_once "restful.php";
require_once "conf.php";

require_once "user.php";
require_once "group.php";

date_default_timezone_set("UTC");

function sql_extract_assoc($r) {
    return oci_fetch_assoc($r);    
}

function sql_extract_row($r) {
    return oci_fetch_row($r);
}

class Backend extends DefaultIRest {
    public static function instance() {
        static $instance;
        if (!($instance instanceof self))
            $instance = new self();
        return $instance; 
    }

    private $_conn;
    private $_config;
    
    protected function __construct() {
        $this->_conn = NULL;
        $this->_config = Config::instance();
    }

    public function get_db_conn() {
        if ($this->_conn === NULL)
            $this->_conn = $this->_config->get_db_conn();
        return $this->_conn;
    }

    public function sql($conn, $sql) {
        error_log($sql);
        $stid = oci_parse($conn, $sql);
        if (!$stid)
            throw new Exception(oci_error($conn)["message"]);
        $result = oci_execute($stid);
        if (!$result)
            throw new Exception(oci_error($stid)["message"]);
        oci_free_statement($stid);
        return $result;
    }

    public function sql_for_result($conn, $sql) {
        error_log($sql);
        $stid = oci_parse($conn, $sql);
        if (!$stid)
            throw new Exception(oci_error($conn)["message"]);
        $result = oci_execute($stid);
        if (!$result)
            throw new Exception(oci_error($stid)["message"]);
        return $stid;
    }

    public function sql_close_result($r) {
        oci_free_statement($r);
    }

    public function clear_db_conn() {
        if (!($this->conn === NULL))
            oci_close($this->conn);
    }

    public function reset_db() {
        /* Initialize database */
        $conn = $this->get_db_conn();
        /* Drop old tables */
        try {
            
        } catch (Exception $e) { }
            
        try {
            try { $this->sql($conn, "DROP TABLE tbl_user_group"); } catch (Exception $e) { }
            try { $this->sql($conn, "DROP TABLE tbl_session"); } catch (Exception $e) { }
            try { $this->sql($conn, "DROP TABLE tbl_group"); } catch (Exception $e) { }
            try { $this->sql($conn, "DROP TABLE tbl_photo"); } catch (Exception $e) { }
            try { $this->sql($conn, "DROP TABLE tbl_message"); } catch (Exception $e) { }
            try { $this->sql($conn, "DROP TABLE tbl_transaction"); } catch (Exception $e) { }
            try { $this->sql($conn, "DROP TABLE tbl_item"); } catch (Exception $e) { }
            try { $this->sql($conn, "DROP TABLE tbl_catagory"); } catch (Exception $e) { }
            try { $this->sql($conn, "DROP TABLE tbl_user"); } catch (Exception $e) { }
            try { $this->sql($conn, "DROP SEQUENCE seq_id"); } catch (Exception $e) { }
            
            /* Create new tables */
            $this->sql($conn,
                       "CREATE TABLE tbl_user (" .
                       "email VARCHAR(100)," .
                       "name VARCHAR(100)," .
                       "password VARCHAR(40)," .
                       "address VARCHAR(100)," .
                       "phone VARCHAR(20)," .
                       "PRIMARY KEY (email))");
            
            $this->sql($conn,
                       "CREATE TABLE tbl_session (" .
                       "email VARCHAR(100) NOT NULL," .
                       "session_key RAW(16) DEFAULT SYS_GUID()," .
                       "PRIMARY KEY (email, session_key)," .
                       "FOREIGN KEY (email) REFERENCES tbl_user ON DELETE CASCADE)");
            
            $this->sql($conn,
                       "CREATE TABLE tbl_group (" .
                       "gname VARCHAR(100)," .
                       "gdesc VARCHAR(1000)," .
                       "PRIMARY KEY (gname))");
            
            $this->sql($conn,
                       "CREATE TABLE tbl_user_group (" .
                       "email VARCHAR(100) NOT NULL," .
                       "gname VARCHAR(100) NOT NULL," .
                       "PRIMARY KEY (email, gname)," .
                       "FOREIGN KEY (email) REFERENCES tbl_user ON DELETE SET NULL," .
                       "FOREIGN KEY (gname) REFERENCES tbl_group ON DELETE SET NULL)");

            $this->sql($conn,
                       "CREATE TABLE tbl_catagory (" .
                       "cname VARCHAR(100)," .
                       "PRIMARY KEY (cname))");

            $this->sql($conn,
                       "CREATE TABLE tbl_item (" .
                       "iname     VARCHAR(100) NOT NULL," .
                       "item_id   RAW(16) DEFAULT SYS_GUID()," .
                       "idesc     VARCHAR(1000)," .
                       "price     NUMBER(10, 2)," .
                       "cname     VARCHAR(100) NOT NULL," .
                       "email     VARCHAR(100) NOT NULL," .
                       "post_date DATE," .
                       "PRIMARY KEY (item_id)," .
                       "FOREIGN KEY (cname) REFERENCES tbl_catagory ON DELETE SET NULL," .
                       "FOREIGN KEY (email) REFERENCES tbl_user ON DELETE SET NULL" .
                       ")");

            $this->sql($conn,
                       "CREATE TABLE tbl_photo (" .
                       "image_data BLOB," .
                       "image_id   RAW(16) DEFAULT SYS_GUID()," .
                       "item_id    RAW(16) NOT NULL," .
                       "PRIMARY KEY (image_id)," .
                       "FOREIGN KEY (item_id) REFERENCES tbl_item ON DELETE SET NULL" .
                       ")");

            $this->sql($conn,
                       "CREATE TABLE tbl_transaction (" .
                       "trans_id  RAW(16) DEFAULT SYS_GUID()," .
                       "last_date DATE," .
                       "price     NUMBER(10,2)," .
                       "email     VARCHAR(100) NOT NULL," .
                       "item_id   RAW(16) NOT NULL," .
                       "PRIMARY KEY (trans_id)," .
                       "FOREIGN KEY (email) REFERENCES tbl_user ON DELETE SET NULL," .
                       "FOREIGN KEY (item_id) REFERENCES tbl_item ON DELETE SET NULL" .
                       ")");

            $this->sql($conn,
                       "CREATE TABLE tbl_message (" .
                       "msg_id    RAW(16) DEFAULT SYS_GUID()," .
                       "post_date DATE," .
                       "content   VARCHAR(1000)," .
                       "trans_id  RAW(16) NOT NULL," .
                       "PRIMARY KEY (msg_id)," .
                       "FOREIGN KEY (trans_id) REFERENCES tbl_transaction ON DELETE SET NULL" .
                       ")");

            $this->sql($conn, "CREATE SEQUENCE seq_id"); 
            
        } catch (Exception $e) {
            echo $e->getMessage();
            return FALSE;
        }

        return TRUE;
    }

    public function dispatch($path) {
        if ($this->_parse_path($path, $name, $remain) === FALSE)
            return NULL;
        switch ($name) {
        case "":
            return $this;
        case "user":
            return UserManager::instance()->dispatch($remain);
        case "session":
            return SessionManager::instance()->dispatch($remain);
        case "group":
            return GroupManager::instance()->dispatch($remain);
        default:
            return NULL;            
        }
    }

    public function get($args) {
        if ($args["reset"] === "yespleasedoit") {
            if ($this->reset_db())
                return array("result" => "success");
            else return array("result" => "failed");
        }

        return array("result" => "nothing to do");
    }

    public function handleException(Exception $e) {
        return "Exception(" . $e->getMessage() . ")\n";
    }
};

?>