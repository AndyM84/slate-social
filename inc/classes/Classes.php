<?php

	global $Io;

	$Io->load('~/inc/classes/ClassField.php');
	$Io->load('~/inc/classes/BaseClass.php');

	$files = $Io->getFolderFiles("~/inc/classes");

	if (count($files) > 0) {
		foreach (array_values($files) as $file) {
			if (substr($file, -8) == '.cls.php') {
				$Io->load($file);
			}
		}
	}
