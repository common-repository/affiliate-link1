<?php
/*
Plugin Name: Affiliate Link
Description: Format, track and share any URL on the Internet from your WordPress website!
Author: Affilate Media
Version: 1.1
*/

require_once('affli-config.php');
require_once(AFLI_MODELS_PATH . '/models.inc.php');
require_once('affli-api.php'); // load api methods
require_once('affli-xmlrpc.php'); // load xml-rpc api methods

$affli_inc_utils = new PrliUtils();

add_action('admin_menu', 'affli_menu');

// Provide Back End Hooks to the Pro version of Link
if($affli_inc_utils->pro_is_installed())
  require_once(AFLI_PATH.'/pro/pretty-link-pro.php');

function affli_menu()
{
  add_menu_page('Link Cloaker', 'Link Cloaker', 8, AFLI_PATH.'/affli-links.php','',''); 
  add_submenu_page(AFLI_PATH.'/affli-links.php', 'Link Cloaker | Add New Link', 'Add New Link', 8, AFLI_PATH.'/affli-add-link.php');
  add_submenu_page(AFLI_PATH.'/affli-links.php', 'Link Cloaker | Link Prepend', 'Link Prepend settings', 8, AFLI_PATH.'/affli-prepend-link.php');
  add_submenu_page(AFLI_PATH.'/affli-links.php', 'Link Cloaker | Hits', 'Hits', 8, AFLI_PATH.'/affli-clicks.php');
  

  add_action('admin_head-affiliate-link/affli-clicks.php', 'affli_reports_admin_header');
  add_action('admin_head-affiliate-link/affli-links.php', 'affli_links_admin_header');
  add_action('admin_head-affiliate-link/affli-add-link.php', 'affli_links_admin_header');
  add_action('admin_head-affiliate-link/affli-linksprepend.php', 'affli_prependlinks_admin_header');
}

/* Add header to affli-options page */
function affli_options_admin_header()
{
  require_once 'classes/views/affli-options/head.php';
}

/* Add header to affli-clicks page */
function affli_reports_admin_header()
{
  // Don't show this sheesh if we're displaying the vuid or ip grouping
  if(!isset($_GET['ip']) and !isset($_GET['vuid']))
  {
    global $affli_siteurl, $affli_click, $affli_utils;

    $params = $affli_click->get_params_array();
    $first_click = $affli_utils->getFirstClickDate();

    // Adjust for the first click
    if(isset($first_click))
    {
      $min_date = (int)((time()-$first_click)/60/60/24);

      if($min_date < 30)
        $start_timestamp = $affli_utils->get_start_date($params,$min_date);
      else
        $start_timestamp = $affli_utils->get_start_date($params,30);

      $end_timestamp = $affli_utils->get_end_date($params);
    }
    else
    {
      $min_date = 0;
      $start_timestamp = time();
      $end_timestamp = time();
    }

    $link_id = $params['l'];
    $type = $params['type'];
    $group = $params['group'];

    require_once 'classes/views/affli-clicks/head.php';
  }
}

/* Add header to the affli-links page */
function affli_links_admin_header()
{
  global $affli_siteurl;
  require_once 'classes/views/affli-links/head.php';
}
function affli_prependlinks_admin_header()
{
	global $affli_siteurl;
  	require_once 'classes/views/affli-links/affli-prependlinks.php';
}
/* Add header to the affli-links page */
function affli_groups_admin_header()
{
  global $affli_siteurl;
  require_once 'classes/views/affli-groups/head.php';
}

