<?php

// For now, we will assume this is only for grade 5 
/* Does not contain logic for repeating passages
Does not contain logic for picking Free Qs from a month
Does not contain logic for changing student levels 
Simplified Lexile array to contain only one value per Lexile
*/

// Variables    
$user_grade = 5;
$Current_Item = 0;
$Reading_Attempt_Count = 0;
$last_writing_FQ = 0;
$today=date_create(date('Y-m-d'));  // today's date
$last_writing_date = $today;   // changes even if a student writes on a topic from the writing page
$last_writing_datedif = date_diff($today,$last_writing_date);  // calculating the number of days since the last writing
$last_game_FQ = 0;
$last_game_date = $today;  // changes even if a student does a game from the games page
$last_game_datedif = date_diff($today,$last_game_date);  // calculating the number of days since the last game
$month = date("m");  // current month needed for fetching grammar Qs
$Reading_Attempt_Count_NonLex = 0;  // counter for non lexile passages

// Arrays
$max_items = array('S'=>1, 'G'=>3, 'V'=>3); // Max no. of an item type to be fetched in one go
IF(empty($user_level)) {
$user_level = array('R'=>'Lower', 'L'=>3,  'G'=>3, 'V'=>3);  // set the default values for user level for each item
};
$current_user_accuracy = array('R'=>'Lower', 'L'=>3,  'G'=>3, 'V'=>3);  // user accuracy levels
$Grade_Lexile_Range = array(
4 =>array('Lower' => 500, 'Upper' => 980),
5 =>array('Lower' => 600, 'Upper' => 1010),
6 =>array('Lower' => 750, 'Upper' => 1100),
7 =>array('Lower' => 850, 'Upper' => 1200),
8 =>array('Lower' => 900, 'Upper' => 1290),
9 =>array('Lower' => 960, 'Upper' => 1350)
);

$Item_List = array('Reading', 'Grammar', 'Listening', 'Vocab','Reading', 'Speaking', 'Writing', 'Game'); // list of all items that come cyclically

/*$ReadingID[5] = array(0=>array(123,141,150,230), 600=>array(957, 1524, 937), 610=>851, 620 =>array(1628, 1647, 816)…); // collection of all reading passages, where the key is the Lexile score and the value is the passage ID */

$ReadingID[5] = array(0=>123, 600=>957, 610=>851, 620 =>1628); // collection of all reading passages, where the key is the Lexile score and the value is the passage ID
$Lexiles[5] = array_flip($ReadingID[5]);  //store the Passage IDs as keys and the Lexile scores as values

$ReadingID_Attempts = array(); // collection of all attempted reading passages
$Reading_Attempt_Count = count($ReadingID_Attempts);  // counter for reading attempts
IF(empty($Reading_Attempt_Count))
{
	$Reading_Attempt_Count = 0;
}
$ListeningID[5] = array(
1 => array(441, 573, 439, 425), 
2 => array(490, 422, 133, 561, 401), 
3 => array(1839, 430, 455, 614),
4 => array(1191, 465, 466, 515)
);

$ListeningID_Attempts = array();
$Listening_Attempt_Count = count($ListeningID_Attempts);

$GrammarID[5] = array(
1 =>array(27, 871, 133, 561, 401), 
2 => array(2137, 875, 138, 9867, 6983), 
3 => array(7089, 8171, 1031, 5611, 4031),
4 => array(909, 2111, 1004, 6914, 7097)
);   // collection of grammar questions in each quartile for grade 5
// $GrammarID_Unattempted = $GrammarID[$user_grade]; collection of all unattempted grammar Qs
$GrammarID_Attempts = array();
$Grammar_Attempt_Count = count($GrammarID_Attempts);

$VocabID[5] = array(
1 => array(677, 6981, 3113, 3201, 4018), 
2 => array(2811, 10937, 1237, 8735, 6923),
3 => array(2551, 3786, 8656, 3765, 3890),
4 => array(2401, 3169, 4071, 555, 9983)
);    // collection of vocab questions in each quartile for grade 5
// $VocabID_Unattempted = $VocabID[$user_grade]; collection of all unattempted vocab Qs
$VocabID_Attempts = array();
$Vocab_Attempt_Count = count($VocabID_Attempts);

$WritingID[5] = array(					//grade-wise writing topics
4 => array(1, 2, 3, 4), 
5 => array(5, 6, 7, 8), 
6 => array(9, 10, 11, 12),
7 => array(13, 14, 15, 16),
8 => array(17, 18, 19, 20),
9 => array(21, 22, 23, 24)
);
$WritingID_Unattempted = $WritingID[$user_grade]; // collection of all unattempted writing passages
$WritingID_Attempts = array();

$GameID = array(					//grade-wise games (overlaps allowed)
4 => array(1, 2, 3, 4), 
5 => array(4, 5, 6, 7), 
6 => array(6, 7, 8, 9),
7 => array(6, 8, 9, 10),
8 => array(9, 10, 11, 12),
9 => array(11, 12, 13, 14)
);
$GameID_Unattempted = $GameID[$user_grade]; // collection of all unattempted games
$GameID_Attempts = array();


