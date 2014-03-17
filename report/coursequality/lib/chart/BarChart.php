<?php
require_once 'Chart.php';
require_once 'DrawingText.php';
class BarChart extends AbstractChart{

	/**
	 * @var int
	 */
	protected $axis;
	/**
	 * @var int
	 */
	protected $oppositeAxis;
		
	private $axisWidth;
	private $axisHeight;
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
	private $hLayout;
	private $vLayout;
	
	private $h = 15;
	private $s = 16;
	private $c = 3;
	private $gap = 15;
		
	function BarChart($axis = Chart::X, Padding $padding = null, $width = 800, $height = 600) {
		parent::__construct(new Point(0, $height));
		$this->axis = $axis;
		$this->oppositeAxis = ($axis==Chart::X)? Chart::Y : Chart::X;
		$this->hLayout = new HorizontalLayout();//($axis==Chart::X)
		$this->vLayout = new VerticalLayout();//($axis==Chart::Y)
		$this->layout = ($this->axis==Chart::X)?$this->vLayout:$this->hLayout;
		$this->labelList[Chart::X] = array();
		$this->labelList[Chart::Y] = array();
		$this->setSize($width, $height, $padding);
	}
	
	public function setColumnSize($gap, $heigth){
		$this->gap = $gap;
		$this->s = $height + 1;
		$this->h = $heigth;
	}
	
	public function setSize($width, $height, $padding = null){
		parent::setSize($width, $height);
		if($padding==null) $padding = new Padding(20);
		$this->position->x = $padding->left;
		$this->position->y = $height-$padding->bottom;
		$this->axisWidth = $width-$padding->left-$padding->right;
		$this->axisHeight = $height-$padding->top-$padding->bottom;
		$this->hLayout->setGridSize($this->axisHeight);
		$this->axisList[Chart::X] = new Axis($this->position, $this->axisWidth, $this->hLayout);
		$this->axisList[Chart::Y] = new Axis($this->position, $this->axisHeight, $this->vLayout);
	}
		
	public function addDataSet(DefaultDataSet $data){
		$this->labelList[$this->axis][] = $data->getTitle();
		if($data->max()>$this->max) $this->max = $data->max();
		$this->dataList[] = $data;	
    }

	public function draw($image){
		if(count($this->dataList)==1)$this->s += $this->gap;
		
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
			//var_dump($this->s);
			$labels = [];
			$data = $this->dataList[0];
			for ($i=0;$i<$data->size();$i++)
				$labels[] = $data->get($i)->getTitle();
			
			$this->axisList[$this->axis]->setLabels($labels,($this->s+$this->gap)/2,$this->s);
		}
		$this->axisList[$this->axis]->draw($image);
	}
	
	private function drawColumns($image){

		$height = $this->h;
		$length = $this->s;
		foreach ($this->dataList as $data){
			for($i=0;$i<$data->size();$i++){
		
				$columnValue = $data->get($i)->getValue();
				//var_dump($columnValue);
				$max = $this->normalizedMax(10, 0, $this->max);
				$columnWidth = ($columnValue/$max)*$this->axisWidth;
				$rectangle = new FilledRectangle(new Point($this->position->x, $this->position->y-$length), $columnWidth, $height, $data->get($i)->getColor());
				$rectangle->draw($image);
				
				$position = new Point($this->position->x+$columnWidth,$this->position->y-$length+12);
				$text = new DrawingText($columnValue, $position, new DefaultColor(Color::WHITE));
				$text->setSize(9);
				$position->move(-$text->getWidth()-5, 0);
				if($columnWidth>10)$text->draw($image);
				
				$length+=$this->s;
			}
			$length+=$this->s*$this->c;
		}
	}
	
		
}