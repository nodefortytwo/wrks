<?php
class Render{

	public static function link($text, $url, $class = '', $root = false, $title = ''){
	    if (empty($title)) {$title = trim(strip_tags($text));
	    }

	    if (!empty($title)) {$title = 'title="' . trim($title) . '"';
	    }
	    if (!empty($class)) {$class = 'class="' . trim($class) . '"';
	    }

	    $url = get_url($url);
	    $return = '<a href="' . $url . '" ' . $class . ' ' . $title . '>' . $text . '</a>';
	    return $return;
	}

	public static function btnGroup($btns){
		$html = '';
		foreach($btns as $btn){
			$btn['class'] = isset($btn['class']) ? $btn['class'] : '';

			$html .= Render::link($btn['title'], $btn['url'], $btn['class'] . ' btn');
		}
		return $html;
	}
	
	public static function ulist($array, $class = '') {
	    $return = '<ul class="' . $class . '">';
	    foreach ($array as $key => $item) {
	        $class = '';
	        if (is_array($item)) {
	            if (array_key_exists('class', $item)) {
	                $class = 'class="' . $item['class'] . '"';
	            }
	            if (array_key_exists('text', $item)) {
	                $item = $item['text'];
	            }
	        }
	        $return .= '<li id="' . $key . '" ' . $class . '>';
	        $return .= trim($item);
	        $return .= '</li>';
	    }
	    $return .= '</ul>';

	    return $return;
	}

	public static function table($headers, $rows, $class = '', $args = array()) {
	    $return = '';
	    $return .= '<table class="table table-striped table-bordered ' . $class . '">';
	    $return .= '<thead>';
	    $return .= '<tr>';
	    foreach ($headers as $key=>$header) {
	        if(isset($args['widths'][$key])){
	                $width = ' width="' . $args['widths'][$key] . '"';
	            }else{
	                $width = '';
	            }
	        $return .= '<th '.$width.'>' . $header . "</th>\n";
	    }
	    $return .= "</tr>\n";
	    $return .= "</thead>\n";
	    $return .= '<tbody>';
	    foreach ($rows as $key => $row) {
	        
	        $return .= '<tr id="' . $key . '">';
	        foreach ($row as $key=>$col) {

	            if(isset($args['widths'][$key])){
	                $width = ' width="' . $args['widths'][$key] . '"';
	            }else{
	                $width = '';
	            }
	            if(is_object($col)){
	               switch(get_class($col)){
	                   case 'MongoDate':
	                       $col = Render::date($col);
	                       break;
	                   default:
	                       $col = (string) $col;
	               }    
	            }elseif(is_array($col)){

	            	$col = implode('<br/>', $col);
	            }

	            $return .= '<td '.$width.'>' . $col . "</td>\n";
	        }
	        $return .= "</tr>\n";
	    }
	    $return .= "</tbody>\n";
	    $return .= "</table>\n";
	    return $return;
	}

	public static function date($date = null, $format = 'dS M @ g:ia', $auto_update = true) {
	    if (is_null($date)) {$date = time();
	    }
	    if(is_object($date)){
	        $date = $date->sec;
	    }
	    if (!is_numeric($date)) {
	        $date = strtotime($date);
	    }
	    $class = '';
	    if($auto_update){
	        $class = 'date-update';
	    }

	    $now = time();
	    //if (($now - $date) > 86400 || $date > time()) {
	        return '<span class="date '.$class.'" data-timestamp="'.$date.'">'.date($format, $date) . '</span>';
	    //} else {
	        return '<span class="date '.$class.'" data-timestamp="'.$date.'">'.Render::time_ago($date) . ' ago</span>';
	    //}
	}

	public static function time_ago($tm, $rcs = 0) {
	    $cur_tm = time();
	    $dif = $cur_tm - $tm;
	    $pds = array('second', 'minute', 'hour', 'day', 'week', 'month', 'year', 'decade');
	    $lngh = array(1, 60, 3600, 86400, 604800, 2630880, 31570560, 315705600);
	    for ($v = sizeof($lngh) - 1; ($v >= 0) && (($no = $dif / $lngh[$v]) <= 1); $v--);
	    if ($v < 0)
	        $v = 0;
	    $_tm = $cur_tm - ($dif % $lngh[$v]);

	    $no = floor($no);
	    if ($no <> 1)
	        $pds[$v] .= 's';
	    $x = sprintf("%d %s ", $no, $pds[$v]);
	    if (($rcs == 1) && ($v >= 1) && (($cur_tm - $_tm) > 0))
	        $x .= time_ago($_tm);
	    return $x;
	}

	public static function markDown($content){
		return \Michelf\Markdown::defaultTransform($content);
	}

	public static function navigation(){
		$nav = System::execHook('nav');
		$items = array();
		foreach($nav as $module){
			foreach($module as $link){
				array_push($items, Render::link($link['title'], $link['path']));
			}
		}

		return Render::ulist($items, 'nav');
	}

	public static function currency($number, $dec = '2', $symbol = '£'){

		if($number < 0){
			$symbol = '-' . $symbol;
		}

		return $symbol . number_format(abs($number), $dec);
	}

}