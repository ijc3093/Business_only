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

    if(isset($_GET['reply'])){
        $replyto = $_GET['reply'];
    } else {
        $replyto = '';
    }

    if(isset($_POST['submit'])){

        // ✅ From form
        $receiver = $_POST['email'];      // readonly field
        $title    = $_POST['title'];
        $message  = $_POST['message'];

        // ✅ Sender/admin
        $sender   = 'Admin';
        $notitype = 'Send Message';

        // ✅ Attachment: your form does NOT have attachment input, so keep it empty safely
        $attachment = '';

        // ✅ If you later add <input type="file" name="attachment">, this will work
        if(isset($_FILES['attachment']) && !empty($_FILES['attachment']['name'])){
            $file = $_FILES['attachment']['name'];
            $file_loc = $_FILES['attachment']['tmp_name'];

            // In admin folder, this becomes admin/attachment/
            $folder = "attachment/";
            if(!is_dir($folder)){
                mkdir($folder, 0755, true);
            }

            $new_file_name = strtolower($file);
            $final_file = str_replace(' ', '-', $new_file_name);

            if(move_uploaded_file($file_loc, $folder.$final_file)){
                $attachment = $final_file;
            }
        }

        // ✅ Insert notification
        $sqlnoti="INSERT INTO notification (notiuser, notireceiver, notitype)
                  VALUES (:notiuser, :notireceiver, :notitype)";
        $querynoti=$dbh->prepare($sqlnoti);
        $querynoti->bindParam(':notiuser', $sender, PDO::PARAM_STR);
        $querynoti->bindParam(':notireceiver', $receiver, PDO::PARAM_STR);
        $querynoti->bindParam(':notitype', $notitype, PDO::PARAM_STR);
        $querynoti->execute();

        // ✅ Insert feedback message (reply)
        $sql="INSERT INTO feedback (sender, receiver, title, feedbackdata, attachment)
              VALUES (:sender, :receiver, :title, :feedbackdata, :attachment)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':sender', $sender, PDO::PARAM_STR);
        $query->bindParam(':receiver', $receiver, PDO::PARAM_STR);
        $query->bindParam(':title', $title, PDO::PARAM_STR);
        $query->bindParam(':feedbackdata', $message, PDO::PARAM_STR);
        $query->bindParam(':attachment', $attachment, PDO::PARAM_STR);
        $query->execute();

        $msg="Feedback Send";
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
	
	<title>Reply Feedback</title>

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
    <?php include('includes/header.php');?>
	<div class="ts-main-content">
	<?php include('includes/leftbar.php');?>
		<div class="content-wrapper">
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-12">
                        <h2 class="page-title">Reply Feedback</h2>

						<div class="panel panel-default">
							<div class="panel-heading">Send Reply</div>

                            <?php if($error){ ?>
                                <div class="errorWrap"><strong>ERROR</strong>:<?php echo htmlentities($error); ?> </div>
                            <?php } else if($msg){ ?>
                                <div class="succWrap"><strong>SUCCESS</strong>:<?php echo htmlentities($msg); ?> </div>
                            <?php } ?>

							<div class="panel-body">
								<form method="post" class="form-horizontal" enctype="multipart/form-data">

									<div class="form-group">
										<label class="col-sm-2 control-label">Email<span style="color:red">*</span></label>
										<div class="col-sm-4">
											<input type="text" name="email" class="form-control" readonly required value="<?php echo htmlentities($replyto);?>">
										</div>

										<label class="col-sm-2 control-label">Title<span style="color:red">*</span></label>
										<div class="col-sm-4">
											<input type="text" name="title" class="form-control" required>
										</div>
									</div>

									<div class="form-group">
										<label class="col-sm-2 control-label">Message<span style="color:red">*</span></label>
										<div class="col-sm-6">
											<textarea name="message" class="form-control" cols="30" rows="10" required></textarea>
										</div>
									</div>

                                    <!-- Optional: enable attachment later by adding this input -->
                                    <!--
									<div class="form-group">
										<label class="col-sm-2 control-label">Attachment</label>
										<div class="col-sm-6">
											<input type="file" name="attachment" class="form-control">
										</div>
									</div>
                                    -->

									<div class="form-group">
										<div class="col-sm-8 col-sm-offset-2">
											<button class="btn btn-primary" name="submit" type="submit">Send Reply</button>
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
