<?php
function get_server_cpu_usage(){
 
	$load = sys_getloadavg();
	return $load[0];
 
}
?>
