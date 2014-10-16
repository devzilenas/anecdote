<?
class Topic extends Dbobj {
	protected static $table = 'topics';
	protected static $FIELDS = array( 
			'id'       => '%d',
			'name'     => '%s',
			'language' => '%s');

	public function to_s() {
		return $this->name;
	}

	public function hasValidationErrors() {
		$validation = array();

		if ($v = self::validateNotEmpty('name')) $validation['name'] = $v;

		return $validation;
	}
}
