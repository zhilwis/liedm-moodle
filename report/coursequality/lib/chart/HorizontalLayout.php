<?php
require_once 'lib/chart/LayoutManager.php';
require_once 'lib/chart/Line.php';
require_once 'FilledRectangle.php';
class HorizontalLayout extends LayoutManager {
	public function createParallelLine(Point $p, $length, Color $color = null) {
		return new Line ( $p, new Point ( $p->x + $length, $p->y ), $color );
	}
	public function createIntersectingLine(Point $p, $distance, $length, Color $color = null) {
		//var_dump(($p->x + $distance)." ".$p->y);
		return new Line ( new Point ( $p->x + $distance, $p->y ), new Point ( $p->x + $distance, $p->y + $length ), $color );
	}
	public function createIntersectingLabel(Point $p, $distance, $length, $delta, $value,  Color $color = null){
		return new DrawingText ($value, new Point ( $p->x + $distance - $delta, $p->y  + $length +20 ), $color );
	}
	public function createColumn(Point $p, $width, $height, Color $color = null){
		return new FilledRectangle($p, $width, $height, $color);
	}
}