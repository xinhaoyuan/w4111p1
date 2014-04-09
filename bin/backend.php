<?php
/* dummy root object */
require_once "restful.php";
require_once "conf.php";

date_default_timezone_set("UTC");

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
        if ($this->_conn->connect_errno)
            $this->_conn = NULL;
        return $this->_conn;
    }

    public function sql($conn, $sql) {
        $result = $conn->query($sql);
        if ($result === FALSE) throw new Exception("sql error: " . $sql);
    }

    public function reset_db() {
        /* Initialize database */
        $conn = $this->get_db_conn();
        /* Drop old tables */
        try {
            
        } catch (Exception $e) { }
            
        try {
            $this->sql($conn, "DROP TABLE IF EXISTS tbl_user;");
            $this->sql($conn, "DROP TABLE IF EXISTS tbl_session;");
            $this->sql($conn, "DROP TABLE IF EXISTS tbl_group;");
            $this->sql($conn, "DROP TABLE IF EXISTS tbl_user_group;");
            $this->sql($conn, "DROP TABLE IF EXISTS tbl_catagory;");
            $this->sql($conn, "DROP TABLE IF EXISTS tbl_item;");
            $this->sql($conn, "DROP TABLE IF EXISTS tbl_photo;");
            $this->sql($conn, "DROP TABLE IF EXISTS tbl_transaction;");
            $this->sql($conn, "DROP TABLE IF EXISTS tbl_message;");
            
            /* Create new tables */
            $this->sql($conn,
                       "CREATE TABLE tbl_user (" .
                       "email VARCHAR(100)," .
                       "name VARCHAR(100)," .
                       "password VARCHAR(40)," .
                       "address VARCHAR(100)," .
                       "phone VARCHAR(20)," .
                       "PRIMARY KEY (email));");
            $this->sql($conn,
                       "CREATE TABLE tbl_session (" .
                       "email VARCHAR(100) NOT NULL," .
                       "session_key VARCHAR(40) NOT NULL," .
                       "PRIMARY KEY (email, session_key));");
            
            $this->sql($conn,
                       "CREATE TABLE tbl_group (" .
                       "gname VARCHAR(100)," .
                       "gdesc TEXT," .
                       "PRIMARY KEY (gname));");
            
            $this->sql($conn,
                       "CREATE TABLE tbl_user_group (" .
                       "email VARCHAR(100) NOT NULL," .
                       "gname VARCHAR(100) NOT NULL," .
                       "PRIMARY KEY (email, gname)," .
                       "FOREIGN KEY (email) REFERENCES tbl_user," .
                       "FOREIGN KEY (gname) REFERENCES tbl_group);");

            $this->sql($conn,
                       "CREATE TABLE tbl_catagory (" .
                       "cname VARCHAR(100)," .
                       "PRIMARY KEY (cname));");

            $this->sql($conn,
                       "CREATE TABLE tbl_item (" .
                       "iname VARCHAR(100) NOT NULL," .
                       "item_id INT NOT NULL AUTO_INCREMENT," .
                       "idesc TEXT," .
                       "price FLOAT," .
                       "cname VARCHAR(100) NOT NULL," .
                       "email VARCHAR(100) NOT NULL," .
                       "post_date DATE," .
                       "PRIMARY KEY (item_id)," .
                       "FOREIGN KEY (cname) REFERENCES tbl_catagory ON DELETE NO ACTION," .
                       "FOREIGN KEY (email) REFERENCES tbl_user ON DELETE NO ACTION" .
                       ");");

            $this->sql($conn,
                       "CREATE TABLE tbl_photo (" .
                       "image_data BLOB," .
                       "image_id INT NOT NULL AUTO_INCREMENT," .
                       "item_id INT NOT NULL," .
                       "PRIMARY KEY (image_id)," .
                       "FOREIGN KEY (item_id) REFERENCES tbl_item ON DELETE NO ACTION" .
                       ");");

            $this->sql($conn,
                       "CREATE TABLE tbl_transaction (" .
                       "trans_id INT NOT NULL AUTO_INCREMENT," .
                       "date DATE," .
                       "price FLOAT," .
                       "email VARCHAR(100) NOT NULL," .
                       "item_id INT NOT NULL," .
                       "PRIMARY KEY (trans_id)," .
                       "FOREIGN KEY (email) REFERENCES tbl_user ON DELETE NO ACTION," .
                       "FOREIGN KEY (item_id) REFERENCES tbl_item ON DELETE NO ACTION" .
                       ");");

            $this->sql($conn,
                       "CREATE TABLE tbl_message (" .
                       "msg_id INT NOT NULL AUTO_INCREMENT," .
                       "date DATE," .
                       "content TEXT," .
                       "trans_id INT NOT NULL," .
                       "PRIMARY KEY (msg_id)," .
                       "FOREIGN KEY (trans_id) REFERENCES tbl_transaction ON DELETE NO ACTION" .
                       ");");
            
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
            return SessionManager::instace()->dispatch($remain);
        default:
            return NULL;            
        }
    }

    public function get($args) {
        if ($args["reset"] === "yespleasedoit") {
            if ($this->reset_db())
                return ["result" => "success"];
            else return ["result" => "failed"];
        }

        return ["result" => "nothing to do"];
    }
};

