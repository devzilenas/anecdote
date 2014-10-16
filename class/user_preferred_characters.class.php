<? 
include_once 'lib/sql/dbobj.class.php';
include_once 'character.class.php';

class UserPreferredCharacters extends Dbobj {
	protected static $table  = 'user_preferred_characters';
	protected static $FIELDS = array(
			'user_id'      => '%d',
			'character_id' => '%d');

	public function to_s() {
		return Character::load($this->character_id)->name;
	}

	public static function preferred($language, $user_id) {
		$filterPc = new SqlFilter("c.*, upc.user_id = $user_id AS user_character");
		$filterPc->setFrom("characters c"); 
		$filterPc->setJoin('LEFT OUTER JOIN user_preferred_characters upc');
		$filterPc->setOn('ON c.id = upc.character_id');
		$filterPc->setOrderBy('user_character DESC, c.name');
		$filterPc->setWhere("c.language = '$language'");
		$filterPc->setCount("COUNT(*) AS cnt");
		return $filterPc;
/* SELECT c.*, upc.user_id = 1 as user_character
FROM characters c LEFT OUTER JOIN user_preferred_characters upc
ON c.id = upc.character_id
ORDER BY user_character DESC, c.name */
	}

	public static function characters($language, $user_id) {
		$filter = Character::newFilter(array("Character" => "*"));
		$filter->setFrom(array("Character" => "c"));
		$filter->setJoinTables(array("UserPreferredCharacters" => "upc"));
		$filter->setJoinOn("c.id = upc.character_id");
		$filter->setWhere(array(
			"c.language"  => $language,
			"upc.user_id" => $user_id));
		return Character::find($filter);
	}

}

