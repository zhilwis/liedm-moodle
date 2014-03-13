<?php
class DrawingText extends DrawingComponent{
	private $value;
	/**
	 * @var Point
	 */
	private $position;
	private $color;
	private $size = 11;
	public function DrawingText($value, Point $position, $color = null) {
		if($color == null) $color = new DefaultColor(Color::BLACK);
		$this->color = $color;
		$this->value = $value;
		$this->position = $position;
	}
	
	public function setSize($size){
		$this->size = $size;
	}
	
	public function draw($image){
		//var_dump("Drawing Image");
		imagettftext($image, $this->size, 0, $this->position->x, $this->position->y, $this->color->getColor($image), "lib/fonts/arial.ttf",$this->value);
	}
}