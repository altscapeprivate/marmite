<?php
header('Content-type: text/plain');
include 'functions.php';
include 'config.php';

global $skills;
## get name from url
$name = '';
$name = str_replace(" ","_",strtolower(htmlspecialchars(@$_GET["name"])));

## check if given a name
if(strcmp($name,"")==0){
	#calculateEHP(explode("\n",$testXP),$footrates);
	die("add ?name=<your RSN> to the URL");
}

## get user ID, or create new record
$userId = getId($name);
$data = update($name);

# split the data up by skills
$scores = explode("\n",$data); 

echo 'Hello ' . $name . " user id: " . $userId . '!' . PHP_EOL;
echo "Skill \t Rank \t Level \t xp \n";

for($x = 0; $x <= 23; $x+=1){
	#split each skill into rank,level,xp
	#format is rank,level,xp
	$skillData = explode(",",$scores[$x]);
	
	#print each skills data
	echo $skills[$x] . "\t" . $skillData[0] . "\t" . $skillData[1] . "\t" . $skillData[2] . "\t"  . PHP_EOL;
		
}
?>