<?

class Character extends Dbobj {
	protected static $table = 'characters';
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

	public static function toCharacters($chr_str, $language) {
		$chrs = mb_split('[,;]+\s*', $chr_str);
		$cs   = array();
		foreach($chrs as $chr) {
			$filter = self::newFilter(array("Character" => array("*")));
			$filter->setFrom(array("Character" => "c"));
			$filter->setWhere(array(
						"c.name"     => $chr,
						"c.language" => $language));
			$filter->setLimit(1);
			$character = self::find($filter);
			if (count($character) == 1) { 
				$cs[] = current($character);
			} else {
				$cs[] = Character::fromForm(array(
							"language" => $language,
							"name"     => $chr));
			}
		}
		return $cs;
	}

	public function anecdotesFilter() {
		$filter = new Filter(array('Anecdote' => '*'));
		$filter->setFrom(array('Anecdote' => 'a' ));
		$filter->setWhere(array('AnecdoteCharacters.character_id' => $this->id));
		$filter->setJoinTables(array('AnecdoteCharacters' => 'ac'));
		$filter->setJoinOn(array('AnecdoteCharacters.anecdote_id' => 'Anecdote.id'));
		return $filter;
	} 
}

