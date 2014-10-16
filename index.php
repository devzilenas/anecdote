<?

include 'includes.php';

DB::connect(); 
Login::CheckLogin();
Req::process();
$language  = UserSession::language();
$anecdote  = Req::$anecdote;

?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title><?= t('Funny anecdotes'); ?></title> 
	<link rel="stylesheet" href="css/style.css" type="text/css">
</head>
<body>

<? #----------- LOGGER ------------------- ?>
<?= HtmlBlock::messages() ?>

<? #----------- DEBUG ------------------- ?>
<? print_session_debug() ?>

<?
if (!Login::isLoggedIn()) {
	HtmlBlock::login();
} else {
?>

<p class="meniu"><?= t("Menu").':'; ?> <a href="?anecdotes"><?= t('Anecdotes'); ?></a> <a href="?topics"><?= t('Topics'); ?></a> <a href="?characters"><?= t('Characters'); ?></a> </p>

<ul>
	<li class="inl"><a href="?anecdotes"><?= t("Read anecdotes!"); ?><img src="media/img/knyga.png" alt="akiniai" title="<?= t("Read anecdotes!"); ?>" /></a></li>
	<li class="inl"><a href="?anecdote&new"><?= t('Write anecdote!'); ?><img src="media/img/edit.png" <?= HtmlBlock::altTitle(t("New anecdote")); ?>/></a></li>
</ul>

<?
	$upcs  = UserPreferredCharacters::characters($language, Login::loggedId());
	$upts  = UserPreferredTopics::topics($language, Login::loggedId());
	$upcts = array_merge($upcs, $upts);
	$preff_cnt = Anecdote::anecdotesCntPreferred($language, Login::loggedId());
	$total_cnt = Anecdote::anecdotesCnt($language, Login::loggedId());
?>

<p class="small">Total anecdotes: <span class="large"><?= $total_cnt ?></span><span>(preferred: <?= $preff_cnt ?>)</span></p>
<?= HtmlBlock::simpleList($upcts, Req::get0('page')) ?>

<? # --------------------------------------
   # ----------- READ ANECDOTE ------------
   # --------------------------------------

if (Req::isAnecdoteRead() && !empty($anecdote)) { ?> 
<h2><?= so($anecdote->title); ?></h2>
<pre><?= showanecdote($anecdote); ?></pre>

<form method="post" action="?anecdote">
	<?= Form::hiddenInput("action", "delete");  ?>
	<?= Form::hiddenInput("id", $anecdote->id); ?>
	<?= Form::submit(t("Delete"));              ?>
	<a href="?anecdote=<?= $anecdote->id; ?>&edit"><?= t("Edit"); ?></a>
</form> 
<? } ?>

<?
# -----------------------------------------
# -------------- LISTS --------------------
# -----------------------------------------

# -------------- ITEMS LIST ---------------
ItemsListHtml::detectList();

# -------------- TOPICS -------------------
if(isset($_REQUEST['topics'])) {
	echo '<h2>'.t("Topics").'</h2><a href="?topic&new">'.t("new topic").'</a> <a href="?topics&preferred_select">'.t("Preferred topics").'</a>';
	HtmlBlock::topicsList($language);
}

# -------------- CHARACTERS ---------------
if(isset($_REQUEST['characters'])) {
	echo '<h2>'.t("Characters").'</h2><a href="?character&new">'.t("new character").'</a> <a href="?characters&preferred_select">'.t("Preferred characters").'</a>';
	HtmlBlock::charactersList($language);
}

# -------------- ANECDOTES ----------------
if (isset($_REQUEST['anecdotes'])) {
	echo '<h2>'.t("Anecdotes").'</h2>';
	HtmlBlock::anecdotesList($language, Login::loggedId());
}
?>

<?
# -----------------------------------------
# ------------- ANECDOTE ------------------
# -----------------------------------------
# ------------- NEW ----------------------- 
if(Req::isAnecdoteNew()) {
	$a = new Anecdote();

	//restore from session
	if (isset($_SESSION['anecdote'])) {
		$a = Anecdote::fromForm($_SESSION['anecdote'], array('contents', 'title', 'language', 'topic_id'), $a);
		if(isset($_SESSION['anecdote']['character_ids']))
			$a->setCharacters($_SESSION['anecdote']['character_ids']);
		unset($_SESSION['anecdote']);
	}

# -------------- CHOOSE TOPIC -------------
	if (isset($_SESSION['list_items'])) {
		$selectedIds = Session::gSessionArray('selected_ids');
		unset($_SESSION['selected_ids']);
		if ('Topic' === $_SESSION['list_items']) { 
			if(count($selectedIds) > 0) {
				$sid = current($selectedIds);
				if(Topic::exists($sid)) $a->topic_id = $sid;
			}
		} else if ('Character' === $_SESSION['list_items']) {
# ------------- CHOOSE CHARACTERS ---------
			if(count($selectedIds) > 0) {
				$chrs = array();
				foreach($selectedIds as $selid) {
					if (Character::exists($selid))
						$chrs[] = Character::load($selid, array("id", "name"));
				}
				$a->characters = $chrs;
			}
		}
		unset($_SESSION['list_items']);
	}

?>

<form method="post" action="?anecdotes&new">
	<?= Form::inputHtml("hidden", "action", "add" ); ?>
	<?= Form::inputHtml("hidden", "anecdote[language]", $language); ?>
	<?= HtmlBlock::anecdoteForm($a); ?>
	<?= Form::inputHtml("submit", "save", t("Save")); ?>
	<a href="?anecdotes"><?= t("Cancel"); ?></a>
</form>
<? } ?>
<?
# ------------- EDIT ----------------------
if(Req::isAnecdoteEdit()) { ?>
<form method="post" action="?anecdote"> 
	<?= Form::actionUpdate(); ?>
	<?= Form::hiddenInput("anecdote[id]", $anecdote->id); ?>
	<?= HtmlBlock::anecdoteForm($anecdote); ?>
	<?= Form::submit(t('Save')); ?>
	<a href="?anecdote=<?= $anecdote->id; ?>&read"><?= t('Cancel'); ?></a> 
</form>
<ol>
	<li><a href="?anecdote=<?= $anecdote->id ?>&select_topic"><?= t("Change topic"); ?></a></li>
	<li><a href="?anecdote=<?= $anecdote->id ?>&select_characters"><?= t("Change characters"); ?></a></li>
</ol>
<? } ?>

<?
# -----------------------------------------
# -------------- TOPIC --------------------
# -----------------------------------------
# -------------- NEW ----------------------
if(Req::isTopicNew()) {
	$t = new Topic();
?>

<form method="post" action="?topics&new">
	<?= Form::inputHtml("hidden", "topic[language]", $language); ?>
	<?= HtmlBlock::topicForm($t); ?>
	<?= Form::inputHtml("submit", "save", t("Save")); ?>
	<a href="?topics"><?= t("Cancel"); ?></a>
</form>

<? }
# ------------- EDIT ----------------------
if($topic = Req::isEdit('Topic')) { ?>
	<form method="post" action="?topic">
		<?= Form::actionUpdate(); ?>
		<?= Form::hiddenInput("topic[id]", $topic->id); ?>
		<?= HtmlBlock::topicForm($topic); ?>
		<?= Form::submit(t('Save')); ?>
		<a href="?topics"><?= t("Cancel"); ?></a>
	</form> 
	<form method="post" action="?topic">
		<?= Form::hiddenInput("action", "delete");  ?>
		<?= Form::hiddenInput("id", $topic->id); ?>
		<?= Form::submit(t("Delete"));              ?>
	</form> 
<? } ?>

<?
# -----------------------------------------
# -------------- CHARACTER ----------------
# -----------------------------------------
# -------------- NEW ----------------------
if(Req::isCharacterNew()) {
	$c = new Character();
?>
<form method="post" action="?characters&new">
	<?= Form::inputHtml("hidden", "character[language]", $language); ?>
	<?= HtmlBlock::characterForm($c); ?>
	<?= Form::inputHtml("submit", "save", t("Save")); ?>
	<a href="?characters"><?= t("Cancel"); ?></a>
</form>
<? } 
# ------------- EDIT CHARACTER ------------
if($character = Req::isEdit('Character')) { ?>
	<form method="post" action="?character">
		<?= Form::actionUpdate(); ?>
		<?= Form::hiddenInput("character[id]", $character->id); ?>
		<?= HtmlBlock::characterForm($character); ?>
		<?= Form::submit(t('Save')); ?>
		<a href="?characters"><?= t("Cancel"); ?></a>
	</form>
	<form method="post" action="?character">
		<?= Form::hiddenInput("action", "delete");  ?>
		<?= Form::hiddenInput("id", $character->id); ?>
		<?= Form::submit(t("Delete"));              ?>
	</form> 
<? } ?>

<p><?= t('Website version'); ?>: <?= HtmlBlock::language(); ?></p>
<p>2012-2013 <a href="mailto:mzilenas@gmail.com">Marius Žilėnas</a></p>
<? } ?>
</body>
</html>