class UserManager extends DefaultIRest {

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

        $email = $name;
        return new UserProxy($email);
    }

    public function post($args) {
        /* register new users */
        
        $email    = $args["email"];
        $name     = $args["name"];
        $password = $args["password"];
        $address  = $args["address"];
        $phone    = $args["phone"];

        $conn = Backend::instance()->get_db_conn();

        try {
            Backend::instance()->sql($conn,
                                     'INSERT INTO tbl_user (email, name, password, address, phone) VALUES ' .
                                     '("' . $email . '", "' . $name . '", "' . $password . '", "' .
                                     $address . '", "' . $phone . '");');
        } catch (Exception $e) {
            try {
                $r = Backend::instance()->sql($conn,
                                              'SELECT COUNT(*) FROM tbl_user WHERE email = "' . $email . '";');
                $count = (int)$r->fetch_row()[0];
            } catch (Exception $e) {
                $count = 0;
            }
            
            if ($count === 1) {
                /* user already exists */
                return ["result" => "failed",
                        "reason" => "user already exists"];
            } else {
                /* other reason */
                return ["result" => "failed",
                        "reason" => "sql error"];
            }
        }

        /* register success */
        return ["result" => "success"];
    }
};

class SessionManager extends DefaultIRest {
    public static function instance() {
        static $instance;
        if (!($instance instanceof self))
            $instance = new self();
        return $instance; 
    }

    public function __construct() {
    }

    public function dispatch($path) {
        return $this;
    }

    public function post($args) {
        /* login to get session id */
        $email = $args["email"];
        $password = $args["password"];

        try {
            $conn = Backend::instance()->get_db_conn();
            $r = Backend::instance()->sql($conn,
                                          "SELECT COUNT(*) FROM tbl_user WHERE email = \"" . $email . "\" AND password = \"" . $password . "\";");
            if ((int)$r->fetch_row()[0] == 1) {
                /* success */
                $session_key = sha1((string)time() . $password);
                Backend::instance()->sql($conn,
                                         "INSERT IGNORE INTO tbl_session (email, session_key) values " .
                                         "(\"" . $email . "\", \"" . $session_key . "\");");
                return ["result" => "success",
                        "session_key" => $session_key];
            } else {
                /* mismatch */
                return ["result" => "failed",
                        "reason" => "mismatch"];
            }
        } catch (Exception $e) {
            return ["result" => "failed",
                    "reason" => "sql error"];
        }
    }

    public function authenticate_session($args) {
        $email = $args["email"];
        $session_key = $args["session_key"];

        try {
            $conn = Backend::instance()->get_db_conn();
            $r = Backend::instance()->sql($conn,
                                          "SELECT COUNT(*) FROM tbl_session WHERE email = \"" . $email .
                                          "\" and session_key = \"" . $session_key . "\";");
            if ((int)$r->fetch_row()[0] == 1) {
                return TRUE;
            } else throw new Exception("not found");
        } catch (Exception $e) {
            return FALSE;
        }
    }
}

class UserProxy extends DefaultIRest {
    private $_email;
    
    public function __construct($email) {
        $this->_email = $email;
    }
};

?>