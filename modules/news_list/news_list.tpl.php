<?php 
/* Report all errors except E_NOTICE */
error_reporting(E_ALL^E_NOTICE);
?>
<div id="b_ct_header"></div>
	<div id="b_imper" class="b_ct_w">
		<div class="b_imper_top">
			<ul class="clearfix">
				<li>
					<img src="<?php print drupal_get_path('theme', 'chinaportal');?>/images/imper_li_top_1.jpg" alt="">
					<p class="b_imper_top_tit">
						General
					</p>
					<p class="b_imper_top_ct">
						
						更快创联只能事物，了解IOT为您的事物工作中所需要的东西
					</p>
					<a href="#">
						阅读全文 >
					</a>
				</li>
				<li>
					<img src="<?php print drupal_get_path('theme', 'chinaportal');?>/images/imper_li_top_2.jpg" alt="">
					<p class="b_imper_top_tit">
						General
					</p>
					<p class="b_imper_top_ct">
						
						更快创联只能事物，了解IOT为您的事物工作中所需要的东西
					</p>
					<a href="#">
						阅读全文 >
					</a>
				</li>
				<li>
					<img src="<?php print drupal_get_path('theme', 'chinaportal');?>/images/imper_li_top_3.jpg" alt="">
					<p class="b_imper_top_tit">
						General
					</p>
					<p class="b_imper_top_ct">
						
						更快创联只能事物，了解IOT为您的事物工作中所需要的东西
					</p>
					<a href="#">
						阅读全文 >
					</a>
				</li>
				<li>
					<img src="<?php print drupal_get_path('theme', 'chinaportal');?>/images/imper_li_top_4.jpg" alt="">
					<p class="b_imper_top_tit">
						General
					</p>
					<p class="b_imper_top_ct">
						
						更快创联只能事物，了解IOT为您的事物工作中所需要的东西
					</p>
					<a href="#">
						阅读全文 >
					</a>
				</li>
			</ul>
		</div>
		<div class="b_imper_mid">
			<ul>
			<?php 
			$count = count($record);
			for($i=0;$i<$count-1;$i++){
			$path = 'node/'.$record[$i]->nid;?>
				<li class="clearfix">


					<div class="b_imper_mid_img">
					<?php
					$str = $record[$i]->body_value;
					$pattern ='<img.*?src="(.*?)">';
					if (preg_match($pattern,$str,$matches)) {
						$thumbnail = $matches[1];	//获取文章中的第一张图片
					?>
						<img src="<?=$thumbnail?>" alt="">
					<?php }else{?>
						<img src="<?php print drupal_get_path('theme', 'chinaportal');?>/images/imp_1.jpg" alt="">
					<?php }?>
					
					</div>
					<div class="b_imper_mid_ct">
						<h2>
							<?=$record[$i]->title?>
						</h2>
						<?php
						
						//去除img标签
						$str = preg_replace('/<img[^>]+>/i','',$str);
 						//去除A标签
						$str= preg_replace('/(<a.*?>[\s\S]*?<\/a>)/','',$str);
						//取1500个字符串
						$str = substr($str, 0, 204);
						?>
						<?=$str?>
						<a href="<?=drupal_get_path_alias($path)?>">
							<?=$record[$i]->title?>
						</a>

					</div>
				</li>
				<?php }?>
				
			</ul>
		</div>
	</div>
	<div id="b_ct_footer">
		<div class="b_ct_w">
			<div class="left b_ct_footer_l">
				<h2>
					<?=$record[$count-1]->title?>
				</h2>
				<?php
					$paths = 'node/'.$record[$count-1]->nid;
					$str = $record[$count-1]->body_value;
					$pattern2 ='<img.*?src="(.*?)">';
					if (preg_match($pattern2,$str,$matches2)) {
						$thumbnail2 = $matches2[1];	//获取文章中的第一张图片
					}
					
					//去除img标签
					$str = preg_replace('/<img[^>]+>/i','',$str);
					//去除A标签
					$str= preg_replace('/(<a.*?>[\s\S]*?<\/a>)/','',$str);
					//取1500个字符串
					$str = substr($str, 0, 204);
					echo $str;
				?>
				
				<a href="<?=drupal_get_path_alias($paths)?>" class="b_ct_ft_yd">阅读新闻稿 ></a>
				<p class="b_ct_ft_gk">
					<a href="<?=drupal_get_path_alias($paths)?>">
						<?=$record[$count-1]->title?>
					</a>
				</p>
			</div>

			<div class="right b_ct_footer_r">
			<?php if ($thumbnail2 == '') { ?>
				<img src="<?php print drupal_get_path('theme', 'chinaportal');?>/images/ft_r.jpg" alt="">
			<?php }else{?>
				<img src="<?=$thumbnail2?>" alt="">
			<?php }?>
			</div>
		</div>
	</div>