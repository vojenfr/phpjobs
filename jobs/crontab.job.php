<?php

if (@constant('JOBS_CONTEXT') != 'worker') exit();

$post_data_restored = restore_post_data();

job_start();

$rc = -1;

if ($_POST['process']=='GET') {
$crontab_data = '';
$cmd = 'crontab -l';
}

if ( ($_POST['process']=='SET') && strlen($_POST['crontab']) ) {
$crontab_data = $_POST['crontab'];
$cmd = 'crontab -';
}

if(isset($cmd)) {
	
	$descriptorspec = array(
   	0 => array("pipe", "r"),  
   	1 => array("pipe", "w"), 
   	2 => array("pipe", "w") 
	);
	
	$pipes = array();
	$process = proc_open($cmd, $descriptorspec, $pipes);

	if (is_resource($process)) {
    // input new crontab
    fwrite($pipes[0], $crontab_data);
    fclose($pipes[0]);
    
    // retrieve stdout
    echo stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    // retrieve stderr
    echo stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    
    // retrieve rc
    $return_value = proc_close($process);
    //echo "$return_value\n";
    // advice: concatenate both, return them as is along with the rc 
    // => let the client side display errors to end users
	}
}

$job_state_data['return_code'] = $rc;

