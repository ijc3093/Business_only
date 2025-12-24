<?php
session_start();
error_reporting(0);

include('./controller.php');

// ✅ Use controller.php for DB connection
$controller = new Controller();
$dbh = $controller->__construct();

if(strlen($_SESSION['alogin'])==0){
    header('location:index.php');
}else{

    // Delete user (kept as-is from your logic)
    if(isset($_GET['del'])){
        $id = $_GET['del'];
        $sql = "DELETE FROM users WHERE id=:id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $msg = "Data Deleted successfully";
    }

    // Unconfirm user
    if(isset($_REQUEST['unconfirm'])){
        $aeid = intval($_GET['unconfirm']);
        $memstatus = 1;

        // ✅ FIX: SET (not SETS)
        $sql = "UPDATE users SET status=:status WHERE id=:aeid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':status', $memstatus, PDO::PARAM_STR);
        $query->bindParam(':aeid', $aeid, PDO::PARAM_STR);
        $query->execute();
        $msg = "Changes successfully";
    }

    // Confirm user
    if(isset($_REQUEST['confirm'])){
        $aeid = intval($_GET['confirm']);
        $memstatus = 0;

        // ✅ FIX: SET (not SETS)
        $sql = "UPDATE users SET status=:status WHERE id=:aeid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':status', $memstatus, PDO::PARAM_STR);
        $query->bindParam(':aeid', $aeid, PDO::PARAM_STR);
        $query->execute();
        $msg = "Changes successfully";
    }

    // Delete a message
    if(isset($_GET['del']) && isset($_GET['name'])){
        $id=$_GET['del'];
        $name=$_GET['name'];

        $sql="delete from feedback WHERE id=:id";
        $query=$dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_STR);
        $query->execute();

        $sql2="insert into deletedfeedback (email) values (:name)";
        $query2=$dbh->prepare($sql2);
        $query2->bindParam(':name', $name, PDO::PARAM_STR);
        $query2->execute();

        $msg="Data Deleted Successfully";
    }
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Feedback</title>

	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
	<link rel="stylesheet" href="css/bootstrap-select.css">
	<link rel="stylesheet" href="css/style.css">

	<style>
        .errorWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #dd3d36;
            color:#fff;
        }
        .succWrap{
            padding: 10px;
            margin: 0 0 20px 0;
            background: #5cb85c;
            color:#fff;
        }
	</style>
</head>

<body>
<?php include('includes/header.php');?>
<div class="ts-main-content">
<?php include('includes/leftbar.php');?>

<div class="content-wrapper">
<div class="container-fluid">

<div class="row">
<div class="col-md-12">
<h2 class="page-title">Manage Feedback</h2>

<div class="panel panel-default">
<div class="panel-heading">List Feedback</div>
<div class="panel-body">

<a href="contact.php">New Compose</a>

<?php if($msg){ ?>
<div class="succWrap"><?php echo htmlentities($msg); ?></div>
<?php } ?>

<table id="zctb" class="display table table-striped table-bordered table-hover">
<thead>
<tr>
    <th>#</th>
    <th>From: User Email</th>
    <th>Title</th>
    <th>Message</th>
    <th>Attachment</th>
    <th>Date Time</th>
    <th>Action</th>
</tr>
</thead>

<tbody>
<?php
$receiver = 'Admin';
$sql = "SELECT * FROM feedback WHERE receiver = :receiver";
$query = $dbh->prepare($sql);
$query->bindParam(':receiver', $receiver, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);
$cnt = 1;

if($query->rowCount() > 0){
    foreach($results as $result){
?>
<tr>
    <td><?php echo htmlentities($cnt); ?></td>
    <td><?php echo htmlentities($result->sender); ?></td>
    <td><?php echo htmlentities($result->title); ?></td>
    <td><?php echo htmlentities($result->feedbackdata); ?></td>
    <td>
        <?php if(!empty($result->attachment)){ ?>
            <a href="../attachment/<?php echo htmlentities($result->attachment); ?>">
                <?php echo htmlentities($result->attachment); ?>
            </a>
        <?php } else { echo "N/A"; } ?>
    </td>
    <td><?php echo htmlentities($result->time); ?></td>
    <td>
        <a href="sendreply.php?reply=<?php echo urlencode($result->sender); ?>"><i class="fa fa-mail-reply"></i></a>&nbsp;&nbsp;
        <a href="feedback.php?del=<?php echo $result->id;?>&name=<?php echo htmlentities($result->email);?>" onclick="return confirm('Do you want to Delete');"><i class="fa fa-trash" style="color:red"></i></a>
    </td>
    
</tr>
<?php
    $cnt++;
    }
}
?>
</tbody>
</table>

</div>
</div>
</div>
</div>

</div>
</div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.bootstrap.min.js"></script>
<script>
$(document).ready(function(){
    $('#zctb').DataTable();
    setTimeout(function(){
        $('.succWrap').slideUp('slow');
    },3000);
});
</script>

</body>
</html>
<?php } ?>
