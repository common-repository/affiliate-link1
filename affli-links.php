<?php
require_once 'affli-config.php';
require_once(AFLI_MODELS_PATH . '/models.inc.php');

$params = $affli_link->get_params_array();

if($params['action'] == 'list')
{

  if($params['regenerate'] == 'true')
  {
    $wp_rewrite->flush_rules();
    $affli_message = "Your Links were Successfully Regenerated";
  }

  affli_display_links_list($params, $affli_message);
}
else if($params['action'] == 'list-form')
{
  if(apply_filters('affli-link-list-process-form', true))
    affli_display_links_list($params, affli_get_main_message());
}
else if($params['action'] == 'quick-create')
{
  $errors = $affli_link->validate($_POST);

  if( count($errors) > 0 )
  {
//     $groups = $affli_group->getAll('',' ORDER BY name');
//     $values = setup_new_vars($groups);
    require_once 'classes/views/affli-links/new.php';
  }
  else
  {
    $_POST['param_forwarding'] = 'off';
    $_POST['param_struct'] = '';
    $_POST['name'] = '';
    $_POST['description'] = '';
    if( $affli_options->link_track_me )
      $_POST['track_me'] = 'on';
    if( $affli_options->link_nofollow )
      $_POST['nofollow'] = 'on';

    $_POST['redirect_type'] = $affli_options->link_redirect_type;

    $record = $affli_link->create( $_POST );

    $affli_message = "Your Link was Successfully Created";
    affli_display_links_list($params, $affli_message, '', 1);
  }
}
else if($params['action'] == 'create')
{
  $errors = $affli_link->validate($_POST);

  $errors = apply_filters( "affli_validate_link", $errors );

  if( count($errors) > 0 )
  {
	if($_GET['from']=='post')
	{
		$str='<ul>';
		foreach( $errors as $error )
		{
			$str.='<li><strong>ERROR</strong>: '.$error.'</li>';
		}
		$str.='</ul>';
		echo '<script type="text/javascript">parent.document.getElementById("linkerrors").style.display="block";parent.document.getElementById("linkerrors").innerHTML="'.$str.'";</script>';
		exit;
	}
	else
	{
// 	$groups = $affli_group->getAll('',' ORDER BY name');
// 	$values = setup_new_vars($groups);
	require_once 'classes/views/affli-links/new.php';
	}
  }
  else
  {
    $record = $affli_link->create( $_POST );

    do_action( "affli_update_link", $record );

    $affli_message = "Your Link was Successfully Created";
	if($_GET['from']=='post')
	{
		echo '<script type="text/javascript">parent.document.getElementById("linkerrors").style.display="block";parent.document.getElementById("linkerrors").innerHTML="<ul>'.$affli_message.'</ul>";parent.document.post.target=\'\';parent.document.post.action=\'post.php\';parent.document.getElementById("linksdisplay").innerHTML="'.addslashes(links_list()).'";parent.document.getElementById("url").value="";parent.document.getElementById("name").value="";parent.document.getElementById("slug").value="";</script>';
		exit;
	}
	else
	{
    		affli_display_links_list($params, $affli_message, '', 1);
	}
  }
}
else if($params['action'] == 'edit')
{
  $groups = $affli_group->getAll('',' ORDER BY name');

  $record = $affli_link->getOne( $params['id'] );
  $values = setup_edit_vars($groups,$record);
  $id = $params['id'];
  require_once 'classes/views/affli-links/edit.php';
}
else if($params['action'] == 'bulk-update')
{
  if(apply_filters('affli-bulk-link-update', true))
  {
    $affli_message = "Your Links were Successfully Updated";
    affli_display_links_list($params, $affli_message, '', 1);
  }
}
else if($params['action'] == 'update')
{
  $errors = $affli_link->validate($_POST);
  $id = $_POST['id'];

  $errors = apply_filters( "affli_validate_link", $errors );

  if( count($errors) > 0 )
  {
//     $groups = $affli_group->getAll('',' ORDER BY name');
    $record = $affli_link->getOne( $params['id'] );
//     $values = setup_edit_vars($groups,$record);
    require_once 'classes/views/affli-links/edit.php';
  }
  else
  {
    $record = $affli_link->update( $_POST['id'], $_POST );

    do_action( "affli_update_link", $id );

    $affli_message = "Your Link was Successfully Updated";
    affli_display_links_list($params, $affli_message, '', 1);
  }
}
else if($params['action'] == 'reset')
{
  $affli_link->reset( $params['id'] );
  $affli_message = "Your Link was Successfully Reset";
  affli_display_links_list($params, $affli_message, '', 1);
}
else if($params['action'] == 'destroy')
{
  $affli_link->destroy( $params['id'] );
  $affli_message = "Your Link was Successfully Destroyed";
  affli_display_links_list($params, $affli_message, '', 1);
}

