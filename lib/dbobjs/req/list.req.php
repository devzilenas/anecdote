<?

class ReqList { 
	
# ------------- SELECT ITEM ----------- 
	public static function isSelectItem() {
		return isset($_REQUEST['item']) && isset($_REQUEST['select']);
	}
	
	private static function isSelectItemCancel() {
		return self::isSelectItem() && isset($_REQUEST['cancel']);
	}

	public static function isSelectItemSubmit() {
		return self::isSelectItem() && isset($_POST['item_form_submit']);
	}

# -------------------------------------

# ------------- SELECT ITEMS ----------
	public static function isSelectItems() {
		return isset($_REQUEST['items']) && isset($_REQUEST['select']);
	}
	
	public static function isSelectItemsCancel() {
		return self::isSelectItems() && isset($_REQUEST['cancel']);
	}

	public static function isSelectItemsSubmit() {
		return self::isSelectItems() && isset($_POST['items_select_submit']);
	}

	public static function isSelectItemsContinue() {
		return self::isSelectItems() && isset($_POST['items_select_continue']);
	}

# ----------------------------------------
	
# ---------- ITEMS LIST PROCESS ----------
	private static function listProcess() { 
		$temporary_selected_ids   = Session::gSessionArray('temporary_selected_ids');
		$temporary_unselected_ids = Session::gSessionArray('temporary_unselected_ids');

		$pre_items_c  = Request::gPostArray('pre_itemsc');
		$pre_items_u  = Request::gPostArray('pre_itemsu');
		$all_items = array_merge($pre_items_c, $pre_items_u);

		$submittedIds = Request::gPostArray('item_id');

		$item_class   = $_SESSION['list_items'];

		// A. GAUNAMI DUOMENYS PAZYMETI. VISI GAUTI PAZYMETI DUOMENYS.
		//   DUOMUO GALI BUTI
		//   1. LAIKINAI PAZYMETU SARASE. NEDARYTI NIEKO.
		//   2. PASTOVIAI PAZYMETU SARASE. NEDARYTI NIEKO.
		//   3. LAIKINAI ATZYMETU SARASE. ISBRAUKTI IS LAIKINAI ATZYMETU SARASO
		//   4. PASTOVIAI ATZYMETU SARASE. ITRAUKTI I LAIKINAI PAZYMETU SARASA
		foreach($submittedIds as $item_id) {
			// A. 3.
			if (in_array($item_id, $temporary_unselected_ids)) {
				if(($key = array_search($item_id, $temporary_unselected_ids)) !== FALSE) unset($temporary_unselected_ids[$key]);
			}
			// A. 4.
			if (in_array($item_id, $pre_items_u) && !in_array($item_id, $temporary_selected_ids)) $temporary_selected_ids[] = $item_id;
		}

		// B. NEGAUTI DUOMENYS APIE ATZYMETUS. VISI LANGE BUVE DUOMENYS MINUS GAUTIEJI PAZYMETI DUOMENYS
		//   DUOMUO GALI BUTI.
		//   1. BUVO PASTOVIAI PAZYMETAS
		//      a. DABAR BUVO LAIKINAI ATZYMETAS. NEDARYTI NIEKO.
		//      b. DABAR ATZYMETAS. ITRAUKTI I LAIKINAI PAZYMETU SARASA.
		//   2. BUVO LAIKINAI PAZYMETAS. ISBRAUKTI IS LAIKINAI PAZYMETU.
		//   3. BUVO PASTOVIAI ATZYMETAS. NEDARYTI NIEKO.
		//   4. BUVO LAIKINAI ATZYMETAS. NEDARYTI NIEKO.
		$all_unsuccessful = array_diff($all_items, $submittedIds);
		foreach($all_unsuccessful as $item_id) {
			// B. 1.
			if(in_array($item_id, $pre_items_c)) {
				// 1.b.
				if(!in_array($item_id, $submittedIds) && !in_array($item_id, $temporary_unselected_ids)) $temporary_unselected_ids[] = $item_id; 
			}
			// B. 2.
			if(in_array($item_id, $temporary_selected_ids)) {
				if(($key = array_search($item_id, $temporary_selected_ids)) !== FALSE) unset($temporary_selected_ids[$key]);
			}
		}

		$_SESSION['temporary_selected_ids']   = $temporary_selected_ids;
		$_SESSION['temporary_unselected_ids'] = $temporary_unselected_ids;
	}

# -------- SELECT ITEMS --------------------------
	public static function processList() {
# -------- CANCEL ITEMS SELECT -------------------
		if (ReqList::isSelectItemsCancel()) {
			unset($_SESSION['list_items']);
			unset($_SESSION['list_filter']);
			unset($_SESSION['list_selected']);
			unset($_SESSION['return_url']);
			unset($_SESSION['selected_ids']);
			Request::hlexit(Session::sgu('cancel_url'));
		}
# -------- CONTINUE ITEMS SELECT -----------------
		if (ReqList::isSelectItemsContinue()) {
			self::listProcess();
			self::redirect_select_items();
		}
# -------- ITEMS SELECT SUBMITTED ----------------
		if (ReqList::isSelectItemsSubmit()) { 

			self::listProcess();

			$selected_ids   = Session::gSessionArray('temporary_selected_ids');
			$unselected_ids = Session::gSessionArray('temporary_unselected_ids');

			$_SESSION['selected_ids']   = $selected_ids  ; 
			$_SESSION['unselected_ids'] = $unselected_ids;

			unset($_SESSION['temporary_selected_ids']);
			unset($_SESSION['temporary_unselected_ids']);
			unset($_SESSION['cancel_url']);
			unset($_SESSION['list_filter']);
			unset($_SESSION['list_selected']);

			Request::hlexit(Session::sgu('return_url'));
		}

# -------- CANCEL ITEM SELECT ------------------------------
		if(ReqList::isSelectItemCancel()) {
			unset($_SESSION['list_items']);
			unset($_SESSION['list_filter']);
			unset($_SESSION['list_selected']);
			unset($_SESSION['temporary_selected_ids']);
			unset($_SESSION['return_url']); 
			unset($_SESSION['selected_ids']);
			Request::hlexit(Session::sgu('cancel_url'));
		}

# ----- SELECTED ITEM SUBMITED ---------------
		if (ReqList::isSelectItemSubmit()) {
			//get submitted item id
			if (isset($_POST['item_id']))
				$_SESSION['selected_ids'] = array($_POST['item_id']);

			unset($_SESSION['cancel_url']);
			unset($_SESSION['list_filter']);
			unset($_SESSION['list_selected']);

			Request::hlexit(Session::sgu('return_url'));
		}
	} 

	public static function redirect_select_item() {
		Request::hlexit("?item&select");
	}

	public static function redirect_select_items() {
		Request::hlexit("?items&select");
	}

}

