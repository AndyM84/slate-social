<?php

	ini_set('display_errors', 'On');

	define('CORE_PATH', '../');
	require(CORE_PATH . 'inc/Core.php');

	global $SiteSettings, $Db, $Io, $User, $Page;

	if (!isAuthenticated($Db)) {
		header('Location: ./logout.php');
		exit;
	}

	$Page = new N2f\Page();
	$User = new User($Db, $_SESSION[BackendStrings::Session_UserIdKey]);
	$userRepo = new Users($Db);
	$users = $userRepo->getAllUsers();

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
					<li><a href="">Users</a></li>
				</ul>
				<!-- END Breadcrumb -->

				<div class="row">
					<div class="col-md-4">
						<div class="block block-themed">
							<div class="block-title">
								<h4 id="user-form">User Form</h4>
							</div>
							<div class="block-content">
								<input type="hidden" id="user-action" name="user-action" value="create" />
								<input type="hidden" id="user-id" name="user-id" value="0" />
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon">Username</span>
										<input type="text" id="user-username" name="user-username" class="form-control" />
									</div>
								</div>
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon">Email Address</span>
										<input type="email" id="user-email" name="user-email" class="form-control" />
									</div>
								</div>
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon">Password</span>
										<input type="password" id="user-password1" name="user-password1" class="form-control" />
									</div>
								</div>
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon">Confirm Password</span>
										<input type="password" id="user-password2" name="user-password2" class="form-control" />
									</div>
								</div>
								<div class="form-group form-actions">
									<div class="text-right">
										<button type="button" id="btn-reset" class="btn btn-danger" onclick="javascript:resetUserForm();"><i class="fa fa-repeat"></i> Reset</button>
										<button type="button" id="btn-submit" class="btn btn-success" onclick="javascript:submitUserForm('<?php echo($_SESSION[BackendStrings::Session_ApiKey]); ?>');"><i class="fa fa-check"></i> Create</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-8">
						<div class="block-section">
							<table id="user-list" class="table table-bordered table-hover">
								<thead>
									<tr>
										<th><i class="fa fa-user"></i> Username</th>
										<th><i class="fa fa-envelope-o"></i> Email</th>
										<th>Date Joined</th>
										<th>Last Login</th>
										<th><i class="fa fa-bolt"></i></th>
									</tr>
								</thead>
								<tbody>
<?php if (count($users) < 1): ?>									<tr>
										<td colspan="5">No users found</td>
									</tr>
<?php else: ?><?php foreach (array_values($users) as $usr): ?>									<tr>
										<td><?php echo($usr->username); ?></td>
										<td><a href="mailto:<?php echo($usr->email); ?>"><?php echo($usr->email); ?></a></td>
										<td><?php echo(($usr->dateJoined !== null) ? $usr->dateJoined->format('m/d/Y') : "N/A"); ?></td>
										<td><?php echo(($usr->lastLogin !== null) ? $usr->lastLogin->format('m/d/Y') : "N/A"); ?></td>
										<td>
											<div class="btn-group">
												<a href="javascript:void(0);" data-toggle="tooltip" title="Edit" class="btn btn-xs btn-success" onclick="javascript:startUserEditForm(<?php echo($usr->id); ?>, '<?php echo($_SESSION[BackendStrings::Session_ApiKey]); ?>');"><i class="fa fa-pencil"></i></a>
												<a href="javascript:void(0);" data-toggle="tooltip" title="Delete" class="btn btn-xs btn-danger" onclick="javascript:deleteUser('<?php echo($usr->username); ?>', <?php echo($usr->id); ?>, '<?php echo($_SESSION[BackendStrings::Session_ApiKey]); ?>');"><i class="fa fa-times"></i></a>
											</div>
										</td>
									</tr>
<?php endforeach; ?><?php endif; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
<?php $Page->startBlock('scripts'); ?>
		<script type="text/javascript">
			var submitButtonPrefix = '<i class="fa fa-check"></i>';

			var getUserFormData = function () {
				return {
					"action": $('#user-action').val(),
					"id": $('#user-id').val(),
					"username": $('#user-username').val(),
					"email": $('#user-email').val(),
					"password1": $('#user-password1').val(),
					"password2": $('#user-password2').val()
				}
			};

			var userFormDataToApiData = function (data, token) {
				return {
					"token": token,
					"id": data.id,
					"username": data.username,
					"email": data.email,
					"new-password": data.password1,
					"new-password-2": data.password2
				};
			}

			var resetUserFormData = function () {
				$('#user-action').val("create");
				$('#user-id').val("0");
				$('#user-username').val("");
				$('#user-email').val("");
				$('#user-password1').val("");
				$('#user-password2').val("");

				return;
			};

			var resetUserForm = function () {
				var submit = $('#btn-submit');
				submit.html(submitButtonPrefix + ' Create');
				submit.removeAttr('disabled');

				resetUserFormData();

				return;
			};

			var startUserEditForm = function (uid, token) {
				$.getJSON("/api/1/account", { "id": uid, "token": token }, function (data) {
					var submit = $('#btn-submit');
					submit.html(submitButtonPrefix + ' Edit');

					$('#user-action').val('edit');
					$('#user-id').val(uid);
					$('#user-username').val(data.username);
					$('#user-email').val(data.email);

					return;
				});

				return;
			};

			var deleteUser = function (username, uid, token) {
				if (confirm("Are you sure you want to delete " + username + "?")) {
					$.ajax({
						type: "POST",
						url: "/api/1/account/delete",
						data: JSON.stringify({ "token": token, "id": uid }),
						dataType: "json",
						success: function (data) {
							location.href = "/back/users.php?rando=" + Math.random();
						},
						error: function (xhr, txtStatus, errorThrown) {
							alert(xhr.responseText);
						}
					});
				}
			};

			var submitUserForm = function (token) {
				var data = getUserFormData();
				$('#btn-submit').attr('disabled', 'disabled');

				if (data.action == 'create') {
					if (data.password1.length > 0 && data.password1 === data.password2) {
						$.ajax({
							type: "POST",
							url: "/api/1/account/create",
							data: JSON.stringify(userFormDataToApiData(data, token)),
							dataType: "json",
							success: function (data) {
								location.href = "/back/users.php?rando=" + Math.random();
							},
							error: function (xhr, txtStatus, errorThrown) {
								alert(xhr.responseText);
								$('#btn-submit').removeAttr('disabled');
							}
						});
					} else {
						alert("You must enter a valid set of passwords to create a user");
					}
				} else {
					$.ajax({
						type: "POST",
						url: "/api/1/account",
						data: JSON.stringify(userFormDataToApiData(data, token)),
						dataType: "json",
						success: function (data) {
							resetUserForm();
						},
						error: function (xhr, txtStatus, errorThrown) {
							alert(xhr.responseText);
							$('#btn-submit').removeAttr('disabled');
						}
					});
				}

				return;
			};

			$(function () {
				$('#user-list').dataTable();
			});
		</script>
<?php $Page->endBlock('scripts'); ?>
			<!-- END Page Content -->
<?php $Io->load('~/back/tpl/footer.php', true); ?>