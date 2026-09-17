<?

session_start();

include("include/connect.php");

$sql = "SELECT * FROM user WHERE username='" . $_POST['user_log'] . "' AND password='" . $_POST['pass_log'] . "'"; 

//echo $sql;

$dbquery = mysql_query($sql);

$num_rows = mysql_num_rows($dbquery);

if($num_rows==1){

   $row_array = mysql_fetch_array($dbquery);

   $status = $row_array["status"];

 // $name = $row_array["name"];

   $USER_ID = $row_array["user_id"];

   $_SESSION["USER_ID"] = $USER_ID;



   /*-------------------------------------------------------------------------------------------------------*/

	?>

   	       <form name="form1" method="post">

		   </form>

	<?



   if($status == 2){

	   ?>

	   	   <meta http-equiv="refresh" content="3;URL=agi.php?view=system">

	   <div align="center">

	   <br><br><br><br><br><br>

	   <table width="400" height="250" cellspacing="1" cellpadding="4" border="0" bgcolor="#000000">

		<tr bgcolor="#FFFFCC">

			<td align="center">

			<br>

			<font color="#FF6600" size="3">Welcome .. <?echo $name; ?> to Faculty of Agriculture, <br>Natural Resources and Environment</font>

			</td>

		</tr>

		</table>

		</div>

	   <?
} else if($status == 1) {
	?>
	   	   <meta http-equiv="refresh" content="3;URL=boys.php?view=page1">
	   <div align="center">
	   <br><br><br><br><br><br>
	   <table width="400" height="250" cellspacing="1" cellpadding="4" border="0" bgcolor="#000000">
		<tr bgcolor="#FFFFCC">
			<td align="center">
			<br>
			<font color="#FF6600" size="3">Username No Active .. Boys</font>
			</td>
		</tr>
		</table>
		</div>

	<?

   }else if($status == 0){

	   ?>

	   <meta http-equiv="refresh" content="2;URL=http://www.agi.nu.ac.th/agi2010/agi.php?view=system">

	   <div align="center">

	   <br><br><br><br><br><br>

	   <table width="400" height="250" cellspacing="1" cellpadding="4" border="0" bgcolor="#000000">

		<tr bgcolor="#FFFFCC">

			<td align="center">

			<br>

			<font color="#FF6600" size="3">Welcome <b> Administrator </b> to System</font>

			</td>

		</tr>

		</table>

		</div>

	   <?

			}

} else {

	?>

		 <meta http-equiv="refresh" content="2;URL=http://www.agi.nu.ac.th.php">

	   <div align="center">

	   <br><br><br><br><br><br>

	   <table width="400" height="250" cellspacing="1" cellpadding="4" border="0" bgcolor="#000000">

		<tr bgcolor="#FFFF99">

			<td align="center">

			<br>

			<font color='red' size='3'>Username and Password Wrong <br></font>

			</td>

		</tr>

		</table>

		</div>

	<?

}

?>