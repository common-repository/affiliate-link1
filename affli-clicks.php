<?php

if(isset($_GET['action']) and $_GET['action'] == 'csv')
{
  $root = dirname(dirname(dirname(dirname(__FILE__))));
  if (file_exists($root.'/wp-load.php')) 
    require_once($root.'/wp-load.php');
  else
    require_once($root.'/wp-config.php');
}

require_once 'affli-config.php';
require_once(AFLI_MODELS_PATH . '/models.inc.php');
//require_once(AFLI_PATH . '/affli-image-lookups.php');

$controller_file = basename(__FILE__);

if($_GET['action'] == null and $_POST['action'] == null)
{
  $page_params = '';

  $params = $affli_click->get_params_array();

  $current_page = $params['paged'];

  $start_timestamp = $affli_utils->get_start_date($params);
  $end_timestamp = $affli_utils->get_end_date($params);

  $start_timestamp = mktime(0, 0, 0, date('n', $start_timestamp), date('j', $start_timestamp), date('Y', $start_timestamp));
  $end_timestamp   = mktime(0, 0, 0, date('n', $end_timestamp),   date('j', $end_timestamp),   date('Y', $end_timestamp)  );

  $sdyear = date('Y',$start_timestamp);
  $sdmon  = date('n',$start_timestamp);
  $sddom  = date('j',$start_timestamp);

  $edyear = date('Y',$end_timestamp);
  $edmon  = date('n',$end_timestamp);
  $eddom  = date('j',$end_timestamp);

  $where_clause = " cl.created_at BETWEEN '$sdyear-$sdmon-$sddom 00:00:00' AND '$edyear-$edmon-$eddom 23:59:59'";

  if(!empty($params['sdate']))
    $page_params .= "&sdate=".$params['sdate'];

  if(!empty($params['edate']))
    $page_params .= "&edate=".$params['edate'];

  if(!empty($params['l']) and $params['l'] != 'all')
  {
    $where_clause .= (($params['l'] != 'all')?" AND cl.link_id=".$params['l']:'');
    $link_name = $wpdb->get_var("SELECT name FROM ".$wpdb->prefix."affli_links WHERE id=".$params['l']);
    $link_slug = $wpdb->get_var("SELECT slug FROM ".$wpdb->prefix."affli_links WHERE id=".$params['l']);

    $page_params .= "&l=".$params['l'];
  }
  else if(!empty($params['ip']))
  {
    $link_name = "IP Address: " . $params['ip'];
    $where_clause .= " AND cl.ip='".$params['ip']."'";
    $page_params .= "&ip=".$params['ip'];
  }
  else if(!empty($params['vuid']))
  {
    $link_name = "Visitor: " . $params['vuid'];
    $where_clause .= " AND cl.vuid='".$params['vuid']."'";
    $page_params .= "&vuid=".$params['vuid'];
  }

  else
  {
    $link_name = "All Links";
    $where_clause .= "";
    $page_params .= "";
  }

  if($params['type'] == "unique")
  {
    $where_clause .= " AND first_click=1";
    $page_params .= "&type=unique";
  }

  $click_vars = affli_get_click_sort_vars($params,$where_clause);
  $sort_params = $page_params . $click_vars['sort_params'];
  $page_params .= $click_vars['page_params'];
  $sort_str = $click_vars['sort_str'];
  $sdir_str = $click_vars['sdir_str'];
  $search_str = $click_vars['search_str'];

  $where_clause = $click_vars['where_clause'];
  $order_by = $click_vars['order_by'];
  $count_where_clause = $click_vars['count_where_clause'];

  $record_count = $affli_click->getRecordCount($count_where_clause);
  $page_count = $affli_click->getPageCount($page_size,$count_where_clause);
  $clicks = $affli_click->getPage($current_page,$page_size,$where_clause,$order_by,true);
  $page_last_record = $affli_utils->getLastRecordNum($record_count,$current_page,$page_size);
  $page_first_record = $affli_utils->getFirstRecordNum($record_count,$current_page,$page_size);

  require_once 'classes/views/affli-clicks/list.php';
}
else if($_GET['action'] == 'csv' or $_POST['action'] == 'csv')
{
  if(isset($_GET['l']))
  {
    $where_clause = " link_id=".$_GET['l'];
    $link_name = $wpdb->get_var("SELECT name FROM ".$wpdb->prefix."affli_links WHERE id=".$_GET['l']);
    $link_slug = $wpdb->get_var("SELECT slug FROM ".$wpdb->prefix."affli_links WHERE id=".$_GET['l']);
  }
  else if(isset($_GET['ip']))
  {
    $link_name = "ip_addr_" . $_GET['ip'];
    $where_clause = " cl.ip='".$_GET['ip']."'";
  }
  else if(isset($_GET['vuid']))
  {
    $link_name = "visitor_" . $_GET['vuid'];
    $where_clause = " cl.vuid='".$_GET['vuid']."'";
  }

  else
  {
    $link_name = "all_links";
    $where_clause = "";
  }

  $clicks = $affli_click->getAll($where_clause);
  require_once 'classes/views/affli-clicks/csv.php';
}

