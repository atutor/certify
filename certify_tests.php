<?php

define('AT_INCLUDE_PATH', '../../include/');
require (AT_INCLUDE_PATH.'vitals.inc.php');
authenticate(AT_PRIV_CERTIFY);

$certify_id = '';
if (isset($_POST['certify_id'])) {
    $certify_id = $addslashes($_POST['certify_id']);
} else if (isset($_GET['certify_id'])) {
    $certify_id = $addslashes($_GET['certify_id']);
}

if (isset($_POST['edit'])) { // Commit edit

	// STORE EDITS

	$certify_selected = $_POST['selected'];

	$update_remove = array();
	$update_add = array();

	$certify_tests = fetchTestList($certify_id);
	foreach ($certify_tests as $test_id => $test) {
		if ($test['selected'] and !isset($certify_selected[$test_id])) {
			$update_remove[] = $test_id;
		} else if (!$test['selected'] and isset($certify_selected[$test_id])) {
			$update_add[] = $test_id;
		}

	}

	if (count($update_add) > 0) {
		$sqlrows = array();
		foreach ($update_add as $testid) {
			$sqlrows[] = '('.$certify_id.",".$testid.')';
		}
		
                $query = "INSERT INTO %scertify_tests
				(certify_id, 
				 test_id) 
					VALUES %s";
			
                $params = array_merge(array(TABLE_PREFIX, implode(',',$sqlrows))) ;
		$result = queryDB($query, $params);   

                write_to_log(AT_ADMIN_LOG_INSERT, 'certify', count($result), printf($query, TABLE_PREFIX, implode(',',$sqlrows)));


	}

	if (count($update_remove) > 0) {
            $query = "DELETE FROM %scertify_tests
                            WHERE certify_id = $certify_id
                            AND test_id IN (%s)";

            $params = array(TABLE_PREFIX, implode(",",$update_remove));
            $result = queryDB($query, $params);

            write_to_log(AT_ADMIN_LOG_DELETE, 'certify', count($result), printf($query, TABLE_PREFIX, implode(",",$update_remove)));


	}

	$msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');


} else if (isset($_POST['cancel']) || strlen($certify_id) == 0) {
	
	// CANCEL
	
	// FIXME: Is really a "return" function - need a new text for the button?

	$msg->addFeedback('CANCELLED');
	header('Location: index_instructor.php');
	exit;

}


// FETCH INFO FOR VIEW	

$certify_tests = fetchTestList($certify_id);

require(AT_INCLUDE_PATH.'header.inc.php'); 
$msg->printAll();

?>

<fieldset>
<legend>
For instructor to edit certificate
</legend>
<form name="certifydetails" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<input type="hidden" name="certify_id" value="<?php echo $certify_id; ?>">

<table class="data" summary="" rules="cols">

<thead>
<tr>
	<th scope="col"></th>
	<th scope="col"><?php echo _AT('certify_title'); ?></a></th>
</tr>
</thead>

<tfoot>
<tr>
	<td colspan="2">
		<input type="submit" name="edit" value="<?php echo _AT('save'); ?>" /> 
		<input type="submit" name="cancel" value="<?php echo _AT('cancel'); ?>" />
	</td>
</tr>
</tfoot>

<tbody>
	<?php foreach ($certify_tests as $id => $test) { ?>
		<tr>
			<td><input type="checkbox" <?php if ($test['selected']) { echo 'checked="checked" '; } ?> name="selected[<?php echo $id ?>]" value="1"></td>
			
			<td><label for=""><?php echo $test['title']; ?></label></td>
			
		</tr>
	
	<?php } ?>
	<?php if (count($certify_tests)==0) { ?>
	
		<tr>
			<td colspan="2"><?php echo _AT('none_found'); ?></td>
		</tr>
	
	<?php } ?>

</tbody>

</table>

</form>
</fieldset>


<?php

require (AT_INCLUDE_PATH.'footer.inc.php');

function fetchTestList($certify_id) {
	global $db, $_SESSION;

	// Fetch all tests for course
	// FIXME: Need to filter out tests that doesn't have a pass criteria
        
        $query =  "SELECT test_id, title FROM %stests WHERE course_id=%d";
        $params = array(TABLE_PREFIX, $_SESSION['course_id']);
	$result = queryDB($query, $params);
	
        
	$certify_tests = array();
	
        foreach($result as $row){  
		$this_test = array();
		$this_test['title'] = $row['title'];
		$this_test['selected'] = false;
		$certify_tests[$row['test_id']] = $this_test;
	}
	
	// Fetch associated tests
        $query =  "SELECT test_id FROM %scertify_tests ";
	$query .= "WHERE %scertify_tests.certify_id=%d";
	$params = array(TABLE_PREFIX, TABLE_PREFIX, $certify_id);
        $result = queryDB($query, $params);

        foreach($result as $row){
		$certify_tests[$row['test_id']]['selected'] = true;
	}
	return $certify_tests;

}

?>
