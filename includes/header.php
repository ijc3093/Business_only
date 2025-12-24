<?php
    
    if(strlen($_SESSION['alogin'])==0){
        header('location:index.php');
    }else{
    
?>
	
	<div class="brand clearfix">
	<h4 class="pull-left text-white" style="margin:20px 0px 0px 20px"><i class="fa fa-rocket"></i>&nbsp; Gospel</h4>
	

		<span class="menu-btn"><i class="fa fa-bars"></i></span>
		
		<ul class="ts-profile-nav">
		<h4 class="pull-left text-white" style="margin:20px 0px 0px 20px"></i>Hi, <?php echo htmlentities($_SESSION['alogin']);?></h4>
			<li class="ts-account">
				<a href="#"><img src="img/ts-avatar.jpg" class="ts-avatar hidden-side" alt=""> Account <i class="fa fa-angle-down hidden-side"></i></a>
				
				<ul>
					<li><a href="change-password.php">Change Password</a></li>
					<li><a href="logout.php">Logout</a></li>
				</ul>
			</li>
		</ul>
	</div>
<?php } ?>