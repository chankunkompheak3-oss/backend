<?php
session_start();
//Check user login
if (!isset($_SESSION['username'])) {
    header("Location: ../../public/index.php");
    exit;
}

require_once __DIR__ . "/../Controllers/StudentController.php";

// Initialize student controller
$student = new StudentController($con);

if (isset($_POST['date_from'])) {
    $from = $_POST["date_from"];
    $to = $_POST["date_to"];
    $result = $student->showReport($from, $to);
} else {
    //get date last 1 month
    $from = date("Y-m-d", strtotime("first day of last month"));
    $to = date("Y-m-d");
    $result = $student->showReport($from, $to);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        tr {
            vertical-align: middle;
        }
    </style>
</head>

<body>

    <div class="container">
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <a class="navbar-brand" href="dashboard.php"><i class="fa-solid fa-graduation-cap"></i> SMS</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Reports</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Setting</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Profile
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Manage Profile</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
    <br>

    <!-- Container for report filter -->
    <div class="container">
        <form action="" method="post">
            <div class="row">
                <div class="col">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="inputGroup-sizing-default">From</span>
                        <input type="date" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default" name="date_from">
                    </div>
                </div>
                <div class="col">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="inputGroup-sizing-default">To</span>
                        <input type="date" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default" name="date_to">
                    </div>
                </div>
                <div class="col">
                    <button class="btn btn-info" type="submit">Show</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Container for display students list -->
    <div>&nbsp;</div>
    <div class="container">
        <div style="text-align: right; padding-bottom: 5px;">
            <a href="" class="btn btn-primary" onclick="printTable()"><i class="fa-solid fa-print"></i> Print</a>
            <a href="" class="btn btn-danger"><i class="fa-regular fa-file-pdf"></i> PDF</a>
            <a href="" class="btn btn-success"><i class="fa-solid fa-file-excel"></i> Excel</a>
        </div>
        <div id="reportTable">
            <h1 style="text-align:center;">STUDENT REPORT</h1>
            <hr>

            <h4>Report From: <?php echo date("d-F-Y", strtotime($from)); ?> - To: <?php echo date("d-F-Y", strtotime($to)); ?></h4>

            <table class="table table-bordered">
                <tr>
                    <th>#</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Date of Birth</th>
                </tr>
                <?php
                $i = 1;
                foreach ($result as $rows) {
                ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td><img src="<?php echo $rows["photo_url"]; ?>" alt="" width="150px"></td>
                        <td><?php echo $rows["last_name"] . " " . $rows["first_name"]; ?></td>
                        <td><?php echo ucfirst($rows["gender"]); ?></td>
                        <td><?php echo $rows["date_of_birth"]; ?></td>
                    </tr>
                <?php
                    $i++;
                }
                ?>
            </table>
        </div>
    </div>
    <script>
        function printTable() {
            var divToPrint = document.getElementById("reportTable");
            var newWin = window.open("");
            newWin.document.write("<html><head><title>Student Report</title><style>");
            newWin.document.write("table { border-collapse: collapse; width: 100%; font-family: Arial; }");
            newWin.document.write("th, td { border: 1px solid silver; padding: 8px; text-align: center; }");
            newWin.document.write("th { background-color: #f2f2f2; }");
            newWin.document.write("tr:nth-child(even) { background-color: #f9f9f9; }");
            newWin.document.write("</style></head><body>");
            newWin.document.write(divToPrint.outerHTML);
            newWin.document.write("</body></h1>");
            newWin.document.close();
            newWin.print();
        }
    </script>

</body>

</html>