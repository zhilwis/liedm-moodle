<?php
require_once "Rectangle.php";
class FilledRectangle extends Rectangle{
	
	public function __construct(Point $position, $width, $height, $color = null){
		parent::__construct($position, $width, $height, $color);
	}
	
	public function draw($image){
		//var_dump("DRAWING: w = ".$this->width.", h = ".$this->height."");
		imagefilledrectangle($image, $this->position->x, $this->position->y, $this->position->x+$this->width, $this->position->y+$this->height, $this->color->getColor($image));
	}
}