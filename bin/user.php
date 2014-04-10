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
                                     "INSERT INTO tbl_user (email, name, password, address, phone) VALUES " .
                                     "('$email', '$name', '$password', '$address', '$phone')");
        } catch (Exception $e) {
            try {
                $r = Backend::instance()->sql_for_result($conn,
                                                         "SELECT COUNT(*) FROM tbl_user WHERE email = '$email'");
                $count = (int)sql_extract_row($r)[0];
                Backend::instance()->sql_close_result($r);
            } catch (Exception $e) {
                $count = 0;
            }
            
            if ($count === 1) {
                /* user already exists */
                return array("result" => "failed",
                             "reason" => "user already exists");
            } else {
                /* other reason */
                return array("result" => "failed",
                             "reason" => "sql error");
            }
        }

        /* register success */
        return array("result" => "success");
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
            $result = Backend::instance()->sql_for_result(
                $conn, "SELECT * FROM tbl_user WHERE email = '$this->_email'");
            $r = sql_extract_assoc($result);
            Backend::instance()->sql_close_result($result);
        } catch (Exception $e) {
            return array("result" => "failed",
                         "reason" => "sql error");
        }

        $is_self = SessionManager::instance()->authenticate_session($args) &&
            $args["email"] === $this->_email;

        if ($is_self) {
            return array("result"  => "success",
                         "email"   => $r["EMAIL"],
                         "name"    => $r["NAME"],
                         "address" => $r["ADDRESS"],
                         "phone"   => $r["PHONE"]);
        } else {
            return array("result" => "success",
                         "email"  => $r["EMAIL"],
                         "name"   => $r["NAME"]);
        }
    }

    public function put($args) {
        if (!SessionManager::instance()->authenticate_session($args) ||
            !($args["email"] === $this->_email))
            return array("result" => "failed",
                         "reason" => "authentication failed");

        try {

            $sql = "UPDATE tbl_user SET";
            $to_modify = FALSE;
            foreach (["name" ,"password", "address", "phone"] as $field_name) {
                if (isset($args[$field_name])) {
                    $to_modify = TRUE;
                    $sql .= " $field_name = '$args[$field_name]'";
                }
            }

            if (!$to_modify)
                return ["result" => "success"];

            $sql = $sql . " WHERE email='$this->_email'";

            $conn = Backend::instance()->get_db_conn();
            $r = Backend::instance()->sql(
                $conn, $sql);

            return array("result" => "success");
            
        } catch (Exception $e) {
            return array("result" => "failed",
                         "reason" => "sql error");
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
            $r = Backend::instance()->sql_for_result(
                $conn,
                "SELECT COUNT(*) FROM tbl_user WHERE " .
                "email = '$email' AND password = '$password'");
            $count = (int)sql_extract_row($r)[0];
            Backend::instance()->sql_close_result($r);
            if ($count == 1) {
                /* success */
                $session_key = sha1((string)time() . $password);
                $st = oci_parse($conn,
                                "INSERT INTO tbl_session (email) values " .
                                "('$email') RETURNING session_key INTO :session_key");
                oci_bind_by_name($st, ":session_key", $sk, 32);
                oci_execute($st);
                oci_free_statement($st);                
                return array("result" => "success",
                             "session_key" => $sk);
            } else {
                /* mismatch */
                return array("result" => "failed",
                             "reason" => "mismatch");
            }
        } catch (Exception $e) {
            return array("result" => "failed",
                         "reason" => "sql error");
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
            $r = Backend::instance()->sql_for_result(
                $conn,
                "SELECT COUNT(*) FROM tbl_session WHERE email = '$email' AND " .
                "session_key = '$session_key'");
            $count = (int)sql_extract_row($r)[0];
            Backend::instance()->sql_close_result($r);
            if ($count == 1) {
                return TRUE;
            } else throw new Exception();
        } catch (Exception $e) {
            return FALSE;
        }
    }
};

?>