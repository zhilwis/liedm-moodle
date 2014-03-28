<?php
class Padding {
	
	public $top = 0;
	public $right = 0;
	public $bottom = 0;
	public $left = 0;
	
	public function Padding($size) {
		$this->top = $this->right = $this->bottom = $this->left = $size;
	}
}