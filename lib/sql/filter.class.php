<?
// SQL query builder
class Filter {
	public $what      ; //select what array('ClassName' => array('field', 'field'), 'ClassName' => array('field', 'field'))
						// or array('COUNT(*)' => 'cnt'), or array('ClassName' => '*')
	protected $from    = array(); //array('ClassName' => 'alias')
	public $joinTables = array(); //array('ClassName' => 'alias')
	public $joinOn     ; //array('ClassName.field' => 'ClassName.field') or str 'table.field = table2.field AND table3.field = table4.field'
	public $where     ; //sql where: str or array('ClassName.field' => 'value')
	public $groupBy   ;
	public $order     ;
	public $limit     ;
	public $offset    ;

	public $cnt        = '';

	public function setCount($cnt) {
		$this->cnt = $cnt;
	}

	public function getCount() {
		return $this->cnt;
	}
	
	
	public function setWhere($where) {
		$this->where = $where;
	}
	public function setWhat($what) {
		$this->what = $what;
	}
	public function setFrom($from) { 
		$this->from = $from;
	}
	public function setLimit($limit) {
		$this->limit = $limit;
	}
	public function setOffset($offset) {
		$this->offset = $offset;
	}
	public function setJoinTables($joinTables) {
		$this->joinTables = $joinTables;
	}
	public function setJoinOn($joinOn) {
		$this->joinOn = $joinOn;
	}
	public function setGroupBy($groupBy) {
		$this->groupBy = $groupBy;
	}

	public function setOrderBy($order) {
		$this->order = $order;
	}

	function __construct($what) {
		$this->what = $what;
	}

	private function isJoin() {
		return empty($this->joinTables);
	}

	private function tablesList() {
		return array_merge($this->from, $this->joinTables);
	}

	private function hasAlias($table) {
		$alias = self::tableAlias($table);
		return NULL !== $alias;
	}

	private function tableAlias($table) {
		$tables = self::tablesList();
		return $tables[$table];
	}

	private function classnameByAlias($alias) {
		$tables = array_flip(self::tablesList());
		return $tables[$alias];
	}

	private function isAlias($name) {
		return in_array($name, array_values(self::tablesList()));
	} 

	protected static function makeLimitStr($limit, $offset) {
		if (!empty($limit)) {
			if (!empty($offset)) {
				$limit = (int)$offset.','.(int)$limit;
			} 
			return "LIMIT $limit"; 
		} else {
			return '';
		}
	}

	protected static function makeGroupByStr($groupBy) {
		return (!empty($groupBy)) ? "GROUP BY $groupBy" : $groupBy;
	}

	protected static function makeOrderByStr($orderBy) {
		return (!empty($orderBy)) ? "ORDER BY $orderBy" : $orderBy;
	}


