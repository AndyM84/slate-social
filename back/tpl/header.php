<?php

	global $SiteSettings, $Db, $Io, $User, $BackendMenu;

	$userRepo = new Users($Db);
	$userStats = $userRepo->getUserStatistics();

	$Io->load('~/back/tpl/menu.php');

?>
<!DOCTYPE html>
<html class="no-js">
	<head>
		<meta charset="utf-8">
		<title><?php echo $SiteSettings[SettingsStrings::SiteTitle]; ?> - Dashboard</title>
		<meta name="description" content="<?php echo $SiteSettings[SettingsStrings::SiteTitle]; ?> Backend">
		<meta name="robots" content="noindex, nofollow">
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1.0">

		<!-- Icons -->
		<link rel="shortcut icon" href="img/favicon.ico" />
		<link rel="apple-touch-icon" href="img/icon57.png" sizes="57x57" />
		<link rel="apple-touch-icon" href="img/icon72.png" sizes="72x72" />
		<link rel="apple-touch-icon" href="img/icon76.png" sizes="76x76" />
		<link rel="apple-touch-icon" href="img/icon114.png" sizes="114x114" />
		<link rel="apple-touch-icon" href="img/icon120.png" sizes="120x120" />
		<link rel="apple-touch-icon" href="img/icon144.png" sizes="144x144" />
		<link rel="apple-touch-icon" href="img/icon152.png" sizes="152x152" />
		<!-- END Icons -->

		<!-- Stylesheets -->
		<link rel="stylesheet" href="css/bootstrap.css" />
		<link rel="stylesheet" href="css/plugins.css" />
		<link rel="stylesheet" href="css/main.css" />
		<link rel="stylesheet" href="css/themes.css" />
		<!-- END Stylesheets -->

		<!-- Modernizr (browser feature detection library) & Respond.js (Enable responsive CSS code on browsers that don't support it, eg IE8) -->
		<script src="js/vendor/modernizr-2.7.1-respond-1.4.2.min.js"></script>
	</head>
	<body>
		<div id="page-container" class="full-width">
			<header class="navbar navbar-inverse">
				<!-- div#row -->
				<div class="row">
					<!-- Sidebar Toggle Buttons (Desktop & Tablet) -->
					<div class="col-sm-4 hidden-xs">
						<ul class="navbar-nav-custom pull-left">
							<!-- Desktop Button (Visible only on desktop resolutions) -->
							<li class="visible-md visible-lg">
								<a href="javascript:void(0)" id="toggle-side-content">
									<i class="fa fa-bars"></i>
								</a>
							</li>
							<!-- END Desktop Button -->

							<!-- Divider -->
							<li class="divider-vertical"></li>
						</ul>
					</div>
					<!-- END Sidebar Toggle Buttons -->

					<!-- Brand and Search Section -->
					<div class="col-sm-4 col-xs-12 text-center">
						<!-- Logo -->
						<a href="#" class="navbar-brand">&nbsp;</a>
						<!-- END Logo -->

						<!-- Loading Indicator, Used for demostrating how loading of notifications could happen, check main.js - uiDemo() -->
						<div id="loading" class="display-none"><i class="fa fa-spinner fa-spin"></i></div>
					</div>
					<!-- END Brand and Search Section -->

					<!-- Header Nav Section -->
					<div id="header-nav-section" class="col-sm-4 col-xs-12 clearfix">
						<!-- Header Nav -->
						<ul class="navbar-nav-custom pull-right">
							<!-- Theme Options, functionality initialized at main.js - templateOptions() -->
							<li class="dropdown dropdown-theme-options">
								<a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">Theme Options</a>
								<ul class="dropdown-menu dropdown-menu-right">
									<!-- Page Options -->
									<li class="theme-extra visible-md visible-lg">
										<label for="theme-page-full">
											<input type="checkbox" id="theme-page-full" name="theme-page-full" class="input-themed" />
											Full width page
										</label>
									</li>
									<!-- END Page Options -->

									<!-- Divider -->
									<li class="divider visible-md visible-lg"></li>

									<!-- Sidebar Options -->
									<li class="theme-extra visible-md visible-lg">
										<label for="theme-sidebar-sticky">
											<input type="checkbox" id="theme-sidebar-sticky" name="theme-sidebar-sticky" class="input-themed" />
											Sticky Sidebar
										</label>
									</li>
									<!-- END Sidebar Options -->

									<!-- Divider -->
									<li class="divider visible-md visible-lg"></li>

									<!-- Header Options -->
									<li class="theme-extra">
										<label for="theme-header-top">
											<input type="checkbox" id="theme-header-top" name="theme-header-top" class="input-themed" />
											Top fixed header
										</label>
										<label for="theme-header-bottom">
											<input type="checkbox" id="theme-header-bottom" name="theme-header-bottom" class="input-themed" />
											Bottom fixed header
										</label>
									</li>
									<!-- END Header Options -->

									<!-- Divider -->
									<li class="divider"></li>

									<!-- Color Themes -->
									<li>
										<ul class="theme-colors clearfix">
											<li class="active">
												<a href="javascript:void(0)" class="img-circle themed-background-default themed-border-default" data-theme="default" data-toggle="tooltip" title="Default"></a>
											</li>
											<li>
												<a href="javascript:void(0)" class="img-circle themed-background-amethyst themed-border-amethyst" data-theme="css/themes/amethyst.css" data-toggle="tooltip" title="Amethyst"></a>
											</li>
											<li>
												<a href="javascript:void(0)" class="img-circle themed-background-army themed-border-army" data-theme="css/themes/army.css" data-toggle="tooltip" title="Army"></a>
											</li>
											<li>
												<a href="javascript:void(0)" class="img-circle themed-background-asphalt themed-border-asphalt" data-theme="css/themes/asphalt.css" data-toggle="tooltip" title="Asphalt"></a>
											</li>
											<li>
												<a href="javascript:void(0)" class="img-circle themed-background-autumn themed-border-autumn" data-theme="css/themes/autumn.css" data-toggle="tooltip" title="Autumn"></a>
											</li>
											<li>
												<a href="javascript:void(0)" class="img-circle themed-background-cherry themed-border-cherry" data-theme="css/themes/cherry.css" data-toggle="tooltip" title="Cherry"></a>
											</li>
											<li>
												<a href="javascript:void(0)" class="img-circle themed-background-city themed-border-city" data-theme="css/themes/city.css" data-toggle="tooltip" title="City"></a>
											</li>
											<li>
												<a href="javascript:void(0)" class="img-circle themed-background-dawn themed-border-dawn" data-theme="css/themes/dawn.css" data-toggle="tooltip" title="Dawn"></a>
											</li>
											<li>
												<a href="javascript:void(0)" class="img-circle themed-background-deepsea themed-border-deepsea" data-theme="css/themes/deepsea.css" data-toggle="tooltip" title="Deepsea"></a>
											</li>
											<li>
												<a href="javascript:void(0)" class="img-circle themed-background-diamond themed-border-diamond" data-theme="css/themes/diamond.css" data-toggle="tooltip" title="Diamond"></a>
											</li>
											<li>
												<a href="javascript:void(0)" class="img-circle themed-background-fire themed-border-fire" data-theme="css/themes/fire.css" data-toggle="tooltip" title="Fire"></a>
											</li>
											<li>
												<a href="javascript:void(0)" class="img-circle themed-background-grass themed-border-grass" data-theme="css/themes/grass.css" data-toggle="tooltip" title="Grass"></a>
											</li>
											<li>
												<a href="javascript:void(0)" class="img-circle themed-background-leaf themed-border-leaf" data-theme="css/themes/leaf.css" data-toggle="tooltip" title="Leaf"></a>
											</li>
											<li>
												<a href="javascript:void(0)" class="img-circle themed-background-night themed-border-night" data-theme="css/themes/night.css" data-toggle="tooltip" title="Night"></a>
											</li>
											<li>
												<a href="javascript:void(0)" class="img-circle themed-background-ocean themed-border-ocean" data-theme="css/themes/ocean.css" data-toggle="tooltip" title="Ocean"></a>
											</li>
											<li>
												<a href="javascript:void(0)" class="img-circle themed-background-oil themed-border-oil" data-theme="css/themes/oil.css" data-toggle="tooltip" title="Oil"></a>
											</li>
											<li>
												<a href="javascript:void(0)" class="img-circle themed-background-stone themed-border-stone" data-theme="css/themes/stone.css" data-toggle="tooltip" title="Stone"></a>
											</li>
											<li>
												<a href="javascript:void(0)" class="img-circle themed-background-sun themed-border-sun" data-theme="css/themes/sun.css" data-toggle="tooltip" title="Sun"></a>
											</li>
											<li>
												<a href="javascript:void(0)" class="img-circle themed-background-tulip themed-border-tulip" data-theme="css/themes/tulip.css" data-toggle="tooltip" title="Tulip"></a>
											</li>
											<li>
												<a href="javascript:void(0)" class="img-circle themed-background-wood themed-border-wood" data-theme="css/themes/wood.css" data-toggle="tooltip" title="Wood"></a>
											</li>
										</ul>
									</li>
									<!-- END Color Themes -->
								</ul>
							</li>
							<!-- END Theme Options -->

							<!-- Divider -->
							<li class="divider-vertical"></li>

							<!-- Notifications -->
							<li class="dropdown dropdown-notifications">
								<a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
									<i class="fa fa-exclamation-triangle"></i>
									<!--<span class="badge badge-neutral">4</span>-->
								</a>
								<ul class="dropdown-menu dropdown-menu-right">
									<!--<li>
										<div class="alert alert-warning">
											<i class="fa fa-bell-o"></i> <strong>App</strong> Please pay attention!
										</div>
										<div class="alert alert-danger">
											<i class="fa fa-bell"></i> <strong>App</strong> There was an error!
										</div>
										<div class="alert alert-info">
											<i class="fa fa-bolt"></i> <strong>App</strong> Info message!
										</div>
										<div class="alert alert-success">
											<i class="fa fa-bullhorn"></i> <strong>App</strong> Service restarted!
										</div>
									</li>-->
									<li class="divider"></li>
									<li>
										<a href="javascript:void(0)"><i class="fa fa-exclamation-triangle pull-right"></i>Notification Center</a>
									</li>
								</ul>
							</li>
							<!-- END Notifications -->

							<!-- Messages -->
							<li class="dropdown dropdown-messages">
								<a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
									<i class="fa fa-envelope-o"></i>
									<span class="badge badge-neutral display-none"></span>
									<!--<span class="badge badge-neutral">4</span>-->
								</a>
								<ul class="dropdown-menu dropdown-menu-right">
									<!--<li>
										<div class="media">
											<a class="pull-left" href="javascript:void(0)" data-toggle="tooltip" title="Newbie">
												<img src="img/placeholders/image_64x64_dark.png" alt="fakeimg" class="img-circle" />
											</a>
											<div class="media-body">
												<div class="media-heading clearfix"><span class="label label-success">1 min ago</span><a href="javascript:void(0)">Username</a></div>
												<div class="media">Lorem ipsum dolor sit amet, consectetur..</div>
											</div>
										</div>
									</li>
									<li class="divider"></li>
									<li>
										<div class="media">
											<a class="pull-left" href="javascript:void(0)" data-toggle="tooltip" title="Pro">
												<img src="img/placeholders/image_64x64_dark.png" alt="fakeimg" class="img-circle" />
											</a>
											<div class="media-body">
												<div class="media-heading clearfix"><span class="label label-success">2 hours ago</span><a href="javascript:void(0)">Username</a></div>
												<div class="media">Lorem ipsum dolor sit amet, consectetur..</div>
											</div>
										</div>
									</li>
									<li class="divider"></li>
									<li>
										<div class="media">
											<a class="pull-left" href="javascript:void(0)" data-toggle="tooltip" title="VIP">
												<img src="img/placeholders/image_64x64_dark.png" alt="fakeimg" class="img-circle" />
											</a>
											<div class="media-body">
												<div class="media-heading clearfix"><a href="javascript:void(0)">Username</a><span class="label label-success">3 days ago</span></div>
												<div class="media">Lorem ipsum dolor sit amet, consectetur..</div>
											</div>
										</div>
									</li>-->
									<li class="divider"></li>
									<li>
										<a href="javascript:void(0);"><i class="fa fa-envelope-o pull-right"></i>Message Center</a>
									</li>
								</ul>
							</li>
							<!-- END Messages -->
						</ul>
						<!-- END Header Nav -->

						<!-- Mobile Navigation, Shows up on tables and mobiles -->
						<ul class="navbar-nav-custom pull-left visible-xs visible-sm" id="mobile-nav">
							<li>
							<!-- It is set to open and close the main navigation on tables and mobiles. The class .navbar-main-collapse was added to aside#page-sidebar -->
								<a href="javascript:void(0)" data-toggle="collapse" data-target=".navbar-main-collapse">
									<i class="fa fa-bars"></i>
								</a>
							</li>
							<li class="divider-vertical"></li>
						</ul>
						<!-- END Mobile Navigation, Shows up on tables and on mobiles -->
					</div>
					<!-- END Header Nav Section -->
				</div>
				<!-- END div#row -->
			</header>
			<!-- END Header -->

			<!-- Left Sidebar -->
			<!-- In the PHP version you can set the following options from the config file -->
			<!-- Add the class .sticky for a sticky sidebar -->
			<aside id="page-sidebar" class="collapse navbar-collapse navbar-main-collapse">
				<div class="side-scrollable">
					<!-- Mini Profile -->
					<div class="mini-profile">
						<div class="mini-profile-options">
							<!-- Modal div is at the bottom of the page before including javascript code, we use .enable-tooltip class for the tooltip because data-toggle is used for modal -->
							<a href="#modal-user-account" class="badge badge-success enable-tooltip" role="button" data-toggle="modal" data-placement="right" title="Settings">
								<i class="gi gi-cogwheel"></i>
							</a>
							<a href="logout.php" class="badge badge-danger" data-toggle="tooltip" data-placement="right" title="Log out">
								<i class="fa fa-sign-out"></i>
							</a>
						</div>
						<a href="javascript:void(0);">
							<img src="img/template/avatar2.jpg" alt="Avatar" class="img-circle" />
						</a>
					</div>
					<!-- END Mini Profile -->

					<!-- Sidebar Tabs -->
					<div class="sidebar-tabs-con">
						<ul class="sidebar-tabs" data-toggle="tabs">
							<li class="active">
								<a href="#side-tab-menu"><i class="gi gi-list"></i></a>
							</li>
							<li>
								<a href="#side-tab-extra"><i class="gi gi-charts"></i></a>
							</li>
						</ul>
						<div class="tab-content">
							<div class="tab-pane active" id="side-tab-menu">
								<!-- Primary Navigation -->
								<nav id="primary-nav">
									<ul>
<?php foreach (array_values($BackendMenu->getElements()) as $element) {
				$elem = $element[N2f\BackendMenuStrings::ElementKey];
				$elem_class = array();

				if ($elem->isActive($_SERVER['REQUEST_URI'])) {
					$elem_class[] = "active";
				}

				if (count($element[N2f\BackendMenuStrings::SubElementKey]) > 0) {
					$elem_class[] = "menu-link";
				}

				?>										<li<?php if ($elem->isActive($_SERVER['REQUEST_URI'])) { ?> class="active"<?php } ?>>
											<a href="<?php echo($elem->href); ?>"<?php if (count($elem_class) > 0) { ?> class="<?php echo(implode(' ', $elem_class)); ?>"<?php } ?>><?php if ($elem->icon !== null) { ?><i class="gi <?php echo($elem->icon); ?>"></i><?php } ?><?php echo($elem->title); ?></a><?php if (count($element[N2f\BackendMenuStrings::SubElementKey]) > 0) { ?>
											<ul>
<?php foreach (array_values($element[N2f\BackendMenuStrings::SubElementKey]) as $subelem) { $sub = $subelem[N2f\BackendMenuStrings::ElementKey]; ?>												<li>
													<a href="<?php echo($sub->href); ?>"<?php if ($sub->isActive($_SERVER['REQUEST_URI'])) { ?> class="active"<?php } ?>><?php if ($sub->icon !== null) { ?><i class="gi <?php echo($sub->icon); ?>"></i><?php } ?><?php echo($sub->title); ?></a>
												</li>
<?php } ?>
											</ul>
<?php } ?>
										</li>
<?php } ?>
									</ul>
								</nav>
								<!-- END Primary Navigation -->
							</div>
							<div class="tab-pane tab-pane-side" id="side-tab-extra">
								<h5><i class="fa fa-users pull-right"></i><a href="javascript:void(0)" class="side-link">User Statistics</a></h5>
								<div><?php echo $userStats[BackendStrings::Stats_User_Confirmed]; ?> confirmed</div>
								<div><?php echo $userStats[BackendStrings::Stats_User_Total]; ?> total</div>
								<div><?php echo $userStats[BackendStrings::Stats_User_Logins]; ?> login(s) today</div>
							</div>
						</div>
					</div>
					<!-- END Sidebar Tabs -->
				</div>
				<!-- END Wrapper for scrolling functionality -->
			</aside>
			<!-- END Left Sidebar -->
