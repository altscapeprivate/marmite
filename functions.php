<?php
header('Content-type: text/plain');
include 'config.php';

#gets the oldeset data in the database that is younger than $age in hours
function getOldData($age,$name){
	global $conn;
	
	$id = getId($name);
	$time = time();
	$pastTime = $time - ($age * 24 * 60 * 60);
	#echo $time . PHP_EOL;
	#echo $pastTime . PHP_EOL;
	$sql = "SELECT XP,UNIX_TIMESTAMP(TimePulled) FROM highscoresdata WHERE isCurrent = 0 && UserID = " . $id . " && UNIX_TIMESTAMP(TimePulled) > " . $pastTime;
	
	$oldData = "";
	$oldestTimeInFrame = $time;
	
	$result = mysqli_query($conn,$sql);
	if(mysqli_num_rows($result) > 0){
		while($row = mysqli_fetch_row($result)){
			if($row[1] < $oldestTimeInFrame){
				$oldData = $row[0];
				$oldestTimeInFrame = $row[1];
			}
		}
		
	}
	return $oldData;
}
function update($name){
	global $conn;
	
	
	$id = getId($name);
	$url = "http://services.runescape.com/m=hiscore_oldschool/index_lite.ws?player=" . $name;
	$response = get_headers($url);
	
	if($response[0] === 'HTTP/1.1 200 OK') {
		$highscoreData = file_get_contents("http://services.runescape.com/m=hiscore_oldschool/index_lite.ws?player=" . $name);
		
	}else{
		die("Username not found or highscores down");
	}

	$oldData = "";
	#pull old data to see if there is any change
	$sql = "SELECT * FROM highscoresdata WHERE IsCurrent = 1 && UserID = " . $id;
	$result = mysqli_query($conn,$sql);
	
	if(mysqli_num_rows($result) > 0){#check if user has been tracked before
		$row = mysqli_fetch_row($result);
		$oldData = $row[2]; # $row[2] is the highscores api result
		
		if(strcmp($oldData,$highscoreData)!= 0){ #check if theres a change
		
			#change the old data to be set to old
			$sql = "UPDATE highscoresdata SET IsCurrent=0 WHERE IsCurrent = 1 && UserID = " . $id;
			mysqli_query($conn,$sql);
			
			#insert the new data into the db
			$sql = "INSERT INTO highscoresdata (UserID,XP) VALUES (" . $id . ",'" . $highscoreData  .")";
			mysqli_query($conn,$sql);
		}
	}else{
		#user hasn't been tracked so just insert data
		$sql = "INSERT INTO highscoresdata (UserID,XP) VALUES (" . $id . ",'" . $highscoreData . ")";
		mysqli_query($conn,$sql);
	}
	
	return $highscoreData; #returns the new data
	
}

function getID($name){
	global $conn;
	
	
	$sql = "SELECT UserID FROM User WHERE DisplayName = '" . $name . "'";
	$result = mysqli_query($conn,$sql);
	
	if(mysqli_num_rows($result) > 0){ #check if theres an id for the username
		$row = mysqli_fetch_row($result);
		$userId = $row[0];
	} else{
		#if theres no id for that username, insert a new row for user, return its ID
		$sql = "INSERT INTO User (DisplayName) VALUES ('" . $name . "')";
		mysqli_query($conn,$sql);
		
		#get the ID of the new user
		$sql = "SELECT UserID FROM User WHERE DisplayName = '" . $name . "'";
		mysqli_query($conn,$sql);
		$result = mysqli_query($conn,$sql);
		$row = mysqli_fetch_row($result);
		$userId = $row[0];
	}
	
	return $userId;
	
}



?>