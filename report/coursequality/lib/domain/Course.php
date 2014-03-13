<?php
require_once "lib/data/Chartable.php";
class Course extends AbstractDatum implements Chartable{
	private $color;
	private $value;
	private $short;
	public function Course($title,$short,$color,$value){
		parent::__construct($title);
		$this->short = $short;
		$this->color = $color;
		$this->value = $value;
	}
	public function getColor(){
		return $this->color;
	}
	public function getValue(){
		return $this->value;
	}
	public function getShort(){
		return $this->short;
	}
}