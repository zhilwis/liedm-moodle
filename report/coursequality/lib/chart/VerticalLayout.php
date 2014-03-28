<?php
require_once 'lib/chart/LayoutManager.php';
require_once 'lib/chart/Line.php';
require_once 'FilledRectangle.php';
class VerticalLayout extends LayoutManager {
	public function createParallelLine(Point $p, $delta, Color $color = null) {
		return new Line ( $p, new Point ( $p->x, $p->y - $delta ), $color );
	}
	public function createIntersectingLine(Point $p, $distance, $length, Color $color = null) {
		//var_dump($p->x ." ".($p->y - $distance));
		return new Line ( new Point ( $p->x, $p->y - $distance ), new Point ( $p->x - $length, $p->y - $distance ), $color );
	}
	public function createIntersectingLabel(Point $p, $distance, $length, $value, Color $color = null){
		$position = new Point ( $p->x - $length, $p->y - $distance + 5 );
		$text = new DrawingText ($value, $position, $color );
		//var_dump($text->getWidth());
		$position->move(-$text->getWidth(), 0);
		//$text->setPosition($position);
		return $text;
	}
	public function createColumn(Point $p, $width, $height, Color $color = null){
		return new FilledRectangle(new Point($p->x, $p->y-1), $height, -$width, $color);
	}
}