/********* ADD REDIRECTS FOR STANDARD MODE ***********/
function affli_redirect()
{
  // we're now catching the 404 error before the template_redirect
  // instead of checking for pretty link redirect on each page load
  if(is_404())
  {
    global $affli_blogurl, $wpdb, $affli_link;
    
    // Resolve WP installs in sub-directories
    preg_match('#^http://.*?(/.*)$#', $affli_blogurl, $subdir);

    $match_str = '#^'.$subdir[1].'/(.*?)([\?/].*?)?$#';
    
    if(preg_match($match_str, $_SERVER['REQUEST_URI'], $match_val))
    {
      // match short slugs (most common)
      affli_link_redirect_from_slug($match_val[count($match_val)-1],$match_val[count($match_val)-2]);

      // Match nested slugs (pretty link sub-directory nesting)
	
      $possible_links = $wpdb->get_col("SELECT slug FROM " . $affli_link->table_name . " WHERE CONCAT('/',slug) like '%".$match_val[count($match_val)-1]."%'",0);
      foreach($possible_links as $possible_link)
      {
        // Try to match the full link against the URI
        if( preg_match('#^'.$subdir[1].'/(.*)/('.$possible_link.')([\?/].*?)?$#', $_SERVER['REQUEST_URI'], $match_val) )
          affli_link_redirect_from_slug($possible_link,$match_val[count($match_val)-2]);
      }
    }
  }
}

// For use with the affli_redirect function
function affli_link_redirect_from_slug($slug,$param_str)
{
  global $affli_link, $affli_utils;

  $link = $affli_link->getOneFromSlug(urldecode($slug));
  if(isset($link->slug) and !empty($link->slug))
  {
    $custom_get = $_GET;
  
    if(isset($link->param_forwarding) and $link->param_forwarding == 'custom')
      $custom_get = $affli_utils->decode_custom_param_str($link->param_struct, $param_str);
  
    $affli_utils->track_link($link->slug,$custom_get); 
    exit;
  }
}

//add_action('init', 'affli_redirect'); //Redirect
add_action('template_redirect', 'affli_redirect',0); //Redirect



/********* EXPORT LINK API VIA XML-RPC ***********/
function affli_export_api($api_methods)
{
  $api_methods['affli.create_pretty_link']  = 'affli_xmlrpc_create_pretty_link';
  $api_methods['affli.get_all_groups']      = 'affli_xmlrpc_get_all_groups';
  $api_methods['affli.get_all_links']       = 'affli_xmlrpc_get_all_links';
  $api_methods['affli.get_link']            = 'affli_xmlrpc_get_link';
  $api_methods['affli.get_link_from_slug']  = 'affli_xmlrpc_get_link_from_slug';
  $api_methods['affli.get_pretty_link_url'] = 'affli_xmlrpc_get_pretty_link_url';
  $api_methods['affli.api_version']         = 'affli_xmlrpc_api_version';

  return $api_methods;
}

add_filter('xmlrpc_methods', 'affli_export_api');

