<?php
require_once __DIR__ . '/includes/session_admin.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/controller.php';

$controller = new Controller();
$dbh = $controller->pdo();

// ✅ Admin identity (from session_admin.php login)
$adminLogin = $_SESSION['admin_login'];          // username/email
$adminRole  = (int)($_SESSION['userRole'] ?? 0); // 1 Admin, 2 Manager, 3 Gospel, 4 Staff

// Optional: if you want Admin-only blocks
$isAdmin = ($adminRole === 1);

// -------------------------------------------------
// 1) Get reply-to email safely from URL
// -------------------------------------------------
$replyto = '';
if (isset($_GET['reply'])) {
    $replyto = trim($_GET['reply']);
    $replyto = filter_var($replyto, FILTER_SANITIZE_EMAIL);

    if (!filter_var($replyto, FILTER_VALIDATE_EMAIL)) {
        $replyto = '';
    }
}

$msg = '';
$error = '';

// -------------------------------------------------
// 2) Submit reply
// -------------------------------------------------
if (isset($_POST['submit'])) {

    $receiver = trim($_POST['email'] ?? '');
    $title    = trim($_POST['title'] ?? '');
    $message  = trim($_POST['message'] ?? '');

    // Sender: you can use the logged-in admin username
    $sender = $_SESSION['admin_login'] ?? 'Admin';

    if (!filter_var($receiver, FILTER_VALIDATE_EMAIL)) {
        $error = "Receiver email is invalid.";
    } elseif ($title === '' || $message === '') {
        $error = "Title and message are required.";
    } else {

        // Optional attachment (if you enable the input later)
        $attachment = '';

        if (isset($_FILES['attachment']) && !empty($_FILES['attachment']['name'])) {

            $file     = $_FILES['attachment']['name'];
            $file_loc = $_FILES['attachment']['tmp_name'];

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','pdf','doc','docx'];

            if (!in_array($ext, $allowed, true)) {
                $error = "Attachment not allowed (jpg, jpeg, png, pdf, doc, docx only).";
            } else {

                $folder = __DIR__ . "/attachment/";
                if (!is_dir($folder)) {
                    mkdir($folder, 0755, true);
                }

                $base = preg_replace('/[^a-zA-Z0-9-_]/', '-', pathinfo($file, PATHINFO_FILENAME));
                $final_file = strtolower($base . '-' . time() . '.' . $ext);

                if (move_uploaded_file($file_loc, $folder . $final_file)) {
                    $attachment = $final_file;
                } else {
                    $error = "Attachment upload failed.";
                }
            }
        }

        if ($error === '') {
            try {
                // ✅ Insert notification for the receiver (public user email)
                $sqlnoti = "INSERT INTO notification (notiuser, notireceiver, notitype)
                           VALUES (:notiuser, :notireceiver, :notitype)";
                $querynoti = $dbh->prepare($sqlnoti);
                $querynoti->execute([
                    ':notiuser' => $sender,
                    ':notireceiver' => $receiver,
                    ':notitype' => 'Send Message'
                ]);

                // ✅ Insert feedback
                $sql = "INSERT INTO feedback (sender, receiver, title, feedbackdata, attachment)
                        VALUES (:sender, :receiver, :title, :feedbackdata, :attachment)";
                $query = $dbh->prepare($sql);
                $query->execute([
                    ':sender' => $sender,
                    ':receiver' => $receiver,
                    ':title' => $title,
                    ':feedbackdata' => $message,
                    ':attachment' => $attachment
                ]);

                $msg = "Reply sent successfully!";
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
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

    <style>
        .errorWrap { padding:10px; margin:0 0 20px 0; background:#dd3d36; color:#fff; }
        .succWrap  { padding:10px; margin:0 0 20px 0; background:#5cb85c; color:#fff; }
    </style>
</head>

<body>
<?php include(__DIR__ . '/includes/header.php'); ?>
<div class="ts-main-content">
<?php include(__DIR__ . '/includes/leftbar.php'); ?>

    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">

                    <h2 class="page-title">Reply Feedback</h2>

                    <div class="panel panel-default">
                        <div class="panel-heading">Send Reply</div>

                        <?php if(!empty($error)) { ?>
                            <div class="errorWrap"><strong>ERROR</strong>: <?php echo htmlentities($error); ?></div>
                        <?php } elseif(!empty($msg)) { ?>
                            <div class="succWrap"><strong>SUCCESS</strong>: <?php echo htmlentities($msg); ?></div>
                        <?php } ?>

                        <div class="panel-body">
                            <form method="post" class="form-horizontal" enctype="multipart/form-data">

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Email<span style="color:red">*</span></label>
                                    <div class="col-sm-4">
                                        <input type="text" name="email" class="form-control" readonly required
                                               value="<?php echo htmlentities($replyto); ?>">
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

                                <!-- OPTIONAL attachment input (uncomment if you want it)
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

<script>
$(document).ready(function () {
    setTimeout(function() {
        $('.succWrap').slideUp("slow");
    }, 3000);
});
</script>
</body>
</html>
