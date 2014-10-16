<?
include_once 'lib/sql/dbobj.class.php';
include_once 'character.class.php';

class AnecdoteCharacters extends Dbobj { 
	protected static $table  = "anecdote_characters";
	protected static $FIELDS = array(
			'anecdote_id'  => '%d',
			'character_id' => '%d');

	public static function insertIfNew($aid, $chid) {
		//jei poros nėra tai sukuriam
		$filter = self::newFilter(array("AnecdoteCharacters" => array("*")));
		$filter->setFrom(array("AnecdoteCharacters" => "ac"));
		$filter->setWhere(array(
					"ac.anecdote_id"  => $aid,
					"ac.character_id" => $chid));
		$filter->setLimit(1);
		$ac = AnecdoteCharacters::find($filter);
		if (count($ac) == 0) self::fromForm(array(
					'anecdote_id'  => $aid,
					'character_id' => $chid ))->insert();
	}
}