/********* INSTALL PLUGIN ***********/
function affli_install()
{

  global $wpdb, $affli_utils;
  $db_version = 5; // this is the version of the database we're moving to
  $old_db_version = get_option('affli_db_version');

  $groups_table       = $wpdb->prefix . "affli_groups";
  $clicks_table       = $wpdb->prefix . "affli_clicks";
  $pretty_links_table = $wpdb->prefix . "affli_links";
  $link_metas_table   = $wpdb->prefix . "affli_link_metas";
 $link_metas_table1   = $wpdb->prefix . "prependsettings";
  $charset_collate = '';
  if( $wpdb->has_cap( 'collation' ) )
  {
    if( !empty($wpdb->charset) )
      $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
    if( !empty($wpdb->collate) )
      $charset_collate .= " COLLATE $wpdb->collate";
  }

  if($db_version != $old_db_version)
  {
    $affli_utils->migrate_before_db_upgrade();

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    /* Create/Upgrade Clicks (Hits) Table */
    $sql = "CREATE TABLE " . $clicks_table . " (
              id int(11) NOT NULL auto_increment,
              ip varchar(255) default NULL,
              browser varchar(255) default NULL,
              btype varchar(255) default NULL,
              bversion varchar(255) default NULL,
              os varchar(255) default NULL,
              referer varchar(255) default NULL,
              host varchar(255) default NULL,
              uri varchar(255) default NULL,
              robot tinyint default 0,
              first_click tinyint default 0,
              created_at datetime NOT NULL,
              link_id int(11) default NULL,
              vuid varchar(25) default NULL,
              PRIMARY KEY  (id),
              KEY link_id (link_id),
              KEY vuid (vuid)".
              // We won't worry about this constraint for now.
              //CONSTRAINT ".$clicks_table."_ibfk_1 FOREIGN KEY (link_id) REFERENCES $pretty_links_table (id)
            ") {$charset_collate};";
    
    dbDelta($sql);
    
    /* Create/Upgrade Links Table */
    $sql = "CREATE TABLE " . $pretty_links_table . " (
              id int(11) NOT NULL auto_increment,
              name varchar(255) default NULL,
              description text default NULL,
              url text default NULL,
              slug varchar(255) default NULL,
              nofollow tinyint(1) default 0,
              track_me tinyint(1) default 1,
              param_forwarding varchar(255) default NULL,
              param_struct varchar(255) default NULL,
              redirect_type varchar(255) default '307',
              created_at datetime NOT NULL,
              group_id int(11) default NULL,
              PRIMARY KEY  (id),
              KEY group_id (group_id),
              KEY slug (slug)
            ) {$charset_collate};";
    
    dbDelta($sql);


    $sql = "CREATE TABLE {$link_metas_table} (
              id int(11) NOT NULL auto_increment,
              meta_key varchar(255) default NULL,
              meta_value longtext default NULL,
              link_id int(11) NOT NULL,
              created_at datetime NOT NULL,
              PRIMARY KEY  (id),
              KEY link_id (link_id)
            ) {$charset_collate};";
    
    dbDelta($sql);

	$sql = "CREATE TABLE {$link_metas_table1} (
              id int(11) NOT NULL auto_increment,
              prependname varchar(255) default NULL,
              PRIMARY KEY  (id)
            ) {$charset_collate};";
    
    dbDelta($sql);
    $affli_utils->migrate_after_db_upgrade();
  }

  // Install / Upgrade Link Pro
  $afflipro_username = get_option( 'afflipro_username' );
  $afflipro_password = get_option( 'afflipro_password' );

  if( !empty($afflipro_username) and !empty($afflipro_password) and
      $affli_utils->get_pro_user_type($afflipro_username,$afflipro_password) != false )
    $afflipro_response = $affli_utils->download_and_install_pro( $afflipro_username, $afflipro_password );

  /***** SAVE OPTIONS *****/
  $affli_options_str = get_option('affli_options');
  $affli_options = unserialize($affli_options_str);
  
  // If unserializing didn't work
  if(!$affli_options)
    $affli_options = new PrliOptions();
  else
    $affli_options->set_default_options(); // Sets defaults for unset options

  $affli_options_str = serialize($affli_options);
  delete_option('affli_options');
  add_option('affli_options',$affli_options_str);

  /***** SAVE DB VERSION *****/
  delete_option('affli_db_version');
  add_option('affli_db_version',$db_version);
}
function affli_install3()
{
	$link_metas_table1   = $wpdb->prefix . "prependsettings";
	$charset_collate = '';
	if( $wpdb->has_cap( 'collation' ) )
	{
	if( !empty($wpdb->charset) )
	$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	if( !empty($wpdb->collate) )
	$charset_collate .= " COLLATE $wpdb->collate";
	}
	$sql = "CREATE TABLE IF NOT EXISTS {$link_metas_table1} (
              id int(11) NOT NULL auto_increment,
              prependname varchar(255) default NULL,
              PRIMARY KEY  (id)
            ) {$charset_collate};";
    
    dbDelta($sql);
}
// Ensure this gets called on first install
register_activation_hook(__FILE__,'affli_install');
//add_action('activate_affiliate-link/affiliate-link.php', 'affli_install3');
//customize to display the add link options while adding post

