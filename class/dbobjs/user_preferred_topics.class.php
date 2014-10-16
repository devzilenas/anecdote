<?

class UserPreferredTopics extends DbObj {
	protected static $table  = 'user_preferred_topics';
	protected static $FIELDS = array(
			'user_id'  => '%d',
			'topic_id' => '%d');

	public static function topics($language, $user_id) {
		$filter = Topic::newFilter(array("Topic" => "*"));
		$filter->setFrom(array("Topic" => "t"));
		$filter->setJoinTables(array("UserPreferredTopics" => "upt"));
		$filter->setJoinOn("t.id = upt.topic_id");
		$filter->setWhere(array(
			"t.language"  => $language,
			"upt.user_id" => $user_id));
		return Topic::find($filter);
	}

	public static function preferred($language, $user_id) {
		$filterPc = new SqlFilter("t.*, upt.user_id = $user_id AS user_topic");
		$filterPc->setFrom("topics t"); 
		$filterPc->setJoin('LEFT OUTER JOIN user_preferred_topics upt');
		$filterPc->setOn('ON t.id = upt.topic_id');
		$filterPc->setWhere("t.language = '$language'");
		$filterPc->setOrderBy('user_topic DESC, t.name');
		$filterPc->setCount("COUNT(*) AS cnt");
		return $filterPc;
	}

}

