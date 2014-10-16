<?

class Form {

	public static function submit($value) {
		return '<input type="submit" value="'.$value.'" />';
	}

	public static function inputHtml($type, $name, $value) {
		return '<input type="'.$type.'" name="'.$name.'" value="'.$value.'" />';
	}

	public static function hiddenInput($name, $value) {
		return self::inputHtml("hidden", $name, $value);
	}

	public static function actionUpdate() {
		return self::hiddenInput("action", "update");
	}

	public static function actionDelete() {
		return self::hiddenInput("action", "delete");
	}

	public static function options($options, $selected) {
		$out = ''; $sel = '';
		if (is_array($options)) {
			foreach($options as $name => $val) {
				$sel = ' ';
				if ($val == $selected) $sel = ' selected ';
				$out .= "<option{$sel}value=\"$val\">$name</option>";
			}
		}
		return $out;
	}
	
	public static function label($for, $txt) {
		return '<label for="'.$for.'">'.$txt.'</label>';
	}

	public static function validation($name, $field) {
		if($v = hasV($name, $field)) {
			unset($_SESSION[$name][$field]);
			return '<span>'.$v.'</span>';
		}
	}

}

