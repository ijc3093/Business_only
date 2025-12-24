<?php 
	if(!isset($_SESSION['userRole'])){
		switch($_SESSION['userRole']){
		  case 1:
		  case 2:
		  case 3:
		  case 4:
			echo '<script>window.location.replace("index.php");</script>';
			break;
		  default:
			break;
		}
	  }

?>
<nav class="ts-sidebar">
<ul class="ts-sidebar-menu">
<?php
				$userRole = $_SESSION['userRole'];
				if( empty($userRole) ){
					echo "<script>alert('User Role is empty');</script>";
				}  
				//Admin
                //Notice that #1 is not in $_SESSION[...] and why? because if #1 is not there, it meant that #1 as Admin can access all nav in the left.
                //Notice that #2, 3, 4 are still in $_SESSION[...] and why? because they are unable to access admin's page.
                if($userRole == 2 || $userRole == 3 || $userRole == 4){
                    //
                }else{
					echo '<li><a href="profile.php"><i class="fa fa-user"></i> &nbsp;Profile</a></li>';
					echo '<li><a href="dashboard.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>';
					echo '<li><a href="roleslist.php"><i class="fa fa-dashboard"></i> Roles</a></li>';
					echo '<li><a href="userlist.php"><i class="fa fa-users"></i> Userlist</a></li>';
					echo '<li><a href="feedback.php"><i class="fa fa-envelope"></i> &nbsp;Feedback</a></li>';
					echo '<li><a href="notification.php"><i class="fa fa-bell"></i> &nbsp;Notification <sup style="color:red">*</sup></a></li>';
					echo '<li><a href="deleteduser.php"><i class="fa fa-user-times"></i> &nbsp;Deleted Users</a></li>';
					echo '<li><a href="download.php"><i class="fa fa-download"></i> &nbsp;Download Users-List</a></li>';
				}

				//Manager
                //manager.php
                if($userRole == 1 || $userRole == 3 || $userRole == 4){
                    //
                }else{
					echo '<li><a href="profile.php"><i class="fa fa-user"></i> &nbsp;Profile</a></li>';
                    echo '<li><a href="manager.php"><i class="fa fa-user"></i> Manager</a></li>';
					echo '<li><a href="dashboard.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>';
					echo '<li><a href="feedback_.php"><i class="fa fa-envelope"></i> Feedback</a></li>';
					echo '<li><a href="messages_.php"><i class="fa fa-user-times"></i> Messages</a></li>';
					echo '<li><a href="notification_.php"><i class="fa fa fa-bell"></i> Notification</a></li>';
                }

				//Staff
                //staff.php
                if($userRole == 1 || $userRole == 2 || $userRole == 3){
                    //
                }else{
					echo '<li><a href="profile.php"><i class="fa fa-user"></i> &nbsp;Profile</a></li>';
                    echo '<li><a href="staff.php"><i class="fa fa-user"></i>Staff</a></li>';
					echo '<li><a href="dashboard.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>';
					echo '<li><a href="feedback_.php"><i class="fa fa-envelope"></i>Feedback</a></li>';
					echo '<li><a href="messages_.php"><i class="fa fa-user-times"></i>Messages</a></li>';
					echo '<li><a href="notification_.php"><i class="fa fa fa-bell"></i>Notification</a></li>';
                }
				

?>
</ul>
</nav>

		