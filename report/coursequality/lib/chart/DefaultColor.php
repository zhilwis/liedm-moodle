<?php
require_once "Color.php";
class DefaultColor extends Color{
	
	private static $COLORS = [14822282, 20640, 11119360, 55295, 
	                          12087782, 628464, 3381555, 7536856,
							  3785122, 5349, 14852379, 49407,
							  8421376, 10855845, 11788021, 4678655];
	private $i = 0;
	
	function DefaultColor($value = 0) {
		parent::__construct(($value & 0x0000ff),($value & 0x00ff00)/256,($value & 0xff0000)/65536);
	}
	
	public function next(){
		if($this->i<count(DefaultColor::$COLORS)){
			$color = new DefaultColor(DefaultColor::$COLORS[$this->i]);
			$this->i++;
			return $color;
		}else{
			$r = mt_rand(50,200); //r(ed)
     		$g = mt_rand(50,200); //g(reen)
     		$b = mt_rand(50,200); //b(lue)
			return new Color($r, $g, $b);
		}
	}
	
	public function reset(){
		$this->i = 0;
	}
	
}