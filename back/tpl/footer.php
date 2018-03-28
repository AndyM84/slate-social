<?php global $SiteSettings, $Db, $Io, $User, $Page; ?>
			<!-- Footer -->
			<footer>
				<div class="pull-right">
					<?php echo $SiteSettings[SettingsStrings::SiteTitle]; ?> v<?php echo $SiteSettings[SettingsStrings::Version]; ?>
				</div>
				<div class="pull-left">
					Copyright &copy; 2017-<?php echo(date('Y')); ?> <?php echo $SiteSettings[SettingsStrings::SiteTitle]; ?>
				</div>
			</footer>
			<!-- END Footer -->
		</div>
		<!-- END Page Container -->

		<!-- Scroll to top link, check main.js - scrollToTop() -->
		<a href="#" id="to-top"><i class="fa fa-chevron-up"></i></a>

		<!-- User Modal Account, appears when clicking on 'User Settings' link found on user dropdown menu (header, top right) -->
		<div id="modal-user-account" class="modal fade">
			<!-- Modal Dialog -->
			<div class="modal-dialog">
				<!-- Modal Content -->
				<div class="modal-content">
					<!-- Modal Body -->
					<div class="modal-body remove-padding">
						<!-- Modal Tabs -->
						<div class="block-tabs block-themed">
							<div class="block-options">
								<a href="javascript:void(0)" class="btn btn-option" data-dismiss="modal">X</a>
							</div>
							<ul class="nav nav-tabs" data-toggle="tabs">
								<li class="active"><a href="#modal-user-account-account"><i class="fa fa-cog"></i> Account</a></li>
							</ul>
							<div class="tab-content">
								<!-- Account Tab Content -->
								<div class="tab-pane active" id="modal-user-account-account">
									<form action="javascript:void(0);" method="post" class="form-horizontal" onsubmit="return false;">
										<div class="form-group">
											<label class="control-label col-md-4">Username</label>
											<div class="col-md-8">
												<p class="form-control-static"><?php echo($User->username); ?></p>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-4" for="modal-account-email">Email</label>
											<div class="col-md-8">
												<input type="text" id="modal-account-email" name="modal-account-email" class="form-control" value="<?php echo($User->email); ?>" />
											</div>
										</div>
										<h4 class="sub-header">Change Password</h4>
										<div class="form-group">
											<label class="control-label col-md-4" for="modal-account-pass">Current Password</label>
											<div class="col-md-8">
												<input type="password" id="modal-account-pass" name="modal-account-pass" class="form-control" />
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-4" for="modal-account-newpass">New Password</label>
											<div class="col-md-8">
												<input type="password" id="modal-account-newpass" name="modal-account-newpass" class="form-control" />
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-4" for="modal-account-newrepass">Retype New Password</label>
											<div class="col-md-8">
												<input type="password" id="modal-account-newrepass" name="modal-account-newrepass" class="form-control" />
											</div>
										</div>
									</form>
								</div>
								<!-- END Account Tab Content -->
							</div>
						</div>
						<!-- END Modal Tabs -->
					</div>
					<!-- END Modal Body -->

					<!-- Modal footer -->
					<div class="modal-footer">
						<button class="btn btn-success" onclick="javascript: saveAccountModal('<?php echo($_SESSION[BackendStrings::Session_ApiKey]); ?>', <?php echo($User->id); ?>);"><i class="fa fa-floppy-o"></i> Save</button>
					</div>
					<!-- END Modal footer -->
				</div>
				<!-- END Modal Content -->
			</div>
			<!-- END Modal Dialog -->
		</div>
		<!-- END User Modal Settings -->

		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script>!window.jQuery && document.write(unescape('%3Cscript src="js/vendor/jquery-1.11.0.min.js"%3E%3C/script%3E'));</script>
		<script src="js/vendor/bootstrap.min.js"></script>
		<script src="js/plugins.js"></script>
		<script src="js/main.js"></script>
		<!-- Google Maps API + Gmaps Plugin - Remove 'http:' if you have SSL -->
		<script src="//maps.google.com/maps/api/js?key=AIzaSyCyTL9VRCs-_Yl7i-mqTdrR4TsPUr2oWKU"></script>
		<script src="js/helpers/gmaps.min.js"></script>
<?php if ($Page && $Page !== null && $Page instanceof N2f\Page) { $Page->renderBlock('scripts', false, ""); } ?>
	</body>
</html>