<?php
date_default_timezone_set('UTC');

//Everything in this file has to do with our database. Getting data, putting data, etc.

//sanitize input
function validateInput($conn, $data)
{
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	//okokok that's cool and all, but like, can y ou protect yoru database?
	$data = mysqli_real_escape_string($conn, $data);
	//ok cool thx
	return $data;
}


//log an action
function logUserAction($conn, $uid, $pid, $action)
{
	$date = date('Y-m-d H:i:s');
	$ip = $_SERVER[REMOTE_ADDR];
	$sql = "INSERT INTO user_logs(uid, pid, action, date, ip)
		VALUES ('$uid', '$pid', '$action', '$date', '$ip')";
	$result = $conn->query($sql);
}

//return an array of all users and their information
function listUsers($conn)
{
	$sql = "SELECT * FROM users";
	$result = $conn->query($sql);
	$user = array();
	while ($row = $result->fetch_assoc())
	{
		$user[] = $row;
	}
	return $user;
}

//lists all the posts
function listPosts($conn)
{
	$sql = "SELECT * FROM posts";
	$result = $conn->query($sql);
	$posts = array();
	while ($row = $result->fetch_assoc())
	{
		$posts[] = $row;
	}
	return $posts;
}

//search through users and get us some data
function getUser($conn, $usid)
{
	$sql = "SELECT * FROM users";
	$result = $conn->query($sql);
	$user = 0;
	while($row = $result->fetch_assoc())
	{
		if ($row['usid'] == $usid)
		{
			$user = $row;
		}
	}
	return $user;
}

//search through users, by but the unique id, and get us some data
function getUserByUid($conn, $uid)
{
	$sql = "SELECT * FROM users";
	$result = $conn->query($sql);
	$user = 0;
	while($row = $result->fetch_assoc())
	{
		if ($row['uid'] == $uid)
		{
			$user = $row;
		}
	}
	return $user;
}

//returns a uid by a usid
function getUid($conn, $usid)
{
	$sql = "SELECT * FROM users";
	$result = $conn->query($sql);
	$uid = -1;
	while($row = $result->fetch_assoc())
	{
		if ($row['usid'] == $usid)
		{
			$uid = $row['uid'];
		}
	}
	return $uid;
}

//check is the fucker is even currently staff
function isStaff($conn, $usid)
{
	$user = getUser($conn, $usid);
	if ($user != 0 || $user['rank'] != 0)
		return true;
	else
		return false;
}

//the following functions just check ranks
function isMod($conn, $usid)
{
	if (isStaff($conn, $usid))
	{
		$user = getUser($conn, $usid);
		if ($user['rank'] >= 1)
			return true;
	} else
		return false;
}

function isAdmin($conn, $usid)
{
	if (isStaff($conn, $usid))
	{
		$user = getUser($conn, $usid);
		if ($user['rank'] >= 2)
			return true;
	} else
		return false;
}

function isLead($conn, $usid)
{
	if (isStaff($conn, $usid))
	{
		$user = getUser($conn, $usid);
		if ($user['rank'] >= 3)
			return true;
	} else
		return false;
}

function isDev($conn, $usid)
{
	if (isStaff($conn, $usid))
	{
		$user = getUser($conn, $usid);
		if ($user['rank'] >= 4)
			return true;
	} else
		return false;
}

function isOwner($conn, $usid)
{
	if (isStaff($conn, $usid))
	{
		$user = getUser($conn, $usid);
		if ($user['rank'] >= 5)
			return true;
	} else
		return false;
}

//returns true if user has higher perms than other user
function permsCheck($conn, $usid, $ousid)
{
	//oh boy HERE WE FUCKING GOOOOOOO
	$user = getUser($conn, $usid);
	$ouser = getUser($conn, $ousid);
	if ($user['rank'] > $ouser['rank'])
		return true;
	else
		return false;
	//wait that wasn't bad at all
}

//returns true if user has higher perms than other user
function permsCheckByUid($conn, $uid, $ouid)
{
	//oh boy HERE WE FUCKING GOOOOOOO
	$user = getUserByUid($conn, $uid);
	$ouser = getUserByUid($conn, $ouid);
	if ($user['rank'] > $ouser['rank'])
		return true;
	else
		return false;
	//wait that wasn't bad at all
}

