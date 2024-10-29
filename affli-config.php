<?php
define(AFLI_PLUGIN_NAME,"affiliate-link1");
define(AFLI_PATH,WP_PLUGIN_DIR.'/'.AFLI_PLUGIN_NAME);
define(AFLI_MODELS_PATH,AFLI_PATH.'/classes/models');
define(AFLI_VIEWS_PATH,AFLI_PATH.'/classes/views');
//define(AFLI_URL,WP_PLUGIN_URL.'/'.AFLI_PLUGIN_NAME);
define(AFLI_URL,plugins_url($path = '/'.AFLI_PLUGIN_NAME));

require_once(AFLI_MODELS_PATH.'/PrliOptions.php');

global $affli_version;
$affli_version = '1.0.0';

// For IIS compatibility
if (!function_exists('fnmatch'))
{
  function fnmatch($pattern, $string)
  {
    return preg_match("#^".strtr(preg_quote($pattern, '#'), array('\*' => '.*', '\?' => '.'))."$#i", $string);
  }
}
  
// The number of items per page on a table
global $page_size;
$page_size = 10;

global $affli_blogurl;
global $affli_siteurl;
global $affli_blogname;
global $affli_blogdescription;

$affli_blogurl = ((get_option('home'))?get_option('home'):get_option('siteurl'));
$affli_siteurl = get_option('siteurl');
$affli_blogname = get_option('blogname');
$affli_blogdescription = get_option('blogdescription');

/***** SETUP OPTIONS OBJECT *****/
global $affli_options;

$affli_options_str = get_option('affli_options');
$affli_options = unserialize($affli_options_str);

// If unserializing didn't work
if(!$affli_options)
{
  $affli_options = new PrliOptions();

  $affli_options_str = serialize($affli_options);
  delete_option('affli_options');
  add_option('affli_options',$affli_options_str);
}
else
  $affli_options->set_default_options(); // Sets defaults for unset options

/***** TODO: Uh... these functions should find a better home somewhere *****/
function setup_new_vars($groups)
{
  global $affli_link, $affli_options;

  $values = array();
  $values['url'] =  (($_POST['url'] != null)?$_POST['url']:'');
  $values['slug'] = (($_POST['slug'] != null)?$_POST['slug']:$affli_link->generateValidSlug());
  $values['name'] = htmlspecialchars((($_POST['name'] != null)?stripslashes($_POST['name']):''));
  $values['description'] = htmlspecialchars((($_POST['description'] != null)?stripslashes($_POST['description']):''));

  $values['track_me'] = (((isset($_POST['track_me']) and $_POST['track_me'] == 'on') or (!isset($_POST['track_me']) and $affli_options->link_track_me == '1'))?'checked="true"':'');
  $values['nofollow'] = (((isset($_POST['nofollow']) and $_POST['nofollow'] == 'on') or (!isset($_POST['nofollow']) and $affli_options->link_nofollow == '1'))?'checked="true"':'');

  $values['redirect_type'] = array();
  $values['redirect_type']['307'] = (((isset($_POST['redirect_type']) and $_POST['redirect_type'] == '307') or (!isset($_POST['redirect_type']) and $affli_options->link_redirect_type == '307'))?'selected="selected"':'');
  $values['redirect_type']['301'] = (((isset($_POST['redirect_type']) and $_POST['redirect_type'] == '301') or (!isset($_POST['redirect_type']) and $affli_options->link_redirect_type == '301'))?'selected="selected"':'');
  $values['redirect_type']['prettybar'] = (((isset($_POST['redirect_type']) and $_POST['redirect_type'] == 'prettybar') or (!isset($_POST['redirect_type']) and $affli_options->link_redirect_type == 'prettybar'))?'selected="selected"':'');
  $values['redirect_type']['cloak'] = (((isset($_POST['redirect_type']) and $_POST['redirect_type'] == 'cloak') or (!isset($_POST['redirect_type']) and $affli_options->link_redirect_type == 'cloak'))?'selected="selected"':'');
  $values['redirect_type']['pixel'] = (((isset($_POST['redirect_type']) and $_POST['redirect_type'] == 'pixel') or (!isset($_POST['redirect_type']) and $affli_options->link_redirect_type == 'pixel'))?'selected="selected"':'');

  $values['groups'] = array();

  if(is_array($groups))
  {
    foreach($groups as $group)
    {
      $values['groups'][] = array( 'id' => $group->id,
                                   'value' => (($_POST['group_id'] == $group->id)?' selected="true"':''),
                                   'name' => $group->name );
    }
  }

  $values['param_forwarding'] = array();
  $values['param_forwarding']['off'] = (((isset($_POST['param_forwarding']) and $_POST['param_forwarding'] == 'off') or !isset($_POST['param_forwarding']))?'checked="true"':'');
  $values['param_forwarding']['on'] = ((isset($_POST['param_forwarding']) and $_POST['param_forwarding'] == 'on')?'checked="true"':'');
  $values['param_forwarding']['custom'] = ((isset($_POST['param_forwarding']) and $_POST['param_forwarding'] == 'custom')?'checked="true"':'');

  return $values;
}

