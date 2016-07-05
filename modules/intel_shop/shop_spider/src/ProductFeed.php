<?php

namespace Drupal\shop_spider;

class ProductFeed implements ProductFeedInterface{
	
	const INTEL_PRODUCT_FEED_NOT_REFRESH =  0;
	const INTEL_PRODUCT_FEED_REFRESH_DEFAULT = 900;
	const INTEL_PRODUCT_FEED_FETCH_NUMBER = 8;
	
	public $pcOnlineId;
	public $productType;
	public $refresh;
	public $checked;
	public $queued;
	public $modified;
		
	public function getPConlineId(){
		return $this->pcOnlineId;
	}
	
	public function getProductType(){
		return $this->productType;
	}
	
	public function getRefresh(){
		return $this->refresh;
	}
	
	public function getChecked(){
		return $this->checked;
	}
	
	public function getQueued(){
		return $this->queued;
	}
	
	public function getModified(){
		return $this->modified;
	}
	
	public function setPConlineId($pcOnlineId){
		$this->pcOnlineId = $pcOnlineId;
		return $this;
	}
	
	public function setProductType($productType){
		$this->productType = $productType;
		return $this;
	}
	
	public function setRefresh($refresh){
		$this->refresh = $refresh;
		return $this;
	}
	
	public function setChecked($checked){
		$this->checked = $checked;
		return $this;
	}
	
	public function setQueued($queued){
		$this->queued = $queued;
		return $this;
	}
	
	public function setModified($modified){
		$this->modified = $modified;
		return $this;
	}
	
}