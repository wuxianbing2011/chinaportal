<?php
/**
 * @file
 * Default theme implementation to display a single Drupal page.
 *
 * The doctype, html, head and body tags are not in this template. Instead they
 * can be found in the html.tpl.php template in this directory.
 *
 * Available variables:
 *
 * General utility variables:
 * - $base_path: The base URL path of the Drupal installation. At the very
 *   least, this will always default to /.
 * - $directory: The directory the template is located in, e.g. modules/system
 *   or themes/bartik.
 * - $is_front: TRUE if the current page is the front page.
 * - $logged_in: TRUE if the user is registered and signed in.
 * - $is_admin: TRUE if the user has permission to access administration pages.
 *
 * Site identity:
 * - $front_page: The URL of the front page. Use this instead of $base_path,
 *   when linking to the front page. This includes the language domain or
 *   prefix.
 * - $logo: The path to the logo image, as defined in theme configuration.
 * - $site_name: The name of the site, empty when display has been disabled
 *   in theme settings.
 * - $site_slogan: The slogan of the site, empty when display has been disabled
 *   in theme settings.
 *
 * Navigation:
 * - $main_menu (array): An array containing the Main menu links for the
 *   site, if they have been configured.
 * - $secondary_menu (array): An array containing the Secondary menu links for
 *   the site, if they have been configured.
 * - $breadcrumb: The breadcrumb trail for the current page.
 *
 * Page content (in order of occurrence in the default page.tpl.php):
 * - $title_prefix (array): An array containing additional output populated by
 *   modules, intended to be displayed in front of the main title tag that
 *   appears in the template.
 * - $title: The page title, for use in the actual HTML content.
 * - $title_suffix (array): An array containing additional output populated by
 *   modules, intended to be displayed after the main title tag that appears in
 *   the template.
 * - $messages: HTML for status and error messages. Should be displayed
 *   prominently.
 * - $tabs (array): Tabs linking to any sub-pages beneath the current page
 *   (e.g., the view and edit tabs when displaying a node).
 * - $action_links (array): Actions local to the page, such as 'Add menu' on the
 *   menu administration interface.
 * - $feed_icons: A string of all feed icons for the current page.
 * - $node: The node object, if there is an automatically-loaded node
 *   associated with the page, and the node ID is the second argument
 *   in the page's path (e.g. node/12345 and node/12345/revisions, but not
 *   comment/reply/12345).
 *
 * Regions:
 * - $page['help']: Dynamic help text, mostly for admin pages.
 * - $page['highlighted']: Items for the highlighted content region.
 * - $page['content']: The main content of the current page.
 * - $page['sidebar_first']: Items for the first sidebar.
 * - $page['sidebar_second']: Items for the second sidebar.
 * - $page['header']: Items for the header region.
 * - $page['footer']: Items for the footer region.
 *
 * @see bootstrap_preprocess_page()
 * @see template_preprocess()
 * @see template_preprocess_page()
 * @see bootstrap_process_page()
 * @see template_process()
 * @see html.tpl.php
 *
 * @ingroup templates
 */
?>
<script>
	$(function () {
		var ct_mid_ul="";
		var ct="";
		$('.b_ct_mid  .block-promoted-news').each(function(){
			ct=$(this).html();
				ct_mid_ul+=ct;
		});
			$('.b_ct_mid ul').html(ct_mid_ul);
	});
</script>
<body class="b_body">
	<div id="b_header">
		<img src="<?php print $base_path . drupal_get_path('theme', 'chinaportal');?>/images/clq.png" alt="" class="b_hd_clq">
	</div>
	<div id="b_content" class="clearfix">
		<div class="b_ct_top clearfix">
			<div class="left">
				<img src="<?php print $base_path . drupal_get_path('theme', 'chinaportal');?>/images/local.jpg" alt="">
				<div class="b_ct_top_date">
					活动时间<br>
					2016年5月31日 （星期二）
					<p>14: 30-16: 00</p>
					<a href="aggregator/" target="_blank">
						了解更多信息
					</a>
				</div>
			</div>
			<div class="right">
				<img src="<?php print $base_path . drupal_get_path('theme', 'chinaportal');?>/images/com_.jpg" alt="">
				<a href="#" class="b_ct_top_r_more">
					了解更多信息
				</a>
			</div>
		</div>
		<div class="b_ct_mid b_w">
		<ul class="clearfix">
		<?php print render($page['content']); ?>
		</ul>
		</div>
		<div class="forum">
			<div class="forum_ b_w clearfix">
				<div class="left">
					<img src="<?php print $base_path . drupal_get_path('theme', 'chinaportal');?>/images/forum.jpg" alt="">
				</div>
				<div class="right">
					<img src="<?php print $base_path . drupal_get_path('theme', 'chinaportal');?>/images/forum_font.png" alt="">
					<ul>
						<li>
							1.随着时代的发展，企业新服务层出不穷，业务需求不断增长。

						</li>
						<li>
							2. IT系统正面临着越来越大的压力。
							
						</li>
						<li>
							3. 设计运行于软件定义基础架构上的新一代数据中心将会成为企业数字服务的基础。
						</li>
					</ul>
					<a href="forum/" class="b_trans">
						立即参与
					</a>
				</div>
			</div>
		</div>
		<div class="b_ct_bt clearfix b_w">
			
			<div class="left b_ct_bt_l">
				<img src="<?php print $base_path . drupal_get_path('theme', 'chinaportal');?>/images/add.jpg" alt="">
				<a href="#" class="b_ct_bt_more b_trans">
					了解更多信息
				</a>
			</div>
			<div class="right b_ct_bt_r">
				<img src="<?php print $base_path . drupal_get_path('theme', 'chinaportal');?>/images/org.jpg" alt="">
				<a href="#"  class="b_ct_bt_more b_trans">
					了解更多信息
				</a>
			</div>
		</div>
	</div>
</body>