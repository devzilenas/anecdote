<?

include 'config.inc.php';
include 'db.inc.php';
include 'lib.inc.php';

# --------- USER ----------
include 'lib/auth/user.class.php';
include 'lib/auth/crypt.class.php';

class Install {

	public static function createTables() {
		mysql_query("
				CREATE TABLE IF NOT EXISTS anecdotes (
				id       INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
				topic_id INTEGER,
				title    VARCHAR(255),
				contents TEXT,
				language VARCHAR(30))")
			or die(t("Table 'anecdotes' not created!") . mysql_error());
		mysql_query("
				CREATE TABLE IF NOT EXISTS characters (
				id       INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name     VARCHAR(255),
				language VARCHAR(30))")
			or die(t("Table 'characters' not created!") . mysql_error());
		mysql_query("
				CREATE TABLE IF NOT EXISTS topics (
				id       INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name     VARCHAR(255),
				language VARCHAR(30))")
			or die(t("Table 'topics' not created!") . mysql_error());
		mysql_query("
				CREATE TABLE IF NOT EXISTS anecdote_characters (
					anecdote_id  INTEGER NOT NULL,
					character_id INTEGER NOT NULL)")
			or die(t("Table 'anecdote_characters' not created!") . mysql_error());
		
		mysql_query("
				CREATE TABLE IF NOT EXISTS user_preferred_characters (
					user_id INTEGER NOT NULL,
					character_id INTEGER NOT NULL)")
			or die(t("Table 'user_preferred_characters' not created!") . mysql_error());

		mysql_query("
				CREATE TABLE IF NOT EXISTS user_preferred_topics (
					user_id INTEGER NOT NULL,
					topic_id INTEGER NOT NULL)")
			or die(t("Table 'user_preferred_topics' not created!") . mysql_error());

#----- USER ------------

		mysql_query("
				CREATE TABLE IF NOT EXISTS users (
					id      INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
					login   VARCHAR(255),
					phash   VARCHAR(32),
					sid     VARCHAR(32),
					bgcolor VARCHAR(255),
					email   VARCHAR(255),
					aid     VARCHAR(32),
					active  TINYINT(1))")
			or die(t("Table 'users' not created!") . mysql_error());

		return TRUE;
	}

    public static function demoUserOk() {
		$filter = User::newFilter();
		$filter->setWhere(array('User.login' => 'demo')); 
		$filter->setLimit(1);
		return count(User::find($filter)) > 0;
	}

	public static function createDemoUser() {
		$user = User::fromForm( array(
					'login' => 'demo',
					'email' => 'demo@example.com',
					'phash' => Crypt::genPhash('demo'),
					'aid'   => Crypt::genAid()));
		if ($user_id = $user->insert())
			return User::activate($user_id, $user->aid);
	}


}

?>
<h1><?= t("Installation"); ?></h1>
	<p>
	<? if (DB::connect()) { ?>
		<b><?= t("WORKS"); ?></b>
	<? } else { ?>
		<b><?= t("DOESN'T WORK"); ?></b>
	<? } ?> <?= t("Connection with database"); ?> <b><?= Config::$DB_NAME ?></b>
	</p>

	<p>
	<? if (Install::createTables()) { ?>
		<b><?= t("OK"); ?></b> <?= t("Created database tables"); ?>
	<? } ?>
	</p>

	<?# ----------- USER ------------- ?>
	<p>
	<? if (!Install::demoUserOk() && Install::createDemoUser()) { ?>
		<?= t("OK"); ?></b> <?= t("Created user - login: demo, password: demo!"); ?>
	<? } else { ?>
		<b><?= t("User not created!"); ?></b>
	<? } ?>
	</p>

<a href="./"> <?= t("Start using"); ?> </a>
