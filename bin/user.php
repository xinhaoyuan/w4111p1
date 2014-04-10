<?php

require_once "backend.php";

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
                                              "SELECT COUNT(*) FROM tbl_user WHERE email = '$email';");
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

class UserProxy extends DefaultIRest {
    private $_email;
    
    public function __construct($email) {
        $this->_email = $email;
    }

    public function get($args) {
        try {
            $conn = Backend::instance()->get_db_conn();
            $r = Backend::instance()->sql(
                $conn, "SELECT * FROM tbl_user WHERE email = \"" . $this->_email . "\";");
            $r = $r->fetch_assoc();
        } catch (Exception $e) {
            return ["result" => "failed",
                    "reason" => "sql error"];
        }

        $is_self = SessionManager::instance()->authenticate_session($args) &&
            $args["email"] === $this->_email;

        if ($is_self) {
            return ["result"  => "success",
                    "email"   => $r["email"],
                    "name"    => $r["name"],
                    "address" => $r["address"],
                    "phone"   => $r["phone"]];
        } else {
            return ["result" => "success",
                    "email"  => $r["email"],
                    "name"   => $r["name"]];
        }
    }

    public function put($args) {
        if (!SessionManager::instance()->authenticate_session($args) ||
            !($args["email"] === $this->_email))
            return ["result" => "failed",
                    "reason" => "authentication failed"];

        try {

            $sql = "UPDATE tbl_user SET";
            $to_modify = FALSE;
            foreach (["name" ,"password", "address", "phone"] as $field_name) {
                if (isset($args[$field_name])) {
                    $to_modify = TRUE;
                    $sql .= " " . $field_name . " = \"" . $args[$field_name] . "\"";
                }
            }

            if (!$to_modify)
                return ["result" => "success"];

            $sql = $sql . " WHERE email=\"" . $this->_email . "\";";

            $conn = Backend::instance()->get_db_conn();
            $r = Backend::instance()->sql(
                $conn, $sql);

            return ["result" => "success"];
            
        } catch (Exception $e) {
            return ["result" => "failed",
                    "reason" => "sql error"];
        }
        
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
        if (!isset($args["email"]) ||
            !isset($args["session_key"]))
            return FALSE;
            
        $email = $args["email"];
        $session_key = $args["session_key"];

        try {
            $conn = Backend::instance()->get_db_conn();
            $r = Backend::instance()->sql($conn,
                                          "SELECT COUNT(*) FROM tbl_session WHERE email = \"" . $email .
                                          "\" and session_key = \"" . $session_key . "\";");
            if ((int)$r->fetch_row()[0] == 1) {
                return TRUE;
            } else throw new Exception();
        } catch (Exception $e) {
            return FALSE;
        }
    }
};

?>