<?php
require_once __DIR__ . '/includes/session_admin.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/controller.php';

$controller = new Controller();
$dbh = $controller->pdo();

// ✅ Admin identity (from session_admin.php login)
$adminLogin = $_SESSION['admin_login'];          // username/email
$adminRole  = (int)($_SESSION['userRole'] ?? 0); // 1 Admin, 2 Manager, 3 Gospel, 4 Staff

// Optional: if you want Admin-only blocks
$isAdmin = ($adminRole === 1);

$msg = '';
$error = '';

// ✅ keep same receiver logic you used before
$receiver = 'Admin';

// =======================
// DELETE ONE MESSAGE
// =======================
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];

    try {
        $sql = "DELETE FROM feedback WHERE id = :id AND receiver = :receiver";
        $stmt = $dbh->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':receiver' => $receiver
        ]);

        if ($stmt->rowCount() > 0) {
            $msg = "Message deleted successfully";
        } else {
            $error = "Delete failed (not found or not allowed).";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// =======================
// DELETE ALL MESSAGES
// =======================
if (isset($_POST['delete_all'])) {
    try {
        $sql = "DELETE FROM feedback WHERE receiver = :receiver";
        $stmt = $dbh->prepare($sql);
        $stmt->execute([':receiver' => $receiver]);
        $msg = "All messages deleted successfully";
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// =======================
// FETCH MESSAGES
// =======================
$sql = "SELECT id, sender, title, feedbackdata, attachment, created_at
        FROM feedback
        WHERE receiver = :receiver
        ORDER BY created_at DESC";
$query = $dbh->prepare($sql);
$query->execute([':receiver' => $receiver]);
$rows = $query->fetchAll(PDO::FETCH_OBJ);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Messager</title>

<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/font-awesome.min.css">
<link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="css/style.css">

<style>
.succWrap{ background:#5cb85c; color:#fff; padding:10px; margin-bottom:15px; }
.errorWrap{ background:#dd3d36; color:#fff; padding:10px; margin-bottom:15px; }
.action-icons i{ font-size:16px; margin-right:10px; }
.actions-bar{display:flex;gap:10px;align-items:center;justify-content:space-between;margin:10px 0 15px;flex-wrap:wrap;}
</style>
</head>

<body>

<?php include 'includes/header.php'; ?>
<div class="ts-main-content">
<?php include 'includes/leftbar.php'; ?>

<div class="content-wrapper">
<div class="container-fluid">

<h2 class="page-title">Messager</h2>

<?php if ($error): ?>
<div class="errorWrap"><?php echo htmlentities($error); ?></div>
<?php endif; ?>

<?php if ($msg): ?>
<div class="succWrap"><?php echo htmlentities($msg); ?></div>
<?php endif; ?>

<div class="actions-bar">
    <div>
        <strong>Total:</strong> <?php echo count($rows); ?>
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
    <th>Title</th>
    <th>Message</th>
    <th>Attachment</th>
    <th>Date & Time</th>
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
    <td><?php echo htmlentities($row->sender); ?></td>
    <td><?php echo htmlentities($row->title); ?></td>
    <td><?php echo nl2br(htmlentities($row->feedbackdata)); ?></td>

    <td>
    <?php if (!empty($row->attachment)): ?>
        <a href="../attachment/<?php echo htmlentities($row->attachment); ?>" target="_blank" title="Open attachment">
            <i class="fa fa-paperclip"></i>
        </a>
    <?php else: ?>
        N/A
    <?php endif; ?>
    </td>

    <td><?php echo date('Y-m-d h:i A', strtotime($row->created_at)); ?></td>

    <td class="action-icons">
        <!-- Reply -->
        <a href="sendreply.php?reply=<?php echo urlencode($row->sender); ?>" title="Reply">
            <i class="fa fa-mail-reply text-primary"></i>
        </a>

        <!-- Delete -->
        <a href="feedback.php?del=<?php echo (int)$row->id; ?>"
           onclick="return confirm('Delete this message?');"
           title="Delete">
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

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.bootstrap.min.js"></script>

<script>
$(function(){
    // ✅ DataTables gives:
    // - "Showing X to Y of Z entries"
    // - Prev / Next pagination
    // - search
    $('#zctb').DataTable({
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
    });

    setTimeout(() => $('.succWrap,.errorWrap').fadeOut(), 3000);
});
</script>

</body>
</html>
