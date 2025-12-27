<?php
require_once __DIR__ . '/session_user.php';
requireUserLogin();

// session values set by setUserSession()
$userEmail = $_SESSION['user_login'] ?? '';
$userName  = $_SESSION['user_name'] ?? '';
$userImage = $_SESSION['user_image'] ?? 'default.jpg';

// fallback display name
$displayName = $userName !== '' ? $userName : $userEmail;

// avatar path (prevent ? broken image)
$avatarWeb = 'images/default.jpg';
$avatarAbs = __DIR__ . '/../images/default.jpg';

if (!empty($userImage)) {
    $tryAbs = __DIR__ . '/../images/' . $userImage;
    if (file_exists($tryAbs)) {
        $avatarWeb = 'images/' . $userImage;
        $avatarAbs = $tryAbs;
    }
}
?>

<div class="brand clearfix">
    <h4 class="pull-left text-white" style="margin:20px 0px 0px 20px">
        <i class="fa fa-rocket"></i>&nbsp; Gospel
    </h4>

    <h4 class="pull-left text-white" style="margin:20px 0px 0px 20px">
        Hi, <?php echo htmlentities($displayName); ?>
    </h4>

    <span class="menu-btn"><i class="fa fa-bars"></i></span>

    <ul class="ts-profile-nav">
        <li class="ts-account">
            <a href="#">
                <img
                    src="<?php echo htmlentities($avatarWeb); ?>"
                    class="ts-avatar hidden-side"
                    alt="Profile"
                    style="width:40px;height:40px;border-radius:50%;object-fit:cover;"
                    onerror="this.onerror=null;this.src='images/default.jpg';"
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
