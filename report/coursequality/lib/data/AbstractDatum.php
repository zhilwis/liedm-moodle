<?php
abstract class AbstractDatum{
	
	private $title;
	
	public function AbstractDatum($title){
		$this->title = $title;
	}
	
	public function getTitle(){
		return $this->title;
	}
}