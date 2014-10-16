<?
include_once 'lib/sql/dbobj.class.php';
include_once 'anecdote_characters.class.php';
include_once 'topic.class.php';

class Anecdote extends Dbobj {
	protected static $table  = "anecdotes";
	protected static $FIELDS = array(
			'id'       => '%d',
			'topic_id' => '%d',
			'title'    => '%s',
			'contents' => '%s',
			'language' => '%s');

	public function hasValidationErrors() {
		$validation = array();
		if($v = self::validateNotEmpty('title')) $validation['title'] = $v;
		if($v = self::validateNotEmpty('contents')) $validation['contents'] = $v;
		return $validation;
	}

	public function setCharacters($chids) {
		$characters = array();
		foreach($chids as $chid)
			if (Character::exists($chid)) $characters[] = Character::load($chid);
		$this->characters = $characters;
	}

	public function anecdotesCnt($language) {
		return self::cnt(self::anecdotesAll($language));
	}

	public function anecdotesCntPreferred($language, $user_id) {
		return self::cnt(self::anecdotesFilter($language, $user_id));
	}

	public function anecdotesFilter($language, $user_id) {
		$filterc = self::newFilter(array('Anecdote' => '*'));
		$filterc->setFrom(array("Anecdote" => 'a'));
		$filterc->setWhere(array(
					'a.language'  => $language,
					'upc.user_id' => $user_id));
		$filterc->setJoinTables( array(
					'AnecdoteCharacters'      => 'ac',
					'UserPreferredCharacters' => 'upc'));
		$filterc->setJoinOn('a.id = ac.anecdote_id AND ac.character_id = upc.character_id');

		$filtert = self::newFilter(array('Anecdote' => '*'));
		$filtert->setFrom(array("Anecdote" => 'a'));
		$filtert->setWhere(array(
					'a.language'  => $language,
					'upt.user_id' => $user_id));
		$filtert->setJoinTables( array(
					'AnecdoteCharacters'      => 'ac',
					'UserPreferredTopics'     => 'upt'));
		$filtert->setJoinOn('a.topic_id = upt.topic_id');

		return array($filterc, $filtert);
# ----------- MAKES ------------
/* (SELECT a.* FROM anecdotes a 

JOIN anecdote_characters ac
ON (ac.anecdote_id = a.id)

JOIN user_preferred_characters upc
ON (ac.character_id = upc.character_id)

WHERE a.language='lt' AND upc.user_id='1' 
)

UNION 

(SELECT a.* FROM anecdotes a

JOIN user_preferred_topics upt
ON (a.topic_id = upt.topic_id)
WHERE a.language='lt' AND upt.user_id = '1'
)
*/
	}

	public function topic() {
		$filter = new Filter(array("Topic" => array("*")));
		$filter->setFrom(array('Topic' => 't'));
		$filter->setWhere(array('t.id' => $this->topic_id));
		$filter->setLimit(1);
		$topic = Topic::find($filter);
		return (!empty($topic)) ? $topic[0] : NULL ;  
	}

	public function topicIds() {
		$ret = array();
		if (NULL !== $this->topic_id)
			$ret[] = $this->topic_id;
		return $ret;
	}

	public function charactersFilter() {
		$filter = Character::newFilter(array("Character" => "*"));
		$filter->setWhat(array('Character' => array('*')));
		$filter->setFrom(array('Character' => 'c'));
		$filter->setWhere(array('AnecdoteCharacters.anecdote_id' => $this->id));
		$filter->setJoinTables(array('AnecdoteCharacters' => 'ac'));
		$filter->setJoinOn('ac.character_id = c.id');
		return $filter;
	}

	public function anecdoteTopicsList($list_selected = 'anecdote_topic') {
		$filter = new SqlFilter("t.*, SUM(a.id = $this->id) AS $list_selected");
		$filter->setFrom("topics t");
		$filter->setJoin("LEFT OUTER JOIN anecdotes a");
		$filter->setOn("ON a.topic_id = t.id");
		$filter->setWhere("t.language = '$this->language'");
		$filter->setGroupBy("t.id");
		$filter->setOrderBy("anecdote_topic DESC, t.name");
		$filter->setCount("COUNT(DISTINCT t.id) as cnt");
		return $filter;
	}

	public function anecdoteCharactersList($list_selected = 'anecdote_character') {
		$filterAc = new SqlFilter("c.id, c.name, ac.*, SUM(ac.anecdote_id = $this->id) AS $list_selected");
		$filterAc->setFrom("characters c"); 
		$filterAc->setJoin('LEFT OUTER JOIN anecdote_characters ac');
		$filterAc->setOn('ON c.id = ac.character_id');
		$filterAc->setWhere("c.language = '$this->language'");
		$filterAc->setGroupBy("c.id");
		$filterAc->setOrderBy('anecdote_character DESC, c.name');
		$filterAc->setCount("COUNT(DISTINCT c.id) as cnt");
		return $filterAc;
		/*
SELECT c.id, c.name, ac.*, SUM(ac.anecdote_id = 49) AS anecdote_character
FROM characters c LEFT OUTER JOIN anecdote_characters ac
ON c.id = ac.character_id
WHERE c.language = 'lt'
GROUP BY c.id
ORDER BY anecdote_character DESC, c.name
		   
		   */
	}

	public static function anecdotesAll($lang) {
		$filter = self::newFilter(array("Anecdote" => "*"));
		$filter->setFrom(array("Anecdote" => "a"));
		$filter->setWhere(array("Anecdote.language" => $lang));
		return $filter;
	}
	
	public function characters() {
		$ch = array();
		if (!$this->isNew()) {
			$f  = self::charactersFilter();
			$ch = self::find($f);
		} else {
			$ch = $this->characters;
		}
		return $ch;
	}

	public function charactersNames() {
		$cs  = $this->characters();
		$ret = array();
		if(!empty($cs) && is_array($cs)) {
			foreach($cs as $c) {
				$ret[] = so($c->name);
			}
		}
		return $ret;
	}

	public function beforeInsert() {
		if(!Topic::exists($this->topic_id)) $this->topic_id = NULL;
	}

	public function afterInsert($id, $obj) {

		if(isset($obj->character_ids) && is_array($obj->character_ids)) {
			$chids = $obj->character_ids;
			foreach($chids as $chid) 
				AnecdoteCharacters::insertIfNew($id, $chid);
		}
	}

	//grazina atsitiktini elementa, jei nera visai elementu grazina false
	public static function getNext($lang = NULL) {
		$filter = new Filter();
		$filter->setFrom(array('Anecdote' => 'a'));
		if(Language::valid($lang)) {
			$filter->setWhere(array('Anecdote.language' => $lang));
		}

		$cnt = self::cnt($filter);
		$aid = false;
		if ($cnt > 0) {
			$idx = $cnt - 1;
			$filter->setWhat(array('Anecdote' => array('id')));
			$filter->setLimit(1);
			$filter->setOffset(
				mt_rand(0, (mt_getrandmax() < $idx) ? mt_getrandmax() : $idx));
			$as = self::find($filter);
			$aid = $as[0]->id;
		}
		return self::load($aid);
	}
}

?>
