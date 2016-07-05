<?php
if (!$user->uid){
	form_set_error('','请登录');
	drupal_goto('user/login',array('query' => array('destination' => 'rss_article')));
}
// print_r($record);
for($i=0;$i<count($record);$i++){
	$path = 'node/'.$record[$i]->nid;
	echo $record[$i]->nid.'<br />';
	echo $i<2 ? 'NEW' : '';
// 	echo '文章的标题是：<a href="node/'.$record[$i]->nid.'">'.$record[$i]->title.'</a><br />';
	echo '文章的标题是：<a href ="'.drupal_get_path_alias($path).'">'.$record[$i]->title.'</a><br />';
	echo '文章发布的时间是：'.date('Y-m-d H：i：s',$record[$i]->created).'<br />';
	echo '文章的内容是：'.$record[$i]->body_value.'<br /><hr />';
}
