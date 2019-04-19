<?php
//clean up input
function validateTextInput($data)
{
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}

//check if string is an actual link
function isLink($data)
{
	if (filter_var($data, FILTER_VALIDATE_URL))
	{
		return true;
	} else
	{
		return false;
	}
}

//convert SteamID

//validate the steamid is a legit steamid
function sidIsValid($sid)
{
	if (preg_match("/^STEAM_[01]:[01]:\d+$/", $sid))
		return true;
	else
		return false;
}

function convertRank($rid)
{
	if ($rid == 1)
		return "Mod";
	else if ($rid == 2)
		return "Admin";
	else if ($rid == 3)
		return "Lead Admin";
	else if ($rid == 4)
		return "Developer";
	else if ($rid == 5)
		return "Owner";
	else
		return "None";
}
?>
