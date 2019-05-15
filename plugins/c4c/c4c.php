<?php
/*
Plugin Name: Chat 4 Club
Plugin URI: http://qkomp.zp.ua
Description: Only admin area yet.
Version: 0.6
Author: -=Pronik=-
Author URI: http://qkomp.zp.ua
*/


//При активации плагина - создать таблицу с настройкми, активировать
//настройки по умолчанию
register_activation_hook(__FILE__, 'c4c_set_options');
//При деактивации плагина - удалить настройки и сбросить таблицу настроек
register_deactivation_hook(__FILE__, 'c4c_unset_options');

//Добавить новое меню в админку Wordpress - меню управления плагином
add_action('admin_menu', 'c4c_admin_page');
//add_action('init', 'init_textdomain');

//Получить дескриптор таблицы настроек плагина для работы с ней
$c4c_prefs_table = c4c_get_table_handle('prefs');


//Возвращает дескриптор таблицы настроек плагина
function c4c_get_table_handle($tablename) {
    //объявить встроенную в WP переменную - дескриптор БД блога
    global $wpdb;
    //вернуть дескриптор таблицы настроек плагина, расположенной в БД WP-блога
    return $wpdb->prefix . "c4c_".$tablename;
}


//Создаёт таблицу настроек плагина, устанавливает настройки по умолчанию.
//Вызывается в момент активации плагина
function c4c_set_options() {
    global $wpdb;

    //Установить опции по умолчанию (они будут храниться в таблице настроек WP)
    add_option('c4c_version', '0.6');
    //1. Будет ли плагин по умолчанию обрабатывать заголовки записей. 0 - нет
    add_option('c4c_modify_title', 0);
    //То же для тела записей. 1 - да
    add_option('c4c_modify_content', 1);


    //Вызов функции повторяется, т.к. данные действия происходят на этапе установки плагина,
    //когда вызов в теле еще не может быть осуществлён
    $c4c_prefs_table = c4c_get_table_handle('prefs');

    //Установить кодировку таблицы (пустая - использовать умолчальную кодировку MySQL
    $charset_collate = '';

    //Если версия MySQL не ниже указанной - установить кодировку для хранения
    //и сравнения как UTF-8
    //if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') )
        $charset_collate = "DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci";

    //Если в БД блога еще нёт таблицы настроек плагина - создать её.
    if($wpdb->get_var("SHOW TABLES LIKE '%c4c_prefs'") != $c4c_prefs_table) {
        $sql = "CREATE TABLE `" . $c4c_prefs_table . "` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL default '',
            `body` VARCHAR(255) NOT NULL default '',
            UNIQUE KEY id (id)
        )$charset_collate";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); //обращение к функциям WP для
        dbDelta($sql); //работы с БД. создаём новую таблицу
    }
} //Конец функции установки настроек плагина


//При деактивации плагина удаляет настройки и очищает таблицу настроек
function c4c_unset_options() {
    global $wpdb, $c4c_prefs_table;
    delete_option('c4c_version');
    delete_option('c4c_modify_title');
    delete_option('c4c_modify_content');
    $sql = "DROP TABLE ".$c4c_prefs_table;
    $wpdb->query($sql);
}


//Создаёт кнопку для перехода к страницу настроек плагина в админке WP
function c4c_admin_page() {
    add_menu_page('c4c', 'Chat 4 Club', 8, __FILE__, 'c4c_options_page');
}


//Подгружает перевод плагина из указанной директории
function init_textdomain() {
    if (function_exists('load_plugin_textdomain')) {
        load_plugin_textdomain('example_plugin', 'wp-content/plugins/wp-example_plugin');
    }
}


