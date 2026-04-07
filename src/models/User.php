<?php
    class User {
        private $conn;
        private $table = 'users';

        public $id;
        public $email;
        public $password;
        public $name;
        public $profile_picture;

        public function __construct($conn){
            $this->conn = $conn;
        }

        public function create(){
            $query = "INSERT INTO " . $this->table . "(email, password) VALUES (:email, :password)";
            $stmt = $this->conn->prepare($query);

            $this->password = password_hash($this->password, PASSWORD_BCRYPT);

            return $stmt->execute([
                ':email' => $this->email,
                ':password' => $this->password
            ]);
        }
    }

?>