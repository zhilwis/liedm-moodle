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
		return new Line ( new Point ( $p->x, $p->y - $distance ), new Point ( $p->x - $length, $p->y - + $distance ), $color );
	}
	public function createIntersectingLabel(Point $p, $distance, $length, $delta, $value, Color $color = null){
		return new DrawingText ($value, new Point ( $p->x - $length - $delta*2.1, $p->y - $distance + 5 ), $color );
	}
	public function createColumn(Point $p, $width, $height, Color $color = null){
		return new FilledRectangle(new Point($p->x, $p->y-1), $height, -$width, $color);
	}
}