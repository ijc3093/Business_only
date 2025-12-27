<?php
// /Business_only/profile.php

require_once __DIR__ . '/includes/session_user.php';   // ✅ USER cookie/session
require_once __DIR__ . '/admin/controller.php';        // ✅ PDO

error_reporting(E_ALL);
ini_set('display_errors', '1');

$controller = new Controller();
$dbh = $controller->pdo();

// ✅ Must be logged in as USER
requireUserLogin();

$msg = '';
$error = '';

// ------------------------------------
// LOAD USER DATA (by session email)
// ------------------------------------
$sessionEmail = $_SESSION['user_login'];

$sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
$query = $dbh->prepare($sql);
$query->execute([':email' => $sessionEmail]);
$result = $query->fetch(PDO::FETCH_OBJ);

if (!$result) {
    // If user record missing, logout user session only
    session_destroy();
    header('location:index.php');
    exit;
}

// ------------------------------------
// UPDATE PROFILE
// ------------------------------------
if (isset($_POST['submit'])) {

    $name        = trim($_POST['name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $mobileno    = trim($_POST['mobile'] ?? '');
    $designation = trim($_POST['designation'] ?? '');

    // keep existing image unless new one uploaded
    $image = trim($_POST['current_image'] ?? ($result->image ?? 'default.jpg'));

    // validate
    if ($name === '' || $email === '' || $mobileno === '' || $designation === '') {
        $error = "Please fill all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    }

    // optional image upload
    if ($error === '' && !empty($_FILES['image']['name'])) {

        $file     = $_FILES['image']['name'];
        $file_loc = $_FILES['image']['tmp_name'];
        $ext      = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowed, true)) {
            $error = "Image must be JPG, JPEG, or PNG.";
        } else {

            $folder = __DIR__ . "/images/";
            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }

            $base = preg_replace('/[^a-zA-Z0-9-_]/', '-', pathinfo($file, PATHINFO_FILENAME));
            $final_file = strtolower($base . '-' . time() . '.' . $ext);

            if (move_uploaded_file($file_loc, $folder . $final_file)) {
                $image = $final_file;
            } else {
                $error = "Image upload failed.";
            }
        }
    }

    // prevent duplicate email (only if changed)
    if ($error === '' && strtolower($email) !== strtolower($result->email)) {
        $dup = $dbh->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $dup->execute([':email' => $email]);
        if ($dup->fetchColumn()) {
            $error = "This email already exists.";
        }
    }

    // do update (use logged-in user id, not POSTed id)
    if ($error === '') {

        $sql = "UPDATE users
                SET name = :name,
                    email = :email,
                    mobile = :mobile,
                    designation = :designation,
                    image = :image
                WHERE id = :id";

        $upd = $dbh->prepare($sql);
        $ok = $upd->execute([
            ':name'        => $name,
            ':email'       => $email,
            ':mobile'      => $mobileno,
            ':designation' => $designation,
            ':image'       => $image,
            ':id'          => (int)$result->id
        ]);

        if ($ok) {
            // reload user
            $query = $dbh->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
            $query->execute([':id' => (int)$result->id]);
            $result = $query->fetch(PDO::FETCH_OBJ);

            // ✅ update USER session in one place (prevents admin/user mix)
            setUserSession([
                'id'    => (int)$result->id,
                'email' => (string)$result->email,
                'name'  => (string)$result->name,
                'image' => (string)$result->image,
            ]);

            $msg = "Information Updated Successfully";
        } else {
            $error = "Update failed. Please try again.";
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

<title>Edit Profile</title>

<link rel="stylesheet" href="css/font-awesome.min.css">
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="css/bootstrap-social.css">
<link rel="stylesheet" href="css/bootstrap-select.css">
<link rel="stylesheet" href="css/fileinput.min.css">
<link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
<link rel="stylesheet" href="css/style.css">

<style>
.errorWrap { padding:10px; background:#dd3d36; color:#fff; margin-bottom:15px; }
.succWrap  { padding:10px; background:#5cb85c; color:#fff; margin-bottom:15px; }
</style>
</head>

<body>

<?php include __DIR__ . '/includes/header.php'; ?>
<div class="ts-main-content">
<?php include __DIR__ . '/includes/leftbar.php'; ?>

<div class="content-wrapper">
<div class="container-fluid">
<div class="row">
<div class="col-md-12">
<h2 class="page-title">Profile</h2>

<div class="panel panel-default">
<div class="panel-heading"><?php echo htmlentities($_SESSION['user_login']); ?></div>

<?php if($error): ?>
<div class="errorWrap"><strong>ERROR:</strong> <?php echo htmlentities($error); ?></div>
<?php elseif($msg): ?>
<div class="succWrap"><strong>SUCCESS:</strong> <?php echo htmlentities($msg); ?></div>
<?php endif; ?>

<div class="panel-body">
<form method="post" class="form-horizontal" enctype="multipart/form-data">

<div class="form-group text-center">
    <img
        src="images/<?php echo htmlentities($result->image ?? 'default.jpg'); ?>"
        style="width:200px;height:200px;border-radius:50%;margin:10px;object-fit:cover;"
        alt="Profile"
    >
    <input type="file" name="image" class="form-control">
    <input type="hidden" name="current_image" value="<?php echo htmlentities($result->image ?? 'default.jpg'); ?>">
</div>

<div class="form-group">
    <label class="col-sm-2 control-label">Name *</label>
    <div class="col-sm-4">
        <input type="text" name="name" class="form-control" required
               value="<?php echo htmlentities($result->name ?? ''); ?>">
    </div>

    <label class="col-sm-2 control-label">Email *</label>
    <div class="col-sm-4">
        <input type="email" name="email" class="form-control" required
               value="<?php echo htmlentities($result->email ?? ''); ?>">
    </div>
</div>

<div class="form-group">
    <label class="col-sm-2 control-label">Mobile *</label>
    <div class="col-sm-4">
        <input type="text" name="mobile" class="form-control" required
               value="<?php echo htmlentities($result->mobile ?? ''); ?>">
    </div>

    <label class="col-sm-2 control-label">Designation *</label>
    <div class="col-sm-4">
        <input type="text" name="designation" class="form-control" required
               value="<?php echo htmlentities($result->designation ?? ''); ?>">
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

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
setTimeout(() => $('.succWrap').slideUp('slow'), 3000);
</script>
</body>
</html>
