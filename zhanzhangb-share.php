<?php
/**
    Plugin Name: zhanzhangb-share
	Plugin URI: https://www.zhanzhangb.com/2020-817.html
    Text Domain: zhanzhangb-share
    Description: 站长帮分享插件，含微信分享（集成微信分享带图API）、微博分享、QQ空间分享、QQ分享、LinkedIn分享、邮件分享。
    Version: 1.0.0
    Author: 站长帮
    Author URI: https://www.zhanzhangb.com
    License: GNU General Public License (GPL) version 3
    License URI: https://www.gnu.org/licenses/gpl-3.0.html

    Copyright (c) 2020, 站长帮（zhanzhangb.com）

    Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/*
*    BOOTSTRAP FILE
*/

defined( 'ABSPATH' ) || exit;

if (!class_exists('zhanzhangbshare')){

class zhanzhangbshare{
	function __construct(){
		$share_location = get_option('zhanzhangb_share_location');
		register_activation_hook( __FILE__, array( $this,'zhanzhangb_share_install') );
		add_action( 'wp_enqueue_scripts', array( $this,'zhanzhangb_add_css_js') );
		if ( $share_location && $share_location != '3'){
			add_filter('the_content',array( $this, 'zhanzhangb_content_filter' ));
		}
		if ( $share_location == '3') {
			add_action( 'init', array( $this, 'add_zhanzhangb_shortcode' ));
		}
		if( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'zhanzhangb_share_menu' ));
			add_action( 'admin_init', array( $this, 'settings_init' ) );
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'add_action_links' ));
		}
	}

	/***********************************************************************************/
	function add_zhanzhangb_shortcode(){
			add_shortcode( 'zhanzhangb_share', array( $this, 'zhanzhangb_share_do' ) );
	}
	function zhanzhangb_share_do(){
		if ( is_single() ){
			$weixin_AppID = get_option('zhanzhangb_share_weixin_AppID');
			$weixin_AppSecret = get_option('zhanzhangb_share_weixin_AppSecret');
			$zhanzhangb_share_js = '<script>jQuery(function(){jQuery("#zhanzhangbqrcode").qrcode({width: 100,height: 100,background: "#FFF",text: "'.get_permalink().'"});})</script>';
			if ($weixin_AppID && $weixin_AppSecret){
				require_once plugin_dir_path( __FILE__ )."jssdk.php";
				if (class_exists('zhanzhangbshare_JSSDK')){
					$jssdk = new JSSDK($weixin_AppID, $weixin_AppSecret);
					$signPackage = $jssdk->GetSignPackage();
					
					$title = get_the_title();
					$summary = wp_trim_words( get_the_content(), 46 );
					$pic = get_the_post_thumbnail_url( '', 'full' ); 
					$url = get_permalink();
					$appId = $signPackage["appId"];
					$timestamp = $signPackage["timestamp"];
					$nonceStr = $signPackage["nonceStr"];
					$signature = $signPackage["signature"];
					$share_js = <<<EOF
<script type="text/javascript">
     setShareInfo({
         title:          '$title',
         summary:        '$summary',
         pic:            '$pic',
         url:            '$url',
         WXconfig:       {
             swapTitleInWX: true,
             appId: '$appId',
             timestamp: '$timestamp',
             nonceStr: '$nonceStr',
             signature: '$signature'
         }
     });
</script>
EOF;

					$zhanzhangb_share_js = $share_js . $zhanzhangb_share_js;
				}
			}
			return $this->zhanzhangb_share_filter() . $zhanzhangb_share_js;
		}
	}
	function zhanzhangb_content_insert( $return = 0 ) {// 插入的内容
		$str = $this->zhanzhangb_share_do();
		if ($return) { return $str; } else { echo $str; }
	}
	function zhanzhangb_content_filter($content) {
		if(get_option('zhanzhangb_share_location') == '1' && is_single() && is_main_query()) {
			$content .= $this->zhanzhangb_content_insert(0);// 0在正文上面
		}
		if(get_option('zhanzhangb_share_location') == '2' && is_single() && is_main_query()) {
			$content .= $this->zhanzhangb_content_insert(1);//1在正文下面
		}
		return $content;
	}
	/***********************************************************************************/
	function zhanzhangb_share_settings_title(){
		//echo $args['title'];
		echo '插件支持：<a href="';
		echo esc_url( 'https://www.zhanzhangb.com/2020-817.html' );
		echo '" target="_blank"><span>';
		echo esc_html__( 'https://www.zhanzhangb.com/2020-817.html ', 'zhanzhangb-share' );
		echo '</span></a>';
	}
	
	//function share_location_setting_cb(){
	//	echo '<input type="checkbox" name="zhanzhangb_share_location" value="1"';
	//	if ( get_option('zhanzhangb_share_location') == '1' ) {
	//		echo ' checked';
	//	}
	//	echo '/>';
	//}
	function share_location_setting_cb(){
		$select = get_option('zhanzhangb_share_location');
		echo '<select name="zhanzhangb_share_location">';
		echo '<option value="1"';
		if ( $select == '1' ) {
			echo 'selected="selected"';
		}
		echo '>' . esc_html__('文章正文前','zhanzhangb-share') . '</option><option value="2"';
		if ( $select == '2' ) {
			echo 'selected="selected"';
		}
		echo '>' . esc_html__('文章正文后','zhanzhangb-share') . '</option><option value="3"';
		if ( $select == '3' ) {
			echo 'selected="selected"';
		}
		echo '>' . esc_html__('启用短代码','zhanzhangb-share') . '</option></select>';
	}
	function weixin_AppID_setting_cb(){
		echo '<input maxlength="18" size="33" type="text" pattern="[A-z0-9]{18}" name="zhanzhangb_share_weixin_AppID" value="'.get_option('zhanzhangb_share_weixin_AppID').'" /> '. esc_html__('如果不正确输入，微信分享无缩略图','zhanzhangb-share');
	}
	function weixin_AppSecret_setting_cb(){
		echo '<input maxlength="32" size="33" type="text" pattern="[A-z0-9]{32}" name="zhanzhangb_share_weixin_AppSecret" value="'.get_option('zhanzhangb_share_weixin_AppSecret').'" /> '. esc_html__('如果不正确输入，微信分享无缩略图','zhanzhangb-share');
	}
	function weibo_Appkey_setting_cb(){
		echo '<input maxlength="10" size="33" type="text" pattern="[0-9]{10}" name="zhanzhangb_share_weibo_Appkey" value="'.get_option('zhanzhangb_share_weibo_Appkey').'" /> ';
	}
	function weibo_uid_setting_cb(){
		echo '<input maxlength="10" size="33" type="text" pattern="[0-9]{10}" name="zhanzhangb_share_weibo_uid" value="'.get_option('zhanzhangb_share_weibo_uid').'" /> ';
	}
	/***********************************************************************************/
	function zhanzhangb_share_menu() {
		if( is_admin() ) {
			add_options_page(
				'站长帮 - 分享插件设置',
				'站长帮 - 分享插件',
				'manage_options',
				'zhanzhangb_share',
				array( $this, 'zhanzhangb_share_options' )
			);
		}
	}
	/***********************************************************************************/
	function zhanzhangb_share_options() {
	 if ( !current_user_can( 'manage_options' ) )  {
		  wp_die( __( 'Sorry, you are not allowed to manage options for this site.' ) );
	 }
	?>
	<div class="wrap">
		<form method="post" action="options.php">
			<?php settings_fields( 'zhanzhangb_share_settings' ) ?>
			<?php do_settings_sections( 'zhanzhangb_share_settings' ); ?>
			<?php submit_button(); ?>
		</form>
		<div>
		<p><h4><?php echo esc_html__( '插件作者：', 'zhanzhangb-share' );?><a href="<?php echo esc_url( 'https://www.zhanzhangb.com/' ); ?>" target="_blank"><?php echo esc_html__( '站长帮', 'zhanzhangb-share' );?></a> 
<?php
		echo '<p>';
		echo '<span style="color:#003399">' . esc_html__('分享图标短代码为：[zhanzhangb_share]','zhanzhangb-share') . '</span><br />';
		echo '<span style="color:#003399">' . esc_html__( '如果启用了短代码选项，主题文件中使用：<?php echo do_shortcode("[zhanzhangb_share]"); ?> //插入到主题的文章页模板中的相应位置', 'zhanzhangb-share' ) . '</span><br />';
		echo '</p>';
		echo '</div></div>';
	}
	/***********************************************************************************/
	function add_action_links ( $links ) {
	 $mylinks = array(
	 '<a href="' . admin_url( 'options-general.php?page=zhanzhangb_share' ) . '">' . __('Settings') . '</a>'
	 );
	return array_merge( $links, $mylinks );
	}
	/***********************************************************************************/
	function zhanzhangb_share_install() {
    update_option('zhanzhangb_share_location','0');
	}
	/***********************************************************************************/
	function settings_init(){
		add_settings_section(
			'zhanzhangb_share_set',
			__( '站长帮分享插件设置', 'zhanzhangb-share' ),
			array( $this, 'zhanzhangb_share_settings_title' ),
			'zhanzhangb_share_settings'
		);
		add_settings_field(
			'zhanzhangb_share_location', //id
			__( '选择分享图标输出的方式：', 'zhanzhangb-share' ), 
			array( $this, 'share_location_setting_cb' ), 
			'zhanzhangb_share_settings', 
			'zhanzhangb_share_set',
			'share_location',
			array( 'label_for' => 'zhanzhangb_share_location' ) 
		);
		add_settings_field(
			'zhanzhangb_share_weixin_AppID', //id
			__( '微信开发者ID(AppID)：', 'zhanzhangb-share' ), 
			array( $this, 'weixin_AppID_setting_cb' ), 
			'zhanzhangb_share_settings', 
			'zhanzhangb_share_set',
			'weixin_AppID',
			array( 'label_for' => 'zhanzhangb_share_weixin_AppID' ) 
		);
		add_settings_field(
			'zhanzhangb_share_weixin_AppSecret', //id
			__( '微信开发者密码(AppSecret)：', 'zhanzhangb-share' ), 
			array( $this, 'weixin_AppSecret_setting_cb' ), 
			'zhanzhangb_share_settings', 
			'zhanzhangb_share_set',
			'weixin_AppSecret',
			array( 'label_for' => 'zhanzhangb_share_weixin_AppSecret' ) 
		);
		add_settings_field(
			'zhanzhangb_share_weibo_Appkey', //id
			__( '微博网页应用 App Key：', 'zhanzhangb-share' ), 
			array( $this, 'weibo_Appkey_setting_cb' ), 
			'zhanzhangb_share_settings', 
			'zhanzhangb_share_set',
			'weibo_Appkey',
			array( 'label_for' => 'zhanzhangb_share_weibo_Appkey' ) 
		);
		add_settings_field(
			'zhanzhangb_share_weibo_uid', //id
			__( '微博账号ID：', 'zhanzhangb-share' ), 
			array( $this, 'weibo_uid_setting_cb' ), 
			'zhanzhangb_share_settings', 
			'zhanzhangb_share_set',
			'weibo_uid',
			array( 'label_for' => 'zhanzhangb_share_weibo_uid' ) 
		);
		register_setting( 'zhanzhangb_share_settings', 'zhanzhangb_share_location' );
		register_setting( 'zhanzhangb_share_settings', 'zhanzhangb_share_weixin_AppID' );
		register_setting( 'zhanzhangb_share_settings', 'zhanzhangb_share_weixin_AppSecret' );
		register_setting( 'zhanzhangb_share_settings', 'zhanzhangb_share_weibo_Appkey' );
		register_setting( 'zhanzhangb_share_settings', 'zhanzhangb_share_weibo_uid' );
	}
	/***********************************************************************************/
	function zhanzhangb_add_css_js(){
		wp_register_script( 'zhanzhangb-qrcode-scripts', plugin_dir_url( __FILE__ ) . 'js/jquery.qrcode.min.js', array( 'jquery' ),'1.0.0', true );//注册生成二维码JS,配合微信分享
		wp_register_script( 'zhanzhangb-pop-ups-scripts', plugin_dir_url( __FILE__ ) . 'js/zhanzhangb-pop-ups.js', array( 'jquery' ),'1.0.0', true );//分享弹窗JS
		wp_register_script( 'zhanzhangb-share-scripts', plugin_dir_url( __FILE__ ) . 'js/share.js', array( 'jquery' ),'1.0.0', false );//API
		wp_register_style( 'zhanzhangb-share-css', plugin_dir_url( __FILE__ ) . 'css/zhanzhangb-share.css', array(), 'all' );
		if ( is_single() ){
			wp_enqueue_script( 'zhanzhangb-qrcode-scripts' );//排对引入jquery.qrcode.min.js
			wp_enqueue_script( 'zhanzhangb-pop-ups-scripts' );//排对引入zhanzhangb-pop-ups.js
			wp_enqueue_script( 'zhanzhangb-share-scripts' );//排对引入zhanzhangb-pop-ups.js
			wp_enqueue_style( 'zhanzhangb-share-css');
		}
	}
	
	function zhanzhangb_share_filter() {
		global $post;
		$id = get_the_ID();
		$title = get_the_title( $id );
		$url = urlencode( get_permalink( $id ) );
		$excerpt = wp_trim_words( get_the_content( $id ), 50 );
		$thumbnail = get_the_post_thumbnail_url( $id, 'full' );
		$author_id = $post->post_author;
		$author = get_the_author_meta( 'display_name' , $author_id );
		$blog_title = get_bloginfo('name'); 
		$weibo_Appkey = get_option('zhanzhangb_share_weibo_Appkey');
		$weibo_uid = get_option('zhanzhangb_share_weibo_uid');

		$weixin = '<svg t="1585977702776" class="icon" viewBox="0 0 1126 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="11504" width="24" height="24"><path d="M742.4 0h-358.4C199.68 0 51.2 148.48 51.2 332.8v358.4C51.2 875.52 199.68 1024 384 1024h358.4C926.72 1024 1075.2 875.52 1075.2 691.2v-358.4C1075.2 148.48 926.72 0 742.4 0zM220.16 203.093333c0-8.533333 3.413333-17.066667 10.24-23.893333 6.826667-6.826667 15.36-10.24 23.893333-10.24H392.533333c18.773333 0 34.133333 8.533333 34.133334 27.306667s-15.36 23.893333-34.133334 23.893333h-105.813333c-6.826667 0-13.653333 6.826667-13.653333 13.653333v105.813334c0 17.066667-5.12 32.426667-20.48 34.133333h3.413333-6.826667 3.413334c-17.066667-1.706667-30.72-17.066667-30.72-34.133333v-136.533334z m170.666667 651.946667h-138.24c-8.533333 0-17.066667-3.413333-23.893334-10.24-6.826667-6.826667-10.24-15.36-10.24-23.893333v-136.533334c0-18.773333 8.533333-34.133333 27.306667-34.133333s23.893333 15.36 23.893333 34.133333v105.813334c0 3.413333 1.706667 6.826667 3.413334 10.24 3.413333 3.413333 6.826667 3.413333 10.24 3.413333H392.533333c18.773333 0 34.133333 5.12 34.133334 23.893333 0 18.773333-15.36 27.306667-35.84 27.306667z m515.413333-34.133333c0 8.533333-3.413333 17.066667-10.24 23.893333-6.826667 6.826667-15.36 10.24-23.893333 10.24H733.866667c-18.773333 0-34.133333-8.533333-34.133334-27.306667s15.36-23.893333 34.133334-23.893333h105.813333c3.413333 0 6.826667-1.706667 10.24-3.413333 3.413333-3.413333 3.413333-6.826667 3.413333-10.24v-105.813334c0-18.773333 5.12-34.133333 23.893334-34.133333 18.773333 0 27.306667 15.36 27.306666 34.133333v136.533334z m-25.6-286.72H245.76c-13.653333 0-25.6-11.946667-25.6-25.6s11.946667-25.6 25.6-25.6h633.173333c13.653333 0 25.6 11.946667 25.6 25.6 1.706667 15.36-10.24 25.6-23.893333 25.6z m25.6-194.56c0 18.773333-8.533333 34.133333-27.306667 34.133333s-23.893333-15.36-23.893333-34.133333v-105.813334c0-3.413333-1.706667-6.826667-3.413333-10.24-3.413333-3.413333-6.826667-3.413333-10.24-3.413333H733.866667c-18.773333 0-34.133333-5.12-34.133334-23.893333 0-18.773333 15.36-27.306667 34.133334-27.306667h138.24c8.533333 0 17.066667 3.413333 23.893333 10.24 6.826667 6.826667 10.24 15.36 10.24 23.893333v136.533334z" p-id="11505" fill="#005599"></path></svg>';
		$weibo = '<svg t="1585942121611" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="3383" width="24" height="24"><path d="M411.270737 607.649684c-17.973895-7.504842-41.189053 0.229053-52.264421 17.542737-11.223579 17.394526-5.955368 38.103579 11.870316 46.201263 18.108632 8.232421 42.132211 0.417684 53.342316-17.421474C435.253895 635.944421 429.446737 615.370105 411.270737 607.649684zM455.545263 589.352421c-6.885053-2.721684-15.508211 0.579368-19.550316 7.329684-3.920842 6.790737-1.751579 14.524632 5.146947 17.367579 7.019789 2.883368 16.006737-0.458105 20.048842-7.370105C465.071158 599.740632 462.551579 591.912421 455.545263 589.352421zM427.52 469.315368c-115.968 11.439158-203.924211 82.216421-196.378947 158.073263 7.531789 75.910737 107.654737 128.161684 223.649684 116.749474 115.994947-11.439158 203.924211-82.216421 196.392421-158.140632C643.664842 510.140632 543.541895 457.889684 427.52 469.315368zM529.300211 648.299789c-23.673263 53.355789-91.769263 81.798737-149.530947 63.232-55.754105-17.933474-79.373474-72.811789-54.945684-122.246737 23.956211-48.464842 86.352842-75.870316 141.541053-61.561263C523.506526 542.437053 552.663579 596.143158 529.300211 648.299789zM512 0C229.241263 0 0 229.227789 0 512c0 282.758737 229.241263 512 512 512 282.772211 0 512-229.241263 512-512C1024 229.227789 794.772211 0 512 0zM455.531789 794.974316c-145.354105 0-293.941895-70.197895-293.941895-185.667368 0-60.362105 38.386526-130.182737 104.474947-196.069053 88.252632-87.929263 191.164632-127.986526 229.874526-89.397895 17.084632 17.003789 18.741895 46.457263 7.760842 81.623579-5.726316 17.690947 16.666947 7.895579 16.666947 7.936 71.343158-29.763368 133.564632-31.514947 156.321684 0.862316 12.139789 17.246316 10.954105 41.472-0.215579 69.510737-5.173895 12.921263 1.589895 14.928842 11.466105 17.879579 40.178526 12.422737 84.924632 42.455579 84.924632 95.380211C772.837053 684.638316 646.090105 794.974316 455.531789 794.974316zM718.672842 427.802947c4.715789-14.457263 1.765053-30.962526-9.202526-43.061895-10.954105-12.072421-27.136-16.666947-42.037895-13.527579l0-0.026947c-12.463158 2.694737-24.724211-5.268211-27.392-17.664-2.667789-12.463158 5.281684-24.697263 17.744842-27.338105 30.531368-6.467368 63.595789 2.937263 85.989053 27.715368 22.447158 24.764632 28.456421 58.489263 18.849684 88.064-3.907368 12.099368-16.936421 18.728421-29.062737 14.848-12.139789-3.920842-18.782316-16.922947-14.874947-28.995368L718.672842 427.816421zM853.261474 471.134316c-0.013474 0.013474-0.013474 0.080842-0.013474 0.107789-4.567579 14.026105-19.712 21.706105-33.778526 17.165474-14.133895-4.554105-21.854316-19.590737-17.300211-33.670737l0-0.013474c13.999158-43.169684 5.12-92.429474-27.567158-128.565895-32.714105-36.122947-80.949895-49.92-125.507368-40.488421-14.484211 3.085474-28.752842-6.130526-31.838316-20.574316-3.098947-14.403368 6.144-28.631579 20.641684-31.717053l0.026947 0c62.625684-13.271579 130.519579 6.117053 176.545684 56.966737C860.483368 341.113263 872.892632 410.381474 853.261474 471.134316z" p-id="3384" fill="#d81e06"></path></svg>';
		$linkedin = '<svg t="1585942229909" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4375" width="24" height="24"><path d="M512 1024C229.2224 1024 0 794.7776 0 512 0 229.2224 229.2224 0 512 0c282.7776 0 512 229.2224 512 512 0 282.7776-229.2224 512-512 512z m-137.762133-286.378667V397.380267h-102.4V737.621333h102.4z m-51.2-488.448c-33.024 0-54.5792 22.954667-53.9136 53.589334-0.682667 29.218133 20.8896 52.872533 53.248 52.872533 33.672533 0 55.2448-23.6544 55.2448-52.8896-0.682667-30.6176-21.572267-53.572267-54.5792-53.572267z m133.410133 488.448h102.4V541.405867c0-9.728 1.365333-20.1728 4.061867-26.453334 6.724267-19.456 23.569067-39.645867 51.882666-39.645866 37.034667 0 51.882667 29.917867 51.882667 73.762133V737.621333h102.4V535.842133c0-100.181333-50.517333-146.1248-117.9136-146.1248-54.562133 0-88.251733 32.7168-101.7344 54.272h-2.030933l-4.7104-46.609066h-88.9344c1.348267 29.917867 2.696533 66.0992 2.696533 108.544V737.621333z" fill="#1296db" p-id="4376"></path></svg>';
		$qzone = '<svg t="1585978353333" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="14500" width="24" height="24"><path d="M512 1024c282.76736 0 512-229.23264 512-512S794.76736 0 512 0 0 229.23264 0 512s229.23264 512 512 512z m20.48-307.2s-204.06272 124.04736-216.6784 113.90976c-12.61568-10.1376 41.3696-241.2544 41.3696-241.2544s-182.76352-152.20736-175.28832-170.88512c7.4752-18.69824 242.25792-35.2256 242.25792-35.2256S511.36512 163.84 532.48 163.84c21.11488 0 108.3392 219.52512 108.3392 219.52512s235.76576 16.5888 242.25792 35.20512c6.49216 18.59584-175.3088 170.86464-175.3088 170.86464s2.27328 14.78656 4.89472 25.8048c0.16384 0.7168-84.84864 2.6624-154.54208 0-36.59776-1.4336-80.65024-8.192-80.65024-8.192l201.728-143.872s-72.94976-13.12768-146.71872-15.7696c-80.7936-2.8672-163.49184 4.9152-175.3088 7.90528-7.41376 1.88416 52.24448 1.72032 120.29952 7.86432 47.63648 4.3008 110.32576 15.7696 110.32576 15.7696l-201.728 150.17984s86.38464 5.24288 163.88096 4.73088c87.2448-0.57344 165.94944-11.42784 166.44096-9.33888 15.17568 65.72032 43.2128 195.584 32.768 206.19264C734.96576 845.14816 532.48 716.8 532.48 716.8z" p-id="14501" fill="#FFCC00"></path></svg>';
		$email = '<svg t="1585942367597" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="11191" width="24" height="24"><path d="M741.12 305.737143H276.114286L511.817143 528.457143z" fill="#0d780a" p-id="11192"></path><path d="M524.8 566.857143a18.651429 18.651429 0 0 1-25.417143 0.182857l-62.72-59.245714L256 668.525714v49.737143h512v-49.737143l-181.577143-161.645714L524.8 566.857143zM256 337.005714v282.514286l153.965714-136.96zM768 619.52V330.788571l-155.245714 150.491429z" fill="#0d780a" p-id="11193"></path><path d="M512 9.142857C234.24 9.142857 9.142857 234.24 9.142857 512S234.24 1014.857143 512 1014.857143 1014.857143 789.76 1014.857143 512 789.76 9.142857 512 9.142857z m292.571429 727.405714c0 10.057143-8.228571 18.285714-18.285715 18.285715H237.714286c-10.057143 0-18.285714-8.228571-18.285715-18.285715V287.451429c0-10.057143 8.228571-18.285714 18.285715-18.285715h548.571428c10.057143 0 18.285714 8.228571 18.285715 18.285715v449.097142z" fill="#0d780a" p-id="11194"></path></svg>';
    
		// Add support to change email body
		if ( !function_exists( 'gp_social_email_body' ) ) {
			$email_body = __('推荐一篇好文，作者:', 'zhanzhangb-share');
			$email_body .= ' ' . $author . '； 文章链接：';
			$email_body .= $url;
		} else {
			$email_body = gp_social_email_body();
		}
		$weixin_link = '<div class="fb-share zhanzhangb-wx"><div id="zhanzhangbqrcode">' . esc_html__( '微信或QQ扫一扫', 'zhanzhangb-share' ) . '</div>' . $weixin . '</div>';
		$weibo_link = '<a href="https://service.weibo.com/share/share.php?url=' . $url . '&appkey=' . $weibo_Appkey .'&title=' . $title . ' | ' . $blog_title . '&pic=' . $thumbnail . '&ralateUid=' . $weibo_uid .'&language=zh_cn" class="tw-share" title="' . __( '分享到微博', 'zhanzhangb-share' ) . '">' . $weibo . '</a>';
		$linkedin_link = '<a href="http://www.linkedin.com/shareArticle?url=' . $url . '&title=' . $title . '" class="li-share" title="' . __( '分享到LinkedIn', 'zhanzhangb-share' ) . '">' . $linkedin . '</a>';
		$qzone_link = '<a href="https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?title=' . $title . '&summary=' . $excerpt . '&url=' . $url . '&pics=' . $thumbnail . '" class="gp-share" title="' . __( '分享到QQ空间', 'zhanzhangb-share' ) . '">' . $qzone . '</a>';
		$social_links = array();
		$list = '';
		// Add support to add prefix text
		if( has_filter('add_social_prefix') ) {
			$list .= apply_filters( 'add_social_prefix', $content );
		}
		$list .= '<div class="zhanzhangb-floating"><ul id="zhanzhangb-social-share">';
			if( $weixin ) {
				$list .= '<li class="zhanzhangb-share-weixin">' . $weixin_link . '</li>';
			}
			if( $weibo ) {
				$list .= '<li class="zhanzhangb-share-weibo">' . $weibo_link . '</li>';
			}
			if( $linkedin ) {
				$list .= '<li class="zhanzhangb-share-linkedin">' . $linkedin_link . '</li>';
			}
			if( $qzone ) {
				$list .= '<li class="zhanzhangb-share-qzone">' . $qzone_link . '</li>';
			}
			if( $email ) {
				$list .= '<li class="zhanzhangb-share-email"><a href="mailto:?Subject=' .  $title . '&Body=' . $email_body . '" target="_top" class="em-share" title="' . __( '通过邮件分享', 'zhanzhangb-share' ) . '">' . $email . '</a></li>';
			}

		// Create the social list
		foreach( $social_links as $social_link ) :
			
			$list .= '<li>' . $social_link . '</li>';

		endforeach;

		$list .= '</ul></div>';

		return $list;
	}// zhanzhangb_share_filter end
}
}
new zhanzhangbshare()

?>