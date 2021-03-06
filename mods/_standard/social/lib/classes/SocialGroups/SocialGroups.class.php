<?php
/****************************************************************/
/* ATutor														*/
/****************************************************************/
/* Copyright (c) 2002-2009										*/
/* Inclusive Design Institute                                   */
/* http://atutor.ca												*/
/*                                                              */
/* This program is free software. You can redistribute it and/or*/
/* modify it under the terms of the GNU General Public License  */
/* as published by the Free Software Foundation.				*/
/****************************************************************/
// $Id$

require_once(AT_SOCIAL_INCLUDE.'classes/Activity.class.php');

/**
 * Social Groups, this is different from the Groups class in ATutor.
 */
class SocialGroups{

	/**
	 * constructor
	 */
	 function __construct(){}

	/**
	 * Adding a group
	 * @param	int		the group type specified in the table, social_groups_types
	 * @param	string	name of the group
	 * @param	string	description of the group
	 * @param	int		privacy setting, public is 0, private is 1.  Public means everyone can see the message board and users.  Private is the opposite
	 * @return	the id of this new group if succeded, false otherwise.
	 */
	 function addGroup($type_id, $name, $description, $privacy){
		 global $addslashes;

		 $type_id = intval($type_id);
		 $name = $addslashes($name);
		 $description = $addslashes($description);
		 $privacy = intval($privacy);
		 $member_id = $_SESSION['member_id'];

		 $sql = "INSERT INTO %ssocial_groups (`member_id`, `type_id`, `name`, `description`, `privacy`, `created_date`, `last_updated`) VALUES (%d, %d, '%s', '%s', %d, NOW(), NOW())";
		 $result = queryDB($sql, array(TABLE_PREFIX, $member_id, $type_id, $name, $description, $privacy));
		 $group_id = at_insert_id();
		 
		 if ($result > 0){
			 //add it to the group member table
			 $sql = "INSERT INTO %ssocial_groups_members (group_id, member_id) VALUES (%d, %d)";
			 $result = queryDB($sql, array(TABLE_PREFIX, $group_id, $_SESSION['member_id']));

			 if ($result > 0){
				$act = new Activity();		
				$str1 = _AT('has_added_group', '<a href="'. url_rewrite(AT_SOCIAL_BASENAME . 'groups/view.php?id='.$group_id).'">'.htmlentities_utf8($name)).'</a>';
				$act->addActivity($member_id, $str1);
				unset($act);
			 }
			 return $group_id;
		 }
		 return false;
	 }

	/**
	 * Removing a group, invovles removing everything related to this group.
	 * @param	int		the group id
	 */
	 function removeGroup($id){
		 $social_group = new SocialGroup($id);

		 //remove group activities
		 $status = $social_group->removeGroupActivities();

		 //remove group forums
		 $status &= $social_group->removeGroupForums();

		 //remove group members
		 $status &= $social_group->removeGroupMembers();

		 //remove group requests
		 $status &= $social_group->removeGroupRequests();

		 //remove group invitations
		 $status &= $social_group->removeGroupInvitations();

		 //remove message board
		 $status &= $social_group->removeAllMessages();

		 //remove group logo
		 $status &= $social_group->removeGroupLogo();

		 //remove groups 
		 $sql = 'DELETE FROM %ssocial_groups WHERE id=%d';
		 $status &= queryDB($sql, array(TABLE_PREFIX, $id));
	 }


	/**
	 * Update a group
	 * @param	int		group id
	 * @param	int		member_id, to update the owner of this group
	 * @param	int		the group type specified in the table, social_groups_type
	 * @param	string	name of the group
	 * @param	string	description of the group
	 * @param	string	the filename of the logo
	 * @param	string	group privacy, private for 1, public for 0
	 */
	 function updateGroup($group_id, $member_id, $type_id, $name, $description, $logo, $privacy){
		 global  $addslashes;

		 $group_id = intval($group_id);
		 $member_id = intval($member_id);
		 $type_id = intval($type_id);
		 $name = $addslashes($name);
		 $description = $addslashes($description);
		 $logo = $addslashes($logo);
		 $privacy = ($privacy=='private')?1:0;
		 //only include logo sql iff it is not empty, otherwise the old entry will be erased.
		 if ($logo!=''){
			 $logo_sql = "`logo`='$logo', ";
		 } else {
			 $logo_sql = '';
		 }

		 $sql = "UPDATE %ssocial_groups SET `member_id`=%d, `type_id`=%d, ".$logo_sql."`name`='%s', `privacy`=%d, `description`='%s', `last_updated`=NOW() WHERE id=%d";
		 $result = queryDB($sql, array(TABLE_PREFIX, $member_id, $type_id, $name, $privacy, $description, $group_id));
		 if ($result > 0){
			 $act = new Activity();		
			 $str1 = _AT('has_updated_group', '<a href="'. url_rewrite(AT_SOCIAL_BASENAME . 'groups/view.php?id='.$group_id).'">'.htmlentities_utf8($name)).'</a>';
			 $act->addActivity($member_id, $str1);
			 unset($act);
			 return true;
		 } 
		 return false;
	 }


