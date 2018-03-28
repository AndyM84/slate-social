<?php

	global $Io;

	$Io->load('~/inc/repos/BaseRepo.php');

	$files = $Io->getFolderFiles("~/inc/repos");

	if (count($files) > 0) {
		foreach (array_values($files) as $file) {
			if (substr($file, -8) == '.rpo.php') {
				$Io->load($file);
			}
		}
	}
