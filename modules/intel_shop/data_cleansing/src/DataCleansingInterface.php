<?php 

namespace Drupal\data_cleansing;

interface DataCleansingInterface{
	
	public function main();
	
	public function delete();
	
	public function detailsImagesExplorer();
	
	public static function fieldsCleanUp();
}