<?php
require_once __DIR__ . "/../Models/User.php";

class UserController
{
    private $userModel;
    public function __construct($db)
    {
        $this->userModel = new User($db);
    }
    //Controller method calls the model
    public function getUser($username)
    {
        return $this->userModel->selectUser($username);
    }
}
?>