// Helpers
function affli_get_click_sort_vars($params,$where_clause = '')
{
  $count_where_clause = '';
  $page_params = '';

  // These will have to work with both get and post
  $sort_str   = $params['sort'];
  $sdir_str   = $params['sdir'];
  $search_str = $params['search'];

  // Insert search string
  if(!empty($search_str))
  {
    $search_params = explode(" ", $search_str);

    $first_pass = true;
    foreach($search_params as $search_param)
    {
      if($first_pass)
      {
        if($where_clause != '')
          $where_clause .= ' AND';

        $first_pass = false;
      }
      else
        $where_clause .= ' AND';

      $where_clause .= " (cl.ip LIKE '%$search_param%' OR ".
                         "cl.vuid LIKE '%$search_param%' OR ".
                         "cl.btype LIKE '%$search_param%' OR ".
                         "cl.bversion LIKE '%$search_param%' OR ".
                         "cl.host LIKE '%$search_param%' OR ".
                         "cl.referer LIKE '%$search_param%' OR ".
                         "cl.uri LIKE '%$search_param%' OR ".
                         "cl.created_at LIKE '%$search_param%'";
      $count_where_clause = $where_clause . ")";
      $where_clause .= " OR li.name LIKE '%$search_param%')";
    }

    $page_params .="&search=$search_str";
  }

  // Have to create a separate var so sorting doesn't get screwed up
  $sort_params = $page_params;

  // make sure page params stay correct
  if(!empty($sort_str))
    $page_params .="&sort=$sort_str";

  if(!empty($sdir_str))
    $page_params .= "&sdir=$sdir_str";

  if(empty($count_where_clause))
    $count_where_clause = $where_clause;

  // Add order by clause
  switch($sort_str)
  {
    case "ip":
    case "vuid":
    case "btype":
    case "bversion":
    case "host":
    case "referer":
    case "uri":
      $order_by .= " ORDER BY cl.$sort_str";
      break;
    case "link":
      $order_by .= " ORDER BY li.name";
      break;
    default:
      $order_by .= " ORDER BY cl.created_at";
  }

  // Toggle ascending / descending
  if((empty($sort_str) and empty($sdir_str)) or $sdir_str == 'desc')
  {
    $order_by .= ' DESC';
    $sdir_str = 'desc';
  }
  else
    $sdir_str = 'asc';

  return array('count_where_clause' => $count_where_clause,
               'sort_str' => $sort_str, 
               'sdir_str' => $sdir_str, 
               'search_str' => $search_str, 
               'where_clause' => $where_clause, 
               'order_by' => $order_by,
               'sort_params' => $sort_params,
               'page_params' => $page_params);
}


?>