// Helpers
function affli_display_links_list($params, $affli_message, $page_params_ov = false, $current_page_ov = false)
{
  global $wpdb, $affli_utils, $affli_click, $affli_group, $affli_link, $page_size, $affli_options;

  $controller_file = basename(__FILE__);

  if(!empty($params['group']))
  {
    //$where_clause = " group_id=" . $params['group'];
    //$page_params = "&group=" . $params['group'];
  }

  $link_vars = affli_get_link_sort_vars($params, $where_clause);

  if($current_page_ov)
    $current_page = $current_page_ov;
  else
    $current_page = $params['paged'];

  if($page_params_ov)
    $page_params .= $page_params_ov;
  else
    $page_params .= $link_vars['page_params'];

  $sort_str = $link_vars['sort_str'];
  $sdir_str = $link_vars['sdir_str'];
  $search_str = $link_vars['search_str'];

  $record_count = $affli_link->getRecordCount($link_vars['where_clause']);
  $page_count = $affli_link->getPageCount($page_size,$link_vars['where_clause']);
  $links = $affli_link->getPage($current_page,$page_size,$link_vars['where_clause'],$link_vars['order_by']);
  $page_last_record = $affli_utils->getLastRecordNum($record_count,$current_page,$page_size);
  $page_first_record = $affli_utils->getFirstRecordNum($record_count,$current_page,$page_size);

  require_once 'classes/views/affli-links/list.php';
}

function affli_get_link_sort_vars($params,$where_clause = '')
{
  $order_by = '';
  $page_params = '';

  // These will have to work with both get and post
  $sort_str = $params['sort'];
  $sdir_str = $params['sdir'];
  $search_str = $params['search'];

  // Insert search string
  if(!empty($search_str))
  {
    $search_params = explode(" ", $search_str);

    foreach($search_params as $search_param)
    {
      if(!empty($where_clause))
        $where_clause .= " AND";

      $where_clause .= " (li.name like '%$search_param%' OR li.slug like '%$search_param%' OR li.url like '%$search_param%' OR li.created_at like '%$search_param%')";
    }

    $page_params .="&search=$search_str";
  }

  // make sure page params stay correct
  if(!empty($sort_str))
    $page_params .="&sort=$sort_str";

  if(!empty($sdir_str))
    $page_params .= "&sdir=$sdir_str";

  // Add order by clause
  switch($sort_str)
  {
    case "name":
    case "clicks":
    case "slug":
      $order_by .= " ORDER BY $sort_str";
      break;
    default:
      $order_by .= " ORDER BY created_at";
  }

  // Toggle ascending / descending
  if((empty($sort_str) and empty($sdir_str)) or $sdir_str == 'desc')
  {
    $order_by .= ' DESC';
    $sdir_str = 'desc';
  }
  else
    $sdir_str = 'asc';

  return array('order_by' => $order_by,
               'sort_str' => $sort_str, 
               'sdir_str' => $sdir_str, 
               'search_str' => $search_str, 
               'where_clause' => $where_clause, 
               'page_params' => $page_params);
}


?>
