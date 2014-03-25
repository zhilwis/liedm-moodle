<?php
require_once "DrawingComponent.php";
class Axis extends DrawingComponent {
	private $title;
	/**
	 * @var array[string]
	 */
	private $labels;
	private $length;
	/**
	 * @var LayoutManager
	 */
	private $layout;
	/**
	 * @var Point
	 */
	private $position;
	private $delta = 0;
	private $start = 0;
	/**
	 * @var Color
	 */
	private $axisColor = null;
	/**
	 * @var Color
	 */
	private $gridColor = null;
	public function Axis($title, $position, $length, $layout = null) {
		if($layout==null) $layout = new HorizontalLayout();
		$this->title = $title;
		$this->length = $length;
		$this->position = $position;
		$this->layout = $layout;
	}
	public function setAxisColor(Color $color){
		$this->axisColor = $color;
	}
	public function setGridColor(Color $color){
		$this->gridColor = $color;
	}
	public function setLabels($labels, $start = 0, $delta = 0) {
		$this->labels = $labels;
		$this->start = $start;
		$this->delta = ($delta>0)? $delta : $this->length / count ( $labels );
	}
	public function draw($image) {
		$this->layout->createParallelLine ( $this->position, $this->length , $this->axisColor)->draw ( $image );
		if (count ( $this->labels ) > 0) {
			//var_dump($this->start);
			$length = ($this->start>0)?$this->start:$this->delta;
			foreach ( $this->labels as $label ) {
				if ($this->layout->isGrid())
					$this->layout->createIntersectingLine ( $this->position, $length, -$this->layout->getGridSize() , $this->axisColor)->draw ( $image );
				if ($this->layout->isGridLabeled()){
					$text = $this->layout->createIntersectingLabel ( $this->position, $length, 5, strlen($label)*3, $label);
					$text->draw($image);
					
					$this->layout->createIntersectingLine ( $this->position, $length, 3 , $this->gridColor)->draw ( $image );
				}
					
				$length += $this->delta;
			}
		}
	}
	
}