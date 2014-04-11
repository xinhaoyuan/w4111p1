<?php
/* dummy root object */
require_once "restful.php";
require_once "conf.php";

require_once "user.php";
require_once "group.php";
require_once "item.php";
require_once "transaction.php";

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
                       "email    VARCHAR(100)," .
                       "name     VARCHAR(100)," .
                       "password VARCHAR(40)," .
                       "address  VARCHAR(100)," .
                       "phone    VARCHAR(20)," .
                       "PRIMARY KEY (email))");
            
            $this->sql($conn,
                       "CREATE TABLE tbl_session (" .
                       "email       VARCHAR(100) NOT NULL," .
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
                       "iname     VARCHAR(1000) NOT NULL," .
                       "item_id   RAW(16) DEFAULT SYS_GUID()," .
                       "idesc     VARCHAR(2000)," .
                       "price     NUMBER(10, 2)," .
                       "cname     VARCHAR(100)," .
                       "email     VARCHAR(100) NOT NULL," .
                       "post_date DATE," .
                       "PRIMARY KEY (item_id)," .
                       "FOREIGN KEY (cname) REFERENCES tbl_catagory ON DELETE SET NULL," .
                       "FOREIGN KEY (email) REFERENCES tbl_user ON DELETE SET NULL" .
                       ")");

            $this->sql($conn,
                       "CREATE TABLE tbl_photo (" .
                       "image_url  VARCHAR(1000)," .
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
                       "email     VARCHAR(100) NOT NULL," .
                       "PRIMARY KEY (msg_id)," .
                       "FOREIGN KEY (trans_id) REFERENCES tbl_transaction ON DELETE SET NULL," .
                       "FOREIGN KEY (email) REFERENCES tbl_user ON DELETE SET NULL" .
                       ")");

/////////////////////////////////