	/**
	 * Get all the group types
	 * @return	list of group types
	 */
	 function getAllGroupType(){
		 $group_types = array();

		 $sql = 'SELECT * FROM %ssocial_groups_types';
		 $rows_types = queryDB($sql, array(TABLE_PREFIX));
		 if (count($rows_types) > 0){
		    foreach($rows_types as $row){
				$group_types[$row['type_id']] = $row['title'];
			 }
		 }
		 return $group_types;
	 }

	  
	/**
	 * Get the group infromation from the sql	
	 * @param	int		group id
	 * @return mixed	SocialGroup obj
	 */
	 function getGroup($group_id){
		$socialGroup = new SocialGroup($group_id);
		return $socialGroup;
	 }

	
	/**
	 * Get ALL of a person's groups
	 * @param	int	the groups that this member is in.
	 * @param	int	the index of which the entry to get
	 */
	 function getMemberGroups($member_id, $offset=-1){
		 $my_groups = array();

		 $sql = 'SELECT group_id FROM %ssocial_groups_members WHERE member_id=%d';
		 if ($offset >= 0){
			$sql .= " LIMIT $offset, ".SOCIAL_GROUP_MAX;
		 }

		 $rows_members = queryDB($sql, array(TABLE_PREFIX, $member_id));
		 if (count($rows_members > 0)){
		    foreach($rows_members as $row){
				$my_groups[] = $row['group_id'];
			 }
		 }
		 return $my_groups;
	 }


	 /**
	  * Search
	  * @param	string	query string for the search
	  * @param	int	the index of which the entry to get
	  * @param	string	filters
	  */
	 function search($query, $offset=-1, $filters=''){
		/* Perform a simple search for now
		 * That searches only the title? 
		 * Use Joel's search idea? Point based system search? The Google's idea?
		 *
		 * Idea:
		 * 1. Get all results that matches the query separtated by space
		 * 2. Give points to the result that has more match, or whatever our "point system" is
		 * 3. Sort it in the order of most points to the least points.  
		 */
		 global  $addslashes;

		 $sql = 'SELECT * FROM %ssocial_groups';
		 if ($query!=''){
			 $search_result = array();
			 $query = $addslashes(trim($query));
			 $words = explode(' ', $query);
			 foreach($words as $piece){
				$extra .= "`name` LIKE '%%$piece%%' OR ";
				$extra .= "`description` LIKE '%%$piece%%' OR ";
			 }
			 $extra = substr($extra, 0, -3);
			 
			 $sql .= ' WHERE '.$extra;
		 }

		 $rows_groups = queryDB($sql, array(TABLE_PREFIX));
	 
		if ($rows_groups){
		    foreach($rows_groups as $row){
				$search_result[$row['id']]['obj'] = new SocialGroup($row['id']);
				$search_result[$row['id']]['weight'] = $this->inQuery($words, $row['name']);
			}
		 }
		 if (!empty($search_result)){
			 uasort($search_result, array($this, 'search_cmp'));
			 $search_result = array_reverse($search_result);

			 //for paginator
			 if ($offset >= 0){
				$search_result = array_slice($search_result, $offset, SOCIAL_GROUP_MAX);
			 }
		 }

		 return $search_result;
	 }


	 /**
	  * Return the counts of word appeareance in the given string.  
	  * @param	array	the string that the user typed in to search for
	  * @param	string	the name of the group
	  */
	 function inQuery($words, $str){
		 //if either of the input is empty, there is no comparison thus no count.
		 if (empty($words) || $str==''){
			 return 0;
		 }
		 $count = 0;
		 foreach ($words as $index=>$word){
			 if (trim($word)=='') continue;
			 $count += substr_count($str, strtolower($word));
		 }
		 return $count;
	 }
	

	function search_cmp($row1, $row2){
		if ($row1['weight'] == $row2['weight']) return 0;
		return ($row1['weight'] < $row2['weight']) ? -1 : 1;
	}
}
?>