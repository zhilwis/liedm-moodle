<?php
abstract class LayoutManager {
	private $gridSize;
	private $isGridLabeled;
	public function LayoutManager($gridSize = 0, $isGridLabeled = true){
		$this->gridSize = $gridSize;
		$this->isGridLabeled = $isGridLabeled;
	}
	public function setGridSize($gridSize){
		$this->gridSize = $gridSize;
	}
	public function setGridLabeled($isGridLabeled){
		$this->isGridLabeled = $isGridLabeled;
	}
	public function isGridLabeled(){
		return $this->isGridLabeled;
	}
	public function isGrid(){
		return $this->gridSize>0;
	}
	public function getGridSize(){
		return $this->gridSize;
	}
	/**
	 * @param Point $p
	 * @param int $delta
	 * @return Line
	 */
	public abstract function createParallelLine(Point $p, $length, Color $color = null);
	/**
	 * @param Point $p
	 * @param int $delta
	 * @return Line
	 */
	public abstract function createIntersectingLine(Point $p, $distance, $length, Color $color = null); 
	public abstract function createColumn(Point $p, $width, $height, Color $color = null);
	public abstract function createIntersectingLabel(Point $p, $distance, $length, $delta, $value, Color $color = null);
}