<?php
/*
Plugin Name: Chat 4 Club
Plugin URI: http://qkomp.zp.ua
Description: Only admin area yet.
Version: 0.6
Author: -=Pronik=-
Author URI: http://qkomp.zp.ua
*/


//��� ��������� ������� - ������� ������� � ����������, ������������
//��������� �� ���������
register_activation_hook(__FILE__, 'c4c_set_options');
//��� ����������� ������� - ������� ��������� � �������� ������� ��������
register_deactivation_hook(__FILE__, 'c4c_unset_options');

//�������� ����� ���� � ������� Wordpress - ���� ���������� ��������
add_action('admin_menu', 'c4c_admin_page');
//add_action('init', 'init_textdomain');

//�������� ���������� ������� �������� ������� ��� ������ � ���
$c4c_prefs_table = c4c_get_table_handle('prefs');


//���������� ���������� ������� �������� �������
function c4c_get_table_handle($tablename) {
    //�������� ���������� � WP ���������� - ���������� �� �����
    global $wpdb;
    //������� ���������� ������� �������� �������, ������������� � �� WP-�����
    return $wpdb->prefix . "c4c_".$tablename;
}


//������ ������� �������� �������, ������������� ��������� �� ���������.
//���������� � ������ ��������� �������
function c4c_set_options() {
    global $wpdb;

    //���������� ����� �� ��������� (��� ����� ��������� � ������� �������� WP)
    add_option('c4c_version', '0.6');
    //1. ����� �� ������ �� ��������� ������������ ��������� �������. 0 - ���
    add_option('c4c_modify_title', 0);
    //�� �� ��� ���� �������. 1 - ��
    add_option('c4c_modify_content', 1);


    //����� ������� �����������, �.�. ������ �������� ���������� �� ����� ��������� �������,
    //����� ����� � ���� ��� �� ����� ���� ����������
    $c4c_prefs_table = c4c_get_table_handle('prefs');

    //���������� ��������� ������� (������ - ������������ ����������� ��������� MySQL
    $charset_collate = '';

    //���� ������ MySQL �� ���� ��������� - ���������� ��������� ��� ��������
    //� ��������� ��� UTF-8
    //if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') )
        $charset_collate = "DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci";

    //���� � �� ����� ��� �� ������� �������� ������� - ������� �.
    if($wpdb->get_var("SHOW TABLES LIKE '%c4c_prefs'") != $c4c_prefs_table) {
        $sql = "CREATE TABLE `" . $c4c_prefs_table . "` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL default '',
            `body` VARCHAR(255) NOT NULL default '',
            UNIQUE KEY id (id)
        )$charset_collate";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); //��������� � �������� WP ���
        dbDelta($sql); //������ � ��. ������ ����� �������
    }
} //����� ������� ��������� �������� �������


//��� ����������� ������� ������� ��������� � ������� ������� ��������
function c4c_unset_options() {
    global $wpdb, $c4c_prefs_table;
    delete_option('c4c_version');
    delete_option('c4c_modify_title');
    delete_option('c4c_modify_content');
    $sql = "DROP TABLE ".$c4c_prefs_table;
    $wpdb->query($sql);
}


//������ ������ ��� �������� � �������� �������� ������� � ������� WP
function c4c_admin_page() {
    add_menu_page('c4c', 'Chat 4 Club', 8, __FILE__, 'c4c_options_page');
}


//���������� ������� ������� �� ��������� ����������
function init_textdomain() {
    if (function_exists('load_plugin_textdomain')) {
        load_plugin_textdomain('example_plugin', 'wp-content/plugins/wp-example_plugin');
    }
}