//create a user and set his rank
//checks to make sure no tom foolery is going on
function setUser($conn)
{
	if (isset($_POST['userSubmit']))
	if (isAdmin($conn, $_SESSION['steamid'])) //prevents losers from doing this
	{
		$nusid = validateInput($conn, $_POST['nusid']);
		$name = validateInput($conn, $_POST['name']);
		$rank = validateInput($conn, $_POST['rank']);
		$uid = getUid($conn, $_SESSION['steamid']);
		//make sure we don't add the same usid 2x
		$check = listUsers($conn);
		foreach($check as $data)
		{
			if ($data['usid'] == $nusid)
			{
				return false; //just stop the program before it does something stupid
			}
		}
			if (permsCheck($conn, $_SESSION['steamid'], $nusid) && getUserByUid($conn, $uid)['rank'] > $rank)
			{
				$sql = "INSERT INTO users(usid, name, rank)
								VALUES ('$nusid', '$name', '$rank')";
				$result = $conn->query($sql);
				logUserAction($conn, $uid, 0, $name." user has been added with the USID ".$nusid);
				header("Location: users.php?s=1");
			}
			else {
				//this should never happen. Is only possible if they inspect element tom foolery or some shit. :coi:
				logUserAction($conn, $uid, 0, "Attempted to create user with the rank of ".convertRank($rank)." and usid of ".$nusid);
			}
		}
		else
		{
			header("Location: users.php?s=0");
		}
}

//set user rank.
//also checks to make sure no tom foolery is going on
function setUserRank($conn)
{
	if (isAdmin($conn, $_SESSION['steamid'])) //prevents losers from doing this
	{
		if (isset($_POST['userRankSubmit']))
		{
			$ousid = validateInput($conn, $_POST['ousid']);
			$ouid = getUid($conn, $ousid); //other uid
			$uid = getUid($conn, $_SESSION['steamid']);
			$rank = validateInput($conn, $_POST['nrank']);
			//ok, now it's time for more advanced tom foolery.
			//ok I decided to put advanced tom foolery elsewhere. We gonna do a perms check.
			if (permsCheckByUid($conn, $uid, $ouid) && getUserByUid($conn, $uid)['rank'] > $rank)
			{
				$sql = "UPDATE `users` SET `rank` = '$rank' WHERE `users`.`uid` = '$ouid'";
				$result = $conn->query($sql);
				logUserAction($conn, $uid, 0, "Changed the rank of ".$ouid." to ".convertRank($rank));
				header("Location: users.php?s=1");
			}
			else
			{
				//this should never happen. Is only possible if they inspect element tom foolery or some shit. :coi:
				logUserAction($conn, $uid, 0, "Attempted to change the rank of ".$ouid." to ".convertRank($rank));
			}
		}
	}
}

//returns the newest post ID
function latestID($conn)
{
	$latestId = 0;
	$sql = "SELECT * FROM posts";
	$result = $conn->query($sql);
	$info = array();
	while($row = $result->fetch_assoc())
	{
		for ($i=$latestId; $i <= $row['id']; $i++)
			$latestId++;
	}
	return $latestId;
}

//verify the user should actually be able to delete something
function verifyDelete($conn, $id, $uid)
{
	if (isAdmin($conn, $_SESSION['steamid']))
	{
		return true;
	}

	$sql = "SELECT * FROM posts";
	$result = $conn->query($sql);
	while($row = $result->fetch_assoc())
	{
		if ($row['id'] == $id && $row['uid'] == $uid)
		{
			return true;
		}
	}
	//if we make it to here we gotta be false
	return false;
}

//set a post into the database
function setPost($conn)
{
	if (isset($_POST['postSubmit']))
	{
		$uid = getUid($conn, $_SESSION['steamid']);
		$sid = validateInput($conn, $_GET['sid']);
		$date = date('Y-m-d H:i:s');
		$ev = validateInput($conn, $_POST['evidence']);
		$det = validateInput($conn, $_POST['details']);
		$pid = latestID($conn);

		$sql = "INSERT INTO posts(sid, ev, det, date, uid)
			VALUES ('$sid', '$ev', '$det', '$date', '$uid')";
		$result = $conn->query($sql);
		logUserAction($conn, $uid, $pid, "New Post");
		header("Location: index.php?sid=".$sid);
	}
}

