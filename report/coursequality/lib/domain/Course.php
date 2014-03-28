<?php
require_once "lib/data/Chartable.php";
class Course extends AbstractDatum implements Chartable{
	private $color;
	private $value;
	private $fullTitle;
	public function Course($fullTitle,$title,$color,$value){
		parent::__construct($title);
		$this->fullTitle = $fullTitle;
		$this->color = $color;
		$this->value = $value;
	}
	public function getColor(){
		return $this->color;
	}
	public function getValue(){
		return $this->value;
	}
	public function getFullTitle(){
		return $this->fullTitle;
	}
}