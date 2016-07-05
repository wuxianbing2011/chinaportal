<li>
	<?php 
	if ($record['image']['#type'] == 'imperatives') {
		$url = 'imperatives';
		$image = 'news_1_font.png';
	}elseif ($record['image']['#type'] == 'intelligence'){
		$url = 'intelligence';
		$image = 'news_2_font.png';
	}elseif ($record['image']['#type'] == 'innovation'){
		$url = 'innovation';
		$image = 'news_3_font.png';
	}
	?>
	<a href="<?=$url?>"><?=$record['image']['#markup'];?></a>
	<img src="<?php print $base_path . drupal_get_path('theme', 'chinaportal');?>/images/<?=$image?>" alt="" class="news_font">
	<ol>
		<?php
			$count = count($record) - 1;
			for($i=0;$i<$count;$i++){
				$path = 'node/'.$record[$i]->nid;
		?>
		<li class="clearfix">
			<a class="left" href="<?=drupal_get_path_alias($path) ?>"><?=$record[$i]->title?></a>
		<?php 
		if ($i < 3) {
			echo '<p class="right">NEW</p>';
		}
		?>
		</li>
		<?php }?>
	</ol>
</li>
