<?php
/**
 * Link Cloaker WordPress Plugin API
 */

/**
 * Returns the API Version as a string.
 */
function affli_api_version()
{
  return '1.3';
}

/**
 * Create a Link for a long, ugly URL.
 *
 * @param string $target_url Required, it is the value of the Target URL you
 *                           want the Link to redirect to
 * 
 * @param string $slug Optional, slug for the Link (string that comes 
 *                     after the Link's slash) if this value isn't set
 *                     then a random slug will be automatically generated.
 *
 * @param string $name Optional, name for the Link. If this value isn't
 *                     set then the name will be the slug.
 *
 * @param string $description Optional, description for the Link.
 *
 * @param integer $group_id Optional, the group that this link will be placed in.
 *                          If this value isn't set then the link will not be
 *                          placed in a group.
 *
 * @param boolean $link_track_me Optional, If true the link will be tracked,
 *                               if not set the default value (from the link option page) will be used
 *
 * @param boolean $link_nofollow Optional, If true the nofollow attribute will
 *                               be set for the link, if not set the default
 *                               value (from the link option page) will
 *                               be used
 *
 * @param string $link_redirect_type Optional, valid values include '307', '301', 
 *                                   'bar', 'cloak' or 'pixel'
 *                                   if not set the default value (from the 
 *                                   link option page) will be used
 *
 * @return boolean / string The Full Link if Successful and false for Failure.
 *                          This function will also set a global variable named 
 *                          $affli_pretty_slug which gives the slug of the link 
 *                          created if the link is successfully created -- it will
 *                          set a variable named $affli_error_messages if the link 
 *                          was not successfully created.
 */
function affli_create_pretty_link( $target_url,
                                  $slug = '',
                                  $name = '',
                                  $description = '',
                                  $group_id = 0,
                                  $track_me = '',
                                  $nofollow = '',
                                  $redirect_type = '',
                                  $param_forwarding = '',
                                  $param_struct = '' )
{
  global $wpdb, $affli_link, $affli_blogurl;
  global $affli_error_messages, $affli_pretty_link, $affli_pretty_slug, $affli_options;

  $affli_error_messages = array();

  $values = array();
  $values['url']              = $target_url;
  $values['slug']             = (($slug == '')?$affli_link->generateValidSlug():$slug);
  $values['name']             = $name;
  $values['description']      = $description;
  $values['group_id']         = $group_id;
  $values['redirect_type']    = (($redirect_type == '')?$affli_options->link_redirect_type:$redirect_type);
  $values['nofollow']         = (($nofollow == '')?$affli_options->link_nofollow:$nofollow);
  $values['track_me']         = (($track_me == '')?$affli_options->link_track_me:$track_me);
  $values['param_forwarding'] = (($param_forwarding == '')?'off':$param_forwarding);
  $values['param_struct']     = $param_struct;

  // make array look like $_POST
  if(empty($values['nofollow']) or !$values['nofollow'])
    unset($values['nofollow']);
  if(empty($values['track_me']) or !$values['track_me'])
    unset($values['track_me']);

  $affli_error_messages = $affli_link->validate( $values );
    
  if( count($affli_error_messages) == 0 )
  {
    if( $id = $affli_link->create( $values ) )
      return $id;
    else
    {
      $affli_error_messages[] = "An error prevented your Link from being created";
      return false;
    }
  }
  else
    return false;
}

function affli_update_pretty_link( $id,
                                  $target_url = '',
                                  $slug = '',
                                  $name = -1,
                                  $description = -1,
                                  $group_id = '',
                                  $track_me = '',
                                  $nofollow = '',
                                  $redirect_type = '',
                                  $param_forwarding = '',
                                  $param_struct = -1 )
{
  global $wpdb, $affli_link, $affli_blogurl;
  global $affli_error_messages, $affli_pretty_link, $affli_pretty_slug;

  if(empty($id))
  {
    $affli_error_messages[] = "Link ID must be set for successful update.";
    return false;
  }

  $record = $affli_link->getOne($id);

  $affli_error_messages = array();

  $values = array();
  $values['id']               = $id;
  $values['url']              = (($target_url == '')?$record->url:$target_url);
  $values['slug']             = (($slug == '')?$record->slug:$slug);
  $values['name']             = (($name == -1)?$record->name:$name);
  $values['description']      = (($description == -1)?$record->description:$description);
  $values['group_id']         = (($group_id == '')?$record->group_id:$group_id);
  $values['redirect_type']    = (($redirect_type == '')?$record->redirect_type:$redirect_type);
  $values['nofollow']         = (($nofollow == '')?$record->nofollow:$nofollow);
  $values['track_me']         = (($track_me == '')?(int)$record->track_me:$track_me);
  $values['param_forwarding'] = (($param_forwarding == '')?$record->param_forwarding:$param_forwarding);
  $values['param_struct']     = (($param_struct == -1)?$record->param_struct:$param_struct);

  // make array look like $_POST
  if(empty($values['nofollow']) or !$values['nofollow'])
    unset($values['nofollow']);
  if(empty($values['track_me']) or !$values['track_me'])
    unset($values['track_me']);

  $affli_error_messages = $affli_link->validate( $values );
    
  if( count($affli_error_messages) == 0 )
  {
    if( $affli_link->update( $id, $values ) )
      return true;
    else
    {
      $affli_error_messages[] = "An error prevented your Link from being created";
      return false;
    }
  }
  else
    return false;
}

/**
 * Get all the  link groups in an array suitable for creating a select box.
 *
 * @return bool (false if failure) | array A numerical array of associative arrays 
 *                                         containing all the data about the link groups.
 */
function affli_get_all_groups()
{
  global $affli_group;
  $groups = $affli_group->getAll('',' ORDER BY gr.name', ARRAY_A);
  return $groups;
}

/**
 * Get all the links in an array suitable for creating a select box.
 *
 * @return bool (false if failure) | array A numerical array of associative arrays
 *                                         containing all the data about the  *                                         links.
 */
function affli_get_all_links()
{
  global $affli_link;
  $links = $affli_link->getAll('',' ORDER BY li.name', ARRAY_A);
  return $links;
}
                             
/**
 * Gets a specific link from a slug and returns info about it in an array
 *
 * @return bool (false if failure) | array An associative array with all the
 *                                         data about the given link.
 */
function affli_get_link_from_slug($slug, $return_type = OBJECT, $include_stats = false)
{
  global $affli_link;
  $link = $affli_link->getOneFromSlug($slug, $return_type, $include_stats);
  return $link;
}

/**
 * Gets a specific link from id and returns info about it in an array
 *
 * @return bool (false if failure) | array An associative array with all the
 *                                         data about the given link.
 */
function affli_get_link($id, $return_type = OBJECT, $include_stats = false)
{
  global $affli_link;
  $link = $affli_link->getOne($id, $return_type, $include_stats);
  return $link;
}

/**
 * Gets the full link url from an id
 *
 * @return bool (false if failure) | string the link url
 */
function affli_get_pretty_link_url($id)
{
  global $affli_link,$affli_blogurl;

  if($pretty_link = $affli_link->getOne($id))
    return "{$affli_blogurl}/{$pretty_link->slug}";

  return false;
}
                             
?>
