<?php
define('AT_INCLUDE_PATH', '../../include/');

require (AT_INCLUDE_PATH.'vitals.inc.php');
require 'certify_functions.php';

// authenticate(AT_PRIV_CERTIFY); // TODO: Find correct privileges

if (isset($_GET['certify_id']))
	$certify_id	= $addslashes($_GET['certify_id']);

$filebase = AT_CONTENT_DIR .'certify/cert_'.$_SESSION['member_id'].'_'.$certify_id.'.';
$templatefile = AT_CONTENT_DIR .'certify/template_'.$certify_id.'.pdf';
if (file_exists($templatefile)) {
	$template = $templatefile;
} else {
	$template = dirname(realpath('test.pdf')).'/mods/certify/test.pdf';
}


if (!file_exists($filebase.'pdf')) {
	// Fetch cached scores
		       
        $query =  '
	
		SELECT
			%scourses.title AS coursetitle,
			%smembers.first_name,
			%smembers.second_name,
			%smembers.last_name,
			%smembers.email,
			%scertify.title AS certifytitle
	
		FROM %smembers
		INNER JOIN %scertify ON %scertify.certify_id = %d
		INNER JOIN %scourses ON %scertify.course_id = %scourses.course_id
	
		WHERE %smembers.member_id = %d 
	';
        
	//echo $sql;
	//exit();

        $rows = queryDB($query, array(TABLE_PREFIX, TABLE_PREFIX, TABLE_PREFIX, TABLE_PREFIX, TABLE_PREFIX, TABLE_PREFIX, TABLE_PREFIX, TABLE_PREFIX, TABLE_PREFIX,
            $certify_id, TABLE_PREFIX, TABLE_PREFIX, TABLE_PREFIX, TABLE_PREFIX, $_SESSION['member_id']));
        
	
	
	if ( !$row = reset($rows)) { // Probably a hack attempt, so aborting should be sufficient
		echo "Oh no you don't!";
		exit();
	}

	if ( getCertificateProgress($_SESSION['member_id'], $certify_id)<100 ) { // Probably a hack attempt, so aborting should be sufficient
		echo "Oh no you don't!";
		exit();
	}

	// Generate FDF

	$params = array(
		'course_name'	=> iconv("UTF-8", "ISO-8859-1//IGNORE", $row['coursetitle']),
		'full_name'		=> iconv("UTF-8", "ISO-8859-1//IGNORE", implode(' ',array($row['first_name'],$row['second_name'],$row['last_name']))),
		'test_name'		=> iconv("UTF-8", "ISO-8859-1//IGNORE", $row['certifytitle']),
		//'score'			=> iconv("UTF-8", "ISO-8859-1//IGNORE", 'Bestått'),
		'issued_date'	=> iconv("UTF-8", "ISO-8859-1//IGNORE", date('F j, Y'))
	);
	
	$fdfparams = '';
	foreach ($params as $key => $value) {
		$fdfparams .= '<</T('.$key.')/V('.$value.')>>';
	}
	
	//$filename = tempnam('', 'atutor_certify');
	$filename = $filebase.'fdf';

	$handle = fopen($filename,'wb');
	fwrite($handle,"%FDF-1.2
%\xE2\xE3\xCF\xD3
1 0 obj
<< 
/FDF << /Fields [ ".$fdfparams."] 
/F (http://www.helsekompetanse.no/test.pdf) /ID [ <".md5(time()).">
] >> 
>> 
endobj
trailer
<<
/Root 1 0 R 

>>
%%EOF");
	fclose($handle);
	
	// Flatten with PDF

	$output = array();
	$return_var = 0;
	
	/////  ADJUST THE PATH TO YOUR PDFTK IF NECESSARY
	//$exec = '/usr/local/bin/pdftk '.$template.' fill_form '.$filename.' output '.$filebase.'pdf flatten';
	$exec = '/usr/bin/pdftk '.$template.' fill_form '.$filename.' output '.$filebase.'pdf flatten';
	//$exec = 'pdftk '.$template.' fill_form '.$filename.' output '.$filebase.'pdf flatten';
	exec($exec, $output, $return_var);

	//unlink($filename);
}

if (file_exists($filebase.'pdf')) {

  // Send PDF
  header('Content-Description: File Transfer');
  header('Content-Type: application/pdf');
  header('Content-Disposition: attachment; filename="'.basename($filebase.'pdf').'"'); // TODO: Fix better filename
  header('Content-Transfer-Encoding: binary');
  header('Expires: 0');
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
  header('Pragma: public');
  header('Content-Length: ' . filesize($filebase.'pdf'));
  //ob_clean();
  //flush();
  readfile($filebase.'pdf');

} else if (!file_exists($filebase.'pdf')){
  echo _AT('certify_no_certificate_file');  
  exit;
} else {
  echo _AT('certify_no_pdftk');
  exit;
}
?>
