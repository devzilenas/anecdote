<?

class Req {

	public static $anecdote  ;
	public static $lang      ;

	public static function pis($what) {
		return isset($_POST[$what]) ? $_POST[$what] : NULL ;
	}

	public static function getObjset($provider) {
		$filter = $provider::newFilter();
		$filter->setWhere(array("$provider.language" => UserSession::language()));
		$tList = new ObjSet($provider, $filter, self::gp0('page'));
		return $tList;
	}

	public static function isEdit($provider) {
		$str_provider = strtolower($provider);
		if (isset($_REQUEST[$str_provider]) && isset($_REQUEST['edit']) && $provider::exists($_REQUEST[$str_provider]))
			return $provider::load(self::get0($str_provider));
	}

	public static function isImgSubmit($name) {
		return isset($_REQUEST["{$name}_x"]) && isset($_REQUEST["{$name}_y"]);
	}

	public static function isAnecdoteNewFormSubmit() {
		return isset($_POST['action']) && 'add' === $_POST['action'] && isset($_REQUEST['anecdotes']) && isset($_POST['anecdote']);
	}

	public static function isAnecdotesList() {
		return isset($_REQUEST['anecdotes']);
	}

	public static function isAnecdoteNew() {
		return isset($_REQUEST['anecdote']) && isset($_REQUEST['new']);
	}

	public static function isAnecdoteRead() {
		return isset($_REQUEST['anecdote']) && isset($_REQUEST['read']);
	}

	public static function isAnecdoteEdit() {
		return isset($_REQUEST['anecdote']) && isset($_REQUEST['edit']);
	}

	public static function isAnecdoteDelete() {
		return isset($_REQUEST['anecdote']) && self::isDelete();
	}

	public static function isTopicDelete() {
		return isset($_REQUEST['topic']) && self::isDelete();
	}

	public static function isCharacterDelete() {
		return isset($_REQUEST['character']) && self::isDelete();
	}

	public static function isAnecdoteEditCharacters() {
		return isset($_REQUEST['anecdote']) && isset($_REQUEST['action']) && 'set_characters' == $_REQUEST['action'];
	}

	public static function isAnecdoteEditTopic() {
		return isset($_REQUEST['anecdote']) && isset($_REQUEST['action']) && 'set_topic' === $_REQUEST['action'];
	}

	public static function isTopicNew() {
		return isset($_GET['topic']) && isset($_GET['new']);
	}

	public static function isCharacterNew() {
		return isset($_GET['character']) && isset($_GET['new']);
	}

	public static function isTopicNewFormSubmit() {
		return isset($_GET['topics']) && isset($_POST['topic']) && isset($_POST['save']);
	}

	public static function isCharacterNewFormSubmit() {
		return isset($_GET['characters']) && isset($_POST['character']) && isset($_POST['save']);
	}

	private static function isUpdate($provider) {
		return isset($_REQUEST[strtolower($provider)]) && isset($_POST['action']) && 'update' === $_POST['action'];
	}

	private static function isDelete() {
		return isset($_POST['action']) && 'delete' === $_POST['action'];
	}

	private static function processPreferred() {
		self::processPreferredRemove('Topic');
		self::processPreferredRemove('Character');
	}

	private static function processPreferredRemove($cl) {
		$cll = strtolower($cl);
		$key = "remove_preferred_{$cll}_id";

		if (isset($_REQUEST['preferred']) && isset($_REQUEST[$key]) && is_array($_REQUEST[$key]) && count($_REQUEST[$key]) > 0 ) { 
			$upcl = "UserPreferred".pluralize($cl);
			$upcl::delWhere(array(
						'user_id'   => Login::loggedId(),
						"{$cll}_id" => (int)key($_REQUEST[$key]))); 
			$page = Req::get0('page');
			Request::hlexit("?anecdotes&page=$page");
		}
	}

	private static function detectLang() {
		if (!empty($_REQUEST['language']) && Language::valid($_REQUEST['language'])) {
			UserSession::setLanguage($_REQUEST['language']);
			Request::hlexit("?anecdotes");
		}
		if (!Language::valid(UserSession::language()))
			UserSession::setLanguage(Language::d());
	}

	public static function get0($name) {
		return (!empty($_REQUEST[$name])) ? (int)$_REQUEST[$name] : 0;
	}

