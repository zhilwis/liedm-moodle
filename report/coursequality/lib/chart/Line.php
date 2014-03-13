<?php
require_once "DrawingComponent.php";
require_once "Color.php";

class Line extends DrawingComponent{
	private $start;
	private $end;
	private $color;
	
	public function Line(Point $start, Point $end, Color $color = null) {
		if($color==null) $color = new Color(225,225,225);
		$this->start = $start;
		$this->end = $end;
		$this->color = $color;
	}	
	public function draw($image){
		imageline($image, $this->start->x, $this->start->y, $this->end->x, $this->end->y, $this->color->getColor($image));
	}
}