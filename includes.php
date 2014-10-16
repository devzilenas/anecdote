<? 
set_include_path(get_include_path() . PATH_SEPARATOR . 'class');
set_include_path(get_include_path() . PATH_SEPARATOR . 'lib');

# -------------- CONFIG ------------------------
include 'config.inc.php';
include 'db.inc.php';

# -----------------------------------------
# -------------- LIB ----------------------
# -----------------------------------------
# -------------- DBOBJS -------------------
include_once 'dbobjs/dbobj.class.php';
include_once 'dbobjs/objset.class.php';
include_once 'dbobjs/objset_html.class.php';
include_once 'dbobjs/filter.class.php';
include_once 'dbobjs/sql_filter.class.php';
# -------------- ITEMS LIST ---------------
include_once 'dbobjs/html/items_list.html.php';
include_once 'dbobjs/req/list.req.php';
# -------------- LANGUAGE ----------------- 
include_once 'lang/language.class.php';
include_once 'lang/dict/lt.inc.php';
include_once 'lang/dict/ru.inc.php';
include_once 'lang/dict/de.inc.php'; 
# -------------- LOGGER ------------------- 
include_once 'sys/error.inc.php'; 
# -------------- HTML ---------------------
include_once 'html/form.class.php';
# -------------- SESSION ------------------
include_once 'sys/session.class.php';
include_once 'sys/session.inc.php'; 
# -------------- REQUEST ------------------
include_once 'sys/request.class.php';
# -------------- USER ---------------------
include_once 'auth/crypt.class.php'; 
include_once 'auth/user.class.php'; 
include_once 'auth/login.class.php';
include_once 'auth/sys/user_session.class.php';

include_once 'lib.inc.php';
# -----------------------------------------

# -----------------------------------------
# -------------- SETUP --------------------
# -----------------------------------------
$dbobjs = array("anecdote", "character", "topic", "user_preferred_characters", "user_preferred_topics", "anecdote_characters");
foreach($dbobjs as $name) include 'dbobjs/'.$name.'.class.php';
# -------------- REQUEST ------------------
include 'req.class.php';
# -------------- HTML ---------------------
include 'html_block.class.php';
