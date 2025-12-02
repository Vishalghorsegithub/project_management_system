
<?php
class Database {
    private $host     = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "pms";
    public  $conn;

    public function connect() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);

        if ($this->conn->connect_error) {
            die(json_encode([
                "status" => false,
                "message" => "Database Connection Failed"
            ]));
        }

        return $this->conn;
    }
}
?>
