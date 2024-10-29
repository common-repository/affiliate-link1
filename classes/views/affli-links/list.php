<?php
$getPrependname="select * from ".$wpdb->prefix."prependsettings";
$query = $wpdb->prepare($getPrependname);
$resultsarr= $wpdb->get_row($query, 'OBJECT');
$prependname=$resultsarr->prependname;
if($prependname!="")
$prependname=$prependname."/";
?>
<div class="wrap">

  <h2><img src="<?php echo AFLI_URL.'/images/pretty-link-med.png'; ?>"/>&nbsp;Link Cloaker: Links</h2>
  <?php
  if(empty($params['group']))
  {
    $permalink_structure = get_option('permalink_structure');
    if(!$permalink_structure or empty($permalink_structure))
    {
    ?>
      <div class="error" style="padding-top: 5px; padding-bottom: 5px;"><strong>WordPress Must be Configured:</strong> Link Cloaker won't work until you select a Permalink Structure other than "Default" ... <a href="<?php echo $affli_siteurl; ?>/wp-admin/options-permalink.php">Permalink Settings</a></div>
    <?php
    }
	if($affli_message)
	{
  ?>
  <div id="message" class="updated fade" style="padding:5px;"><?php echo $affli_message; ?></div> 
<?php } ?>
  <?php do_action('affli-link-message'); ?>
  <div id="search_pane" style="float: right;">
    <form class="form-fields" name="link_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
      <?php wp_nonce_field('affli-links'); ?>
      <input type="hidden" name="sort" id="sort" value="<?php echo $sort_str; ?>" />
      <input type="hidden" name="sdir" id="sort" value="<?php echo $sdir_str; ?>" />
      <input type="text" name="search" id="search" value="<?php echo $search_str; ?>" style="display:inline;"/>
      <div class="submit" style="display: inline;"><input type="submit" name="Submit" value="Search"/>
      <?php
      if(!empty($search_str))
      {
      ?>
      or <a href="?page=<?php echo AFLI_PLUGIN_NAME; ?>/affli-links.php">Reset</a>
      <?php
      }
      ?>
      </div>
    </form>
  </div>
  <div id="button_bar">
    <p><a href="?page=<?php echo AFLI_PLUGIN_NAME; ?>/affli-add-link.php"><img src="<?php echo AFLI_URL.'/images/pretty-link-add.png'; ?>"/> Add a Link</a>
    <?php do_action('affli-link-nav'); ?>
    </p>
  </div>
  <?php
  }
  else
  {
  ?>
  <h3><?php echo $affli_message; ?></h3> 
  <a href="?page=<?php echo AFLI_PLUGIN_NAME; ?>/affli-groups.php">&laquo Back to Groups</a>
  <br/><br/>
  <?php
  }
  ?>
