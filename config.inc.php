<?
class Config { 

# ------------------------------------------------------
# ---------- INSTALLATION RELATED ----------------------
# ------------------------------------------------------

# ---------- WEB SITE HOST -----------------------------
	public static $BASE         = 'http://localhost/anecdote';

# ---------- DATABASE CONFIGURATION --------------------
	public static $DB_NAME      = 'anecdotes';
	public static $DB_HOST      = 'localhost';
	public static $DB_USER      = 'root';
	public static $DB_PASSWORD  = '';

# ------------------------------------------------------
# ---------- FOR DEVELOPERS  ---------------------------
# ------------------------------------------------------
	public static $SESSION_SHOW = FALSE;
# ---------- TEST DATABASE -----------------------------
	public static $DB_TEST      = 'anecdote_test';
}

