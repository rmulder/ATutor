<?php
/****************************************************************/
/* ATutor														*/
/****************************************************************/
/* Copyright (c) 2002-2004 by Greg Gay & Joel Kronenberg        */
/* Adaptive Technology Resource Centre / University of Toronto  */
/* http://atutor.ca												*/
/*                                                              */
/* This program is free software. You can redistribute it and/or*/
/* modify it under the terms of the GNU General Public License  */
/* as published by the Free Software Foundation.				*/
/****************************************************************/

if (isset($_POST['move_action'])) {
	$dest = $_POST['dest'];

	if (isset($_POST['listofdirs'])) {
		$dirs = explode(',',$_POST['listofdirs']);
		$count = count($dirs);
		
		for ($i = 0; $i < $count; $i++) {
			$source = $dirs[$i];
			@rename($current_path.$pathext.$source, $dest.$source);
		}
		$msg->addFeedback('DIRS_MOVED');
	}
	if (isset($_POST['listoffiles'])) {
		$files = explode(',',$_POST['listoffiles']);
		$count = count($files);

		for ($i = 0; $i < $count; $i++) {
			$source = $files[$i];
			@rename($current_path.$pathext.$source, $current_path.$dest.$source);
		}

		$msg->addFeedback('MOVED_FILES');
	}

}

if (isset($_POST['movefilesub'])) {
	if (!is_array($_POST['check'])) {
		// error: you must select a file/dir 
		$msg->addError('NO_FILE_SELECT');
	} else {
		echo '<h3>';
		if ($_SESSION['prefs'][PREF_CONTENT_ICONS] != 2) {
			echo '&nbsp;<img src="images/icons/default/file-manager-large.gif"  class="menuimageh3" width="42" height="38" alt="" /> ';
		}
		if ($_SESSION['prefs'][PREF_CONTENT_ICONS] != 1) {
			echo _AT('file_manager_move')."\n";
		}
		echo '</h3>'."\n";
		if ($_POST['dir_list_top'] == $_POST['dir_list_bottom']) {
			$dest = $_POST['dir_list_top'];
		} else if (($_POST['dir_list_top'] != "")&& ($_POST['dir_list_bottom'] == "") ) {
			$dest =  $_POST['dir_list_top'];
		} else if (($_POST['dir_list_bottom'] != "") && ($_POST['dir_list_top'] == "")){
			$dest =  $_POST['dir_list_bottom'];
		} else {
			$dest = $_POST['dir_list_top'];
		}
		/* find the files and directories to be copied */
		if (isset($_POST['check'])) {
			$count = count($_POST['check']);
			$countd = 0;
			$countf = 0;
			for ($i=0; $i<$count; $i++) {
				if (is_dir($current_path.$pathext.$_POST['check'][$i])) {
					$dirs[$countd] = $_POST['check'][$i];
					$countd++;
				} else {
					$files[$countf] = $_POST['check'][$i];
					$countf++;
				}
			}
		} else {
			if (isset($_POST['listoffiles'])) 
				$files = explode(',',$_POST['listoffiles']);
			if (isset($_POST['listofdirs'])) 
				$dirs = explode(',',$_POST['listofdirs']);
		}

		echo '<form name="form1" action="'.$_SERVER['PHP_SELF'].'?pathext='.urlencode($pathext).'" method="post">'."\n";
		echo '<input type="hidden" name="pathext" value="'.$pathext.'" />'."\n";
		echo '<input type="hidden" name="dest" value="'.$dest.'" />'."\n";
		if (isset($files)) {
			$list_of_files = implode(',', $files);
			echo '<input type="hidden" name="listoffiles" value="'.$list_of_files.'" />'."\n"; 
			$msg->addWarning(array('CONFIRM_FILE_MOVE', $list_of_files));
		}
		if (isset($dirs)) {
			$list_of_dirs = implode(',', $dirs);
			echo '<input type="hidden" name="listofdirs" value="'.$list_of_dirs.'" />'."\n";
			$msg->addWarning(array('CONFIRM_DIR_MOVE', $list_of_dirs));
		}
		$msg->printWarnings();

		echo '<input type="submit" name="move_action" value="'._AT('move').'" /><input type="submit" name="cancel" value="'._AT('cancel').'"/></p>'."\n";
		echo '</form>';
	}		
} 

?>
