<?php
require_once "DrawingComponent.php";
class Rectangle extends DrawingComponent {
	/**
	 * @var Point
	 */
	protected $position;
	protected $width, $height;
	/**
	 * @var Color
	 */
	protected $color;
	public function Rectangle(Point $position, $width, $height, $color = null) {
		if($this->color == null) $this->color = new Color(225,225,225);
		$this->position = $position;
		$this->width = $width;
		$this->height = $height;
		$this->color = $color;
	}
	public function draw($image) {
			$start = new Point($this->position->x, $this->position->y);
			$end = new Point($this->position->x + $this->width, $this->position->y);
		$line = new Line ( $start, $end, $this->color );
		$line->draw($image);
			$start->copy($end);
			$end->y += $this->height;
		$line->draw($image);
			$start->copy($end);
			$end->x -= $this->width;
		$line->draw($image);
			$start->copy($end);
			$end->y -= $this->height;
		$line->draw($image);
	}
}