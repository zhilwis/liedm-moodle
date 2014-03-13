<?php
class Color {
	
	const GREEN = 65280;
	const RED = 255;
	const BLUE = 16711680;
	const YELLOW = 65535;
	const CYAN = 16776960;
	const SPRINGGREEN = 8388352;
	const MAGENTA = 16711935;
	const BLUEVIOLET = 14822282;
	const BROWN = 2763429;
	const BLACK = 0;
	const WHITE = 16777215;
		
	private $r = 0;
	private $g = 0;
	private $b = 0;
	
	public function Color($r,$g,$b) {
		$this->r = $this->normalize($r);
		$this->g = $this->normalize($g);
		$this->b = $this->normalize($b);
	}
	private function normalize($v){
		if($v<0) $v = 0;
		else if($v>255) $v = 255;
		return $v;
	}
	public function getColor($image){
		return imagecolorallocate($image,$this->r,$this->g,$this->b);		
	}
	
	public function getHTMLColor(){
		return "rgb(".$r.",".$g.",".$b.")";
	}
}