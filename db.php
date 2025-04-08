<?php

try{
	$db = NEW PDO("mysql:host=localhost;dbname=sfgame;", "root", "");
}
catch(PDOException $error){
	Logger::error($error);
	exit("Cannot connect to database");
}

?>