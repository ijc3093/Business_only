<?php
require_once __DIR__ . '/includes/session_user.php';
requireUserLogin();

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/admin/controller.php';

$controller = new Controller();
$dbh = $controller->pdo();

$error = '';
$msg   = '';

$receiver = $_SESSION['user_login'];

// ============================
// DELETE ONE MESSAGE
// ============================
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];

    try {
        $sql = "DELETE FROM feedback
                WHERE id = :id AND receiver = :receiver";
        $stmt = $dbh->prepare($sql);
        $stmt->execute([
            ':id' => $deleteId,
            ':receiver' => $receiver
        ]);

        if ($stmt->rowCount() > 0) {
            $msg = "Message deleted successfully.";
            // ✅ prevent re-delete on refresh
            header("Location: messages.php?msg=deleted");
            exit;
        } else {
            $error = "Delete failed (not found or not allowed).";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// ============================
// DELETE ALL MESSAGES
// ============================
if (isset($_POST['delete_all'])) {
    try {
        $sql = "DELETE FROM feedback WHERE receiver = :receiver";
        $stmt = $dbh->prepare($sql);
        $stmt->execute([':receiver' => $receiver]);

        $msg = "All messages deleted successfully.";
        // ✅ prevent re-delete on refresh
        header("Location: messages.php?msg=all_deleted");
        exit;

    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// ============================
// OPTIONAL UI MESSAGE FROM REDIRECT
// ============================
if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $msg = "Message deleted successfully.";
}
if (isset($_GET['msg']) && $_GET['msg'] === 'all_deleted') {
    $msg = "All messages deleted successfully.";
}

// ============================
// FETCH MESSAGES
// ============================
try {
    $sql = "SELECT id, sender, feedbackdata, created_at
            FROM feedback
            WHERE receiver = :receiver
            ORDER BY created_at DESC";
    $stmt = $dbh->prepare($sql);
    $stmt->execute([':receiver' => $receiver]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $rows = [];
    $error = "Database error: " . $e->getMessage();
}
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Messages</title>

<link rel="stylesheet" href="css/font-awesome.min.css">
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="css/style.css">

<style>
.errorWrap { padding:10px; background:#dd3d36; color:#fff; margin-bottom:15px; }
.succWrap  { padding:10px; background:#5cb85c; color:#fff; margin-bottom:15px; }
.actions-bar{display:flex;gap:10px;align-items:center;justify-content:space-between;margin:10px 0 15px;flex-wrap:wrap;}
.action-icons i{ font-size:16px; margin-right:10px; }
</style>
</head>

<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="ts-main-content">
<?php include __DIR__ . '/includes/leftbar.php'; ?>

<div class="content-wrapper">
<div class="container-fluid">

<h2 class="page-title">Message</h2>

<?php if($error): ?>
<div class="errorWrap"><?php echo htmlentities($error); ?></div>
<?php endif; ?>

<?php if($msg): ?>
<div class="succWrap"><?php echo htmlentities($msg); ?></div>
<?php endif; ?>

<div class="panel panel-default">
<div class="panel-heading">
Messages received by: <strong><?php echo htmlentities($receiver); ?></strong>
</div>

<div class="panel-body">

<div class="actions-bar">
    <div>
        <strong>Total:</strong> <?php echo (int)count($rows); ?>
    </div>

    <form method="post" style="margin:0;">
        <button
            type="submit"
            name="delete_all"
            class="btn btn-danger btn-sm"
            onclick="return confirm('Delete ALL messages? This cannot be undone!');"
            <?php echo (count($rows) === 0) ? 'disabled' : ''; ?>
        >
            <i class="fa fa-trash"></i> Delete All
        </button>
    </form>
</div>

<table id="zctb" class="table table-striped table-bordered">
<thead>
<tr>
    <th>#</th>
    <th>From</th>
    <th>Message</th>
    <th>Received At</th>
    <th>Action</th>
</tr>
</thead>

<tbody>
<?php
$cnt = 1;
foreach ($rows as $row):
?>
<tr>
    <td><?php echo $cnt++; ?></td>
    <td><?php echo htmlentities($row['sender']); ?></td>
    <td><?php echo htmlentities($row['feedbackdata']); ?></td>
    <td><?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></td>
    <td class="action-icons">
        <a href="sendreply.php?reply=<?php echo urlencode($row['sender']); ?>" title="Reply">
            <i class="fa fa-mail-reply text-primary"></i>
        </a>

        <a
            href="messages.php?delete=<?php echo (int)$row['id']; ?>"
            title="Delete"
            onclick="return confirm('Are you sure you want to delete this message?');"
        >
            <i class="fa fa-trash text-danger"></i>
        </a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

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
$(function () {
    // ✅ Showing entries + Prev/Next + Search comes from DataTables
    $('#zctb').DataTable({
        // Don't force ordering by formatted date string
        // DB already returns created_at DESC
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
    });

    setTimeout(function(){
        $('.succWrap,.errorWrap').fadeOut();
    }, 3000);
});
</script>

</body>
</html>
