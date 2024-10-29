<?php
class PrliGroup
{
  var $table_name;

  function PrliGroup()
  {
    global $wpdb;
    $this->table_name = "{$wpdb->prefix}affli_groups";
  }

  function create( $values )
  {
    global $wpdb, $wp_rewrite;

    $query = 'INSERT INTO ' . $this->table_name . 
             ' (name,description,created_at) VALUES (\'' .
                   $values['name'] . '\',\'' . 
                   $values['description'] . '\',' . 
                   'NOW())';
    $query_results = $wpdb->query($query);
    return $wpdb->insert_id;
  }

  function update( $id, $values )
  {
    global $wpdb, $wp_rewrite;

    $query = 'UPDATE ' . $this->table_name . 
                ' SET name=\'' . $values['name'] . '\', ' .
                    ' description=\'' . $values['description'] . '\' ' .
                ' WHERE id='.$id;
    $query_results = $wpdb->query($query);
    return $query_results;
  }

  function destroy( $id )
  {
    require_once(AFLI_MODELS_PATH.'/models.inc.php');
    global $wpdb, $affli_link, $wp_rewrite;

    // Disconnect the links from this group
    $query = 'UPDATE ' . $affli_link->table_name . 
                ' SET group_id = NULL ' .
                ' WHERE group_id='.$id;
    $query_results = $wpdb->query($query);

    $destroy = 'DELETE FROM ' . $this->table_name .  ' WHERE id=' . $id;
    return $wpdb->query($destroy);
  }

  function getOne( $id, $include_stats = false )
  {
      global $wpdb, $affli_link, $affli_click;

      if($include_stats)
        $query = 'SELECT gr.*, (SELECT COUNT(*) FROM ' . $affli_link->table_name . ' li WHERE li.group_id = gr.id) as link_count FROM ' . $this->table_name . ' gr WHERE id=' . $id;
      else
        $query = 'SELECT gr.* FROM ' . $this->table_name . ' gr WHERE id=' . $id;
      return $wpdb->get_row($query);
  }

  function getAll( $where = '', $order_by = '', $return_type = OBJECT, $include_stats = false )
  {
      global $wpdb, $affli_utils, $affli_link, $affli_click;

      if($include_stats)
        $query = 'SELECT gr.*, (SELECT COUNT(*) FROM ' . $affli_link->table_name . ' li WHERE li.group_id = gr.id) as link_count FROM ' . $this->table_name . ' gr' . $affli_utils->prepend_and_or_where(' WHERE', $where) . $order_by;
      else
        $query = 'SELECT gr.* FROM ' . $this->table_name . " gr" . $affli_utils->prepend_and_or_where(' WHERE', $where) . $order_by;
      return $wpdb->get_results($query, $return_type);
  }

  // Pagination Methods
  function getRecordCount($where="")
  {
      global $wpdb, $affli_utils;
      $query = 'SELECT COUNT(*) FROM ' . $this->table_name . $affli_utils->prepend_and_or_where(' WHERE', $where);
      return $wpdb->get_var($query);
  }

  function getPageCount($p_size, $where="")
  {
      return ceil((int)$this->getRecordCount($where) / (int)$p_size);
  }

  function getPage($current_p,$p_size, $where = "", $order_by = '')
  {
      global $wpdb, $affli_link, $affli_utils, $affli_click;
      $end_index = $current_p * $p_size;
      $start_index = $end_index - $p_size;
      $query = 'SELECT gr.*, (SELECT COUNT(*) FROM ' . $affli_link->table_name . ' li WHERE li.group_id = gr.id) as link_count FROM ' . $this->table_name . ' gr' . $affli_utils->prepend_and_or_where(' WHERE', $where) . $order_by .' LIMIT ' . $start_index . ',' . $p_size;
      $results = $wpdb->get_results($query);
      return $results;
  }

  // Set defaults and grab get or post of each possible param
  function get_params_array()
  {
    $values = array(
       'action'     => (isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:'list')),
       'id'         => (isset($_GET['id'])?$_GET['id']:(isset($_POST['id'])?$_POST['id']:'')),
       'paged'      => (isset($_GET['paged'])?$_GET['paged']:(isset($_POST['paged'])?$_POST['paged']:1)),
       'group'      => (isset($_GET['group'])?$_GET['group']:(isset($_POST['group'])?$_POST['group']:'')),
       'search'     => (isset($_GET['search'])?$_GET['search']:(isset($_POST['search'])?$_POST['search']:'')),
       'sort'       => (isset($_GET['sort'])?$_GET['sort']:(isset($_POST['sort'])?$_POST['sort']:'')),
       'sdir'       => (isset($_GET['sdir'])?$_GET['sdir']:(isset($_POST['sdir'])?$_POST['sdir']:''))
    );

    return $values;
  }

  function validate( $values )
  {
    global $wpdb, $affli_utils;

    $errors = array();
    if( empty($values['name']) )
      $errors[] = "Group must have a name.";

    return $errors;
  }
}
?>
