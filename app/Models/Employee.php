<?php
require_once __DIR__ . "/../../config/database.php";

class Employee
{
    private $db;
    public function __construct($con)
    {
        $this->db = $con;
    }

    // Create a method to insert employee into database
    public function createEmployee($employee_code,$first_name,$last_name,$gender,$phone,$email,$position,$salary,$hire_date,$status)
    {
        $stmt = $this->db->prepare("INSERT INTO employees (
                                        employee_code,first_name,last_name,gender,phone,
                                        email,position,salary,hire_date,status
                                        ) VALUES(
                                        :ec, :fn, :ln, :gd, :pn, :em, :ps, :sl, :hd, :st)" );
        $stmt->bindParam(':ec',$employee_code);
        $stmt->bindParam(':fn',$first_name);
        $stmt->bindParam(':ln',$last_name);
        $stmt->bindParam(':gd',$gender);
        $stmt->bindParam(':pn',$phone);
        $stmt->bindParam(':em',$email);
        $stmt->bindParam(':ps',$position);
        $stmt->bindParam(':sl',$salary);
        $stmt->bindParam(':hd',$hire_date);
        $stmt->bindParam(':st',$status);
        $stmt->execute();
    }
    // get employee from database
    public function getAllEmplyees()
    {
        $stmt = $this->db->prepare("SELECT * FROM employees");
        $stmt->execute();
        $stmt->$stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }
    // get employee by ID
    public function getEmployeeById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM employees WHERE employee_id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row;
    }
    public function editEmployee($id,$employee_code,$first_name,$last_name,$gender,$phone,$email,$position,$salary,$hire_date,$status)
    {
        $stmt = $this->db->prepare("UPDATE employees SET 
                                    employee_code =:ec, first_name =:fn, last_name=:ln, gender=:gd,
                                    phone=:pn,email=:em,position=:ps,salary=:sl,hire_date=:hd,status=:st WHERE employee_id = :id" );
        $stmt->bindParam(':ec',$employee_code);
        $stmt->bindParam(':fn',$first_name);
        $stmt->bindParam(':ln',$last_name);
        $stmt->bindParam(':gd',$gender);
        $stmt->bindParam(':pn',$phone);
        $stmt->bindParam(':em',$email);
        $stmt->bindParam(':ps',$position);
        $stmt->bindParam(':sl',$salary);
        $stmt->bindParam(':hd',$hire_date);
        $stmt->bindParam(':st',$status);
        $stmt->bindParam(':id',$id);
        $stmt->execute();
    }
    //Create a method to delete a record of employees from database
    public function deleteEmployee($id)
    {
        $stmt = $this->db->prepare("DELETE FROM employees WHERE employee_id = :id");
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            return $stmt->rowCount() > 0; // return true if a row was deleted 
        }
        return false;
    }
    //Create a method to search employee from database by name
    public function getEmployeeByKeyword($keyword)
    {
        $keyword = "%" . $keyword . "%";
        $stmt = $this->db->prepare("SELECT * FROM employees WHERE first_name LIKE :kw OR last_name LIKE :kw");
        $stmt->bindParam(':kw', $keyword, PDO::PARAM_STR);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }
    //Create a method to filter employee report
    public function getEmployeeReport($from, $to)
    {
        $stmt = $this->db->prepare("SELECT * FROM employees WHERE DATE(created_at) BETWEEN :fr AND :tt");
        $stmt->bindParam(':fr', $from);
        $stmt->bindParam(':tt', $to);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }

}

?>