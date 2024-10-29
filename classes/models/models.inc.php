<?php
require_once(AFLI_MODELS_PATH.'/PrliLink.php');
require_once(AFLI_MODELS_PATH.'/PrliClick.php');
require_once(AFLI_MODELS_PATH.'/PrliGroup.php');
require_once(AFLI_MODELS_PATH.'/PrliUtils.php');
require_once(AFLI_MODELS_PATH.'/PrliUrlUtils.php');
require_once(AFLI_MODELS_PATH.'/PrliLinkMeta.php');

global $affli_link;
global $affli_link_meta;
global $affli_click;
global $affli_group;
global $affli_utils;
global $affli_url_utils;

$affli_link      = new PrliLink();
$affli_link_meta = new PrliLinkMeta();
$affli_click     = new PrliClick();
$affli_group     = new PrliGroup();
$affli_utils     = new PrliUtils();
$affli_url_utils = new PrliUrlUtils();

function affli_get_main_message( $message = "Get started by <a href=\"?page=pretty-link/affli-links.php&action=new\">adding a URL</a> that you want to turn into a pretty link.<br/>Come back to see how many times it was clicked.")
{
  global $affli_utils;
  include_once(ABSPATH."/wp-includes/class-IXR.php");

  if($affli_utils->pro_is_installed())
  {
    $client = new IXR_Client('http://prettylinkpro.com/xmlrpc.php');
    if ($client->query('afflipro.get_main_message'))
      $message = $client->getResponse();
  }
  else
  {
    $client = new IXR_Client('http://blairwilliams.com/xmlrpc.php');
    if ($client->query('affli.get_main_message'))
      $message = $client->getResponse();
  }
 // return $message;
}

?>
