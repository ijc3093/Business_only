<?php 
    
    class Config2 {
        private $host = 'localhost';
        private $user = 'root';
        private $pass = '';
        private $dbname = 'gospel';

        //Will be the PDO object
        private $dbh;
        private $stmt;
        private $error;

        public function __construct(){
            //Set DSN
            $dsn = 'mysql:host='.$this->host.';dbname='.$this->dbname;
            $options = array(
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            );

            //Create PDO instance
            try{
                $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
            }catch(PDOException $e){
                $this->error = $e->getMessage();
                echo $this->error;
            }
        }

        //Prepare statement with query
        public function query($sql){
            $this->stmt = $this->dbh->prepare($sql);
        }

        //Bind values, to prepared statement using named parameters
        public function bind($param, $value, $type = null){
            if(is_null($type)){
                switch(true){
                    case is_int($value):
                        $type = PDO::PARAM_INT;
                        break;
                    case is_bool($value):
                        $type = PDO::PARAM_BOOL;
                        break;
                    case is_null($value):
                        $type = PDO::PARAM_NULL;
                        break;
                    default:
                        $type = PDO::PARAM_STR;
                }
            }
            $this->stmt->bindValue($param, $value, $type);
        }

        //Execute the prepared statement
        public function execute(){
            return $this->stmt->execute();
        }

        //Return multiple records
        public function resultSet(){
            $this->execute();
            return $this->stmt->fetchAll(PDO::FETCH_OBJ);
        }

        //Return a single record
        public function single(){
            $this->execute();
            return $this->stmt->fetch(PDO::FETCH_OBJ);
        }

        //Get row count
        public function rowCount(){
            return $this->stmt->rowCount();
        }
    }
?>

<?php
    
    class Controller{

        private $dbh;
        private $server = "localhost";
        private $username = "root";
        private $password = "";
        private $dbname = "gospel";

        function __construct(){
            
            $this->dbh = null;
            
            try{
                $this->dbh = new PDO("mysql:host=" . $this->server . ";dbname=" . $this->dbname, $this->username, $this->password);
                $this->dbh->exec("set names utf8");
                $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            }catch(PDOException $exception){
                echo "Database could not be connected: " . $exception->getMessage();
            }
            return $this->dbh;

        }

        //////////////////////////////////////////////Register////////////////////////////////////////
        //Register insert (Create A New Account)
        public function register($data){
            $db = new Config2;
            $db->query('INSERT INTO admin (username, email, password, gender, mobile, designation, role, image, status)
            VALUES (:username,:email,:password,:gender,:mobile,:designation,:role,:image,:status)');

            //Bind values
            $db->bind(':username', $data['username']);
            $db->bind(':email', $data['email']);
            $db->bind(':password', $data['password']);
            $db->bind(':gender', $data['gender']);
            $db->bind(':mobile', $data['mobile']);
            $db->bind(':designation', $data['designation']);
            $db->bind(':role', $data['role']);
            $db->bind(':image', $data['image']);
            $db->bind(':status', $data['status']);

            //Execute
            if($db->execute()){
                return true;
            }else{
                return false;
            }
        }

        //////////////////////////////////////////////Login///////////////////////////////////////////////////////////////////////
        // ✅ FIX: make login compatible with password_hash() used during register
        function login($username, $password){
            try{
                $stmt = $this->dbh->prepare("SELECT idadmin, role, password FROM admin WHERE username = ? LIMIT 1");
                $stmt->bindParam(1, $username, PDO::PARAM_STR);
                $stmt->execute();
                $reply = $stmt->fetch(PDO::FETCH_ASSOC);

                if($reply == null){
                    $this->dbh = null;
                    return -1;
                }

                if(!password_verify($password, $reply['password'])){
                    return -1;
                }

                // User's role (userRole)
                $role = $reply['role'];
                // user id add here
                $id = $reply['idadmin'];

                $_SESSION['userRole'] = $role;
                $_SESSION['id'] = $id;

                return 1;

            }catch(PDOException $e){
                echo $e->getMessage();
                return -1;
            }
        }

        // ✅ ADD: fixes "Undefined method findUserByEmail"
        public function findUserByEmail($email){
            $db = new Config2;
            $db->query('SELECT * FROM admin WHERE email = :email');
            $db->bind(':email', $email);

            $row = $db->single();

            if($db->rowCount() > 0){
                return $row;
            }else{
                return false;
            }
        }

        // ✅ ADD: match your register.php table (admin) for duplicate checks
        public function findAdminByEmailOrUsername($email, $username){
            $db = new Config2;
            $db->query('SELECT * FROM admin WHERE username = :username OR email = :email');
            $db->bind(':username', $username);
            $db->bind(':email', $email);

            $row = $db->single();

            if($db->rowCount() > 0){
                return $row;
            }else{
                return false;
            }
        }

        // Your original method (left as-is)
        public function findUserByEmailOrUsername($email, $username){
            $db = new Config2;
            $db->query('SELECT * FROM user WHERE username = :username OR email = :email');
            $db->bind(':username', $username);
            $db->bind(':email', $email);

            $row = $db->single();

            if($db->rowCount() > 0){
                return $row;
            }else{
                return false;
            }
        }
    }
?>