function setup_edit_vars($groups,$record)
{
  global $affli_link;

  $values = array();
  $values['url'] =  (($_POST['url'] != null and $record == null)?$_POST['url']:$record->url);
  $values['slug'] = (($_POST['slug'] != null and $record == null)?$_POST['slug']:$record->slug);
  $values['name'] = htmlspecialchars(stripslashes((($_POST['name'] != null and $record == null)?$_POST['name']:$record->name)));
  $values['description'] = htmlspecialchars(stripslashes((($_POST['description'] != null and $record == null)?$_POST['description']:$record->description)));
  $values['track_me'] = ((($_POST['track_me'] or $record->track_me) and ($_POST['track_me'] == 'on' or $record->track_me == 1))?'checked="true"':'');
  $values['nofollow'] = ((($_POST['nofollow'] or $record->nofollow) and ($_POST['nofollow'] == 'on' or $record->nofollow == 1))?'checked="true"':'');

  $values['groups'] = array();
  foreach($groups as $group)
  {
    $values['groups'][] = array( 'id' => $group->id,
                                 'value' => ((($_POST['group_id'] == $group->id) or ($record->group_id == $group->id))?' selected="true"':''),
                                 'name' => $group->name );
  }

  $values['param_forwarding'] = array();
  $values['param_forwarding']['off'] = ((!isset($_POST['param_forwarding']) or $record->param_forwarding == 'off')?'checked="true"':'');
  $values['param_forwarding']['on'] = (((isset($_POST['param_forwarding']) and $_POST['param_forwarding'] == 'on') or (isset($record->param_forwarding) and $record->param_forwarding == 'on'))?'checked="true"':'');
  $values['param_forwarding']['custom'] = (((isset($_POST['param_forwarding']) and $_POST['param_forwarding'] == 'custom') or (isset($record->param_forwarding) and $record->param_forwarding == 'custom'))?'checked="true"':'');
  $values['param_struct'] = (($_POST['param_struct'] != null and $record == null)?$_POST['param_struct']:$record->param_struct);

  $values['redirect_type'] = array();
  $values['redirect_type']['307'] = ((!isset($_POST['redirect_type']) or (isset($_POST['redirect_type']) and $_POST['redirect_type'] == '307') or (isset($record->redirect_type) and $record->redirect_type == '307'))?' selected="selected"':'');
  $values['redirect_type']['301'] = (((isset($_POST['redirect_type']) and $_POST['redirect_type'] == '301') or (isset($record->redirect_type) and $record->redirect_type == '301'))?' selected="selected"':'');
  $values['redirect_type']['prettybar'] = (((isset($_POST['redirect_type']) and $_POST['redirect_type'] == 'prettybar') or (isset($record->redirect_type) and $record->redirect_type == 'prettybar'))?' selected="selected"':'');
  $values['redirect_type']['cloak'] = (((isset($_POST['redirect_type']) and $_POST['redirect_type'] == 'cloak') or (isset($record->redirect_type) and $record->redirect_type == 'cloak'))?' selected="selected"':'');
  $values['redirect_type']['pixel'] = (((isset($_POST['redirect_type']) and $_POST['redirect_type'] == 'pixel') or (isset($record->redirect_type) and $record->redirect_type == 'pixel'))?' selected="selected"':'');

  return $values;
}
?>

