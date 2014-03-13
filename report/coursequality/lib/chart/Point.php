<?php
class Point{
	var $x;
	var $y;
	
	public function Point($x,$y) {
		$this->x = $x;
		$this->y = $y;
	}
	
	public function copy(Point $p){
		$this->x = $p->x;
		$this->y = $p->y;
	}
	
}