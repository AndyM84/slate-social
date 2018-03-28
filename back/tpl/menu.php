<?php

	global $BackendMenu;
	$BackendMenu = new N2f\BackendMenuHelper();
	
	$BackendMenu->addElement('dashboard', 'Dashboard', 'dashboard.php', '/\/back\/dashboard\.php/i', IconStrings::GiDisplay);
	$BackendMenu->addElement('users', 'Users', 'users.php', '/\/back\/users\.php/i', IconStrings::GiUser);
