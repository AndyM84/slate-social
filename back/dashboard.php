<?php

	define('CORE_PATH', '../');
	require(CORE_PATH . 'inc/Core.php');

	global $SiteSettings, $Db, $Io, $User;

	if (!isAuthenticated($Db)) {
		header('Location: ./logout.php');
		exit;
	}

	$User = new User($Db, $_SESSION[BackendStrings::Session_UserIdKey]);

?>
<?php $Io->load('~/back/tpl/header.php', true); ?>
			<!-- Pre Page Content -->
			<div id="pre-page-content">
				<h1><i class="gi gi-dashboard themed-color"></i>Dashboard<br><small>Welcome <strong><?php echo $User->username; ?></strong>, everything looks good!</small></h1>
			</div>
			<!-- END Pre Page Content -->

			<!-- Page Content -->
			<div id="page-content">
				<!-- Breadcrumb -->
				<ul class="breadcrumb breadcrumb-top">
					<li>
						<a href="dashboard.php"><i class="gi gi-display"></i></a>
					</li>
					<li><a href="">Dashboard</a></li>
				</ul>
				<!-- END Breadcrumb -->

				<!-- Content Row -->
				<div class="row">
					<div class="col-md-12">
						<h2>Content Here</h2>
					</div>
				</div>
				<!-- END Content Row -->
			</div>
			<!-- END Page Content -->
<?php $Io->load('~/back/tpl/footer.php', true); ?>