<?php

	require_once('settings.php');
	require_once('inc/mysql_access.php');


/* 1) for each table
   2) does this table exist? if yes, next table
   3) perform create query
   4) perform custom queries
*/

class TableCheck
{
    var $tableName;
    var $createSQL;
    
    function TableCheck($name, $createcontent)
    {
        $this->tableName = DB_TABLES_PREFIX.$name;
        $this->createSQL = $createcontent;
    }
    
    function Check()
    {
        $sql = "CREATE TABLE IF NOT EXISTS ".$this->tableName." ( ".$this->createSQL." ) ";
        if (my_mysql_query($sql))
        return True;
        else return False;
    }
}

class NonEmptyTableCheck extends TableCheck
{
    var $insertSQL;
    
    function ExtendedTableCheck($name, $createcontent, $extendedqueries)
    {
        $this->TableCheck($name, $createcontent);
        $this->insertSQL = $extendedqueries;       
    }
    
    function InsertContent()
    {
        $sql = "SELECT Count(*) FROM ".$this->tableName;
        $r = my_mysql_query($sql);
        $n = (int) mysql_result($r, 0);
        if (!$n)
        {
            foreach($this->insertSQL as $i)
            {
                $sql = "INSERT INTO ".$this->tableName." ".$i;
                if (!my_mysql_query($sql))
                return False;
            }
        } 
        return True;
    }
    
    function Check()
    {
        TableCheck::Check();
        $this->InsertContent();
    }
}

