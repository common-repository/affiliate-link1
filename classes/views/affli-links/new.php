<div class="wrap">
<h2><img src="<?php echo AFLI_URL.'/images/pretty-link-med.png'; ?>"/>&nbsp;Link Cloaker: Add Link</h2>

<?php
  require(AFLI_VIEWS_PATH.'/shared/errors.php');
?>

<form name="form1" method="post" action="?page=<?php echo AFLI_PLUGIN_NAME ?>/affli-links.php">
<input type="hidden" name="action" value="create">
<?php wp_nonce_field('update-options'); ?>
<input type="hidden" name="id" value="<?php echo $id; ?>">

<?php
  require(AFLI_VIEWS_PATH.'/affli-links/form.php');
?>

<p class="submit">
<input type="submit" name="Submit" value="Create" />&nbsp;or&nbsp;<a href="?page=<?php echo AFLI_PLUGIN_NAME ?>/affli-links.php">Cancel</a>
</p>

</form>
</div>
