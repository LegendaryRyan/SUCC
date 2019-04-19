<?php
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
    <title>StickyNet - SUCC - USERS</title>
    <link rel='stylesheet' type='text/css' href='./stylesheet.css'>
  </head>
	<body>
		<?php
			if(!isset($usid) || !isStaff($conn, $usid))
				header("Location: index.php");
			else {
        echo logoutbutton();
        echo "<a href='./index.php'>HOME</a>";
        $users = listUsers($conn);
		?>
    <div class='top'>
      <h1><a href='./users.php' style='text-decoration: none; color: #FAFAFA;'>Sticky's Uber Cheats Collection (BETA) - USERS</a></h1>
		  <p>Note: When adding a user, use their SteamID64, NOT the normal one. (I'll fix this in a later update.)</p>
    </div>
    <div style='text-align: center; padding-bottom: 16px;'><button onclick="document.getElementById('newEv').style.display='block'">New User</button></div>

    <div id="newEv" class="modal">

      <form autocomplete='off' class="modal-content animate" method='POST' action='<?php setUser($conn) ?>'>
        <div class="container" style='text-align: center; padding-bottom: 16px;'>
          <label for="name"><b>Name</b></label>
          <input type="text" placeholder="Name of User" name="name" required>

          <label for="nusid"><b>SteamID</b></label>
          <input type="text" placeholder="SteamID64" name="nusid" required>

          <label for"rank"><b>Rank</b></label>
          <select name='rank'>
            <option>....</option>
            <option value=1>Mod</option>
            <?php
            if (isLead($conn, $usid))
            { ?>
            <option value=2>Admin</option>
          <?php } ?>
            <?php
            if (isOwner($conn, $usid))
            {
            ?>
              <option value=3>Lead Admin</option>
              <option value=4>Developer</option>
              <option value=5>Owner</option>
            <?php
            }
            ?>
          </select>

        </div>

        <div class="container" style="background-color:#f1f1f1; text-align: center; padding-bottom: 8px; padding-top: 8px;">
          <button type="submit" name='userSubmit'>Add User</button>
          <button type="button" onclick="document.getElementById('newEv').style.display='none'" style="text-align: center;">Cancel</button>
        </div>
      </form>
    </div>

    <table align='center'>
      <tr align='center'>
        <th class='name'>Name</th>
        <th>SteamID64</th>
        <th>Rank</th>
        <th class='actions'>Actions</th>
      </tr>
    <?php
    foreach($users as $data)
    {
    ?>
      <tr align='center'>
        <td><?php echo $data['name']; ?></td>
        <td><?php echo $data['usid']; ?></td>
        <td><?php echo convertRank($data['rank']); ?></td>
        <td class='actions' align='right'>
          <form autocomplete='off' method='POST' action='<?php echo setUserRank($conn); ?>'>
            <input type='hidden' name='ousid' value='<?php echo $data['usid']; ?>'>
            <select name='nrank'>
              <option>....</option>
              <?php if(permsCheck($conn, $usid, $data['usid']))
                    {
              ?>
              <option value=0>DEMOTE</option>
              <option value=1>Mod</option>
              <?php
              if (isLead($conn, $usid))
              { ?>
              <option value=2>Admin</option>
            <?php } ?>
              <?php
              if (isOwner($conn, $usid))
              {
              ?>
                <option value=3>Lead Admin</option>
                <option value=4>Developer</option>
                <option value=5>Owner</option>
              <?php
              }
              ?>
              <?php
                    }
              ?>
            </select>
            <button type='submit' name='userRankSubmit'>Change Rank</button>
          </form>
        </td>
      </tr>
  <?php
  unset($_POST['userRankSubmit']);
    }
  ?>
    </table>
  <?php
	}
	?>
  </body>
</html>
