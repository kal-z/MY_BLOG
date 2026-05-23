<?php

class Database {

    private $host = "localhost";
    private $db_name = "blogdb2";
    private $username = "root";
    private $password = "";

    public $conn;

    // CONNECT DATABASE
    public function getConnection() {

        $this->conn = null;

        try {

            $this->conn = new PDO(

                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",

                $this->username,

                $this->password,

                array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                )
            );

            // ERROR MODE
            $this->conn->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

        } catch(PDOException $exception) {

            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}

?>