<?php
class DrawingText extends DrawingComponent{
	private $value;
	/**
	 * @var Point
	 */
	private $position;
	private $color;
	private $size = 11;
	const FONTFILE = "lib/fonts/arial.ttf";
	private $box; 
	public function DrawingText($value, Point $position, $color = null) {
		if($color == null) $color = new DefaultColor(Color::BLACK);
		$this->color = $color;
		$this->value = $value;
		$this->position = $position;
		$this->box = imagettfbbox($this->size, 0, DrawingText::FONTFILE, $value);
	}
	
	public function setSize($size){
		$this->size = $size;
		$this->box = imagettfbbox($size, 0, DrawingText::FONTFILE, $this->value);
	}

	public function setPosition($position){
		$this->size = $position;
	}
		
	public function draw($image){
		imagettftext($image, $this->size, 0, $this->position->x, $this->position->y, $this->color->getColor($image), DrawingText::FONTFILE,$this->value);
	}
	
	public function getWidth(){
		return $this->box[2] - $this->box[0];
	}
	public function getHeight(){
		return $this->box[1] - $this->box[7];
	}
}