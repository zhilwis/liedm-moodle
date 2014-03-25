<?php
require_once "AbstractDatum.php";
class DefaultDataSet extends AbstractDatum{
	/**
	 * @var array[AbstractDatum]
	 */
	protected $data;
	private $max = 0;
	public function add(AbstractDatum $datum){
		$this->data[] = $datum;
		if($datum->getValue()>$this->max)$this->max = $datum->getValue();
	}
	public function get($i){
		return $this->data[$i];
	}
	public function size(){
		return count($this->data);
	}
	public function max(){
		return $this->max;
	}
}
