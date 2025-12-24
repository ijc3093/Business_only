<?php
session_start();
error_reporting(0);

include('./controller.php');

// ✅ Get PDO connection from controller.php
$controller = new Controller();
$dbh = $controller->__construct();

if(strlen($_SESSION['alogin'])==0){
    header('location:index.php');
}else{
?>
<!doctype html>
<html lang="en" class="no-js">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="theme-color" content="#3e454c">

    <title>Admin Dashboard</title>

    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-social.css">
    <link rel="stylesheet" href="css/bootstrap-select.css">
    <link rel="stylesheet" href="css/fileinput.min.css">
    <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<?php include('includes/header.php');?>
<div class="ts-main-content">

<?php include('includes/leftbar.php');?>
<div class="content-wrapper">
    <div class="container-fluid">

        <div class="row">
            <div class="col-md-12">

                <h2 class="page-title">Dashboard</h2>

                <div class="row">
                    <div class="col-md-12">
                        <div class="row">

                        <?php
                            $sql = "SELECT id FROM users";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $results=$query->fetchAll(PDO::FETCH_OBJ);
                            $bg=$query->rowCount();

                            $userRole = $_SESSION['userRole'];
                            if( empty($userRole) ){
                                echo "<script>alert('User Role is empty');</script>";
                            }

                            if($userRole == 2 || $userRole == 3 || $userRole == 4){
                                //
                            }else{
                                echo '<div class="col-md-3">
                                        <div class="panel panel-default">
                                            <div class="panel-body bk-primary text-light">
                                                <div class="stat-panel text-center">';
                                echo '<div class="stat-panel-number h1 ">'; echo htmlentities($bg);

                                echo '</div>
                                                <div class="stat-panel-title text-uppercase">Total Users</div>
                                            </div>
                                        </div>
                                        <a href="userlist.php" class="block-anchor panel-footer">Full Detail <i class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>';

                                $receiver = 'Admin';
                                $sql1 = "SELECT id FROM feedback WHERE receiver = (:receiver)";
                                $query1 = $dbh->prepare($sql1);
                                $query1->bindParam(':receiver', $receiver, PDO::PARAM_STR);
                                $query1->execute();
                                $results1=$query1->fetchAll(PDO::FETCH_OBJ);
                                $regbd=$query1->rowCount();

                                echo '<div class="col-md-3">
                                        <div class="panel panel-default">
                                            <div class="panel-body bk-success text-light">
                                                <div class="stat-panel text-center">
                                                    <!-- This is issue codes -->';
                                echo '<div class="stat-panel-number h1 ">';echo htmlentities($regbd);
                                echo '</div>
                                                    <div class="stat-panel-title text-uppercase">Feedback</div>
                                                </div>
                                            </div>
                                            <a href="feedback.php" class="block-anchor panel-footer text-center">Full Detail &nbsp; <i class="fa fa-arrow-right"></i></a>
                                        </div>
                                    </div>';

                                $receiver = 'Admin';
                                $sql12 = "SELECT id FROM notification WHERE notireceiver = (:receiver)";
                                $query12 = $dbh->prepare($sql12);
                                $query12->bindParam(':receiver', $receiver, PDO::PARAM_STR);
                                $query12->execute();
                                $results12=$query12->fetchAll(PDO::FETCH_OBJ);
                                $regbd2=$query12->rowCount();

                                echo '<div class="col-md-3">
                                        <div class="panel panel-default">
                                            <div class="panel-body bk-danger text-light">
                                                <div class="stat-panel text-center"><div class="stat-panel-number h1 ">';
                                echo htmlentities($regbd2);
                                echo '</div>
                                                    <div class="stat-panel-title text-uppercase">Notifications</div>
                                                </div>
                                            </div>
                                            <a href="notification.php" class="block-anchor panel-footer text-center">Full Detail &nbsp; <i class="fa fa-arrow-right"></i></a>
                                        </div>
                                    </div>';

                                $sql6= "SELECT id FROM deleteduser";
                                $query6 = $dbh->prepare($sql6);
                                $query6->execute();
                                $results6=$query6->fetchAll(PDO::FETCH_OBJ);
                                $query=$query6->rowCount();

                                echo '<div class="col-md-3">
                                        <div class="panel panel-default">
                                            <div class="panel-body bk-info text-light">
                                                <div class="stat-panel text-center"><div class="stat-panel-number h1 ">';
                                echo htmlentities($query);
                                echo '</div>
                                                    <div class="stat-panel-title text-uppercase">Deleted Users</div>
                                                </div>
                                            </div>
                                            <a href="deleteduser.php" class="block-anchor panel-footer text-center">Full Detail &nbsp; <i class="fa fa-arrow-right"></i></a>
                                        </div>
                                    </div>';
                            }
                        ?>

                        <?php
                            if($userRole == 1 ){
                                //
                            }else{

                                $receiver = 'Admin';
                                $sql1 = "SELECT id FROM feedback WHERE receiver = (:receiver)";
                                $query1 = $dbh->prepare($sql1);
                                $query1->bindParam(':receiver', $receiver, PDO::PARAM_STR);
                                $query1->execute();
                                $results1=$query1->fetchAll(PDO::FETCH_OBJ);
                                $regbd=$query1->rowCount();

                                echo '<div class="col-md-3">
                                        <div class="panel panel-default">
                                            <div class="panel-body bk-success text-light">
                                                <div class="stat-panel text-center">
                                                    <!-- This is issue codes -->';
                                echo '<div class="stat-panel-number h1 ">';echo htmlentities($regbd);
                                echo '</div>
                                                    <div class="stat-panel-title text-uppercase">Feedback</div>
                                                </div>
                                            </div>
                                            <a href="feedback.php" class="block-anchor panel-footer text-center">Full Detail &nbsp; <i class="fa fa-arrow-right"></i></a>
                                        </div>
                                    </div>';

                                $receiver = 'Admin';
                                $sql12 = "SELECT id FROM notification WHERE notireceiver = (:receiver)";
                                $query12 = $dbh->prepare($sql12);
                                $query12->bindParam(':receiver', $receiver, PDO::PARAM_STR);
                                $query12->execute();
                                $results12=$query12->fetchAll(PDO::FETCH_OBJ);
                                $regbd2=$query12->rowCount();

                                echo '<div class="col-md-3">
                                        <div class="panel panel-default">
                                            <div class="panel-body bk-danger text-light">
                                                <div class="stat-panel text-center"><div class="stat-panel-number h1 ">';
                                echo htmlentities($regbd2);
                                echo '</div>
                                                    <div class="stat-panel-title text-uppercase">Notifications</div>
                                                </div>
                                            </div>
                                            <a href="notification.php" class="block-anchor panel-footer text-center">Full Detail &nbsp; <i class="fa fa-arrow-right"></i></a>
                                        </div>
                                    </div>';
                            }
                        ?>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap-select.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.bootstrap.min.js"></script>
<script src="js/Chart.min.js"></script>
<script src="js/fileinput.js"></script>
<script src="js/chartData.js"></script>
<script src="js/main.js"></script>
<script>
window.onload = function(){
    var ctx = document.getElementById("dashReport").getContext("2d");
    window.myLine = new Chart(ctx).Line(swirtData, {
        responsive: true,
        scaleShowVerticalLines: false,
        scaleBeginAtZero : true,
        multiTootipTemplate: "<%if (label){%><%=label%>: <%}%><%= value %>",
    });

    var doctx = document.getElementById("chart-area3").getContext("2d");
    window.myDoughnut = new Chart(doctx).Pie(doughnutData, {responsive : true});

    var doctx = document.getElementById("chart-area4").getContext("2d");
    window.myDoughnut = new Chart(doctx).Dougnut(doughnutData, {responsive : true});
}
</script>
</body>
</html>
<?php } ?>
