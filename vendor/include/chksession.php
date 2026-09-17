<?
session_start();
if ($_SESSION["USER_ID"] == "") 
{
	echo "<meta http-equiv=\"Refresh\" content=\"1;url=../index.php\" />";
	exit();
}
?>