//Выводит таблицу настроек плагина, обрабатывает изменения настроек, сделанные
//пользоваетеле (обновить настройки, показать обновлённые)
function c4c_options_page() {
    global $wpdb, $c4c_prefs_table;

    //Создаём массив с настройками плагина
    $c4c_options = array(
        'c4c_modify_title',
        'c4c_modify_content',
    );

    //Обработка пользовательского ввода в случае изменения настроек
    $cmd = $_POST['cmd'];

    //Обходим массив с настройками и получаем их значения из таблицы настроек
    foreach ($c4c_options as $c4c_opt) {
        $$c4c_opt = get_option($c4c_opt);
    }

    //Если пользователь решил сбросить настройки - очищаем таблицу настроек
    if ($cmd == "del_prefs") {
        $sql = "TRUNCATE TABLE ".$c4c_prefs_table;
        $wpdb->query( $sql );
?>

<!--Вывести сообщение о том, что настройки были очищены-->
<div class="updated"><p><strong> <?php echo __('All settings are dropped','example_plugin'); ?>
</strong></p></div>

<?php
    } //конец блока сброса настроек

    //Если введены новые настройки в соотв. поле - обработаем их
    //(настройки представляют собой фразы, которые плагин будет добавлять в
    //конец заголовка и тела записей. Фразы разделяются символом |.
    //Пример:
    //Добавь это к заголовку|А это добавь к телу записи
    if ($cmd == "add_prefs" && $_POST['prefs_base']) {
        //Ввод разбивается на строки и кладётся в массив, разделитель - перевод строки
        $lines = explode("\n", $_POST['prefs_base']);

        //Перебираем массив с настроками
        foreach($lines as $line){
            //Удалить переводы строк
            $line = trim($line);
            //Пропустить пустые строки (переход к след. итерации цикла)
            if (!$line) continue;
            //Разделение строки на две подстроки, разделитель - |
            //$title будет добавляться к заголовкам записей,
            //$body - к телу записей.
            list($title, $body) = explode("|", $line);
            //Кладём подстроки в таблицу плагина.
            $sql = "INSERT INTO ".$c4c_prefs_table." (title, body) VALUES('".$title."','".$body."')";
            $wpdb->query($sql);
        }
?>

<!--Сообщить о том, что данные были сохранены-->
<div class="updated"><p><strong> <?php echo __('All settings are saved','example_plugin'); ?>
</strong></p></div>


<?php
    } //конец блока добавления настроек

    //Блок сохранения опций плагина (обрабатывать ли заголовки, обрабатывать ли тело записей
    //Обработка нажатия кнопки "Сохранить настройки"
    if ($cmd == "c4c_save_opt") {
        //Перебор массива с настройками
        foreach ($c4c_options as $c4c_opt) {
            //Каждому элементу массива присваиваем введённое пользователем занчение
            $$c4c_opt = $_POST[$c4c_opt];
        }

        //Обновляем настройки плагина в таблице настроек в БД WP
        foreach ($c4c_options as $c4c_opt) {
        update_option($c4c_opt, $$c4c_opt);
        }
?>

<!--Сообщить о том, что опции были сохранены-->
<div class="updated"><p><strong> <?php echo __('Settings saved','example_plugin'); ?>
</strong></p></div>

<?php
    } //конец блока сохранения опций

include('adminpage.php');
} //Конец функции создания и обработки страницы настроек.


//Функция добавления "хвостов" к заголовку записи.
function mod_title($title){
    //Если установлена опция модификации заголовка - сделать это
    if (get_option('c4c_modify_title')) {
        $title = $title . c4c_get_phrase($ph_type = 'title');
    }
    return $title;
}

//Функция добавления "хвостов" к телу записи.
function mod_content($content){
    //Аналогично - если установлена опция модификации тела записи
    if (get_option('c4c_modify_content')) {
        $content = $content . c4c_get_phrase($ph_type = 'body');
    }
    return $content;
}


//Выбирает из таблицы случайную фразу для модификации заголовка|тела записи
//и возвращает её.
//На вход получает тип фразы, которую надо вынуть из БД (тип фразы - это
//title или body - повторяют названия соотв. столбцов в таблице плагина.
function c4c_get_phrase($ph_type){
    global $wpsig_sig_table, $wpdb, $c4c_prefs_table;
    $sql = "SELECT ".$ph_type." FROM ".$c4c_prefs_table." ORDER BY RAND() LIMIT 1";
    $phrase = $wpdb->get_var($sql);
    return $phrase;
}

//Конец плагина
?>