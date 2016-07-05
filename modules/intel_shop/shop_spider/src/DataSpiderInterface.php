<?php 

namespace Drupal\shop_spider;

interface DataSpiderInterface{
	
	public function crawling();
	
	public function fix();
	
	public function merge();
	
	public function download();
}