	public static function gp0($name) {
		$out = 0;
		if (isset($_REQUEST[$name])) {
			$out = (int)$_REQUEST[$name];
		} else if (isset($_POST[$name])) {
			$out = (int)$_POST[$name];
		}
		return $out;
	}

	private static function saveAnecdoteToSession() {
		$_SESSION['anecdote'] = array(
			"title"      => $_POST['anecdote']['title'],
			"contents"   => $_POST['anecdote']['contents'],
			'language'   => $_POST['anecdote']['language'],
			'topic_id'   => $_POST['anecdote']['topic_id']);
		if (isset($_POST['anecdote']['character_ids'])) $_SESSION['anecdote']['character_ids'] = $_POST['anecdote']['character_ids'];
	}

# -----------------------------------------
# -------------- PREFFERED ----------------
# -----------------------------------------
	public static function getCharacters() {
		return UserPreferredCharacters::characters(Login::loggedId());
	}

	public static function getTopics() {
		return UserPreferredTopics::topics(Login::loggedId());
	}


# -----------------------------------------
# -------------- ENTRY POINT --------------
# -----------------------------------------
	public static function process() {
		self::detectLang();

		// Include item list processing
		ReqList::processList();

# -------------- READ ANECDOTE ------------
		if((self::isAnecdoteRead() || self::isAnecdoteEdit() || self::isAnecdoteEditTopic() || self::isAnecdoteEditCharacters()) && Anecdote::exists($_REQUEST['anecdote'])) { 
			self::$anecdote = Anecdote::load(self::get0('anecdote'));
		}

# ----------------------------------------
# -------- NEW ANECDOTE ------------------
# ----------------------------------------
# -------- ADD ANECDOTE ------------------
		if (self::isAnecdoteNewFormSubmit()) {
			if (isset($_POST['save'])) { 
				if($validation = Anecdote::fromForm($_POST['anecdote'], array('contents', 'title'))->hasValidationErrors()) {
					$_SESSION['anecdote_validation'] = $validation;
					self::saveAnecdoteToSession();
					Logger::undefErr(array_values($validation));
				} else {
					//VALID
					$an = Anecdote::fromForm($_POST['anecdote']);
					$an->insert();

					if(isset($_SESSION['anecdote'])) unset($_SESSION['anecdote']);
					Logger::info(t("Anekdotas išsaugotas!"));
					Request::hlexit("?anecdote=$an->id&edit");
				}
# -------- SELECT TOPIC --------------------------
			} else if (isset($_POST['select_topic'])) {
				// Save anecdote data to session
				if(isset($_POST['anecdote']))
					self::saveAnecdoteToSession();

				if (isset($_POST['anecdote']['topic_id']) && Topic::exists($_POST['anecdote']['topic_id'])) {
					$_SESSION['temporary_selected_ids'] = array($_POST['anecdote']['topic_id']);
				}

				$_SESSION['cancel_url']            =
					$_SESSION['return_url']        = '?anecdote&new';

				// SET FILTER FOR THE LIST
				$filter = Topic::newFilter();
				$filter->setWhere(array("Topic.language" => UserSession::language()));
				$_SESSION['list_filter']   = $filter;
				$_SESSION['list_items']    = 'Topic';
				$_SESSION['list_selected'] = NULL;

				ReqList::redirect_select_item();

# ---------- SELECT CHARACTERS --------------------
			} else if (isset($_POST['select_characters'])) {
				if(isset($_POST['anecdote']))
					self::saveAnecdoteToSession();

				$_SESSION['cancel_url']            =
					$_SESSION['return_url']        = '?anecdote&new';

				// if there where selected ids put them to temporarly selected ids
				if(isset($_POST['anecdote']['character_ids']) && is_array($_POST['anecdote']['character_ids'])) $_SESSION['temporary_selected_ids'] = $_POST['anecdote']['character_ids'];

				// SET FILTER FOR THE LIST
				$filter = Character::newFilter();
				$filter->setWhere(array("Character.language" => UserSession::language()));
				$_SESSION['list_filter']   = $filter;
				$_SESSION['list_items']    = 'Character';
				$_SESSION['list_selected'] = NULL;

				ReqList::redirect_select_items();
			}
		}

# ----------------------------------------
# ------- EXISTING ANECDOTE --------------
# ----------------------------------------
# ------- UPDATE ANECDOTE ----------------
		if (self::isUpdate('Anecdote') && Anecdote::exists($_POST['anecdote']['id'])) {
			if($validation = Anecdote::fromForm($_POST['anecdote'], array('contents', 'title'))->hasValidationErrors()) {
				$_SESSION['anecdote_validation'] = $validation;
				Logger::undefErr(array_values($validation));
			} else { //VALID
				if (Anecdote::update(
					$_POST['anecdote']['id'],
					array(
						'title',
						'contents'),
					$_POST['anecdote'])) {
					Logger::info(t("Anecdote").' '.t("updated")."!");
				}
			}
			Request::hlexit("?anecdote={$_POST['anecdote']['id']}&edit");
		}
# ------------- SET TOPIC ----------------
		if (self::isAnecdoteEditTopic()) {
			$selectedIds = Session::gSessionArray('selected_ids');

			if (count($selectedIds) > 0) {
				if(Topic::exists(current($selectedIds))) $topic_id = current($selectedIds);
			} else $topic_id = NULL;

			if(Anecdote::update(
						self::$anecdote->id,
						array('topic_id'),
						array('topic_id' => $topic_id))) {
				Logger::info(t("Anecdote")." ".t("topic")." ".t("changed")."!");

				unset($_SESSION['selected_ids']);
				unset($_SESSION['cancel_url']);
				unset($_SESSION['list_items']);
				unset($_SESSION['list_filter']);
				unset($_SESSION['list_selected']);
				Request::hlexit("?anecdote=".self::$anecdote->id."&edit");
			}
		}
# -------------- SET CHARACTERS -----------
		if (self::isAnecdoteEditCharacters()) {
			$selected_ids   = Session::gSessionArray('selected_ids'); 
			$unselected_ids = Session::gSessionArray('unselected_ids');

			// add selected ids
			foreach($selected_ids as $chId) {
				if(Character::exists($chId)) {
					$ch = Character::load($chId, array("id", "name"));
					if(!AnecdoteCharacters::existsBy( array(
									'AnecdoteCharacters.anecdote_id'  => self::$anecdote->id,
									'AnecdoteCharacters.character_id' => $ch->id))) {
						AnecdoteCharacters::fromForm(array(
									'anecdote_id'  => self::$anecdote->id,
									'character_id' => $ch->id))->insert();
						Logger::info(t("Character").' '.so($ch->name).' '.t("added")."!");
					}
				}
			}

			// remove unselected ids 
			foreach($unselected_ids as $chId) {
				if(Character::exists($chId)) {
					$ch = Character::load($chId, array("id", "name"));
					AnecdoteCharacters::delWhere(array(
								'anecdote_id'  => self::$anecdote->id,
								'character_id' => $ch->id)); 
					Logger::info(t("Character").' '.so($ch->name).' '.t("removed")."!");
				}
			}

			unset($_SESSION['selected_ids']);
			unset($_SESSION['unselected_ids']);

			Request::hlexit("?anecdote=".self::$anecdote->id."&edit");
		}
# -------------- SELECT TOPIC -------------
		if (isset($_GET['select_topic'])) {
			if (isset($_GET['anecdote'])) {
				$a = Anecdote::load($_GET['anecdote']);

				if(isset($_SESSION['selected_ids'])) unset($_SESSION['selected_ids']);

				$_SESSION['cancel_url'] = "?anecdote=$a->id&edit";
				$_SESSION['return_url'] = "?anecdote=$a->id&action=set_topic";

				// SET FILTER FOR THE LIST
				$list_selected = 'anecdote_topic';

				$_SESSION['list_items']    = 'Topic';
				$_SESSION['list_filter']   = $a->anecdoteTopicsList($list_selected);
				$_SESSION['list_selected'] = $list_selected;

			}
			ReqList::redirect_select_item();
		}
# -------------- SELECT CHARACTERS --------
		if(isset($_GET['select_characters'])) {
			if (isset($_GET['anecdote'])) {
				$a = Anecdote::load($_GET['anecdote']);

				if(isset($_SESSION['selected_ids'])) unset($_SESSION['selected_ids']);

				$_SESSION['cancel_url'] = "?anecdote=$a->id&edit";
				$_SESSION['return_url'] = "?anecdote=$a->id&action=set_characters";

				// SET FILTER FOR THE LIST
				$list_selected = 'anecdote_character';
				$_SESSION['list_items']    = 'Character';
				$_SESSION['list_filter']   = $a->anecdoteCharactersList($list_selected);
				$_SESSION['list_selected'] = $list_selected;
			}
			ReqList::redirect_select_items();
		}
# -------------- DELETE ANECDOTE ----------
		if(self::isAnecdoteDelete() && Anecdote::exists($_REQUEST['id'])) {
			Anecdote::del($_REQUEST['id']);
			Request::hlexit('?anecdotes');
		}

# -----------------------------------------
# -------------- TOPICS -------------------
# -----------------------------------------
# -------------- ADD ----------------------
		if(self::isTopicNewFormSubmit()) {
			$t = Topic::fromForm($_POST['topic'], array('name', 'language'));
			if($validation = $t->hasValidationErrors()) {
				$_SESSION['topic_validation'] = $validation;
				Logger::undefErr(array_values($validation));
				Request::hlexit("?topic&new");
			} else {
				//VALID
				if($t->insert()) {
					Logger::info(t("Topic")." ".t("created"));
					Request::hlexit("?topic=$t->id&edit");
				}
			}
		}
# -------------- UPDATE TOPIC -------------
		if (self::isUpdate('Topic') && Topic::exists($_POST['topic']['id'])) {
			if ($validation = Topic::fromForm($_POST['topic'], array('name'))->hasValidationErrors()) {
				$_SESSION['topic_validation'] = $validation;
				Logger::undefErr(array_values($validation));
				Request::hlexit("?topic={$_POST['topic']['id']}&edit");
			} else {
				// VALID 
				if(Topic::update(
							$_POST['topic']['id'],
							array('name'),
							$_POST['topic'])) {
					Logger::info(t("Topic")." ".t("updated")."!");
				}
				Request::hlexit("?topics");
			}
		}
# ------------- DELETE TOPIC --------------
		if(self::isTopicDelete() && Topic::exists($_REQUEST['id'])) {
			$t = Topic::load($_REQUEST['id']);
			$t->delc();
			Logger::info(t("Topic").' '.so($t->name).' '.t('deleted'));
			Request::hlexit("?topics");
		}

# ------------- PREFERRED TOPICS ----------
		if (isset($_REQUEST['topics'])) {
			if (isset($_GET['preferred_select'])) { 
				$_SESSION['cancel_url'] = '?topics';
				$_SESSION['return_url'] = '?topics&set_preferred';
				
				//set filter for the list
				$list_selected = 'user_topic';

				$_SESSION['list_items']  = 'UserPreferredTopics';
				$_SESSION['list_filter'] = UserPreferredTopics::preferred(UserSession::language(), Login::loggedId());

				$_SESSION['list_selected'] = $list_selected;
				ReqList::redirect_select_items();
			}
			// process selected preferred
			if(isset($_GET['set_preferred'])) {
				$selected_ids   = Session::gSessionArray('selected_ids'); 
				$unselected_ids = Session::gSessionArray('unselected_ids');
				
				//add checked
				foreach($selected_ids as $tid) {
					if(!UserPreferredTopics::existsBy(array(
						'UserPreferredTopics.character_id' => $tid,
						'UserPreferredTopics.user_id'      => Login::loggedId()))) {
						UserPreferredTopics::fromForm(array("topic_id" => $tid, "user_id" => Login::loggedId()))->insert();
					}
					$t = Topic::load($tid);
					Logger::info(t("Topic")." $t->name ".t("added to preferred!"));
				}

				//remove unchecked
				foreach($unselected_ids as $tid) {
					UserPreferredTopics::delWhere(array(
						"user_id"      => Login::loggedId(),
						"topic_id" => $tid)); 
					$t = Topic::load($tid);
					Logger::info(t("Topic")." $t->name ".t("removed from preferred!"));
				}
				Request::hlexit("?topics"); 
			}
		}

# ------------- PREFERRED CHARACTERS ------
		if (isset($_REQUEST['characters'])) {
			// SelecT
			if (isset($_REQUEST['preferred_select'])) {
				$_SESSION['cancel_url'] = '?characters';
				$_SESSION['return_url'] = '?characters&set_preferred';
				
				//set filter for the lisT
				$list_selected = 'user_character';

				$_SESSION['list_items']  = 'UserPreferredCharacters';
				$_SESSION['list_filter'] = UserPreferredCharacters::preferred(UserSession::language(), Login::loggedId());

				$_SESSION['list_selected'] = $list_selected;
				ReqList::redirect_select_items();
			}

			// process selected
			if (isset($_GET['set_preferred'])) {
				$selected_ids   = Session::gSessionArray('selected_ids'); 
				$unselected_ids = Session::gSessionArray('unselected_ids');
				//add checked
				foreach($selected_ids as $chid) {
					if(!UserPreferredCharacters::existsBy(array(
						'UserPreferredCharacters.character_id' => $chid,
						'UserPreferredCharacters.user_id'      => Login::loggedId()))) {
						UserPreferredCharacters::fromForm(array("character_id" => $chid, "user_id" => Login::loggedId()))->insert();
					}
					$ch = Character::load($chid);
					Logger::info(t("Character")." $ch->name ".t("added to preferred!"));
				}

				//remove unchecked
				foreach($unselected_ids as $chid) {
					UserPreferredCharacters::delWhere(array(
						"user_id"      => Login::loggedId(),
						"character_id" => $chid)); 
					$ch = Character::load($chid);
					Logger::info(t("Character")." $ch->name ".t("removed from preferred!"));
				}

				Request::hlexit("?characters"); 
			}
		}
# -------------- REMOVE FROM PREFERRED ----
		if (isset($_GET['preferred'])) self::processPreferred();

# -----------------------------------------
# -------------- CHARACTER ----------------
# -----------------------------------------

# -------------- ADD ----------------------
		if(self::isCharacterNewFormSubmit()) {
			$c = Character::fromForm($_POST['character'], array('name', 'language'));
			if($validation = $c->hasValidationErrors()) {
				$_SESSION['character_validation'] = $validation;
				Logger::undefErr(array_values($validation));
				Request::hlexit("?character&new");
			} else {
				//VALID
				if($c->insert()) {
					Logger::info(t("Character")." ".so($c->name).' '.t("created"));
					Request::hlexit("?character=$c->id&edit");
				}
			}
		}
# -------------- UPDATE ------------------
		if (self::isUpdate('Character') && Character::exists($_POST['character']['id'])) {
			if ($validation = Character::fromForm($_POST['character'], array('name'))->hasValidationErrors()) {
				$_SESSION['character_validation'] = $validation;
				Logger::undefErr(array_values($validation));
				Request::hlexit("?character={$_POST['character']['id']}&edit"); 
			} else {
				// valid
				if(Character::update(
							$_POST['character']['id'],
							array('name'),
							$_POST['character'])) {
					Logger::info(t("Character")." ".t("updated")."!");
				}
				Request::hlexit("?characters");
			}
		}
# -------------- DELETE CHARACTER ---------
		if(self::isCharacterDelete() && Character::exists($_REQUEST['id'])) {
			$c = Character::load($_REQUEST['id']);
			$c->delc();
			Logger::info(t("Character").' '.so($c->name).' '.t('deleted'));
			Request::hlexit("?characters");
		}

# -----------------------------------------
# -------------- USER ---------------------
# -----------------------------------------

# -------------- NEW ----------------------
		if (isset($_REQUEST['newuser']) && isset($_POST['user'])) {
			$user = $_POST['user'];
			if ($user['password'] == $user['password_confirm']) {
				$_SESSION['login'] = $user['login'];
				$_SESSION['email'] = $user['email'];
				if (Login::createUser($user['login'], $user['password'], $user['email'])) {
					Request::hlexit("./");
				} else {
					Logger::err("UNDEF", t("User not created. Error!"));
					Request::hlexit("?registration");
				}
			} else {
				Logger::err('PASS_MATCH', t("Passwords don't match!"));
				Request::hlexit("?registration");
			}
		}

# -------------- ACTIVATE -----------------
		if (isset($_REQUEST['activate']) && isset($_REQUEST['id']) && isset($_REQUEST['aid'])) {
			User::activate($_REQUEST['id'], urldecode($_REQUEST['aid']));
			Request::hlexit("./");
		} 
	}

}

