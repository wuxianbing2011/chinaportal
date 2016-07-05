<?php 

namespace Drupal\shop_spider;

interface ProductFeedInterface{
		
	public function getPConlineId();
	
	public function getProductType();
	
	public function getRefresh();
	
	public function getChecked();
	
	public function getQueued();
	
	public function getModified();
	
	public function setPConlineId($pcOnlineId);
	
	public function setProductType($productType);
	
	public function setRefresh($refresh);
	
	public function setChecked($checked);
	
	public function setQueued($queued);
	
	public function setModified($modified);
}