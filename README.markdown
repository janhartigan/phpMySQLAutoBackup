<p>If you haven't worked too much with the Linux bash (or command line), the following tasks may seem over-complicated to you:</p>

<ul class="simple_list">
<li>Run a PHP script every day at a specified time (a cron job)</li>
<li>Have that PHP script get a list of all databases for a given user on a server</li>
<li>Back up all of those databases</li>
</ul>

<p>So in the interest of helping you out, I gift thee a guide...</p>

<p>First let's get that PHP script. You can find it <a href="https://github.com/janhartigan/phpMySQLAutoBackup">on GitHub</a> in the file <em>backup.php</em> or by clicking the following link: <a href="https://github.com/janhartigan/phpMySQLAutoBackup">https://github.com/janhartigan/phpMySQLAutoBackup</a>. Create a directory somewhere on your server (outside of your web root) called <em>scripts</em> and place backup.php in that directory.</p>

<p>Now that the script is on your server, let's go over it and see what it does.</p>

<code class="language-php">&lt;?php

// Creates a mysqldump and emails the resulting dump file
$dbhost = "localhost";

// Edit the following values
$dbuser = "mysql_user"; //the mysql user
$dbpass = "mysql_pass"; //the mysql password
$path = "/home/admin/backups/mysql/"; //the directory path to where you want to store your backups
</code>

<p>Here you must define the following items:</p>

<ul class="simple_list">
<li><strong>$dbhost</strong>: This is normally <em>localhost</em>, unless your mysql driver is hosted on a foreign server</li>
<li><strong>$dbuser</strong>: The mysql user that will connect to the database</li>
<li><strong>$dbpass</strong>: The password for $dbuser</li>
<li><strong>$path</strong>: The directory path where you want to store your backups. On my server, I store them in <em>/home/admin/backups/mysql</em>, but you can store them anywhere you like</li>
</ul>

<div class="article_division"></div>

<p>Next we must get a list of databases using these database settings:</p>

<code class="language-php">//get the list of databases
$link = mysql_connect($dbhost, $dbuser, $dbpass);
$db_list = mysql_list_dbs($link);
</code>

<p>Here we establish a MySQL connection using PHP's <a href="http://php.net/manual/en/function.mysql-connect.php"><em>mysql_connect</em></a> function. Keep an eye on that <em>$link</em> variable as we will need to close it at the end of the script. Using the resource <em>$link</em>, we then call PHP's <a href="http://www.php.net/manual/en/function.mysql-list-dbs.php"><em>mysql_list_dbs</em></a> function which returns a resource that we can use to iterate over our list of databases.</p>

<code class="language-php">//iterate over the list of databases
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
</code>

<p>Now that we've got the list of databases, we can perform a while loop in order to iterate over the results. We store the database name in our <em>$dbname</em> variable and the full directory path (our backups directory plus a directory to contain our daily backups for this database) in the <em>$dir</em> variable. If the <em>$dir</em> directory doesn't exist, we create it. Then we create another variable called <em>$backupfile</em> which is simply a string representing the full directory path to the database's backup directory (<em>$dir</em>) and the database name plus a date string. If I had a database named "bird_watchers" and I ran this script on October 3, 2011, the <em>$backupfile</em> variable would look like this:</p>

<p><em>/home/admin/backups/mysql/bird_watchers/bird_watchers_2011-10-03.sql</em></p>

<p>Now that all the variables are set up, we're ready to run the system command that will actually back up the mysql database that our while loop is currently iterating over. In order to run this command, you need to ensure that you have <em>mysqldump</em> running on your server (most Apache servers have it). The syntax for this command, given the inputs we used above, is:</p>

<p><em>mysqldump -h localhost -u mysql_user -pmysql_pass bird_waters &gt; /home/admin/backups/mysql/bird_watchers/bird_watchers_2011-10-03.sql</em></p>

<p>You should be able to figure out what's going on in there, but <a href="http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html">here's more info</a> in case you need it.</p>

<p>Finally, we close the MySQL connection:</p>

<code class="language-php">//close the mysql connection
mysql_close($link);
</code>