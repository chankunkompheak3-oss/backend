<?php
require_once __DIR__ . "/../../config/database.php";

class User
{
    private $db;
    // constructor: Store PDO connection
    public function __construct($con)
    {
        $this->db = $con;
    }
    // Method to selete user
    public function selectUser($username)
    {   
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :u");
        $stmt->bindParam(':u',$username);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: false;
    }
}
?>