function affli_affiliate_post_menu()
{
global $wpdb,$wp_affiliate_prefix;

$getPrependname="select * from ".$wpdb->prefix."prependsettings";
$query = $wpdb->prepare($getPrependname);
$resultsarr= $wpdb->get_row($query, 'OBJECT');
$prependname=$resultsarr->prependname;
if($prependname!="")
$prependname=$prependname."/";
//require(AFLI_VIEWS_PATH.'/affli-links/form.php');
$category_form = "
<div id=\"wp_affiliate_add_category_results\" style=\"color:red;\"></div>";
echo $category_form;

echo "<h2>Listing Of Links</h2>";

$category_list = links_list();

if($category_list)
	echo '<div id="linksdisplay">'.links_list().'</div>';
else
	echo "<ul id=\"wpanav\"></ul>";//"No categories! Add at least one in order to add links!";

echo '<input type="hidden" name="id" value="'. $id.'"><input type="hidden" name="redirect_type" id="redirect_type" value="307"><input type="hidden" name="param_forwarding" value="off"><input type="hidden" name="param_struct" value=""><input type="hidden" name="track_me" value="1">';
echo "<h2>Add A Link</h2><iframe style='display:none;' name='submitform'></iframe>";
echo '<div  id="linkerrors" class="error" style="display:none;"></div>';
echo '<table>
<tr class="form-field">
    <td width="85px" valign="top">Target URL*: </td>
    <td><input type="text" name="url" id="url" value="'.htmlentities($values['url'],ENT_COMPAT,'UTF-8').'">
    <span class="description toggle_pane"><br/>Enter the URL you want to mask and track. Don\'t forget to start your url with <code>http://</code> or <code>https://</code>. Example: <code>http://www.yoururl.com</code></span></td>
  </tr>
  <tr>
    <td valign="top">Link*: </td>
    <td><strong>'.get_bloginfo('wpurl')."/".$prependname.'</strong><input type="text" id="slug" name="slug" value="'.$values['slug'].'" size="40"/>
    <span class="toggle_pane description"><br/>Enter the slug (word trailing your main URL) that will form your link and redirect to the URL above.</span></td>
  </tr>
  <tr class="form-field">
    <td width="85px" valign="top">Anchor Text: </td>
    <td><input type="text" name="name" id="name" value="'.$values['name'].'" />
      <span class="description toggle_pane"><br/>This will act as the title of your Link. If a name is not entered here then the slug name will be used.</span></td>
  </tr>
<tr>
<td colspan="2">
<input type="button" onclick="javascript:document.post.target=\'submitform\';document.post.action=\'?page='.AFLI_PLUGIN_NAME.'/affli-links.php&from=post&action=create\';document.post.submit();" name="Sub" value="Create" />
</td></tr>
</table>';

}
function links_list($parent = 0, $escape=false)
{	
	global $wpdb,$wp_affiliate_prefix;

	$table_name = $wpdb->prefix . $wp_affiliate_prefix."affli_links";
	
	$results = $wpdb->get_results("SELECT * FROM ".$table_name." order by id asc");
	
	$link_list = "<select name='urls' id='urls'>";
	$i=1;
	$getPrependname="select * from ".$wpdb->prefix."prependsettings";
	$query = $wpdb->prepare($getPrependname);
	$resultsarr= $wpdb->get_row($query, 'OBJECT');
	$prependname=$resultsarr->prependname;
	if($prependname!="")
	$prependname=$prependname."/";
	foreach($results as $result)
	{
		//$clean_cat = get_category_slug($result->category);		
		$link_list .= "<option value='".$i."' >".$result->url." </option>";
		$hiddenids.="<input type='hidden' id='hiddenid".$i."' value='".get_bloginfo('wpurl')."/".$prependname.$result->slug."'><input type='hidden' id='hiddenname".$i."' value='".$result->name."'>";
	$i++;
	}
	
	//$clean_link = get_bloginfo('wpurl')."/".$slugname;
	$send_to_editor = getUrlfun();
	$link_list .= "</select>".$hiddenids."<a href='#' onclick='javascript:getsendtourlfun();'>Send Link To Editor</a> ";
	
	return $link_list;
}
function getUrlfun()
{
?>
<script type="text/javascript" language="JavaScript">
function getsendtourlfun()
{
var url=document.getElementById('urls').value;
send_to_editor("<a href="+(document.getElementById('hiddenid'+url).value)+">"+(document.getElementById('hiddenname'+url).value)+"</a>");
}
</script>
<?php
}
function affli_affiliate_create_menus()
{
add_meta_box("wp_affiliate_link_div","Links", "affli_affiliate_post_menu","post","normal");

}


if (is_admin())
    add_action('admin_menu', 'affli_affiliate_create_menus');
?>
