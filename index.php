<?php

require 'certify_functions.php';

$GLOBALS['dout'] = '';

function dbug($out) {
	$GLOBALS['dout'] .= $out . "\n";
}

define('AT_INCLUDE_PATH', '../../include/');
require (AT_INCLUDE_PATH.'vitals.inc.php');
$_custom_css = $_base_path . 'mods/certify/certify.css'; // use a custom stylesheet

$certify_certificates = array();
	
// Fetch certificates for course

if (isset($_SESSION['course_id']) && $_SESSION['course_id']) {
    $course_id = $_SESSION['course_id'];
    $sql = 'SELECT * FROM %scertify where course_id=%d';
    $result = queryDB($sql, array(TABLE_PREFIX, $course_id));


    foreach($result as $certify){ 
            $this_cert = array();
            $this_cert['title'] = $certify['title'];
            $this_cert['description'] = $certify['description'];
            $this_cert['progress'] = getCertificateProgress($_SESSION[member_id], $certify['certify_id']);
            //echo('course: '.$course_id.' ,member: '.$_SESSION[member_id].' ,progress: '.$this_cert['progress'].', certify:'.$certify['certify_id']);
            $certify_certificates[$certify['certify_id']] = $this_cert;
    }
}


require (AT_INCLUDE_PATH.'header.inc.php');
dbug(var_export($certify_certificates, true));
echo '<!-- <code><pre>'.$dout.'</pre></code> -->';


?>


<table class="data static" summary="" rules="cols">
<thead>
<tr>
	<th scope="col"><?php echo _AT('certify_title'); ?></th>
	<th scope="col"><?php echo _AT('certify_status'); ?></th>
</tr>
</thead>
<tbody>
<?php foreach ($certify_certificates as $certify_id => &$certificate) { ?>

	<?php if ($certificate['progress'] < 100) { ?>

		<tr>
			<td>
				<strong><?php echo $certificate['title']; ?></strong><br />
				<em><?php echo $certificate['description']; ?></em>
			</td>
			
			<td>				
				<div class="certify_bar-border">
					<div class="certify_bar-fill">
						<div class="certify_bar-bar" style="width: <?php echo floor($certificate['progress']); ?>%;"><?php echo floor($certificate['progress']); ?>%
						</div>
					</div>
				</div>
				
			</td>
		</tr>
	<?php } else { ?>

		<tr>
			<td>
				<strong><?php echo $certificate['title']; ?></strong><br />
				<em><?php echo $certificate['description']; ?></em>
			</td>
			
			<td>
				<span class="certify_percent">100%</span> 
				<a href="<?php echo url_rewrite('mods/certify/download_certificate.php?certify_id='.$certify_id); ?>">
				<img src="<?php echo AT_BASE_HREF. "images/file_types/pdf.gif"; ?>" border="0" /> 
				<?php echo _AT('certify_download_certificate'); ?>
				</a>
			</td>
		</tr>
		
	<?php } ?>

<?php } ?>


</tbody>
</table>
	
<?php require (AT_INCLUDE_PATH.'footer.inc.php'); ?>