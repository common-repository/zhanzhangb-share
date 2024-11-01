<?php
if(!defined("WP_UNINSTALL_PLUGIN")) exit();

delete_option('zhanzhangb_share_location');
delete_option('zhanzhangb_share_weixin_AppID');
delete_option('zhanzhangb_share_weixin_AppSecret');
delete_option('zhanzhangb_share_weibo_Appkey');
delete_option('zhanzhangb_share_weibo_uid');