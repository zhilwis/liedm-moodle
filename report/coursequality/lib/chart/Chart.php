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
	
	/**
	 * @var Point
	 */
	protected $position;
	private $width;
	private $height;
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
	protected $titles = ["",""];

	public function AbstractChart($position){
		$this->position = $position;
	}
		
	public function setSize($width, $height) {
		$this->width = $width;
		$this->height = $height;
	}
	
	
	private function createImage(){
		$image =  imagecreate($this->width, $this->height);
		$orange = imagecolorallocate($image, 255, 255, 255);
		return $image;
	}
	
	protected function generateLabels($c,$min,$max){
		
		
		$labels = [];
		$max = $this->normalizedMax($c,$min,$max);
		//var_dump("MAX: ".$max);
		$step = $max / $c;
		//echo(" STEP: ".$step);
		for ($i=1; $i<=$c; $i++)
			$labels[] = $min + $i*$step;
		return $labels;
	}
	
	protected function normalizedMax($c,$min,$max,$scale=10){
		$max = $max*$scale - $min*$scale;
		if(($max % $c)>0)$max += $c - $max % $c;
		return $max/$scale;
	}
	
	public function createPNGChart(){
		$image = $this->createImage();
		$this->draw($image);
		imagepng($image);
		imagedestroy($image);
	}
	
	public function setTitle($axis, $title){
		$this->titles[$axis] = $title;
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