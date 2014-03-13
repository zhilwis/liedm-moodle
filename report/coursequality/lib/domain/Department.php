<?php
require_once('lib/data/Chartable.php');
class Department extends DefaultDataSet implements Chartable {
	
	private $color;
		
	public function Department($title,$color){
		parent::__construct($title);
		$this->color = $color;
	}
	public function getColor(){
		return $this->color;
	}
	public function getValue(){
		$mean = 0;
		$size = count($this->data);
		if ($size>0){
			foreach ($this->data as $data)
				$mean += $data->getValue();
			$mean = $mean/$size;
		}
		return $mean;
	}
	
}