<?php
    class User {
        private $conn;
        private $table = 'users';

        private $id;
        private $email;
        private $password;
        private $username;
        private $profile_picture;

        public function __construct($db){
            $this->conn = $db;
        }

        public function findById($id){
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();

            if($data){
                $this->id = $data['id'];
                $this->email = $data['email'];
                $this->password = $data['password'];
                $this->username = $data['name'];
                $this->profile_picture = $data['profile_picture'];
                return $this;
            }
            return null;
        }

        public function getUsername() {return $this->username;}
        public function getEmail() {return $this->email;}

        public function setUsername($username) {$this->username = $username;}
        public function setEmail($email) {$this->email = $email;}
    }

?>