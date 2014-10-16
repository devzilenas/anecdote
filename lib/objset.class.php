<?

class ObjSet {

	public static $DEFAULT_SIZE = 5; 

	private $current   = 0; // current page
	public  $size         ; // page size
	public  $filter       ; // sql filter
	private $provider     ; // Class that provides objects

	private $objs      = array(); // page objects
	private $o_i       = 0      ; // object iterator

	public function getObjs() {
		return $this->objs;
	}

	public function setFilter($filter) {
		$this->filter = $filter;
	}

	public function getFilterCopy() {
		if (is_array($this->filter)) {
			$tmp = $this->filter;
			$ret = array();
			foreach($tmp as $f) {
				$ret[] = clone $f;
			}
			return $ret;
		} else {
			return (clone $this->filter);
		}
	}

	private function totalObjs() {
		$pr = $this->provider;
        return $pr::cnt($this->getFilterCopy());
	}

	public function totalPages() {
		return ceil($this->totalObjs() / $this->size);
	}

	public function lastPageN() {
		$pages = $this->totalPages();
		return ($pages > 0) ? $pages-1 : 0; 
	}

	private function objsC() { return count($this->objs); }

	public function loadedPage() { return $this->current; }

	public function resetI() { $o_i = 0; }

	public function getNextObj() {
		$ret = FALSE;
		if($this->o_i>= 0 && $this->o_i < count($this->objs) ) {
			$ret = $this->objs[$this->o_i];
			$this->o_i++;
		}
		return $ret;
	}

	private function loadPage($pageN) {
		$pr     = $this->provider;
		$filter = $this->getFilterCopy();
		if (is_array($filter)) {
			//create sql
			$sf = new SqlFilter();
			$sf->setWhat('*');
			$sf->setFrom(Dbobj::unionFilters($filter));
			$filter = $sf;
		}

		$filter->setLimit($this->size);
		$filter->setOffset($pageN * $this->size);

		$this->objs    = $pr::find($filter);
		$this->current = $pageN;//puslapis, kuri pakrovem
		$this->resetI();
		return $this->objsC() > 0;
	}

	public function loadNextPage() {
		$pr     = $this->provider;
		$total  = $this->totalObjs();

		$loadWhat = 0;
		if ($this->objsC() == 0) { //jei dar nera pakroves, tai pakrauti current, kitu atvj sekanti
			$loadWhat = $this->current;
		} else { //jau buvo pakrauta, tai krauti sekanti nepakrauta 
			if ($this->current < $this->lastPageN()) {//jei yra dar lapu , kuriuos galima pakrauti, tai pakrauti sekanti
				$loadWhat = $this->current + 1;
			}
		}
		$this->loadPage($loadWhat);
		return TRUE;
	}

	public function hasPrev() {
		return $this->current != 0;
	}

	public function hasNext() {
		return $this->current != $this->lastPageN();
	}

	public function prevI() {
		//jei yra pries tai, tai grazinti;jei nera, tai grazinti 0
		return ($this->hasPrev()) ? $this->current - 1 : 0;
	}

	public function nextI() {
		//jei yra - grazinti,jei nera - paskutini
		return ($this->hasNext()) ? $this->current + 1 : $this->lastPageN();
	}

	function __construct($provider, $filter, $current = 0, $size = FALSE) {
		if($size === FALSE || !is_int($size)) $size = self::$DEFAULT_SIZE;
		$this->provider = $provider;
		$this->setFilter($filter);
		$this->current  = $current;
		$this->size     = $size;
	}
}
