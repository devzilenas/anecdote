<?

class Dbobj {

	protected $dbd     = array() ; //data from table
	protected $d       = array() ; //other data, not from table

	public function validateNotEmpty($field) {
		if(self::fieldIsString($field) && '' === trim($this->$field)) return sprintf(t(get_called_class()." %s can not be empty!"), t($field));
	}

	public function isNew() {
		return !isset($this->id);
	}

	public static function tableName() {
		return static::$table;
	}

	private static function fieldIsString($name) {
		return self::isField($name) && self::fieldFormat($name) === '%s';
	}

	private static function fieldIsInt($name) {
		return self::isField($name) && self::fieldFormat($name) === '%d';
	}

	private static function fieldAcceptsNULL($name) {
		return self::fieldIsInt();
	}

	public static function fieldFormat($fieldName) {
		return static::$FIELDS[$fieldName];
	}

	protected static function fieldNames() {
		return array_keys(static::$FIELDS);
	} 

	public static function isField($name) {
		return in_array($name, self::fieldNames());
	}

	protected static function filterFields($fields = array()) {
		$retVal = array();
		foreach($fields as $fieldName) {
			$retVal[] = self::isField(end(explode('.', $fieldName)));//fieldname may be with alias
		}
		return $retVal;
	}

	public function __set($name, $val) {
		if (self::isField($name)) { 
			$this->dbd[$name] = $val;
		} else {
			$this->d[$name] = $val;
		}
	}

	public function __get($name) {
		if (isset($this->$name)) { 
			if (self::isField($name)) {
				return $this->dbd[$name];
			} else {
				return $this->d[$name];
			}
		}
	}

	public function __isset($name) {
		return isset($this->dbd[$name]) || isset($this->d[$name]);
	}

	private static function fromSQL($data) {
		$d = new static();
		if (!is_array($data)) {
			return NULL;
		} else {
			foreach($data as $field => $value) {
				$field = end(explode('.',$field)); 
				$d->$field = $value;
			}
			return $d;
		}
	}

	public static function fromForm($data, $fields = NULL, $obj = NULL) {
		$d = (NULL === $obj ? new static() : $obj);
		if (is_array($data)) {
			foreach ($data as $name => $value) {
				if (is_array($fields) && !empty($fields)) {
					if (in_array($name, $fields)) $d->$name = $value;
				} else {
					$d->$name = $value;
				}
			}
		}
		return $d;
	}

	public function delc() {
		if(!$this->isNew()) self::del($this->id);
	}

	public static function del($id) { 
		$query = sprintf("DELETE FROM ".self::tableName()." 
			WHERE id = '%d' LIMIT 1",
			mysql_real_escape_string($id));
		mysql_query($query) or die(t("Record not deleted!") . mysql_error());
	} 

