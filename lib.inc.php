<?

function pluralize($str) {
	return $str.'s';
}

function arrayV($arr = array(), $field) {
	$ret = array();
	if (is_array($arr) && isset($arr->$field))
		foreach($arr as $e) $ret[] = $e->$field;
	return $ret;
}

function nykstuku_kalba($str) {
	$rplp = array(
			'r' => 'j', 'R' => 'J',
			'l' => 'j', 'L' => 'J');

	return strtr($str, $rplp);
}

function t($str) {
	return Language::t($str);
}

function showAnecdote($anecdote) {
	return ($anecdote) ? 
		t(wordwrap(so($anecdote->contents), 70)) : NULL;
}

function so($str) { //safe output
	return htmlspecialchars($str);
}

function od($str, $ifempty) {
	return so(('' != $str) ? $str : $ifempty);
}

