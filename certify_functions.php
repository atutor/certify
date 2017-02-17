<?php

function getCertificateProgress($member_id, $certify_id) {

	global $db;
	
	$certificate = array();
	$progress = 0;

// Fetch associated tests


         
        
        $query =  'SELECT %scertify_tests.*, %stests.* FROM %scertify_tests ';
	$query .= 'INNER JOIN %stests ON %scertify_tests.test_id = %stests.test_id ';
	$query .= "WHERE %scertify_tests.certify_id=%d";

        $params = array(TABLE_PREFIX,TABLE_PREFIX, TABLE_PREFIX, TABLE_PREFIX, TABLE_PREFIX, TABLE_PREFIX, TABLE_PREFIX,
                $certify_id);
	$result = queryDB($query, $params);
	
        
	$certificate['tests'] = array();

	foreach($result as $row) {
                $certificate['tests'][$row['test_id']] = array();
		$certificate['tests'][$row['test_id']]['passscore'] = $row['passscore'];
		$certificate['tests'][$row['test_id']]['passpercent'] = $row['passpercent'];
		$certificate['tests'][$row['test_id']]['out_of'] = $row['out_of'];
		$certificate['tests'][$row['test_id']]['final_score'] = 0;

		// Convert percent scored tests to scores
		if ($certificate['tests'][$row['test_id']]['passpercent'] > 0) {
			$certificate['tests'][$row['test_id']]['passscore'] = $certificate['tests'][$row['test_id']]['out_of'] * $certificate['tests'][$row['test_id']]['passpercent'] / 100;
			$certificate['tests'][$row['test_id']]['passpercent'] = 0;
		}
	}


// Calculate new scores for each test
      
        $query1 =  '
		SELECT %scertify_tests.test_id, %stests_results.* 
		FROM %scertify_tests
		RIGHT JOIN %stests_results ON %stests_results.test_id = %scertify_tests.test_id
		WHERE %stests_results.member_id = %d
		AND %scertify_tests.certify_id = %d
	';

        $params1 = array(TABLE_PREFIX, TABLE_PREFIX, TABLE_PREFIX, TABLE_PREFIX, TABLE_PREFIX, TABLE_PREFIX, 
            TABLE_PREFIX, $member_id, TABLE_PREFIX, $certify_id);
	$result1 = queryDB($query1, $params1);
        
        foreach($result1  as $row){
            if (!isset($certificate['tests'][$row['test_id']]['final_score']) || 
                    $certificate['tests'][$row['test_id']]['final_score'] < $row['final_score'])
            {$certificate['tests'][$row['test_id']]['final_score'] = $row['final_score'];}

	}

// Calculate new percentages for certificate.


	$certificate['available_score'] = 0;
	$certificate['achieved_score'] = 0;

	if (isset($certificate['tests'])) {
		foreach ($certificate['tests'] as $certify_testid => &$test) {
			if ($test['final_score'] >= $test['passscore']) {
				$certificate['achieved_score'] += $test['passscore'];
			} else {
				$certificate['achieved_score'] += $test['final_score'];
			}
			$certificate['available_score'] += $test['passscore'];
			
			if (!isset($test['final_score'])) {
				
			}

		}
	}
	

	if ($certificate['available_score'] != 0) {
		$certificate['progress'] = $certificate['achieved_score'] * 100 / $certificate['available_score'];
	} else {
		$certificate['progress'] = 0;
	}

	return $certificate['progress'];
}






?>