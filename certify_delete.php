<?php
/****************************************************************************/
/* ATutor																	*/
/****************************************************************************/
/* Copyright (c) 2002-2008 by Greg Gay, Joel Kronenberg & Heidi Hazelton	*/
/* Adaptive Technology Resource Centre / University of Toronto				*/
/* http://atutor.ca															*/
/*																			*/
/* This program is free software. You can redistribute it and/or			*/
/* modify it under the terms of the GNU General Public License				*/
/* as published by the Free Software Foundation.							*/
/****************************************************************************/
// $Id: certificate_delete.php 7208 2008-02-20 16:07:24Z cindy $

define('AT_INCLUDE_PATH', '../../include/');
require(AT_INCLUDE_PATH.'vitals.inc.php');
authenticate(AT_PRIV_CERTIFICATE);

if (isset($_POST['submit_no'])) {
	$msg->addFeedback('CANCELLED');
	header('Location: index_instructor.php');
	exit;
} else if (isset($_POST['submit_yes'])) {
	/* delete has been confirmed, delete this category */
	$certify_id	= intval($_POST['certify_id']);
	

  $query = 'DELETE tests FROM %scertify AS certify INNER JOIN %scertify_tests AS tests WHERE certify.certify_id=%d AND certify.certify_id=tests.certify_id';
	queryDB($query,  array(TABLE_PREFIX, TABLE_PREFIX, $_POST['certify_id']));
	$query1 = 'DELETE FROM %scertify WHERE %scertify.certify_id=%d';
	$certify_deleted = queryDB($query1, array(TABLE_PREFIX, TABLE_PREFIX, $_POST['certify_id']));
     
        
	write_to_log(AT_ADMIN_LOG_DELETE, 'certify', count($certify_deleted), sprintf($query1,TABLE_PREFIX, TABLE_PREFIX, $_POST['certify_id']));
         
	$msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');

	header('Location: index_instructor.php');
	exit;
}

//require('../../include/header.inc.php');
require(AT_INCLUDE_PATH.'header.inc.php');

$_GET['certify_id'] = intval($_GET['certify_id']); 

$query = "SELECT certify_id, title FROM %scertify c WHERE c.certify_id=%d";
$result = dbQuery($query,array(TABLE_PREFIX,$_GET[certify_id]));


if (count($result) == 0) {
	$msg->printErrors('ITEM_NOT_FOUND');
} else {
        foreach($rows as $row){
            $hidden_vars['title']= $row['title'];
            $hidden_vars['certify_id']	= $row['certify_id'];

            $confirm = array('DELETE_CERTIFICATE', $row['title']);
            $msg->addConfirm($confirm, $hidden_vars);

            $msg->printConfirm();
        }
}

require(AT_INCLUDE_PATH.'footer.inc.php');

?>