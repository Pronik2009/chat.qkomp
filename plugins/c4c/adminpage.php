<style type="text/css">
   td { 
    border: 1px solid black;
   }
</style>
<?php 
function add_plugin_style() {
    wp_enqueue_style( 'style', get_template_uri().'style.css');
    wp_enqueue_style( 'c4c', plugin_dir_url( __FILE__ ).'c4c.css');
};
add_action( 'wp_enqueue_scripts', 'add_plugin_style' ); 
?>

<!--���� ������ � ������� �������� �������� �������-->

    <div class="wrap">

    <!--�������� ������� ��������-->
    <h3><?php echo __('Settings','example_plugin'); ?></h3>

    <!--������ ����� ��� ��������� ��������. 2 �������� �������� �� ����� ��� ������, ��� ������� � ����� WP-->
    <form method="post" action="<? echo $_SERVER['REQUEST_URI'];?>">
    <div class="guests-form-table">
    <!--������ ������� - ����� �� ������ ������������ ��������� �������-->
        <input name="c4c_modify_title" type="checkbox" <?if($c4c_modify_title)echo "checked";?>> <?php echo __('First settings Cbox','example_plugin'); ?>
    <!--������ ������� - ����� �� ������ ������������ ���� �������-->
        <input name="c4c_modify_content" type="checkbox" <?if($c4c_modify_content)echo "checked";?>> <?php echo __('Second settings Cbox','example_plugin'); ?>
    <!--"��������������" ����� ������ ���������� ��������-->
    <input type="hidden" name="cmd" value="c4c_save_opt">
    <span class="submit">
    <!--����� ������ ���������� �������� � �������. ����������� ������� Wordpress-->
    <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
    </span>
    </div>
    </form> <!--����� ����� ��������� ��������-->

    <!--����� ���������� � �������. �������� - ��� ����������-->
    <h3><?php echo __('Plugin developed','example_plugin'); ?></h3>
    <?php echo __('By: <a href="http://qkomp.zp.ua/" target="_blank">-=Pronik=-</a>','example_plugin'); ?>

<!--��������� ����� ������-->
    <h2>Guest list</h2>
    <form method="post" action="<? echo $_SERVER['REQUEST_URI'];?>">
    <div class="form-table">
      <table>
      <tr>
      <td> </td><td>Guest ID</td><td>Event ID</td><td>Guest Name</td><td>Costs amount</td>
      </tr>
<?php 
    $c4c_prefs_table = c4c_get_table_handle('guests');
    $sql = "SELECT * FROM ".$c4c_prefs_table;
    $guests = $wpdb->get_results($sql, ARRAY_A);
    foreach ($guests as $guest) { ?>
      <tr>
      <td><input type="checkbox" name="id" value="<?php echo $guest[id] ?>" /></td>
      <td><?php echo $guest[id]; ?></td>
      <td><?php echo $guest[event_id]; ?></td>
      <td><?php echo $guest[name]; ?></td>
      <td><?php echo $guest[costs]; ?></td>
      </tr>
    <?php }; ?>
      </table>
      <input type="hidden" name="cmd" value="c4c_cost_adding">
      <span class="submit">
      <input type="text" name="Costs" value="<?php _e('Costs amount') ?>" />
      <!--����� ������ ���������� �������� � �������. ����������� ������� Wordpress-->
      <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
      </span>
    </div>
    </form>
    
<!--����� ��������� ����� ������-->

<!--��������� ����� ����������-->
    <h2>Players list</h2>
    <div class="players-form-table">  
      <table>
      <tr>
      <td>Player ID</td><td>Player Name</td><td>Costs amount</td>
      </tr>
<?php 
    $sql = "SELECT * FROM wp_usermeta um INNER JOIN wp_users u ON um.user_id = u.id and um.meta_key='wp_capabilities' and um.meta_value like '%author%'";
    $players = $wpdb->get_results($sql, ARRAY_A);
    foreach ($players as $player) { ?>
      <tr>
      <td><?php echo $player[ID]; ?></td>
      <td><?php echo $player[display_name]; ?></td>
      <td><?php echo $player[user_url]; ?></td>
      </tr>
    <?php }; ?>
      </table>
    </div>
<!--����� ��������� ����� ����������-->
    
    <!--���� ����� ����� ���� � ������� �������� �������. ������� ��� ������� ��� ������������-->
<div style="display:none;">
    <h3><?php echo __('Adding phrases','example_plugin'); ?></h3>
    <!--������ ����� �����. ����� �������� ��������� ���� ��� ����� ������� 80 �������� � ������� 12 �����-->
    <table class="form-table" width="300px">
    <tr>
    <td>
        <?php echo __('Format phrases: Title|Body','example_plugin'); ?><br />
    <form method="post" action="<? echo $_SERVER['REQUEST_URI'];?>">
    <!--���� ��� ����� ����� ���-->
    <textarea cols=80 rows=12 name="prefs_base"></textarea>
    </td>
    </tr>
    </table>

    <!--������ ��� ���������� ����. �� �������� � ������� ���������� ��������,
    �� ��� ���������� ����������� �-� Wordpress-->
    <input type="hidden" name="cmd" value="add_prefs">
    <p class="submit">
    <input type="submit" name="Submit" value="<?php echo __('Add phrases','example_plugin'); ?>" />
    </p>
    </form>

    <!--�����, ���������� ������������ ������ - ������� ������� �������� �������-->
    <form method="post" action="<? echo $_SERVER['REQUEST_URI'];?>">
    <input type="hidden" name="cmd" value="del_prefs">
    <input type="submit" name="Submit" value="<?php echo __('Remove all phrases from the database','example_plugin'); ?>" />
    </form>
    </div>
