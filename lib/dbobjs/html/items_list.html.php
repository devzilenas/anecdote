<?

class ItemsListHtml {
	public static function detectList() {
		if(ReqList::isSelectItem()) self::itemSelectList();
		if(ReqList::isSelectItems()) self::itemsSelectList();
	}
# ----------------------------------------------
# --------- ITEM SELECT LIST -------------------
# ----------------------------------------------
	public static function itemSelectList() {
		$iList = new Objset($_SESSION['list_items'], $_SESSION['list_filter'], Req::gp0('page'));
		$iList->loadNextPage();
		$items  = $iList->getObjs();
		$tarr   = Session::gSessionArray('temporary_selected_ids');
		$tempId = count($tarr) >0 ? current($tarr) : NULL;

		echo ObjSetHtml::makeListHeader($iList, '?item&select');
		echo HtmlBlock::chooseItemList($items, '?item&select', $_SESSION['list_selected'], $tempId);
	}


# ----------------------------------------------
# --------- ITEMS SELECT LIST -------------------
# ----------------------------------------------
	public static function itemsSelectList() {
		$iList = new Objset($_SESSION['list_items'], $_SESSION['list_filter'], Req::gp0('page'));
		$iList->loadNextPage();
		$items = $iList->getObjs();

		$temporary_selected_ids   = Session::gSessionArray('temporary_selected_ids');
		$temporary_unselected_ids = Session::gSessionArray('temporary_unselected_ids');

		echo ObjSetHtml::makeListHeader($iList, '?items&select');
		echo HtmlBlock::chooseItemsList($items, '?items&select', $_SESSION['list_selected'], $temporary_selected_ids, $temporary_unselected_ids);
	}

}

