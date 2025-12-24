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
    if(isset($_POST['submit'])){

        $file = $_FILES['image']['name'];
        $file_loc = $_FILES['image']['tmp_name'];

        // ✅ This file is in admin folder and you display: <img src="images/...">
        // So upload must go into admin/images/
        $folder = "images/";

        $new_file_name = strtolower($file);
        $final_file = str_replace(' ', '-', $new_file_name);

        $name = $_POST['name'];
        $email = $_POST['email'];
        $mobile = $_POST['mobile'];
        $designation = $_POST['designation'];
        $idedit = $_POST['editid'];

        // ✅ FIX: keep current image from hidden input (otherwise it becomes empty)
        $image = $_POST['current_image'];

        // ✅ Upload only if file selected
        if(!empty($file) && !empty($file_loc)){
            if(!is_dir($folder)){
                mkdir($folder, 0755, true);
            }
            if(move_uploaded_file($file_loc, $folder.$final_file)){
                $image = $final_file;
            }
        }

        // ✅ FIX: use image (lowercase) to match your fetch ($result->image)
        $sql="UPDATE admin SET username=(:name), email=(:email), mobile=(:mobile), designation=(:designation), image=(:image) WHERE id=(:idedit)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':name', $name, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':mobile', $mobile, PDO::PARAM_STR);
        $query->bindParam(':designation', $designation, PDO::PARAM_STR);
        $query->bindParam(':image', $image, PDO::PARAM_STR);
        $query->bindParam(':idedit', $idedit, PDO::PARAM_STR);
        $query->execute();

        $msg="Information Updated Successfully";
    }
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
	
	<title>Edit Profile</title>

	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
	<link rel="stylesheet" href="css/bootstrap-social.css">
	<link rel="stylesheet" href="css/bootstrap-select.css">
	<link rel="stylesheet" href="css/fileinput.min.css">
	<link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
	<link rel="stylesheet" href="css/style.css">

	<script type= "text/javascript" src="../vendor/countries.js"></script>
	<style>
	    .errorWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #dd3d36;
            color:#fff;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
        .succWrap{
            padding: 10px;
            margin: 0 0 20px 0;
            background: #5cb85c;
            color:#fff;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
	</style>
</head>

<body>
<?php
    $email = $_SESSION['alogin'];

    // ✅ Keep your query style
    $sql = "SELECT * FROM admin WHERE username = (:email)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);
?>

<?php include('includes/header.php');?>
<div class="ts-main-content">
<?php include('includes/leftbar.php');?>
<div class="content-wrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="col-md-12">
						<div class="panel panel-default">
							<div class="panel-heading"><?php echo htmlentities($_SESSION['alogin']);?></div>

							<?php if($error){?><div class="errorWrap"><strong>ERROR</strong>:<?php echo htmlentities($error);?></div><?php }
							elseif($msg){?><div class="succWrap"><strong>SUCCESS</strong>:<?php echo htmlentities($msg);?></div><?php }?>

							<div class="panel-body">
								<form method="post" class="form-horizontal" enctype="multipart/form-data">

									<div class="form-group">
										<div class="col-sm-4"></div>
										<div class="col-sm-4 text-center">
											<img src="images/<?php echo htmlentities($result->image);?>" style="width:200px; border-radius:50%; margin:10px;">
											<input type="file" name="image" class="form-control">
										</div>
										<div class="col-sm-4"></div>
									</div>

									<div class="form-group">
										<label class="col-sm-2 control-label">Name<span style="color:red">*</span></label>
										<div class="col-sm-4">
											<input type="text" name="name" class="form-control" required value="<?php echo htmlentities($result->username);?>">
										</div>

										<label class="col-sm-2 control-label">Email<span style="color:red">*</span></label>
										<div class="col-sm-4">
											<input type="email" name="email" class="form-control" required value="<?php echo htmlentities($result->email);?>">
										</div>
									</div>

									<div class="form-group">
										<label class="col-sm-2 control-label">Mobile<span style="color:red">*</span></label>
										<div class="col-sm-4">
											<input type="text" name="mobile" class="form-control" required value="<?php echo htmlentities($result->mobile);?>">
										</div>

										<label class="col-sm-2 control-label">Designation<span style="color:red">*</span></label>
										<div class="col-sm-4">
											<input type="text" name="designation" class="form-control" required value="<?php echo htmlentities($result->designation);?>">
										</div>
									</div>

									<input type="hidden" name="editid" value="<?php echo htmlentities($result->id);?>">
									<input type="hidden" name="current_image" value="<?php echo htmlentities($result->image);?>">

									<div class="form-group">
										<div class="col-sm-8 col-sm-offset-2">
											<button class="btn btn-primary" name="submit" type="submit">Save Changes</button>
										</div>
									</div>

								</form>
							</div><!-- panel-body -->
						</div><!-- panel -->
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
<script type="text/javascript">
$(document).ready(function () {
	setTimeout(function() {
		$('.succWrap').slideUp("slow");
	}, 3000);
});
</script>
</body>
</html>

<?php } ?>
