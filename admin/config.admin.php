<?php



$this->admin->add_main_menu_item(array(
	'title' => 'Комментарии',
	'key'   => 'comments',
	'icon'  => '/' . EXT_FOLDER . '/comments/admin/menu_admin_comments.png',
	'com'   => EXT_PATH . 'comments/admin/ Comments',
	'priority' => 15,
));