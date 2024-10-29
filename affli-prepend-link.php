<?php
require_once 'affli-config.php';
require_once(AFLI_MODELS_PATH . '/models.inc.php');

$getPrependname="select * from ".$wpdb->prefix."prependsettings";
$query = $wpdb->prepare($getPrependname);
$resultsarr= $wpdb->get_row($query, 'OBJECT');
$prependname=$resultsarr->prependname;
$prependid=$resultsarr->id;
if($_POST['Submit'])
{
	if(count($resultsarr)==0)
	{
		$insertprepend="Insert into ".$wpdb->prefix."prependsettings (prependname) values('".$_POST['prepend']."')";
		$prependupdate=$wpdb->query($insertprepend);		
	}
	else
	{
		$updateprepend="Update ".$wpdb->prefix."prependsettings set prependname='".$_POST['prepend']."' where id=".$prependid;
		$prependupdate=$wpdb->query($updateprepend);		
	}
	
	$prependname=$_POST['prepend'];
	$affli_message="Link prepend has been Updated Successfully";
	
}

require_once 'classes/views/affli-links/prependlink.php';


?>
