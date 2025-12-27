<?php
require_once __DIR__ . '/includes/session_user.php';
requireUserLogin();

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/admin/controller.php';

$controller = new Controller();
$dbh = $controller->pdo();

$msg = '';
$error = '';

// ------------------------------------
// SUBMIT FEEDBACK
// ------------------------------------
if (isset($_POST['submit'])) {

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // ✅ logged in user email from user session
    $user     = $_SESSION['user_login']; // email
    $receiver = 'Admin';
    $notitype = 'Send Feedback';

    if ($title === '' || $description === '') {
        $error = "Please fill all required fields.";
    }

    // -----------------------------
    // Attachment (optional)
    // -----------------------------
    $attachment = null;

    // ✅ This folder must exist: /Business_only/attachment/
    $folder = __DIR__ . "/attachment/";
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }

    if ($error === '' && !empty($_FILES['attachment']['name'])) {

        $file     = $_FILES['attachment']['name'];
        $file_loc = $_FILES['attachment']['tmp_name'];

        // ✅ safer allowed extensions
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $allowed  = ['jpg','jpeg','png','pdf','doc','docx'];

        if (!in_array($ext, $allowed, true)) {
            $error = "Invalid attachment type. Allowed: jpg, jpeg, png, pdf, doc, docx.";
        } else {

            $base = preg_replace('/[^a-zA-Z0-9-_]/', '-', pathinfo($file, PATHINFO_FILENAME));
            $final_file = strtolower($base . '-' . time() . '.' . $ext);

            if (move_uploaded_file($file_loc, $folder . $final_file)) {
                $attachment = $final_file;
            } else {
                $error = "Attachment upload failed.";
            }
        }
    }

    // -----------------------------
    // INSERT DATA
    // -----------------------------
    if ($error === '') {

        try {
            // ✅ 1) Notification for Admin
            $sqlNoti = "INSERT INTO notification (notiuser, notireceiver, notitype)
                        VALUES (:user, :receiver, :type)";
            $stmtNoti = $dbh->prepare($sqlNoti);
            $stmtNoti->execute([
                ':user'     => $user,
                ':receiver' => $receiver,
                ':type'     => $notitype
            ]);

            // ✅ 2) Feedback message to Admin
            $sql = "INSERT INTO feedback (sender, receiver, title, feedbackdata, attachment)
                    VALUES (:sender, :receiver, :title, :data, :attachment)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':sender'     => $user,
                ':receiver'   => $receiver,
                ':title'      => $title,
                ':data'       => $description,
                ':attachment' => $attachment
            ]);

            $msg = "Feedback Sent Successfully";

        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Send Feedback</title>

<link rel="stylesheet" href="css/font-awesome.min.css">
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/style.css">

<style>
.errorWrap {
    padding:10px;
    background:#dd3d36;
    color:#fff;
    margin-bottom:15px;
}
.succWrap {
    padding:10px;
    background:#5cb85c;
    color:#fff;
    margin-bottom:15px;
}
</style>
</head>

<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="ts-main-content">
<?php include __DIR__ . '/includes/leftbar.php'; ?>

<div class="content-wrapper">
<div class="container-fluid">

<h2 class="page-title">New Compose</h2>

<?php if($error): ?>
<div class="errorWrap"><strong>ERROR:</strong> <?php echo htmlentities($error); ?></div>
<?php elseif($msg): ?>
<div class="succWrap"><strong>SUCCESS:</strong> <?php echo htmlentities($msg); ?></div>
<?php endif; ?>

<div class="panel panel-default">
<div class="panel-body">

<form method="post" class="form-horizontal" enctype="multipart/form-data">

<div class="form-group">
    <label class="col-sm-2 control-label">Title *</label>
    <div class="col-sm-4">
        <input type="text" name="title" class="form-control" required>
    </div>

    <label class="col-sm-2 control-label">Attachment</label>
    <div class="col-sm-4">
        <input type="file" name="attachment" class="form-control">
        <small>Allowed: jpg, jpeg, png, pdf, doc, docx</small>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-2 control-label">Description *</label>
    <div class="col-sm-10">
        <textarea class="form-control" rows="5" name="description" required></textarea>
    </div>
</div>

<div class="form-group">
    <div class="col-sm-8 col-sm-offset-2">
        <button class="btn btn-primary" name="submit" type="submit">Send</button>
    </div>
</div>

</form>

</div>
</div>

</div>
</div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
setTimeout(() => $('.succWrap').slideUp('slow'), 3000);
</script>

</body>
</html>
