<?php

require_once(dirname(__FILE__).'/../../../config.php');

$rating = required_param('rating',PARAM_INT);  //Course

@header('Content-Type: image/gif');
@header("Expires: ".gmdate("D, d M Y H:i:s") . " GMT" );
@header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate");
@header("Cache-Control: post-check=0, pre-check=0", false);
@header("Pragma: no-cache");

if( $rating >= 0 ){
    echo file_get_contents( $CFG->dirroot.'/blocks/vuagentas/graphic/star'.$rating.'.png' );
}