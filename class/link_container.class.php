<?
class LinkContainer {
	private $keyvalues = array();
	public function add($key, $value) {
		if ('' !== trim($key)) $this->keyvalues[$key] = $value;
	}
	public function s($key=NULL, $value=NULL) {
		$str = ''; $tmp = array();
		foreach($this->keyvalues as $key => $value) {
			$tmp[] = join("=", array($key,$value));
		}
		if(NULL !== $key) $tmp[] = join("=", $key, $value);
		return (count($tmp) > 0) ? '?'.join('&', $tmp) : '';
	}
}
?>
