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

    if(isset($_GET['edit'])){
        $editid = $_GET['edit'];
    }

    if(isset($_POST['submit'])){

        // Upload
        $file = $_FILES['image']['name'];
        $file_loc = $_FILES['image']['tmp_name'];
        $folder = "../images/"; // ✅ matches your <img src="../images/...">

        $new_file_name = strtolower($file);
        $final_file = str_replace(' ', '-', $new_file_name);

        // Form values
        $name = $_POST['name'];
        $email = $_POST['email'];
        $gender = $_POST['gender'];             // ✅ FIX: gender variable
        $mobileno = $_POST['mobileno'];         // ✅ FIX: remove wrong overwrite
        $designation = $_POST['designation'];
        $idedit = $_POST['idedit'];

        // current image from hidden input
        $image = $_POST['image'];

        // ✅ Only upload if file exists
        if(!empty($file) && !empty($file_loc)){
            if(!is_dir($folder)){
                mkdir($folder, 0755, true);
            }
            if(move_uploaded_file($file_loc, $folder.$final_file)){
                $image = $final_file;
            }
        }

        // ✅ FIX: column name should match your DB usage
        // You display: $result->image and hidden input uses $result->image
        // So update uses "image" (not "Image")
        $sql="UPDATE users SET name=(:name), email=(:email), gender=(:gender), mobile=(:mobileno), designation=(:designation), image=(:image) WHERE id=(:idedit)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':name', $name, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':gender', $gender, PDO::PARAM_STR);
        $query->bindParam(':mobileno', $mobileno, PDO::PARAM_STR);
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

	<title>Edit User</title>

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
        $sql = "SELECT * FROM users where id =:editid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':editid', $editid, PDO::PARAM_INT);
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
						<h3 class="page-title">Edit User : <?php echo htmlentities($result->name); ?></h3>
						<div class="row">
							<div class="col-md-12">
								<div class="panel panel-default">
									<div class="panel-heading">Edit Info</div>

                                    <?php if($error){?><div class="errorWrap"><strong>ERROR</strong>:<?php echo htmlentities($error); ?> </div><?php }
                                        else if($msg){?><div class="succWrap"><strong>SUCCESS</strong>:<?php echo htmlentities($msg); ?> </div><?php }?>

                                    <div class="panel-body">
                                        <form method="post" class="form-horizontal" enctype="multipart/form-data" name="imgform">
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label">Name<span style="color:red">*</span></label>
                                                <div class="col-sm-4">
                                                <input type="text" name="name" class="form-control" required value="<?php echo htmlentities($result->name);?>">
                                                </div>
                                                <label class="col-sm-2 control-label">Email<span style="color:red">*</span></label>
                                                <div class="col-sm-4">
                                                <input type="email" name="email" class="form-control" required value="<?php echo htmlentities($result->email);?>">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-sm-2 control-label">Gender<span style="color:red">*</span></label>
                                                <div class="col-sm-4">
                                                    <select name="gender" class="form-control" required>
                                                        <option value="">Select</option>
                                                        <option value="Male" <?php if($result->gender=="Male"){echo "selected";}?>>Male</option>
                                                        <option value="Female" <?php if($result->gender=="Female"){echo "selected";}?>>Female</option>
                                                    </select>
                                                </div>

                                                <label class="col-sm-2 control-label">Designation<span style="color:red">*</span></label>
                                                <div class="col-sm-4">
                                                    <input type="text" name="designation" class="form-control" required value="<?php echo htmlentities($result->designation);?>">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-sm-2 control-label">Image<span style="color:red">*</span></label>
                                                <div class="col-sm-4">
                                                    <input type="file" name="image" class="form-control">
                                                </div>
                                                <label class="col-sm-2 control-label">Mobile No.<span style="color:red">*</span></label>
                                                <div class="col-sm-4">
                                                    <input type="number" name="mobileno" class="form-control" required value="<?php echo htmlentities($result->mobile);?>">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="col-sm-8 col-sm-offset-2">
                                                    <img src="../images/<?php echo htmlentities($result->image);?>" width="150px"/>
                                                    <input type="hidden" name="image" value="<?php echo htmlentities($result->image);?>" >
                                                    <input type="hidden" name="idedit" value="<?php echo htmlentities($result->id);?>" >
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="col-sm-8 col-sm-offset-2">
                                                    <button class="btn btn-primary" name="submit" type="submit">Save Changes</button>
                                                </div>
                                            </div>
                                        </form>
									</div>
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