For ($Current_Item == 0; $Current_Item<7; $Current_Item++)
{
$Current_Item_Type = $Item_List[$Current_Item];

IF($Current_Item_Type == 'Reading')
{
IF($Reading_Attempt_Count < 2)
	{ $Next_Available = $Grade_Lexile_Range[$user_grade]['Lower'];
		DO
		{	$item = $ReadingID[$user_grade][$Next_Available];
			IF(in_array($item,$ReadingID_Attempts))
			{
			  $x = 0;
			  $Next_Available = $Next_Available + 10;
			  }
			else
			  {
			  $x = 1;
			  }
			} WHILE ($x < 1);
			
/*		function getClosest($Grade_Lexile_Range[$user_grade]['Lower'],$ReadingID[$user_grade][$Next_Available]) 
	{
	   $closest = null;
	   foreach ($ReadingID[$user_grade] as $item) {
	      if ($closest === null || abs($Grade_Lexile_Range[$user_grade]['Lower'] - $closest) > abs($item - $Grade_Lexile_range[$user_grade]['Lower'])) {
	         $closest = $item;
	      }
	   }
	   return $closest;
	} */ //There was some error with this function that I couldn't figure out
	
	$Current_ItemID = $item; // Fetch the current passage from lowest Lexile passage within range of Lexiles for that grade
	Array_push($ReadingID_Attempts, $Current_ItemID); // Update the list of attempted passages
	echo "Reading ID (Lexile): ".$Current_ItemID."<br>";
	
	} ELSE {
		IF($Reading_Attempt_Count>2) 
		{
		$Current_ItemID = $readingID[$user_grade][0][$Reading_Attempt_Count_NonLex];
		Array_push($ReadingID_Attempts, $Current_ItemID); // Update the list of attempted passages
		$Reading_Attempt_Count_NonLex = $Reading_Attempt_Count_NonLex+1;
		echo "Reading Passage ID (Non-Lexile):".$Current_ItemID."<br>";
		}
	}

$Reading_Attempt_Count = $Reading_Attempt_Count++;

} ELSE {
	IF($Current_Item_Type == 'Listening')
	{	$x = 0;
		DO
		{	$item = array_rand($ListeningID[$user_grade][$user_level['L']], 1);
			IF(in_array($item,$ListeningID_Attempts))
			{
			  $x = 0;
			  }
			else
			  {
			  $x = 1;
			  }
		} WHILE ($x < 1);

	$Current_ItemID = $item; // Fetch the current passage randomly from pool of unattempted passages
	Array_push($ListeningID_Attempts, $Current_ItemID); // Update the list of attempted passages
    echo "Listening Passage ID: ".$Current_ItemID[0]."<br>";
} ELSE {
	IF($Current_Item_Type == 'Grammar')
	{	$x = 0;
		DO
		{	$item = array_rand($GrammarID[$user_grade][$user_level['G']], $max_items['G']);
			IF(in_array($item,$GrammarID_Attempts))
			{
			  $x = 0;
			  }
			  else
			  {
			  $x = 1;
			  }
		} WHILE ($x < 1);
	$Current_ItemID = $item; // Fetch the max no. of grammar Qs randomly from pool of unattempted Grammar Qs for that month within that quartile
	Array_push($GrammarID_Attempts, $Current_ItemID); // Update the list of attempted grammar Qs
	echo "Grammar Qs: ".implode(", ", $Current_ItemID)."<br>";
	$last_game_FQ = $last_game_FQ + 20;
	$last_writing_FQ = $last_writing_FQ + 20;
} ELSE {
	IF($Current_Item_Type == 'Vocab')
	{
	{	$x = 0;
		DO
		{	$item = array_rand($VocabID[$user_grade][$user_level['V']], $max_items['G']);
			IF(in_array($item,$VocabID_Attempts))
			{
			  $x = 0;
			  }
			  else
			  {
			  $x = 1;
			  }
		} WHILE ($x < 1);
	$Current_ItemID = $item; // Fetch the max no. of vocab Qs randomly from pool of unattempted vocab Qs within that quartile
	Array_push($VocabID_Attempts, $Current_ItemID); // Update the list of attempted vocab Qs
	echo "Vocab Qs: ".implode(", ", $Current_ItemID)."<br>";
	$last_game_FQ = $last_game_FQ + 20;
	$last_writing_FQ = $last_writing_FQ + 20;
}
} ELSE {
	IF($Current_Item_Type == 'Writing' && ($last_writing_FQ >= 400 OR $last_writing_datedif <= 30))
	{
		$random_item = array_rand($WritingID_Unattempted[$user_grade],1);
		$Current_ItemID = $WritingID_Unattempted[$user_grade][$random_item];
		Array_push($WritingID_Attempts, $Current_ItemID); // Update the list of attempted writing topics
		$WritingID_Unattempted = Array_diff($WritingID_Attempts, $WritingID[$user_grade]); // Update the list of unattempted writing topics by removing the attempted one
		$last_writing_date = date_create(date('Y-m-d'));
		$last_writing_FQ = 0;
	}
 ELSE {
	IF($Current_Item_Type == 'Game' && ($last_game_FQ >= 200 OR $last_game_datedif <= 15))
	{
		$Current_ItemID = array_rand($GameID_Unattempted[$user_grade]);
		Array_push($GameID_Attempts, $Current_ItemID); // Update the list of attempted games
		$GameID_Unattempted = Array_diff($GameID_Attempts, $GameID[$user_grade]); // Update the list of unattempted games by removing the attempted one
		$last_game_date = date_create(date('Y-m-d'));
		$last_game_FQ = 0;
	};
/* IF(empty($GameID_Unattempted))
	{
		$GameID_Attempts = 0;
	} */
}

/* 
IF(empty($ReadingID_Unattempted))
	{
	here need to think a bit about linking $current_user_accuracy to $user_level through changes after every 10 passages or so 
	};

IF (Current_Item == 7)
{
$Current_Item = 0;  // reset to the first item type in the cycle
}
echo $Current_ItemID;
*/
}
}
}
}
}
