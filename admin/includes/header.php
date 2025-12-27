<?php
require_once __DIR__ . '/session_admin.php';
requireAdminLogin();

/* ---------------------------
   LOAD CONTROLLER (NO config.php)
   ✅ admin/controller.php is in /Business_only/admin/
   ✅ controller.php also exists in /Business_only/admin/ in your project
---------------------------- */
require_once __DIR__ . '/../controller.php';

$controller = new Controller();
$dbh = $controller->pdo();

/* ---------------------------
   ROLE MAP
---------------------------- */
$roleMap = [
    1 => 'Admin',
    2 => 'Manager',
    3 => 'Gospel',
    4 => 'Staff',
    5 => 'Teacher'
];

/* ---------------------------
   ✅ ADMIN SESSION KEYS ONLY
---------------------------- */
$adminLogin  = $_SESSION['admin_login'] ?? '';         // username OR email
$adminRoleId = (int)($_SESSION['userRole'] ?? 0);      // ✅ use userRole
$roleName    = $roleMap[$adminRoleId] ?? 'Admin';

/* ---------------------------
   LOAD ADMIN DATA (admin table only)
   ✅ FIX: two placeholders (no reuse issues)
---------------------------- */
$user = null;
if ($adminLogin !== '') {
    $stmt = $dbh->prepare("
        SELECT idadmin, username, email, image
        FROM admin
        WHERE username = :u OR email = :e
        LIMIT 1
    ");
    $stmt->execute([
        ':u' => $adminLogin,
        ':e' => $adminLogin
    ]);
    $user = $stmt->fetch(PDO::FETCH_OBJ);
}

/* ---------------------------
   AVATAR (NO '?' image)
   Admin pages must use ../images/...
---------------------------- */
$avatar = '../images/default.jpg'; // fallback

if ($user && !empty($user->image)) {
    // filesystem path to /Business_only/images/<file>
    $abs = dirname(__DIR__, 1) . '/images/' . $user->image; // /Business_only/images/...
    if (file_exists($abs)) {
        $avatar = '../images/' . $user->image; // url from /Business_only/admin/*
    }
}

$displayName = ($user && !empty($user->username)) ? $user->username : $adminLogin;

/* ---------------------------
   ✅ Prevent back-button cached admin pages
---------------------------- */
?>
<script>
window.addEventListener("pageshow", function (event) {
    if (event.persisted) window.location.reload();
});
</script>

<div class="brand clearfix">
    <h4 class="pull-left text-white" style="margin:20px 0 0 20px">
        <i class="fa fa-rocket"></i>&nbsp; Gospel
    </h4>

    <h4 class="pull-left text-white" style="margin:20px 0 0 20px">
        Hi, <?php echo htmlentities($displayName); ?> as <?php echo htmlentities($roleName); ?>
    </h4>

    <span class="menu-btn"><i class="fa fa-bars"></i></span>

    <ul class="ts-profile-nav">
        <li class="ts-account">
            <a href="#">
                <img
                    src="images/<?php echo htmlentities($user->image); ?>"
                    class="ts-avatar hidden-side"
                    alt="Profile"
                    style="width:40px;height:40px;border-radius:50%;object-fit:cover;"
                >
                Account <i class="fa fa-angle-down hidden-side"></i>
            </a>

            <ul>
                <li><a href="profile.php">My Profile</a></li>
                <li><a href="change-password.php">Change Password</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</div>
