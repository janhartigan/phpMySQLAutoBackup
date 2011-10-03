<?php
// Creates a mysqldump and emails the resulting dump file
$dbhost = "localhost";

// Edit the following values
$dbuser = "mysql_user"; //the mysql user
$dbpass = "mysql_pass"; //the mysql password
$path = "/home/admin/backups/mysql/"; //the directory path to where you want to store your backups

//get the list of databases
$link = mysql_connect($dbhost, $dbuser, $dbpass);
$db_list = mysql_list_dbs($link);

//iterate over the list of databases
while ($row = mysql_fetch_object($db_list)) {
	$dbname = $row->Database;
	$dir = $path.$dbname;
	
	//if the db directory doesn't exist yet, create it
	if (!is_dir($dir))
		mkdir($dir);
	
	//create the file name for the backup (if you want to run the update more frequently than once a day, add more specificity to the date
	$backupfile = $dir.'/'.$dbname.'_'.date("Y-m-d").'.sql';
	
	//make the system call to mysqldump
	system("mysqldump -h $dbhost -u $dbuser -p$dbpass $dbname > $backupfile");
}

//close the mysql connection
mysql_close($link);