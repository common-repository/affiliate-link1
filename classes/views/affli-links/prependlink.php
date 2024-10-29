<div class="wrap">
<h2>Link Cloaker: Prepend Link</h2>

<?php
  require(AFLI_VIEWS_PATH.'/shared/errors.php');

if($affli_message)
	{
  ?>
  <div id="message" class="updated fade" style="padding:5px;"><?php echo $affli_message; ?></div> 
<?php } ?>
<form name="form1" method="post" action="?page=<?php echo AFLI_PLUGIN_NAME ?>/affli-prepend-link.php">
<input type="hidden" name="action" value="create">
<?php wp_nonce_field('update-options'); ?>
<input type="hidden" name="id" value="<?php echo $id; ?>">

<input type="text" name="prepend" id="prepend" value="<?php echo $prependname; ?>">

<p class="submit">
<input type="submit" name="Submit" value="Save" />
</p>

</form>
</div>
