<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/11
 * Time: 12:20
 */
return [
    'mini_salt'   => 'oaogms',
    'allow_visit' => array('admin/article/draftbox', 'admin/article/mydocument', 'admin/Category/tree', 'admin/Index/verify', 'admin/file/upload', 'admin/file/download', 'admin/user/updatePassword', 'admin/user/updateNickname', 'admin/user/submitPassword', 'admin/user/submitNickname', 'admin/file/uploadpicture'),
    'deny_visit' => array('admin/Addons/addhook', 'admin/Addons/edithook', 'admin/Addons/delhook', 'admin/Addons/updateHook', 'admin/Admin/getMenus', 'admin/Admin/recordList', 'admin/AuthManager/updateRules', 'admin/AuthManager/tree'),
];