	//SELECT    $what
	// FROM     $from
	// JOIN     ($join)
	// ON       ($on)
	// WHERE    $where
	// GROUP BY $group_by
	// ORDER    ($order)
	// $limit,  $offset
	public function makeSQL() { 
		$whats = array(); $what_str = ''; $froms = array(); $from_str = ''; $joins = array(); $join_str = ''; $ons = array(); $ons_str = ''; $wheres = array(); $where_str = ''; $values= array(); $group_by_str = ''; $order_str = ''; $limit_str = '';

		//SELECT what
		// 1. array('Table' => ('field1', 'field2'))
		// 2. array('Table' => '*')
		// 3. array('COUNT(*)' => 'cnt') results in 'COUNT(*) as cnt'
		// 4. 'Table.field'
		// 5. ''
		if (is_array($this->what)) {
			foreach($this->what as $provider => $fields) {
				if (is_array($fields)) {
					foreach($fields as $field) { // #1
						$whats[] = self::tableAlias($provider).'.'.$field; //alias.fieldname
					}
				} else if ('*' === $fields) {    // #2
					$whats[] = self::tableAlias($provider).'.*';
				} else if ('' !== $fields) {     // #3
					$whats[] = $provider.' as '.$fields;
				}
			}
		} else if (is_string($this->what)) {     // #4
			$whats[] = $this->what;
		} else if (empty($this->what)) {         // #5
			$whats[] = '*';
		}
		if (!empty($whats)) $what_str = join(',', $whats);

		//FROM
		//array('ClassName' => 'alias') results in 'table_name as alias'
		if (is_array($this->from)) {
			foreach($this->from as $provider => $alias) {
				if (self::hasAlias($provider)) {
					$froms[] = $provider::tableName()." $alias";
				} else {
					$froms[] = $provider;
				}
			}
		}
		if(!empty($froms)) $from_str = "FROM ".join(',', $froms); 

		//WHERE
		//TODO:add self::filterFields($fields)
		// 1. array('ClassName.field' => 'value')
		// 2. string "field = 'value' OR field = 'value2'"
		// 3. array('alias.field' => 'value')
		if (!empty($this->where)) {
			foreach($this->where as $f => $value) {
				$tmp = explode('.', $f);
				$class_name = (self::isAlias($tmp[0])) ? 
					self::classnameByAlias($tmp[0]) : $tmp[0];

				if (count($tmp) == 2 && $class_name::isField($tmp[1])) { // #1
					$field = $tmp[1];
					$wheres[] = join('.', array(self::tableAlias($class_name), $field))."='".$class_name::fieldFormat($field)."'";
				}
				$values[] = $value;
			}
		} else if(is_string($this->where)) { #2
			$wheres[] = $this->where;
		}
		if(!empty($wheres)) $where_str = 'WHERE '.join(' AND ', $wheres);

# --------- GROUP BY --------------
		$group_by_str = self::makeGroupByStr($this->groupBy);

# --------- ORDER BY --------------
		$order_str =  self::makeOrderByStr($this->order);

# --------- LIMIT -----------------
		$limit_str = self::makeLimitStr($this->limit, $this->offset);

		if (!$this->isJoin()) {
			//joinTables
			// array('ClassName' => 'alias')
			if (is_array($this->joinTables)) {
				foreach ($this->joinTables as $provider => $alias) {
					$joins[] = $provider::tableName()." $alias";
				}
			}

			//joinOn
			// array('ClassName.field' => 'ClassName.field')
			// string 'table.field = table2.field AND table3.field = table4.field'
			if (is_array($this->joinOn)) {
				foreach($this->joinOn as $f1 => $f2) {
					$tmp = explode('.', $f1);
					$provider1 = $tmp[0]; $field1 = $tmp[1];
					$tmp = explode('.', $f2);
					$provider2 = $tmp[0]; $field2 = $tmp[1];
					$ons[] = self::tableAlias($provider1).'.'.$field1.'='.self::tableAlias($provider2).'.'.$field2;
				}
			} else if (is_string($this->joinOn)) {
				$ons[] = $this->joinOn;
			}
		}
		if(!empty($joins)) $join_str = " JOIN (".join(',', $joins).")";
		if(!empty($ons)) $ons_str = " ON (".join(',', $ons).")";

		// FROM  $from
		// JOIN  ($join)
		// ON    ($on)
		// WHERE $where
		// ORDER ($order)
		// $offset,$limit
		$query = vsprintf("
				SELECT $what_str 
			           $from_str 
					   $join_str 
					   $ons_str
					   $where_str $order_str $group_by_str $limit_str ", 
					array_map('mysql_real_escape_string', $values));
		return $query;
	}
}

# ----------- EXAMPLES ------------

/* Make query with Join
   
   $filter = self::newFilter(array('Anecdote' => '*'));
$filter->setFrom(array("Anecdote" => 'a'));
$filter->setWhere(array(
			'a.language'  => $language,
			'upt.user_id' => $user_id,
			'upc.user_id' => $user_id));
$filter->setJoinTables( array(
			'AnecdoteCharacters'      => 'ac',
			'UserPreferredTopics'     => 'upt',
			'UserPreferredCharacters' => 'upc'));
$filter->setJoinOn('a.topic_id = upt.topic_id AND a.id = ac.anecdote_id AND ac.character_id = upc.character_id');
*/
