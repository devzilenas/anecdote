<?

class HtmlBlock {
	public static function altTitle($str) {
		return "alt=\"$str\" title=\"$str\"";
	}

	public static function language() {
		$languages = Language::languages();
		$strs      = array();
		foreach($languages as $code => $title) {
			$img_str = '<img class="flag" src="media/flags/flag_'.$code.'_small.png" '.self::altTitle($title).' />';
			$strs[] = ($code !== UserSession::language()) ? 
					'<a href="?language='.so($code).'">'.$img_str.'</a>' : 
					$img_str;
		}
		return join(' ',$strs);
	}

	private static function v($str) {
		return 'value="'.$str.'"';
	}

	public static function simpleList($items, $page = 0) {
		$out = '';
		if (count($items) > 0) {
			foreach($items as $item) {
				$cl  = get_class($item);
				$cll = strtolower($cl);
				$tmp[] = so($item->to_s()).'<input type="image" src="media/img/xsas.png" name="remove_preferred_'.$cll.'_id['.$item->id.']" alt="'.t("cross").'" title="'.t("Remove").'" />';
			}
			$out = '<form method="post" action="?preferred&page='.$page.'">'.join(' ', $tmp).'</form>';
		}
		return $out;
	}

	public static function topicsList($language) {
		$filter = Topic::newFilter(array("Topic" => "*"));
		$filter->setWhere(array('Topic.language' => $language));
		$tList = new ObjSet('Topic', $filter, Req::get0('page'));
		echo ObjSetHtml::makeListHeader($tList, '?topics');
		echo self::itemsList($tList);
	}

	public static function charactersList($language) {
		$filter = Character::newFilter(array("Character" => "*"));
		$filter->setWhere(array('Character.language' => $language));
		$cList = new ObjSet('Character', $filter, Req::get0('page'));
		echo ObjSetHtml::makeListHeader($cList, '?characters');
		echo HtmlBlock::itemsList($cList);
	}

	public static function preferredChList($language, $user_id) { 
		$filter = Character::charactersFilter($language, $user_id);
	}

	static function anecdotesList($language, $user_id) { 
		$filter    = Anecdote::anecdotesFilter($language, $user_id);
		$anecdotes = new ObjSet('Anecdote', $filter, Req::get0('page'));
		if ($anecdotes->totalPages() == 0)
			$anecdotes->setFilter(Anecdote::anecdotesAll($language));
		echo ObjSetHtml::makeListHeader($anecdotes, '?anecdotes');
		echo HtmlBlock::anecdotes($anecdotes);
	}

	private static function anecdotes($anecdotes) {
		$anecdotes->loadNextPage();
		$as = array();
		while($anecdote = $anecdotes->getNextObj())
			$as[] = "<li><a href=\"?anecdote=".$anecdote->id."&read\">".od($anecdote->title,$anecdote->id)."</a></li>";
		if (count($as)>0)
			echo '<ol>'.join('', $as).'</ol>';
	}

	public static function topicForm($topic) {
		echo Form::validation('topic_validation','name').
			Form::label("topic[name]", t("Name")).
			'<input type="text" id="topic[name]" name="topic[name]" value="'.so($topic->name).'" /><br />'; 
	}

	public static function characterForm($character) { 
		echo Form::validation('character_validation', 'name').
			Form::label("topic[name]", t("Name")).'
			<input type="text" id="character[name]" name="character[name]" value="'.so($character->name).'" /><br />'; 
	}

	public static function anecdoteForm($a = NULL) {
		$title = $contents = $characters = $topic = '';
		$language = UserSession::language();
		if(!empty($a)) {
			$title           = so($a->title   );
			$contents        = so($a->contents);
			$language        = so($a->language);
			$charactersNames = join(', ',$a->charactersNames());
			$characters      = $a->characters;
			$chOut           = '';
			if (is_array($characters)) 
				foreach($characters as $ch)
					$chOut .= Form::inputHtml("hidden", "anecdote[character_ids][]", $ch->id);
			$topic = ($t = $a->topic()) ? so($t->name) : '';
		} 
		echo '<label for="anecdote[title]">'.t("Title").'</label>'.
		Form::validation('anecdote_validation','title').'
		<input type="text" id="anecdote[title]" name="anecdote[title]" '.self::v($title).' /><br />
		'.Form::label("anecdote[contents]",t("Text")).
		Form::validation('anecdote_validation','contents').'
		<textarea name="anecdote[contents]" id="anecdote[contents]" rows="10" cols="20">'.$contents.'</textarea><br />';
		if (!Req::isAnecdoteEdit()) {
			echo Form::label("anecdote[characters]", t("Characters")).'
				<input type="text" name="anecdote[characters]" id="anecdote[characters]" '.self::v($charactersNames).' disabled />'.$chOut.
				Form::inputHtml("submit", "select_characters", t("Choose characters")).'<br />
				<label for="anecdote[topic_name]">'.t("Topic").'</label>'.
				Form::inputHtml("hidden", "anecdote[topic_id]", $a->topic_id).
				'<input type="text" name="anecdote[topic_name]" id="anecdote[topic_name]" '.self::v($topic).' disabled />'.
				Form::inputHtml("submit", "select_topic", t("Choose topic")).'<br />';
		}
	}

# -------- ITEM LIST -----------
	public static function chooseItemList($items, $action_url, $checked = 'checked', $tempId = NULL) { 

		$out = '<form method="post" action="'.$action_url.'">'.
			Form::inputHtml("hidden", "item_form_submit", "1" );

		foreach($items as $item) {
			$cb = FALSE;
			if(isset($item->$checked) && $item->$checked > 0) $cb = TRUE;
			if ($tempId !== NULL)
				$cb = !($item->id != $tempId);
			$out .= '<label for="item_id_'.$item->id.'">'.so($item->name).'</label>
				<input type="radio"'.( $cb ? ' checked ' : ' ').'name="item_id" id="item_id_'.$item->id.'" value="'.$item->id.'" /><br />';
		}

		$out .= Form::inputHtml("submit", "item_select_submit", t("Ok")).'<a href="?item&select&cancel">'.t('Cancel').'</a></form>';

		return $out;
	}

