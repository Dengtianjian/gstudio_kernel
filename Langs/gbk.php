<?php

use gstudio_kernel\Foundation\Lang;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

$langs = [
  "kernel" => [
    "all" => "ȫ��",
    "footer_copyright" => "COOOCC ��Ȩ����",
    "backend" => "��̨",
    "logo_text" => "��������̨",
    "site_homepage" => "վ����ҳ",
    "addon_center" => "Ӧ������",
    "please_choose" => "��ѡ��",
    "form_color_input_placeholder" => "������16������ɫֵ ���� #AABBCC",
    "save" => "����",
    "choose_upload_img_type_png_jpg_jpeg" => "��ѡ��png��jpg��jpeg ���͵��ļ�",
    "dictionary_file_does_not_exist" => "δ�ҵ� " . CHARSET . " ��������԰��ļ�",
    "llleal_submission" => "�Ƿ��ύ",
    "saved_successfully" => "����ɹ�",
    "not_logged_in" => "δ��¼",
    "authentication_failed_need_to_log_in_again" => "��֤ʧ�ܣ������µ�¼",
    "login_has_expired_please_log_in_again" => "��¼�ѹ��ڣ������µ�¼",
    "unauthorized_access" => "�Ƿ�����",
    "need_to_install_the_core_plugin" => "��ǰ��Ӧ�����İ�װ���Ĳ��",
    "route_does_not_exits" => "·�ɲ�����",
    "method_not_allowed" => "δ��������󷽷�",
    "turn_on" => "����",
    "close" => "�ر�",
    "lllegal_submission" => "�Ƿ��ύ",
    "clean_set_image" => "���ͼƬ"
  ]
];

Lang::add($langs);
