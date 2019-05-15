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

<!--Блок вывода в браузер страницы настроек плагина-->

    <div class="wrap">

    <!--Название раздела настроек-->
    <h3><?php echo __('Settings','example_plugin'); ?></h3>

    <!--Начало формы для обработки настроек. 2 чекбокса оставляю на потом как пример, они пишутся в опции WP-->
    <form method="post" action="<? echo $_SERVER['REQUEST_URI'];?>">
    <div class="guests-form-table">
    <!--Первый чекбокс - будет ли плагин обрабатывать заголовки записей-->
        <input name="c4c_modify_title" type="checkbox" <?if($c4c_modify_title)echo "checked";?>> <?php echo __('First settings Cbox','example_plugin'); ?>
    <!--Второй чекбокс - будет ли плагин обрабатывать тело записей-->
        <input name="c4c_modify_content" type="checkbox" <?if($c4c_modify_content)echo "checked";?>> <?php echo __('Second settings Cbox','example_plugin'); ?>
    <!--"Функциональная" часть кнопки сохранения настроек-->
    <input type="hidden" name="cmd" value="c4c_save_opt">
    <span class="submit">
    <!--Вывод кнопки сохранения настроек в браузер. Стандартная функция Wordpress-->
    <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
    </span>
    </div>
    </form> <!--Конец формы обработки настроек-->

    <!--Вывод информации о плагине. Например - кем разработан-->
    <h3><?php echo __('Plugin developed','example_plugin'); ?></h3>
    <?php echo __('By: <a href="http://qkomp.zp.ua/" target="_blank">-=Pronik=-</a>','example_plugin'); ?>

<!--Заголовок блока гостей-->
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
      <!--Вывод кнопки сохранения настроек в браузер. Стандартная функция Wordpress-->
      <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
      </span>
    </div>
    </form>
    
<!--Конец заголовка блока гостей-->

<!--Заголовок блока участников-->
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
<!--Конец заголовка блока участников-->
    
    <!--Блок ввода новых фраз в таблицу настроек плагина. Сначала идёт справка для пользователя-->
<div style="display:none;">
    <h3><?php echo __('Adding phrases','example_plugin'); ?></h3>
    <!--Начало формы ввода. Форма содержит текстовое поле для ввода шириной 80 символов и высотой 12 строк-->
    <table class="form-table" width="300px">
    <tr>
    <td>
        <?php echo __('Format phrases: Title|Body','example_plugin'); ?><br />
    <form method="post" action="<? echo $_SERVER['REQUEST_URI'];?>">
    <!--Поле для ввода новых фра-->
    <textarea cols=80 rows=12 name="prefs_base"></textarea>
    </td>
    </tr>
    </table>

    <!--Кнопка для сохранения фраз. По аналогии с кнопкой сохранения настроек,
    но без применения стандартной ф-и Wordpress-->
    <input type="hidden" name="cmd" value="add_prefs">
    <p class="submit">
    <input type="submit" name="Submit" value="<?php echo __('Add phrases','example_plugin'); ?>" />
    </p>
    </form>

    <!--Форма, содержащая единственную кнопку - очистки таблицы настроек плагина-->
    <form method="post" action="<? echo $_SERVER['REQUEST_URI'];?>">
    <input type="hidden" name="cmd" value="del_prefs">
    <input type="submit" name="Submit" value="<?php echo __('Remove all phrases from the database','example_plugin'); ?>" />
    </form>
    </div>
