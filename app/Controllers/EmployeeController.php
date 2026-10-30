<?php
require_once __DIR__ . "/../Models/Employee.php";

class EmployeeController
{
    private $employeeModel;
    public function __construct($db)
    {
        $this->employeeModel = new Employee($db);
    }
    // Create a method in controller for store employee record
    public function store ($employee_code,$first_name,$last_name,$gender,$phone,$email,$position,$salary,$hire_date,$status)
    {
        return $this->employeeModel->createEmployee($employee_code,$first_name,$last_name,$gender,$phone,$email,$position,$salary,$hire_date,$status);
    }
    public function show()
    {
        return $this->employeeModel->getAllEmployees();
    }
    public function showById($id)
    {
        return $this->employeeModel->getEmployeeById($id);
    }
    public function update($id,$employee_code,$first_name,$last_name,$gender,$phone,$email,$position,$salary,$hire_date,$status)
    {
        return $this->employeeModel->editEmployee($id,$employee_code,$first_name,$last_name,$gender,$phone,$email,$position,$salary,$hire_date,$status);
    }
    // create a method for delete a employee Record by ID
        public function destroy($id)
    {
        return $this->employeeModel->deleteEmployee($id);
    }
    // Create a method for show a employee records by keywrod search
    public function showByKeyword($keyword)
    {
        return $this->employeeModel->getEmployeeByKeyword($keyword);
    }
    // Create a method for show employee report
    public function showReport($from, $to)
    {
        return $this->employeeModel->getEmployeeReport($from, $to);
    }
}

?>