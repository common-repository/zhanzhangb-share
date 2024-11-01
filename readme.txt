=== zhanzhangb-share ===
Contributors: ywtywt
Donate link: https://www.zhanzhangb.com/
Tags: share,weixin
Requires at least: 5.0
Tested up to: 5.4
Stable tag: 5.4
Requires PHP: 5.5
License: GNU General Public License (GPL) version 3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

== Description ==
插件功能：支持微信分享：带缩略图与摘要、朋友圈分享带缩略图与摘要（均支持未认证公众号）；QQ分享：带缩略图；QQ空间分享：带缩略图与摘要；微博分享：带缩略图与摘要；LinkedIn分享：带缩略图与摘要；邮件分享：调起系统默认邮箱客户端
支持后台设置分享图标的位置，可选出现在文章页面的正文之前或正文之后。
支持短代码，可以通过短代码自定义分享图标的位置。
分享时自动获取文章标题、文章摘要、特色图片等信息。


== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Plugin Name screen to configure the plugin
1. (Make your instructions match the desired user flow for activating and installing your plugin. Include any steps that might be needed for explanatory purposes)


== Frequently Asked Questions ==

= 分享到微信如何才能显示缩略图？ =

1、首先不论是认证过的，还是未认证的，都需要有一个微信公众号。如果没有公众号，请先注册一个。
2、微信公众号后台 -> 设置 -> 安全中心 -> IP白名单 添加自己的主机IP（运行插件的主机）。
3、微信公众号后台 -> 开发 -> 基本配置 获取：开发者ID(AppID) 与 开发者密码(AppSecret)，并正确填入到插件后台中。
4、微信公众号后台 -> 设置 -> 公众号设置 -> 功能设置 -> JS接口安全域名，至少需要将自己网站域名与调用JS、图片的域名设置在安全域名名单里。

= 提示：“Notice: Undefined property”错误 =

是因为微信公众号后台 -> 设置 -> 安全中心 -> IP白名单设置错误。

= 没有提示任何错误，但微信分享还是没图=

检查微信公众号后台 -> 设置 -> 公众号设置 -> 功能设置 -> JS接口安全域名是否与图片调用的域名一致，另外文章特色图片是否大于300×300。

= 为什么我的分享图标显示错位=

检查插件目录中css/zhanzhangb-share.css文件中的样式是否与网站主题的样式冲突。

= 为什么二维码是空白的=

插件使用javascript代码生成二维码，依赖jquery库运行，请确认网站是否正确加载了jquery库，WordPress默认是加载的

== Screenshots ==

1. `/assets/screenshot-1.png` 
2. `/assets/screenshot-2.png` 

== Changelog ==

= 1.0.0 =
* 首次正式发布
* 经过两周的测试已稳定

== Upgrade Notice ==

= 1.0.0 =
正式发布的版本。 立即升级。


== Arbitrary section ==

隐私说明：本插件需要通过微信官方API接口鉴权，如果使用本插件，代表您同意将微信公众号的密钥（SecretId与SecretKey）等信息发送至"api.weixin.qq.com"。

API隐私政策及其它说明（Privacy Policy）：https://mp.weixin.qq.com/cgi-bin/announce?action=getannouncement&key=1503979103&version=1&lang=zh_CN&platform=2

插件支持：https://www.zhanzhangb.com

本插件由站长帮制作并发行，官网：https://www.zhanzhangb.com
== A brief Markdown Example ==

无