	public static function delWhere($where) { 
		if (is_array($where)) {
			$fieldNames = array_keys($where);
			foreach($fieldNames as $field) {
				if (self::isField($field)) {
					$wheres[]  = "$field='".self::fieldFormat($field)."'" ;
					$values[]  = $where[$field];
				}
			}
			$query = vsprintf("
				DELETE FROM ".self::tableName()."
				WHERE ".join(' AND ', $wheres), array_map('mysql_real_escape_string', $values));
			mysql_query($query) or die(t("Records not deleted!") . mysql_error());
		}

	}

	// atnaujina laukus is fieldNames, pagal data
	public static function update($id, $fieldNames = array(), $data = array()) {
		$values = array();
		$set    = array();
		if (is_array($fieldNames)) {
			foreach($fieldNames as $field) {
				if (self::isField($field) && isset($data[$field])) {
					if (NULL === $data[$field] && self::fieldAcceptsNULL($field)) 
					{
						$set[] = "$field=%s"; 
						$values[] = 'NULL';
					} else {
						$set[]    = "$field='".self::fieldFormat($field)."'" ;
						$values[] = $data[$field];
					}
				}
			}
		}
		$values[] = (int)$id;//pridedame i sąrašo pabaigą id
		$query = vsprintf("UPDATE ".self::tableName()." SET ".implode(',', $set)." 
				WHERE id='%d' LIMIT 1",
				array_map('mysql_real_escape_string', $values));
		if (mysql_query($query)) {
			return true;
		} else {
			self::diem(t("Object not updated!"), $query);
		}
	}

	public function insert() {
		if (method_exists($this, 'beforeInsert')) {
			$this->beforeInsert();
		}

		$fields  = array();
		$values  = array();
		$formats = array();
		foreach (static::$FIELDS as $field => $format) {
			if (isset($this->$field)) {
				$fields[]  = $field; 
				$values[]  = $this->$field;
				$formats[] = $format;
			}
		}
		$query  = vsprintf("INSERT INTO ".self::tableName()."(".implode(',', $fields).")
				VALUES(".implode(',',array_map('self::quote', $formats)).")", array_map('mysql_real_escape_string', $values));
		mysql_query($query) or die(t("Object not created!") . mysql_error());
		$insId = mysql_insert_id();
		
		if (method_exists($this, 'afterInsert'))
			$this->afterInsert($insId, $this);

		$this->id = $insId;

		return $insId;
	}

	public static function findBySql($query) {
		if(!$result = mysql_query($query)) {
			debug_print_backtrace();
			die("Query: $query " . t("Object not found!") . mysql_error());
		}

		$objs = array();
		while ($row = mysql_fetch_assoc($result)) { 
			$objs[] = self::fromForm($row);
		}
		return $objs;
	}

	public static function find($filter) {
		return self::findBySql($filter->makeSQL());
	}

	public static function exists($id) {
		$cl = get_called_class();
		$filter = self::newFilter(array($cl => array('id')));
		$filter->setFrom(array($cl => 't'));
		$filter->setWhere(array($cl.'id' => (int)$id)); 
		$filter->setLimit(1);
		$obj = self::find($filter);
		return is_array($obj) && count($obj) == 1;
	}

	public static function load($id, $fields = array()) {
		$cl = get_called_class();
		$filter = $cl::newFilter(array($cl => "*"));
		$filter->setFrom(array($cl => 't'));
		$filter->setWhere(array("$cl.id" => $id));
		$whats = array();
		if (!empty($fields) && is_array($fields)) {
			foreach($fields as $name) {
				$whats[] = $name; //TODO:check if field is valid
			} 
			$filter->setWhat(array($cl => $whats));
		}
		$obj = self::find($filter);
		return (is_array($obj) && !empty($obj)) ? $obj[0] : NULL;
	}

	public static function existsBy($fv) {
		$filter = self::newFilter();
		$filter->setWhere($fv);
		$filter->setLimit(1);
		return count(self::find($filter)) > 0;
	}

	public static function loadBy($fv) {
		$filter = self::newFilter();
		$filter->setWhere($fv);
		$filter->setLimit(1);
		return current(self::find($filter));
	}

	public static function unionFilters($filters) { 
		$sqls = array();
		foreach ($filters as $f) {
			$tmp_filter = $f; 
			$tmp_filter->setLimit(NULL);
			$sqls[] = $tmp_filter->makeSQL();
		}
		$query = '('.join(' UNION ', $sqls).') as t1 ';
		return $query;
	}

	// ACCEPTS
	//      1. $filter
	//      2. array($filter, $filter, ...) - when UNION
	public static function cnt($filter) {
		if (is_array($filter)) { //2
			$query = 'SELECT COUNT(*) as cnt FROM '.self::unionFilters($filter);
		} else {
			$values = array();
			if ('' != $filter->getCount() && is_string($filter->what)) {
				$filter->setWhat($filter->getCount().','.$filter->what);
			} else {
				$filter->setWhat(array("COUNT(*)" => 'cnt'));
			}
			$tmp_filter = $filter;
			$tmp_filter->order   = '';
			$tmp_filter->groupBy = '';
			$tmp_filter->limit   = '';
			$tmp_filter->offset  = '';
			$tmp_filter->setLimit(NULL);
			$query = $tmp_filter->makeSQL();
		}
		$result = mysql_query($query) or die($query . t("Objects count failed!"). mysql_error());
		return mysql_fetch_object($result)->cnt;
	} 

	protected static function quote($str) {
		return "'$str'";
	}

	public static function newFilter($what = NULL) {
		$cl = get_called_class();
		if (NULL === $what) $what = array($cl => array("*"));
		$filter = new Filter($what);
		$filter->setFrom(array($cl => 't1'));
		return $filter;
	}

	private static function diem($message, $query) {
		die ($message.PHP_EOL. "Mysql error!  ".mysql_error().PHP_EOL."Actual query: ".$query.PHP_EOL);
	}

}

