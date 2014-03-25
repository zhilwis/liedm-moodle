<?php
require_once 'Chart.php';
require_once 'DrawingText.php';
class BarChart extends AbstractChart{

	/**
	 * @var array[Axis]
	 */
	private $axisList;
	/**
	 * @var array[AbstractDataSet]
	 */
	private $dataList;
	/**
	 * @var int
	 */
	private $max;
	/**
	 * @var array[array[string]]
	 */
	private $labelList;
	/**
	 * @var array[string]
	 */
	private $layout;
	
	private $h = 15;
	private $s = 16;
	private $c = 3;
		
	function BarChart($width, $height, $xTitle, $yTitle, $axis = Chart::X, $padding = 100) {
		parent::__construct($width, $height, $axis, $padding);
		$hLayout = new HorizontalLayout($this->height);//($axis==Chart::X)
 		$vLayout = new VerticalLayout();//($axis==Chart::Y)
		$this->layout = ($axis==Chart::X)?$vLayout:$hLayout;
		$this->axisList[Chart::X] = new Axis($xTitle, $this->position, $this->width, $hLayout);
		$this->labelList[Chart::X] = [];
		$this->axisList[Chart::Y] = new Axis($yTitle, $this->position, $this->height, $vLayout);
		$this->labelList[Chart::Y] = [];
	}
	
	public function addDataSet(DefaultDataSet $data){
		$this->labelList[$this->axis][] = $data->getTitle();
		if($data->max()>$this->max) $this->max = $data->max();
		$this->dataList[] = $data;	
    }

	public function draw($image){
		$this->drawAxis($image);
		$this->drawColumns($image);
	}
	
	private function drawAxis($image){
		
		//Chart::X
		if(count($this->labelList[$this->oppositeAxis])>1){
			$this->axisList[$this->oppositeAxis]->setLabels($this->labelList[$this->oppositeAxis]);
		}else{
			$this->axisList[$this->oppositeAxis]->setLabels($this->generateLabels(10,0,$this->max));
		}
		$this->axisList[$this->oppositeAxis]->draw($image);
		
		//Chart::Y
		if(count($this->labelList[$this->axis])>1){
			$start = $this->dataList[0]->size()*$this->s/2;
			$delta = $this->dataList[0]->size()*$this->s+$this->s*$this->c;
			$this->axisList[$this->axis]->setLabels($this->labelList[$this->axis],$start,$delta);
		}else{
			$labels = [];
			$data = $this->dataList[0];
			for ($i=0;$i<$data->size();$i++)
				$labels[] = $data->getTitle();
			
			$this->axisList[$this->axis]->setLabels($labels,$this->s/2,$this->s);
		}
		$this->axisList[$this->axis]->draw($image);
	}
	
	private function drawColumns($image){
		$height = $this->h;
		$length = $this->s;
		foreach ($this->dataList as $data){
			for($i=0;$i<$data->size();$i++){
		
				$columnValue = $data->get($i)->getValue();
				$max = $this->normalizedMax(10, 0, $this->max);
				$columnWidth = ($columnValue/$max)*$this->width;
				$rectangle = new FilledRectangle(new Point($this->position->x, $this->position->y-$length), $columnWidth, $height, $data->get($i)->getColor());
				$rectangle->draw($image);
				
				$text = new DrawingText($columnValue, new Point($this->position->x+$columnWidth-30,$this->position->y-$length+12), new DefaultColor(Color::WHITE));
				$text->setSize(9);
				$text->draw($image);
				
				$length+=$this->s;
			}
			$length+=$this->s*$this->c;
		}
	}
	
		
}