//get all the posts pertaining to a steamid from the database
function getPost($conn, $sid)
{
	$sql = "SELECT * FROM posts";
	$result = $conn->query($sql);
	$info = array();
	while($row = $result->fetch_assoc())
	{
		if ($row['sid'] == $sid)
		{
			$info[] = $row;
		}
	}
	return $info;
}

//hide a post in the database
function hidePost($conn, $sid)
{
	if (isset($_POST['deleteSubmit']))
	{
		$id = validateInput($conn, $_POST['id']);
		$uid = getUid($conn, $_SESSION['steamid']);
		if (verifyDelete($conn, $id, $uid))
		{
			$sql = "UPDATE `posts` SET `hidden` = '1' WHERE `posts`.`id` = '$id'";
			$result = $conn->query($sql);
			logUserAction($conn, $uid, $id, "Delete Post");
			if (isset($_GET['sid']))
			{
				header("Location: index.php?sid=".$sid);
			}
			else
			{
				header("Location: index.php");
			}
		}
	}
}

//show a post in the database
function showPost($conn, $sid)
{
	if (isset($_POST['undeleteSubmit']))
	{
		$id = validateInput($conn, $_POST['id']);
		$uid = getUid($conn, $_SESSION['steamid']);
		if (verifyDelete($conn, $id, $uid))
		{
			$sql = "UPDATE `posts` SET `hidden` = '0' WHERE `posts`.`id` = '$id'";
			$result = $conn->query($sql);
			logUserAction($conn, $uid, $id, "Undelete Post");
			if (isset($_GET['sid']))
			{
				header("Location: index.php?sid=".$sid);
			}
			else
			{
				header("Location: index.php");
			}
		}
	}
}

//post table rowssss
function postTableRow($conn, $data, $usid)
{
	if ($data['hidden'] != 1)
	{
		echo "
		<tr align='center'>
			<td class='date'>".$data['date']."</td>";
			if (!isset($_GET['sid']))
			{
				echo "<td class='date'><a href='index.php?sid=".$data['sid']."'>".$data['sid']."</a></td>";
			}
			echo "<td class='ev'>";
				if (isLink($data['ev']))
				{
					echo "<a href='".$data['ev']."' target='_blank'>".$data['ev']."</a>";
				} else {
					echo $data['ev'];
				}
			echo "</td>
			<td class='desc'>".$data['det']."</td>
			<td class='name'>".getUserByUid($conn, $data['uid'])['name']."</td>
			<td>";
				if (verifyDelete($conn, $data['id'], getUid($conn, $usid)))
				{
				echo "<form autocomplete='off' method='POST' action='".hidePost($conn, $data['sid'])."'>
					<input type='hidden' name='id' value='".$data['id']."'>
					<button type='submit' name='deleteSubmit'>Delete</button>
				</form>
				<form action='https://www.seriousgmod.com/adminstats.php'>
					<button type='submit' name='sid' value='".$data['sid']."'>Adminstats</button>
				</form>";
			}
			echo "</td>
		</tr> ";
	} else if ($data['hidden'] == 1 && isAdmin($conn, $usid))
	{
		echo "<tr>
						<td style='color: #ff7f7f;'>(HIDDEN POST)</td>
					</tr>";
		echo "
		<tr align='center'>
			<td class='date'>".$data['date']."</td>";
			if (!isset($_GET['sid']))
			{
				echo "<td class='date'><a href='index.php?sid=".$data['sid']."'>".$data['sid']."</a></td>";
			}
			echo "<td class='ev'>";
				if (isLink($data['ev']))
				{
					echo "<a href='".$data['ev']."' target='_blank'>".$data['ev']."</a>";
				} else {
					echo $data['ev'];
				}
			echo "</td>
			<td class='desc'>".$data['det']."</td>
			<td class='name'>".getUserByUid($conn, $data['uid'])['name']."</td>
			<td>";
				if (verifyDelete($conn, $data['id'], getUid($conn, $usid)))
				{
					echo "<form autocomplete='off' method='POST' action='".showPost($conn, $data['sid'])."'>
						<input type='hidden' name='id' value='".$data['id']."'>
						<button type='submit' name='undeleteSubmit'>Un-Delete</button>
					</form>
				<form action='https://www.seriousgmod.com/adminstats.php'>
					<button type='submit' name='sid' value='".$data['sid']."'>Adminstats</button>
				</form>";
			}
		}
}
?>
