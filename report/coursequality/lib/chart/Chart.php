<?php
require_once 'DrawingComponent.php';

interface Chart{
	const X = 0;
	const Y = 1;
	const Z = 2;
	public function createPNGChart();
	public function setTitle($axis, $title);
	public function setForeground(Color $color);
	public function setBackground(Color $color);
	public function setTextColor(Color $color);
	public function addDataSet(DefaultDataSet $data);
}

abstract class AbstractChart extends DrawingComponent implements Chart{
	
	protected $image;
	/**
	 * @var Point
	 */
	protected $position;
	protected $width;
	protected $height;
	/**
	 * @var int
	 */
	protected $axis;
	/**
	 * @var int
	 */
	protected $oppositeAxis;
		/**
	 * @var Color
	 */
	protected $backgroundColor;
	/**
	 * @var Color
	 */
	protected $foregroundColor;
	/**
	 * @var Color
	 */
	protected $textColor;
	/**
	 * @var array[string]
	 */
	protected $titles;
	
	function AbstractChart($width, $height, $axis, $padding) {
		$this->position = new Point($padding, $height-$padding);
		$this->width = $width-$padding*2;
		$this->height = $height-$padding*2;
		$this->axis = $axis;
		$this->oppositeAxis = ($axis==Chart::X)? Chart::Y : Chart::X;
		$this->createImage($width, $height);
	}
	
	private function createImage($x,$y){
		$this->image  = imagecreate($x, $y);
		$orange = imagecolorallocate($this->image, 255, 255, 255);
	}
	
	protected function generateLabels($c,$min,$max){
		$labels = [];
		$max = $this->normalizedMax($c,$min,$max);
		$step = $max / $c;
		//echo(" STEP: ".$step);
		for ($i=1; $i<=$c; $i++)
			$labels[] = $min + $i*$step;
		return $labels;
	}
	
	protected function normalizedMax($c,$min,$max){
		$max = $max - $min;
		$max += $c - $max % $c;
		return $max;
	}
	
	public function createPNGChart(){
		$this->draw($this->image);
		imagepng($this->image);
		imagedestroy($this->image);
	}
	
	public function setTitle($axis, $title){
		$titles[$axis] = $title;
	}
	public function setForeground(Color $color){
		$this->foregroundColor = $color;
	}
	public function setBackground(Color $color){
		$this->backgroundColor = $color;	
	}
	public function setTextColor(Color $color){
		$this->textColor = $color;
	}
	
}