<form class="form-fields link-list-form" name="link_list_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<input type="hidden" name="action" value="list-form"/>
<?php $footer = false; require(AFLI_VIEWS_PATH.'/shared/link-table-nav.php'); ?>
<table class="widefat post fixed" cellspacing="0">
    <thead>
    <tr>
      <th class="manage-column" width="30%"><?php do_action('affli-list-header-icon'); ?><a href="?page=<?php echo AFLI_PLUGIN_NAME; ?>/affli-links.php&sort=name<?php echo (($sort_str == 'name' and $sdir_str == 'asc')?'&sdir=desc':''); ?>">Name<?php echo (($sort_str == 'name')?'&nbsp;&nbsp;&nbsp;<img src="'.AFLI_URL.'/images/'.(($sdir_str == 'desc')?'arrow_down.png':'arrow_up.png').'"/>':'') ?></a></th>
      <?php do_action('affli_link_column_header'); ?>
      <th class="manage-column" width="10%"><a href="?page=<?php echo AFLI_PLUGIN_NAME; ?>/affli-links.php&sort=clicks<?php echo (($sort_str == 'clicks' and $sdir_str == 'asc')?'&sdir=desc':''); ?>">Hits / Uniq<?php echo (($sort_str == 'clicks')?'&nbsp;&nbsp;&nbsp;<img src="'.AFLI_URL.'/images/'.(($sdir_str == 'desc')?'arrow_down.png':'arrow_up.png').'"/>':'') ?></a></th>
      <!--<th class="manage-column" width="5%"><a href="?page=<?php //echo AFLI_PLUGIN_NAME; ?>/affli-links.php&sort=group_name<?php //echo (($sort_str == 'group_name' and $sdir_str == 'asc')?'&sdir=desc':''); ?>">Group<?php //echo (($sort_str == 'group_name')?'&nbsp;&nbsp;&nbsp;<img src="'.AFLI_URL.'/images/'.(($sdir_str == 'desc')?'arrow_down.png':'arrow_up.png').'"/>':'') ?></a></th>-->
      <th class="manage-column" width="12%"><a href="?page=<?php echo AFLI_PLUGIN_NAME; ?>/affli-links.php&sort=created_at<?php echo (($sort_str == 'created_at' and $sdir_str == 'asc')?'&sdir=desc':''); ?>">Created<?php echo ((empty($sort_str) or $sort_str == 'created_at')?'&nbsp;&nbsp;&nbsp;<img src="'.AFLI_URL.'/images/'.((empty($sort_str) or $sdir_str == 'desc')?'arrow_down.png':'arrow_up.png').'"/>':'') ?></a></th>
      <th class="manage-column" width="20%"><a href="?page=<?php echo AFLI_PLUGIN_NAME; ?>/affli-links.php&sort=slug<?php echo (($sort_str == 'slug' and $sdir_str == 'asc')?'&sdir=desc':''); ?>">Links<?php echo (($sort_str == 'slug')?'&nbsp;&nbsp;&nbsp;<img src="'.AFLI_URL.'/images/'.(($sdir_str == 'desc')?'arrow_down.png':'arrow_up.png').'"/>':'') ?></a></th>
    </tr>
    </thead>
  <?php

  if($record_count <= 0)
  {
      ?>
    <tr>
      <td colspan="5">No Links have been created.</td>
    </tr>
    <?php
  }
  else
  {
    global $affli_blogurl;
    foreach($links as $link)
    {
      $pretty_link_url = "{$affli_blogurl}/{$prependname}{$link->slug}";
      ?>
      <tr style="min-height: 75px; height: 75px;">
        <td class="edit_link">

        <?php do_action('affli_list_icon',$link->id); ?>
        <?php if( $link->redirect_type == 'prettybar' ) { ?>
            <img src="<?php echo AFLI_URL.'/images/pretty-link-small.png'; ?>" title="Using PrettyBar" width="13px" height="13px" />
        <?php }
        else if( $link->redirect_type == 'cloak' ) { ?>
            <img src="<?php echo AFLI_URL.'/images/ultra-cloak.png'; ?>" title="Using Ultra Cloak" width="13px" height="13px" />
        <?php }
        else if( $link->redirect_type == 'pixel' ) { ?>
          <img src="<?php echo AFLI_URL.'/images/pixel_track.png'; ?>" width="13px" height="13px" name="Pixel Tracking Enabled" alt="Pixel Tracking Enabled" title="Pixel Tracking Enabled"/>&nbsp;
        <?php }
        else if( $link->redirect_type == '307' ) { ?>
          <span title="Temporary Redirection (307)" style="font-size: 14px; line-height: 14px; padding: 0px; margin: 0px; color: green;"><strong>T</strong></span>&nbsp;
        <?php }
        else if( $link->redirect_type == '301' ) { ?>
          <span title="Permanent Redirection (301)" style="font-size: 14px; line-height: 14px; padding: 0px; margin: 0px; color: green;"><strong>P</strong></span>&nbsp;
        <?php } ?>

        <?php if( $link->nofollow ) { ?>
            <img src="<?php echo AFLI_URL.'/images/nofollow.png'; ?>" title="nofollow" width="13px" height="13px" />
        <?php }

        if($link->param_forwarding == 'on')
        {
        ?>
          <img src="<?php echo AFLI_URL.'/images/forward_params.png'; ?>" width="13px" height="13px" name="Standard Parameter Forwarding Enabled" alt="Standard Parameter Forwarding Enabled" title="Standard Parameter Forwarding Enabled"/>&nbsp;
        <?php
        }
        else if($link->param_forwarding == 'custom')
        {
        ?>
          <img src="<?php echo AFLI_URL.'/images/forward_params.png'; ?>" width="13px" height="13px" name="Custom Parameter Forwarding Enabled" alt="Custom Parameter Forwarding Enabled" title="Custom Parameter Forwarding Enabled"/>&nbsp;
        <?php
        }
        ?>

        <?php if( $link->redirect_type != 'pixel' )
        {
        ?>
          <a href="<?php echo $link->url; ?>" target="_blank" title="Visit Target URL: <?php echo $link->url; ?> in a New Window"><img src="<?php echo AFLI_URL.'/images/url_icon.gif'; ?>" width="13px" height="13px" name="Visit" alt="Visit"/></a>&nbsp;
          <a href="<?php echo $pretty_link_url; ?>" target="_blank" title="Visit Pretty Link: <?php echo $pretty_link_url; ?> in a New Window"><img src="<?php echo AFLI_URL.'/images/url_icon.gif'; ?>" width="13px" height="13px" name="Visit" alt="Visit"/></a>&nbsp;
        <?php
        }
        ?>
        <a class="slug_name" href="?page=<?php echo AFLI_PLUGIN_NAME; ?>/affli-links.php&action=edit&id=<?php echo $link->id; ?>" title="Edit <?php echo stripslashes($link->name); ?>"><?php echo stripslashes($link->name); ?></a>
          <br/>
          <div class="link_actions">
            <a href="?page=<?php echo AFLI_PLUGIN_NAME; ?>/affli-links.php&action=edit&id=<?php echo $link->id; ?>" title="Edit <?php echo $link->slug; ?>">Edit</a>&nbsp;|
            <a href="?page=<?php echo AFLI_PLUGIN_NAME; ?>/affli-links.php&action=destroy&id=<?php echo $link->id; ?>"  onclick="return confirm('Are you sure you want to delete your <?php echo $link->name; ?> Link? This will delete the Link and all of the statistical data about it in your database.');" title="Delete <?php echo $link->slug; ?>">Delete</a>
            <?php if( $link->track_me ) { ?>
            <!--|&nbsp;<a href="?page=<?php //echo AFLI_PLUGIN_NAME; ?>/affli-links.php&action=reset&id=<?php //echo $link->id; ?>"  onclick="return confirm('Are you sure you want to reset your <?php //echo $link->name; ?> Pretty Link? This will delete all of the statistical data about this Pretty Link in your database.');" title="Reset <?php //echo $link->name; ?>">Reset</a>-->&nbsp;|
            <a href="?page=<?php echo AFLI_PLUGIN_NAME; ?>/affli-clicks.php&l=<?php echo $link->id; ?>" title="View clicks for <?php echo $link->slug; ?>">Hits</a>
            <?php do_action('affli-link-action',$link->id); ?>
            <?php } ?>
            <?php if( $link->redirect_type != 'pixel' )
            {
            ?>
            <!--|&nbsp;<a href="http://twitter.com/home?status=<?php //echo $pretty_link_url; ?>" target="_blank" title="Post <?php //echo $pretty_link_url; ?> to Twitter">Tweet</a>&nbsp;|
            <a href="mailto:?subject=Pretty Link&body=<?php //echo $pretty_link_url; ?>" target="_blank" title="Send <?php //echo $pretty_link_url; ?> in an Email">Email</a>-->
            <?php
            }
            ?>
          </div>
        </td>
        <?php do_action('affli_link_column_row',$link->id); ?>
        <td><?php echo (($link->track_me)?"<a href=\"?page=".AFLI_PLUGIN_NAME."/affli-clicks.php&l=$link->id\" title=\"View clicks for $link->slug\">$link->clicks/$link->uniques</a>":"<img src=\"".AFLI_URL."/images/not_tracking.png\" title=\"This link isn't being tracked\"/>"); ?></td>
       <!-- <td><a href="?page=<?php //echo AFLI_PLUGIN_NAME; ?>/affli-links.php&group=<?php //echo $link->group_id; ?>"><?php //echo $link->group_name; ?></a></td>-->
        <td><?php echo $link->created_at; ?></td>
        </td>
        <td><input type='text' style="font-size: 10px; width: 100%;" readonly="true" onclick='this.select();' onfocus='this.select();' value='<?php echo $pretty_link_url; ?>' /><br/>
        <?php if( $link->redirect_type != 'pixel' )
        {
        ?>
        <span style="font-size: 8px;" title="<?php echo $link->url; ?>"><strong>Target URL:</strong> <?php echo htmlentities((substr($link->url,0,47) . ((strlen($link->url) >= 47)?'...':'')),ENT_COMPAT,'UTF-8'); ?></span></td>
        <?php
        }
        ?>
      </tr>
      <?php
    }
  }
  ?>
    <tfoot>
    <tr>
      <th class="manage-column"><?php do_action('affli-list-header-icon'); ?>Name</th>
      <?php do_action('affli_link_column_footer'); ?>
      <th class="manage-column">Hits / Uniq</th>
      <th class="manage-column">Created</th>
      <th class="manage-column">Links</th>
    </tr>
    </tfoot>
</table>
<?php $footer = true; require(AFLI_VIEWS_PATH.'/shared/link-table-nav.php'); ?>
</form>

</div>
