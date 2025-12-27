<?php
require_once __DIR__ . '/includes/session_admin.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/controller.php';

// ✅ Admin identity (from session_admin.php login)
$adminLogin = $_SESSION['admin_login'];          // username/email
$adminRole  = (int)($_SESSION['userRole'] ?? 0); // 1 Admin, 2 Manager, 3 Gospel, 4 Staff

// Optional: if you want Admin-only blocks
$isAdmin = ($adminRole === 1);

$controller = new Controller();
$dbh = $controller->pdo();

$userRole = (int)($_SESSION['userRole'] ?? 0);

/**
 * Receiver logic:
 * Admin panel notifications usually go to "Admin"
 * If you want per-user notifications, use $_SESSION['alogin']
 */
$receiver = 'Admin'; 
// $receiver = $_SESSION['alogin'];

$msg = '';
$error = '';

// -----------------------------
// DELETE ALL notifications (POST)
// -----------------------------
if (isset($_POST['delete_all'])) {
    try {
        $delAll = $dbh->prepare("DELETE FROM notification WHERE notireceiver = :receiver");
        $delAll->execute([':receiver' => $receiver]);
        $msg = "All notifications deleted.";
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// -----------------------------
// DELETE ONE notification (GET)
// -----------------------------
if (isset($_GET['del']) && $_GET['del'] !== '') {
    $id = (int)$_GET['del'];

    try {
        $del = $dbh->prepare("DELETE FROM notification WHERE id = :id AND notireceiver = :receiver");
        $del->execute([
            ':id' => $id,
            ':receiver' => $receiver,
        ]);

        if ($del->rowCount() > 0) {
            $msg = "Notification deleted.";
        } else {
            $error = "Delete failed (not found or not allowed).";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// -----------------------------
// Fetch notifications
// -----------------------------
try {
    $sql = "SELECT id, notiuser, notitype, created_at
            FROM notification
            WHERE notireceiver = :receiver
            ORDER BY created_at DESC";
    $query = $dbh->prepare($sql);
    $query->execute([':receiver' => $receiver]);
    $results = $query->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    $results = [];
    $error = "Database error: " . $e->getMessage();
}

// Helper to format date/time nicely
function fmt_dt($dt) {
    if (!$dt) return 'N/A';
    return date('Y-m-d h:i A', strtotime($dt));
}
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>Notifications</title>

    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-social.css">
    <link rel="stylesheet" href="css/bootstrap-select.css">
    <link rel="stylesheet" href="css/fileinput.min.css">
    <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
        .actions-bar{
            display:flex;gap:10px;align-items:center;justify-content:space-between;
            margin-bottom:12px;flex-wrap:wrap;
        }
        .icon-action a{font-size:18px;margin-right:8px;}
        .icon-action a:hover{text-decoration:none;opacity:.8;}
        .badge-soft{background:#eef5ff;color:#0b5ed7;padding:4px 10px;border-radius:14px;font-weight:600;}
    </style>
</head>
<body>

<?php include('includes/header.php'); ?>
<div class="ts-main-content">
<?php include('includes/leftbar.php'); ?>

<div class="content-wrapper">
    <div class="container-fluid">

        <div class="row">
            <div class="col-md-12">

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlentities($error); ?></div>
                <?php endif; ?>

                <?php if (!empty($msg)): ?>
                    <div class="alert alert-success"><?php echo htmlentities($msg); ?></div>
                <?php endif; ?>

                <div class="panel panel-default">
                    <div class="panel-heading">Notification</div>

                    <div class="panel-body">

                        <div class="actions-bar">
                            <div>
                                <span class="badge-soft">
                                    Receiver: <?php echo htmlentities($receiver); ?>
                                </span>
                            </div>

                            <form method="post" style="margin:0;">
                                <button
                                    type="submit"
                                    name="delete_all"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete ALL notifications for <?php echo htmlentities($receiver); ?>?');"
                                    <?php echo (count($results) === 0) ? 'disabled' : ''; ?>
                                >
                                    <i class="fa fa-trash"></i> Delete All
                                </button>
                            </form>
                        </div>

                        <table id="notiTable" class="display table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>From</th>
                                    <th>Type</th>
                                    <th>Date &amp; Time</th>
                                    <th style="width:90px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $cnt = 1;
                            foreach ($results as $r):
                            ?>
                                <tr>
                                    <td><?php echo $cnt++; ?></td>
                                    <td><?php echo htmlentities($r->notiuser); ?></td>
                                    <td><?php echo htmlentities($r->notitype); ?></td>
                                    <td><?php echo htmlentities(fmt_dt($r->created_at)); ?></td>
                                    <td class="icon-action">
                                        <a
                                            href="notification.php?del=<?php echo (int)$r->id; ?>"
                                            title="Delete"
                                            onclick="return confirm('Delete this notification?');"
                                        >
                                            <i class="fa fa-trash" style="color:red;"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>

                        <?php if (count($results) === 0): ?>
                            <div class="alert alert-info" style="margin-top:12px;">No notifications found.</div>
                        <?php endif; ?>

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
$(document).ready(function () {
    $('#notiTable').DataTable({
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
    });

    setTimeout(function() {
        $('.alert-success').slideUp("slow");
    }, 3000);
});
</script>
</body>
</html>