$this->sql($conn, "INSERT INTO tbl_group VALUES
('Columbia University', 'Columbia University in the City of New York, commonly referred to as Columbia University, is an American private Ivy League research university located in the Morningside Heights neighborhood of Upper Manhattan in New York City.')");
$this->sql($conn, "INSERT INTO tbl_group VALUES
('New York University', 'New York University is a private, nonsectarian American research university based in New York City. Founded in 1831, NYU is now one of the largest private universities in the United States.')");
$this->sql($conn, "INSERT INTO tbl_group VALUES
('The City College of New York', 'The City College of the City University of New York is a senior college of the City University of New York in New York City. It is the oldest of City University''s twenty-three institutions of higher learning.')");

$this->sql($conn, "INSERT INTO tbl_user VALUES
('BryanMDarnell@columbia.edu', 'Bryan M. Darnell', '123456', '272 Selah Way, South Burlington, VT 05403', '802-625-5739')");
$this->sql($conn, "INSERT INTO tbl_user VALUES
('BrettAButts@columbia.edu', 'Brett A. Butts', 'fiecu5Toh8u', '2438 Hog Camp Road, Chicago, IL 60631','708-415-4581')");
$this->sql($conn, "INSERT INTO tbl_user VALUES
('DonaldCVargas@columbia.edu', 'Donald C. Vargas','aeNgean1','3371 Ridenour Street, Kensington, KS 66951','785-994-2480')");
$this->sql($conn, "INSERT INTO tbl_user VALUES
('PeterMCurrent@nyu.edu','Peter M. Current', 'oj1Ne6bai','563 Golden Street, Doral, FL 33178', '305-599-6008')");
$this->sql($conn, "INSERT INTO tbl_user VALUES
('TerryKFiore@nyu.edu','Terry K. Fiore','u6charei1lah','1292 White Oak Drive, Kansas City, MO 64106','816-754-2334')");
$this->sql($conn, "INSERT INTO tbl_user VALUES
('AmberDMassey@nyu.edu','Amber D. Massey','wai9Toh','4216 Lynn Ogden Lane, Beaumont, TX 77701','409-841-0031')");
$this->sql($conn, "INSERT INTO tbl_user VALUES
('WilmaTSmith@cuny.edu','Wilma T. Smith','Thavivelball','4012 Rowes Lane, Lucas, KY 42156','270-646-3801')");
$this->sql($conn, "INSERT INTO tbl_user VALUES
('JanMCarter@cuny.edu','Jan M. Carter','Dishmed','4550 Candlelight Drive, Houston, TX 77063','281-382-6484')");
$this->sql($conn, "INSERT INTO tbl_user VALUES
('SergioEKnowles@gmail.com','Sergio E. Knowles','Linsomont71','197 Oak Ridge Drive, Jefferson City, MO 65101','573-514-1387')");
$this->sql($conn, "INSERT INTO tbl_user VALUES
('JamesDDavis@gmail.com','James D. Davis','Propeas','1566 Maryland Avenue, Saint Petersburg, FL 33714','727-526-6243')");

$this->sql($conn, "INSERT INTO tbl_user_group VALUES
('BryanMDarnell@columbia.edu', 'Columbia University')");
$this->sql($conn, "INSERT INTO tbl_user_group VALUES
('BrettAButts@columbia.edu','Columbia University')");
$this->sql($conn, "INSERT INTO tbl_user_group VALUES
('DonaldCVargas@columbia.edu','Columbia University')");
$this->sql($conn, "INSERT INTO tbl_user_group VALUES
('PeterMCurrent@nyu.edu','New York University')");
$this->sql($conn, "INSERT INTO tbl_user_group VALUES
('TerryKFiore@nyu.edu','New York University')");
$this->sql($conn, "INSERT INTO tbl_user_group VALUES
('AmberDMassey@nyu.edu', 'New York University')");
$this->sql($conn, "INSERT INTO tbl_user_group VALUES
('WilmaTSmith@cuny.edu','The City College of New York')");
$this->sql($conn, "INSERT INTO tbl_user_group VALUES
('JanMCarter@cuny.edu','The City College of New York')");
$this->sql($conn, "INSERT INTO tbl_user_group VALUES
('SergioEKnowles@gmail.com','The City College of New York')");
$this->sql($conn, "INSERT INTO tbl_user_group VALUES
('SergioEKnowles@gmail.com','New York University')");
$this->sql($conn, "INSERT INTO tbl_user_group VALUES
('JamesDDavis@gmail.com','Columbia University')");
$this->sql($conn, "INSERT INTO tbl_user_group VALUES
('JamesDDavis@gmail.com','New York University')");

$this->sql($conn, "INSERT INTO tbl_catagory VALUES
('Electronics')");
$this->sql($conn, "INSERT INTO tbl_catagory VALUES
('Books')");
$this->sql($conn, "INSERT INTO tbl_catagory VALUES
('Misc')");

$this->sql($conn, "INSERT INTO tbl_item VALUES
(
'Samsung UN32EH5300 32-Inch 1080p 60 Hz Smart LED HDTV (Black)',
'01',
'With this Smart HDTV, Smart Content provides new ways to explore and locate your favorite shows, movies, games, and more. A full web browser with WiFi built-in and innovative apps made for TV, along with Signature Services, enhances your enjoyment. AllShare Play allows you to stream content from other devices and enjoy it on the big screen. The Wide Color Enhancer Plus provides vibrant natural-looking images and it?s all in a sleek ultra slim design.
Never miss a moment with Samsung Smart TV. Watch your favorite movies while you browse the web or explore the Smart Hub. Find more content you love by searching for shows, movies, and videos across vudu, Hulu Plus, YouTube, and other digital content providers. Movies are handpicked for you through recommendations based on your viewing history and ratings. Access all your apps and download new ones such as Netflix, Facebook, YouTube, Hulu Plus, and Twitter! Browse the web while you watch movies and TV shows, and enjoy TV while you chat with friends and family online, all on one screen.',
299.99,
'Electronics',
'BrettAButts@columbia.edu',
'24-may-10')");
$this->sql($conn, "INSERT INTO tbl_photo VALUES
('http://ecx.images-amazon.com/images/I/717JHmTc8pL._SL1500_.jpg','01','01')");
$this->sql($conn, "INSERT INTO tbl_photo VALUES
('http://ecx.images-amazon.com/images/I/61Gw%2BmO9b-L._SL1500_.jpg','02','01')");

$this->sql($conn, "INSERT INTO tbl_item VALUES
(
'ViewSonic PJD5134 SVGA DLP Projectorwith 3D Blu-Ray Ready, Integrated Speaker and Dynamic ECO (Black)',
'02',
'The PJD5134 is a high-performance SVGA 800x600 DLP projector with 3000 ANSI lumens and 15000:1 contrast ratio. This projector is packed with features including HDMI, DynamicECO, multiple PC and video input options, 1.1x optical zoom, keystone correction and integrated speakers. With its HDMI input, the PJD5134 can display 3D content directly from a 3D Blu-ray player. Presenters can put the PJD5134 in ?standby? mode reducing brightness down to 30% with DynamicECO technology when they need to shift audience?s focus without restarting the projector. Filter-less design and energy-saving eco mode provide for virtually zero maintenance and enhanced product reliability. The PJD5134 portable design is ideal for tabletop use or mounting on a ceilingin both classrooms and corporate offices.',
399.99,
'Electronics',
'BrettAButts@columbia.edu',
'30-may-10')");
$this->sql($conn, "INSERT INTO tbl_photo VALUES
('http://ecx.images-amazon.com/images/I/51C%2BWVYIomL._SL1500_.jpg','03','02')");

$this->sql($conn, "INSERT INTO tbl_item VALUES
(
'Samsung BD-F5100 Blu-ray Disc Player',
'03',
null,
57.99,
'Electronics',
'TerryKFiore@nyu.edu',
'01-jul-12')");

$this->sql($conn, "INSERT INTO tbl_item VALUES
(
'Canon EOS Rebel T3i 18 MP CMOS Digital SLR Camera and DIGIC 4 Imaging with EF-S 18-55mm f/3.5-5.6 IS Lens',
'04',
'The EOS Rebel T3i has an 18.0 Megapixel CMOS (Complementary Metal Oxide Semiconductor) sensor that captures images with exceptional clarity and tonal range and offers more than enough resolution for big enlargements or crops. This first-class sensor features many of the same new technologies as used by professional Canon cameras to maximize each pixel?s light-gathering efficiency. Its APS-C size sensor creates an effective 1.6x field of view (compared to 35mm format).',
599.99,
'Electronics',
'JanMCarter@cuny.edu',
'05-dec-13')");
$this->sql($conn, "INSERT INTO tbl_photo VALUES
('http://ecx.images-amazon.com/images/I/71hurE69ltL._SL1500_.jpg','04','04')");
$this->sql($conn, "INSERT INTO tbl_photo VALUES
('http://ecx.images-amazon.com/images/I/41UL8R4TmNL.jpg','05','04')");

$this->sql($conn, "INSERT INTO tbl_item VALUES
(
'Rokinon 8mm F2.8 Ultra-Wide Fisheye Lens for Sony E-mount and NEX Cameras Silver',
'05',
'The NEW ROKINON compact 8mm Ultra Wide Angle Fisheye Lens is the most affordable Fisheye Lens in the market for Sony E-mount and NEX cameras. It features an extremely wide field of view, it is small and compact, and its build quality and optical construction are superb. The Rokinon 8mm Fisheye lens was ergonomically designed by Rokinon for a balanced and comfortable fit on Sony NEX cameras. The lens exhibits exceptional sharpness and color rendition and is a perfect addition to your NEX and E-mount lens assortment.',
313.89,
'Electronics',
'JamesDDavis@gmail.com',
'24-jan-09')");
$this->sql($conn, "INSERT INTO tbl_photo VALUES
('http://ecx.images-amazon.com/images/I/71sw4gQqheL._SL1500_.jpg', '06','05')");

$this->sql($conn, "INSERT INTO tbl_item VALUES
(
'Toshiba CB35-A3120 13.3-Inch Chromebook',
'06',
null,
279.00,
'Electronics',
'JanMCarter@cuny.edu',
'28-mar-11')");
$this->sql($conn, "INSERT INTO tbl_photo VALUES
('http://ecx.images-amazon.com/images/I/71iYVqn4osL._SL1500_.jpg','07','06')");
$this->sql($conn, "INSERT INTO tbl_photo VALUES
('http://ecx.images-amazon.com/images/I/71CCGcBME4L._SL1500_.jpg','08','06')");

$this->sql($conn, "INSERT INTO tbl_item VALUES
(
'Minecraft: Redstone Handbook: An Official Mojang Book',
'07',
'It''s time to wire up and get connected to one of the most complex areas of Minecraft--Redstone. Redstone experts guide you through all aspects of working with Redstone including mining, smelting, using repeaters, circuit components and circuit designs. This handbook also includes exclusive tips from game creator Notch himself and some of the most extraordinary Redstone creations ever made. So power up and get switched on to Redstone--it''s electrifying!',
5.99,
'Books',
'AmberDMassey@nyu.edu',
'23-nov-11')");
$this->sql($conn, "INSERT INTO tbl_photo VALUES
('http://ecx.images-amazon.com/images/I/41gakdZwbtL.jpg','09','07')");
$this->sql($conn, "INSERT INTO tbl_photo VALUES
('http://ecx.images-amazon.com/images/I/51xzJtkowML.jpg','10','07')");

$this->sql($conn, "INSERT INTO tbl_item VALUES
('Jesus: A Pilgrimage',
 '08',
'Hardcover',
20.51,
'Books',
'TerryKFiore@nyu.edu',
'23-oct-12')");

$this->sql($conn, "INSERT INTO tbl_item VALUES
('Wings of Fire Book Five: The Brightest Night',
 '09',
'The dragonets struggle to fulfill the prophecy and -- somehow -- end the war in this thrilling new installment of the bestselling WINGS OF FIRE series!',
9.65,
'Books',
'AmberDMassey@nyu.edu',
'21-Oct-12')");

$this->sql($conn, "INSERT INTO tbl_item VALUES
(
'DISNEY INFINITY Figure Dash',
'10',
'The fastest kid on the planet, Dash uses his super speed for zip maneuvers and attacks. Enemies who try to catch him?just eat his dust!',
8.99,
'Misc',
'WilmaTSmith@cuny.edu',
'12-Dec-12')");
$this->sql($conn, "INSERT INTO tbl_photo VALUES
('http://ecx.images-amazon.com/images/I/81-jBdz2fZL._SL1500_.jpg','11','10')");

$this->sql($conn, "INSERT INTO tbl_transaction VALUES
('01', '24-feb-14', 280.00, 'AmberDMassey@nyu.edu', '01')");

$this->sql($conn, "INSERT INTO tbl_transaction VALUES
('02', '28-feb-14', 276.00, 'DonaldCVargas@columbia.edu', '01')");

$this->sql($conn, "INSERT INTO tbl_transaction VALUES
('03', '01-Sep-13', 449.98, 'AmberDMassey@nyu.edu','02')");
$this->sql($conn, "INSERT INTO tbl_message VALUES
('01', '02-Sep-13', 'Quite expensive','03', 'AmberDMassey@nyu.edu')");

$this->sql($conn, "INSERT INTO tbl_transaction VALUES
('04', '23-Sep-12', 599.99, 'TerryKFiore@nyu.edu','03')");

$this->sql($conn, "INSERT INTO tbl_transaction VALUES
('05', '29-Aug-12', 599.99, 'JanMCarter@cuny.edu','03')");
$this->sql($conn, "INSERT INTO tbl_message VALUES
('02', '30-Aug-12', 'Looks good', '05','JanMCarter@cuny.edu')");


$this->sql($conn, "INSERT INTO tbl_transaction VALUES
('06', '05-may-11', 279.00, 'JamesDDavis@gmail.com','06')");
$this->sql($conn, "INSERT INTO tbl_message VALUES
('03', '01-may-11', 'I have to say, really few people use chrome book, but I still want to give a try', '06','JamesDDavis@gmail.com')");
$this->sql($conn, "INSERT INTO tbl_message VALUES
('04', '05-may-11', 'Just bought it!!!', '06','JamesDDavis@gmail.com')");
$this->sql($conn, "INSERT INTO tbl_message VALUES
('05', '07-may-11', 'Could anybody tell me, how can I install WINDOWS on it???', '06','JamesDDavis@gmail.com')");

$this->sql($conn, "INSERT INTO tbl_transaction VALUES
('07', '23-nov-11', 5.99, 'WilmaTSmith@cuny.edu', '07')");
$this->sql($conn, "INSERT INTO tbl_message VALUES
('06','24-nov-11','This looks a fantastic book', '07','WilmaTSmith@cuny.edu')");
$this->sql($conn, "INSERT INTO tbl_message VALUES
('07','24-nov-11','Do you know how to build a great wall using minecreaft?', '07','WilmaTSmith@cuny.edu')");

$this->sql($conn, "INSERT INTO tbl_transaction VALUES
('08', '24-nov-11', 5.99, 'DonaldCVargas@columbia.edu', '07')");
$this->sql($conn, "INSERT INTO tbl_message VALUES
('08','26-nov-11','Fast delivery','08','DonaldCVargas@columbia.edu')");

$this->sql($conn, "INSERT INTO tbl_transaction VALUES
('09', '25-nov-11', 5.99, 'WilmaTSmith@cuny.edu', '07')");
$this->sql($conn, "INSERT INTO tbl_message VALUES
('09','26-nov-11','Promised buy one more for my friends','09','WilmaTSmith@cuny.edu')");

$this->sql($conn, "INSERT INTO tbl_transaction VALUES
('10', '24-dec-12', 9.99, 'BryanMDarnell@columbia.edu','10')");

$this->sql($conn, "INSERT INTO tbl_transaction VALUES
('11', '29-dec-12', 8.99, 'PeterMCurrent@nyu.edu','10')");

$this->sql($conn, "INSERT INTO tbl_transaction VALUES
('12', '29-Aug-12', 599.99, 'SergioEKnowles@gmail.com','03')");

////////////////////////////////
            $r = $this->dispatch("/group/")->post(
                ["gname" => "g",
                 "gdesc" => "desc"]);

            var_dump($r); echo "<br />";
            
            $r = $this->dispatch("/user/")->post(
                ["email" => "a@b.com",
                 "name"  => "test_a",
                 "password" => "test",
                 "address" => "dont know",
                 "phone" => "1284432"]);

            var_dump($r); echo "<br />";

            $r = $this->dispatch("/user/")->post(
                ["email" => "b@b.com",
                 "name"  => "test_b",
                 "password" => "test",
                 "address" => "haha",
                 "phone" => "111"]);

            var_dump($r); echo "<br />";

            $r = $this->dispatch("/session/")->post(
                ["email" => "a@b.com",
                 "password" => "test"]);

            var_dump($r); echo "<br />";
            $sk = $r["session_key"];

            $r = $this->dispatch("/session/")->post(
                ["email" => "b@b.com",
                 "password" => "test"]);

            var_dump($r); echo "<br />";
            $sk2 = $r["session_key"];

            $r = $this->dispatch("/group/g/")->post(
                ["email" => "a@b.com",
                 "session_key" => $sk]);
            var_dump($r); echo "<br />";

            $r = $this->dispatch("/group/g/")->post(
                ["email" => "b@b.com",
                 "session_key" => $sk2]);
            var_dump($r); echo "<br />";

            $r = $this->dispatch("/item/")->post(
                array("email" => "a@b.com",
                      "session_key" => $sk,
                      "iname" => "test_item",
                      "idesc" => "this is a item for test",
                      "price" => "9.08"));

            var_dump($r); echo "<br />";
            $item_id1 = $r["item_id"];
            
            $r = $this->dispatch("/item/")->post(
                array("email" => "b@b.com",
                      "session_key" => $sk2,
                      "iname" => "other test_item",
                      "idesc" => "this is a item for test",
                      "price" => "9.08"));

            var_dump($r); echo "<br />";
            $item_id2 = $r["item_id"];

            $r = $this->dispatch("/item/$item_id1/photo/")->post(
                array("email" => "a@b.com",
                      "session_key" => $sk1,
                      "image_url" => "http://ecx.images-amazon.com/images/I/71sw4gQqheL._SL1500_.jpg"));

            var_dump($r); echo "<br />";

            $r = $this->dispatch("/transaction/")->post(
                array("email" => "b@b.com",
                      "session_key" => $sk2,
                      "item_id" => $item_id1));

            var_dump($r); echo "<br />";
            $trans_id = $r["trans_id"];
            
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
        case "item":
            return ItemManager::instance()->dispatch($remain);
        case "transaction":
            return TransactionManager::instance()->dispatch($remain);
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
