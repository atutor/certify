<?php
/*******
 * this function named [module_name]_delete is called whenever a course content is deleted
 * which includes when restoring a backup with override set, or when deleting an entire course.
 * the function must delete all module-specific material associated with this course.
 * $course is the ID of the course to delete.
 */

function cerify_delete($course) {
	global $db;


	$query = 'DELETE tests FROM %scertify AS certify INNER JOIN %scertify_tests AS tests WHERE certify.course=%d AND certify.certify_id=tests.certify_id';
	queryDB($query, array(TABLE_PREFIX, TABLE_PREFIX, $course ));
	$query1 = 'DELETE FROM %scertify AS certify WHERE certify.course=%d';
	queryDB($query1, array(TABLE_PREFIX, TABLE_PREFIX, $course ));
}

?>