	public static function chooseItemsList($items, $action_url, $checked = 'checked', $tempIds = array(), $tempUnsIds= array()) { 
		$out = '<form method="post" action="'.$action_url.'">'.
			Form::inputHtml("hidden", "items_form_submit", 1);
		foreach($items as $item) {
			$cb = FALSE;
			if (isset($item->$checked) && $item->$checked > 0) $cb = TRUE;
			if (isset($item->id)) {
				if (is_array($tempUnsIds) && in_array($item->id, $tempUnsIds)) {
					$cb = FALSE;
				}
				if (is_array($tempIds) && in_array($item->id, $tempIds)) {
					$cb = TRUE;
				}
			}

			$out .= '<label for="item_id_'.$item->id.'">'.so($item->name).'</label>
				<input type="checkbox"'.($cb ? ' checked ' : ' ').'name="item_id[]" id="item_id_'.$item->id.'" value="'.$item->id.'" /><br /><input type="hidden" name="pre_items'.(isset($item->$checked) && $item->$checked ? 'c' : 'u').'[]" value="'.$item->id.'" />';
		}

		$out .= Form::inputHtml("submit", 'items_select_submit', t("Ok")).
			Form::inputHtml("submit", "items_select_continue", t("Ok & continue")).
			'<a href="?item&select&cancel">'.t("Cancel").'</a></form>';

		return $out;
	}

# --------- ITEMS LIST -------------

	public static function itemsList($items) {
		$items->loadNextPage();
		$out = array();
		while($item = $items->getNextObj()) {
			$cl = strtolower(get_class($item));
			$out[] = '<li><a href="?'.$cl.'='.$item->id.'&edit">'.so($item->to_s()).'</a></li>';
		}
		if(count($out) > 0) return '<ol>'.join('', $out).'</ol>';
	}

# ----------- USER LOGIN ------------------
	public static function newUser() {
		if (isset($_SESSION['login'])) {
			$login = $_SESSION['login'];
		} else {
			$login = '';
		}
		if (isset($_SESSION['email'])) {
			$email = $_SESSION['email'];
		} else {
			$email = '';
		}
	return '<form action="?newuser" method="post">
			<label for="user[login]">'.t("User").'</label>
			<input class="zmogelis" type="text" name="user[login]" id="user[login]" value="'.htmlspecialchars($login).'" /><br />
			<label for="user[password]">'.t("Password").'</label>
			<input class="password" type="password" name="user[password]" id="user[password]" /><br />
			<label for="user[password_confirm]">'.t("Confirm password").'</label>
			<input class="password" type="password" name="user[password_confirm]" id="user[password_confirm]" /><br />
			<label for="user[email]">'.t("E-mail").'</label>
			<input class="mail" type="text" name="user[email]" id="user[email]" value="'.htmlspecialchars($email).'" /><br />
			<input class="submit" type="submit" value="'.t("Create user").'" />
	</form>';
	}

	public static function loginForm() {
		return '
		<form action="?login" method="post">
		<label for="user[login]">'.t("User").'</label>
		<input class="zmogelis" type="text" name="user[login]" id="user[login]" /><br /> 
		<label for="user[password]">'.t("Password").'</label>
		<input class="password" type="password" name="user[password]" /><br />
		<input class="submit" type="submit" value="'.t("Login").'"> '.t("or").' <a href="index.php?registration">'.t("Register").'</a>
		</form>';
	}

	public static function Login() {
		if (!Login::isLoggedIn()) {
			if(isset($_REQUEST['registration'])) {
				echo self::newUser();
			} else {
				echo self::loginForm();
			}
		}
	}

	# --------------------- LOGGER ---------------
	public static function messages() {
		$val = '';
		while ($error = Logger::nextErr()) {
			$val .= '<li class="error">'.htmlspecialchars($error->msg).'</li>';
		}
		while ($info = Logger::nextInfo()) {
			$val .= '<li class="info">'.htmlspecialchars($info->msg).'</li>';
		}
		if (!empty($val)) echo '<div id="messages"><ul>'.$val.'</ul></div>';
	}

}