$tables = Array();
$tables[] = new TableCheck("commands", "
  id SMALLINT UNSIGNED AUTO_INCREMENT,
  name CHAR(32) NOT NULL DEFAULT '' UNIQUE,
  active TINYINT UNSIGNED NOT NULL DEFAULT 1,
  description TEXT,
  syntax TEXT,
  remarks TEXT,
  PRIMARY KEY(id)
");

$tables[] = new TableCheck("commands_arguments", "

  id MEDIUMINT UNSIGNED AUTO_INCREMENT NOT NULL,
  id_command SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(16) NOT NULL DEFAULT '',
  description TEXT,
  PRIMARY KEY(id)
");

$tables[] = new TableCheck("commands_history", "

  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  id_command SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  id_user SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  action ENUM('created', 'updated', 'changed', 'renamed', 'deleted'),
  id_renamedto SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  time TIMESTAMP(14),
  PRIMARY KEY(id)
");

$tables[] = new TableCheck("variables", "

  id SMALLINT UNSIGNED AUTO_INCREMENT,
  id_vargroup SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  name CHAR(32) NOT NULL DEFAULT '' UNIQUE,
  active TINYINT UNSIGNED NOT NULL DEFAULT 1,
  description TEXT,
  type ENUM('integer', 'float', 'string', 'boolean', 'enum'),
  remarks TEXT,
  flags TINYINT UNSIGNED NOT NULL DEFAULT 0,
  id_group SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(id)
");

$tables[] = new TableCheck("variables_phys", "

  name CHAR(32) NOT NULL DEFAULT '',
  PRIMARY KEY(name)
");

$tables[] = new TableCheck("commands_phys", "

  name CHAR(32) NOT NULL DEFAULT '',
  PRIMARY KEY(name)
");

$tables[] = new TableCheck("variables_values_boolean", "

  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_variable SMALLINT UNSIGNED NOT NULL DEFAULT 0 UNIQUE,
  true_desc text,
  false_desc text,
  PRIMARY KEY(id)
");

$tables[] = new TableCheck("variables_values_enum", "

  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_variable SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  value CHAR(32) NOT NULL DEFAULT 0,
  description text,
  PRIMARY KEY(id)
");

$tables[] = new TableCheck("variables_values_other", "

  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_variable SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  description text,
  PRIMARY KEY(id)
");

$tables[] = new TableCheck("variables_history", "

  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  id_variable SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  id_user SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  action ENUM('created', 'updated', 'changed', 'renamed', 'deleted'),
  id_renamedto SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  time TIMESTAMP(14),
  PRIMARY KEY(id)
");

$tables[] = new TableCheck("variables_support", "

    id INT UNSIGNED AUTO_INCREMENT,
    id_variable SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    id_build SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    default_value CHAR(32) NOT NULL DEFAULT '',
    PRIMARY KEY(id),
    UNIQUE(id_variable, id_build),
    INDEX(id_variable)
");

$tables[] = new NonEmptyTableCheck("builds", "

  id TINYINT UNSIGNED AUTO_INCREMENT NOT NULL,
  abbr CHAR(4) NOT NULL DEFAULT '',
  shortname CHAR(12) NOT NULL DEFAULT '',
  title CHAR(32) NOT NULL DEFAULT '',
  PRIMARY KEY(id),
  UNIQUE(abbr),
  UNIQUE(shortname),
  UNIQUE(title)
",  
  $BUILDS_TABLE_CONTENT
);

$tables[] = new TableCheck("variables_mgroups", "

  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(32) NOT NULL DEFAULT '',
  title VARCHAR(128) NOT NULL DEFAULT '',
  active TINYINT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY(id),
  UNIQUE(name),
  UNIQUE(title)
");

$tables[] = new TableCheck("variables_groups", "

  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_mgroup SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(32),
  title VARCHAR(128),
  active TINYINT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY(id),
  UNIQUE(name),
  UNIQUE(title)
");

$tables[] = new TableCheck("users", "

  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  login CHAR(16) NOT NULL DEFAULT '',
  name CHAR(32) NOT NULL DEFAULT '',
  password CHAR(32) NOT NULL DEFAULT '',
  access TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(id),
  UNIQUE(login),
  UNIQUE(name)
");

$tables[] = new TableCheck("sessions", "

  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_user SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  ip CHAR(16) NOT NULL DEFAULT '127.0.0.1',
  host CHAR(255) NOT NULL DEFAULT '',
  browser CHAR(255) NOT NULL DEFAULT '',
  idstr CHAR(32) NOT NULL DEFAULT '' UNIQUE,
  lasthit TIMESTAMP(14),
  PRIMARY KEY(id)
");

$tables[] = new TableCheck("manuals", "

  id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_group SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  name CHAR(32) NOT NULL DEFAULT '',
  title CHAR(128) NOT NULL DEFAULT '',
  content TEXT,
  active TINYINT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY(id),
  UNIQUE(name),
  UNIQUE(title)
");

$tables[] = new TableCheck("manuals_groups", "

  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  abbr CHAR(32) NOT NULL DEFAULT '',
  name CHAR(128) NOT NULL DEFAULT '',
  PRIMARY KEY(id),
  UNIQUE(abbr),
  UNIQUE(name)
");

$tables[] = new TableCheck("manuals_history", "

  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  id_manual SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  id_user SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  action ENUM('created', 'updated', 'changed', 'renamed', 'deleted'),
  id_renamedto SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  time TIMESTAMP(14),
  PRIMARY KEY(id)
");

$tables[] = new TableCheck("commands_support", "

  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_command SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  id_build SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(id)
");

$tables[] = new TableCheck("options", "

    id MEDIUMINT UNSIGNED AUTO_INCREMENT NOT NULL,
    name CHAR(64) NOT NULL DEFAULT '',
    description TEXT,
    args CHAR(128) NOT NULL DEFAULT '',
    argsdesc TEXT,
    flags SET('GL only', 'Linux only', 'Win32 only', 'Software only'),
    active TINYINT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY(id),
    UNIQUE(name)
");

$tables[] = new TableCheck("options_history", "

  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  id_option SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  id_user SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  action ENUM('created', 'updated', 'changed', 'renamed', 'deleted'),
  id_renamedto SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  time TIMESTAMP(14),
  PRIMARY KEY(id)
");

$tables[] = new TableCheck("settings_index", "

  name CHAR(64) NOT NULL,
  itype ENUM('variable', 'command', 'command-line option'),
  desc1 char(255) NOT NULL DEFAULT '',
  desc2 char(255) NOT NULL DEFAULT '',
  desc3 char(255) NOT NULL DEFAULT '',
  igroup SMALLINT UNSIGNED NOT NULL DEFAULT 0,  
  PRIMARY KEY(name, itype)
");

$tables[] = new TableCheck("search_hits", "

  query VARCHAR(32) NOT NULL PRIMARY KEY,
  hits INT UNSIGNED NOT NULL DEFAULT 1
");

echo "<pre>";

foreach ($tables as $tablecheck)
{
    echo "Checking table ". $tablecheck->tableName ."\n";
    if ($tablecheck->Check())
         echo "OK (existed or created)\n\n";
    else echo "Failed\n\n";
}

echo "</pre>";

?>
