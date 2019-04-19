<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
//neat little thing that lets us sign in with steam
//https://github.com/SmItH197/SteamAuthentication
require './include/steamauth/steamauth.php';
if (isset($_SESSION['steamid']))
	$usid = $_SESSION['steamid'];
//our connection to the database
require './include/dbh.php';
//our database functions
require './include/database.php';
//the basic shit
require './include/functions.php';
//we using UTC because fuck you nigga. No, I won't convert it.
//I want people discussing evidence using UTC time so there's 0 confusion.
date_default_timezone_set('UTC');
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>StickyNet - SUCC</title>
    <link rel='stylesheet' type='text/css' href='./stylesheet.css'>
  </head>
  <body>
		<?php
		if (isset($usid))
		{
			echo logoutbutton();
			if (isAdmin($conn, $usid))
			{
				echo "<a href='./users.php'>USERS</a>";
			}
		}
		?>
		<div class='top'>
    	<h1><a href='./index.php' style='text-decoration: none; color: #FAFAFA;'>Sticky's Uber Cheats Collection (BETA)</a></h1>
			<?php if (!isset($usid)){echo loginbutton();} ?>
		</div>

<?php
if (isset($usid))
{

	if(isStaff($conn, $usid))
	{ ?>
		<div class='search'>
			<form autocomplete='off' method='GET'>
				<input type='text' name='sid' placeholder='SteamID' <?php if (isset($_GET['sid'])){ ?>value='<?php echo $_GET['sid']; ?>' <?php } ?>>
				<button>SEARCH</button>
			</form>
		</div>
		<br>
<?php
		if (!isset($_GET['sid']))
		{
?>
		<?php $content = array_reverse(listPosts($conn)) ?>

		<table align='center'>
			<tr>
				<th class='date'>Date (UTC)</th>
				<th class='date'>SteamID</th>
				<th class='ev'>Evidence</th>
				<th class='desc'>Description</th>
				<th>Posted By</th>
				<th>Actions</th>
			</tr>
			<?php
				foreach($content as $data)
				{
					postTableRow($conn, $data, $usid);
				}
			?>
			</table>
<?php
		}
		if(isset($_GET['sid']))
		{
			//cleanse the steamid in case ryan puts spaces or some stupid shit
			$sid = validateTextInput($_GET['sid']);
			//make sure the SteamID is valid
			if (sidIsValid($sid))
			{
?>
 <div style='text-align: center; padding-bottom: 16px;'><button onclick="document.getElementById('newEv').style.display='block'">New Evidence</button></div>

 <div id="newEv" class="modal">

   <form autocomplete='off' class="modal-content animate" method='POST' action='<?php setPost($conn) ?>'>
     <div class="container" style='text-align: center; padding-bottom: 16px;'>
       <label for="evidence"><b>Evidence</b></label>
       <input type="text" placeholder="https://link-to-your-evidence" name="evidence" required>

       <label for="details"><b>Description</b></label>
       <input type="text" placeholder="Describe the Event" name="details" required>
     </div>

     <div class="container" style="background-color:#f1f1f1; text-align: center; padding-bottom: 8px; padding-top: 8px;">
			 <button type="submit" name='postSubmit'>Add Evidence</button>
       <button type="button" onclick="document.getElementById('newEv').style.display='none'" style="text-align: center;">Cancel</button>
     </div>
   </form>
 </div>

<?php
			//fetch the shit from the database and store it as an array
			$content = array_reverse(getPost($conn, $sid));
			?>
  <table align='center'>
		<tr>
			<th class='date'>Date (UTC)</th>
			<th class='ev'>Evidence</th>
			<th class='desc'>Description</th>
			<th>Posted By</th>
			<th>Actions</th>
		</tr>
		<?php
			foreach($content as $data)
			{
				postTableRow($conn, $data, $usid);
			}
		?>
		</table>

		<br><p style='text-align:center;'>Use <a href='https://limelightgaming.net/temar/TEC/index.php' target='_blank'>TEC</a> to check for alts!</p>
<?php
		}
		else
			echo "ERROR: Invalid SteamID!<br>";
	}
	}
}
  ?>
  </body>
</html>