//������� ������� �������� �������, ������������ ��������� ��������, ���������
//������������� (�������� ���������, �������� ����������)
function c4c_options_page() {
    global $wpdb, $c4c_prefs_table;

    //������ ������ � ����������� �������
    $c4c_options = array(
        'c4c_modify_title',
        'c4c_modify_content',
    );

    //��������� ����������������� ����� � ������ ��������� ��������
    $cmd = $_POST['cmd'];

    //������� ������ � ����������� � �������� �� �������� �� ������� ��������
    foreach ($c4c_options as $c4c_opt) {
        $$c4c_opt = get_option($c4c_opt);
    }

    //���� ������������ ����� �������� ��������� - ������� ������� ��������
    if ($cmd == "del_prefs") {
        $sql = "TRUNCATE TABLE ".$c4c_prefs_table;
        $wpdb->query( $sql );
?>

<!--������� ��������� � ���, ��� ��������� ���� �������-->
<div class="updated"><p><strong> <?php echo __('All settings are dropped','example_plugin'); ?>
</strong></p></div>

<?php
    } //����� ����� ������ ��������

    //���� ������� ����� ��������� � �����. ���� - ���������� ��
    //(��������� ������������ ����� �����, ������� ������ ����� ��������� �
    //����� ��������� � ���� �������. ����� ����������� �������� |.
    //������:
    //������ ��� � ���������|� ��� ������ � ���� ������
    if ($cmd == "add_prefs" && $_POST['prefs_base']) {
        //���� ����������� �� ������ � ������� � ������, ����������� - ������� ������
        $lines = explode("\n", $_POST['prefs_base']);

        //���������� ������ � ����������
        foreach($lines as $line){
            //������� �������� �����
            $line = trim($line);
            //���������� ������ ������ (������� � ����. �������� �����)
            if (!$line) continue;
            //���������� ������ �� ��� ���������, ����������� - |
            //$title ����� ����������� � ���������� �������,
            //$body - � ���� �������.
            list($title, $body) = explode("|", $line);
            //����� ��������� � ������� �������.
            $sql = "INSERT INTO ".$c4c_prefs_table." (title, body) VALUES('".$title."','".$body."')";
            $wpdb->query($sql);
        }
?>

<!--�������� � ���, ��� ������ ���� ���������-->
<div class="updated"><p><strong> <?php echo __('All settings are saved','example_plugin'); ?>
</strong></p></div>


<?php
    } //����� ����� ���������� ��������

    //���� ���������� ����� ������� (������������ �� ���������, ������������ �� ���� �������
    //��������� ������� ������ "��������� ���������"
    if ($cmd == "c4c_save_opt") {
        //������� ������� � �����������
        foreach ($c4c_options as $c4c_opt) {
            //������� �������� ������� ����������� �������� ������������� ��������
            $$c4c_opt = $_POST[$c4c_opt];
        }

        //��������� ��������� ������� � ������� �������� � �� WP
        foreach ($c4c_options as $c4c_opt) {
        update_option($c4c_opt, $$c4c_opt);
        }
?>

<!--�������� � ���, ��� ����� ���� ���������-->
<div class="updated"><p><strong> <?php echo __('Settings saved','example_plugin'); ?>
</strong></p></div>

<?php
    } //����� ����� ���������� �����

include('adminpage.php');
} //����� ������� �������� � ��������� �������� ��������.


//������� ���������� "�������" � ��������� ������.
function mod_title($title){
    //���� ����������� ����� ����������� ��������� - ������� ���
    if (get_option('c4c_modify_title')) {
        $title = $title . c4c_get_phrase($ph_type = 'title');
    }
    return $title;
}

//������� ���������� "�������" � ���� ������.
function mod_content($content){
    //���������� - ���� ����������� ����� ����������� ���� ������
    if (get_option('c4c_modify_content')) {
        $content = $content . c4c_get_phrase($ph_type = 'body');
    }
    return $content;
}


//�������� �� ������� ��������� ����� ��� ����������� ���������|���� ������
//� ���������� �.
//�� ���� �������� ��� �����, ������� ���� ������ �� �� (��� ����� - ���
//title ��� body - ��������� �������� �����. �������� � ������� �������.
function c4c_get_phrase($ph_type){
    global $wpsig_sig_table, $wpdb, $c4c_prefs_table;
    $sql = "SELECT ".$ph_type." FROM ".$c4c_prefs_table." ORDER BY RAND() LIMIT 1";
    $phrase = $wpdb->get_var($sql);
    return $phrase;
}

//����� �������
?>