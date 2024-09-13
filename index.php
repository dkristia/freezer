<html>
<link rel="stylesheet" href="/styles.css">

</html>
<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET');
$conn = @pg_connect("dbname=pakastin");
if (!$conn) {
	echo "the database is fucked";
	echo (pg_last_error());
	exit;
}
$owners = @pg_query($conn, "select distinct on (owner) owner from manifest order by owner;");
if (!$owners) {
	echo "the query is fucked";
	echo (pg_last_error($conn));
	exit;
}
echo "<h3>Pakastimen sisällysluettelo</h3>";
echo "<ul>";
while ($owner = pg_fetch_row($owners)) {
	echo "<li><p class='owner'>".htmlspecialchars($owner[0])."</p><ul>";
	/** @psalm-suppress MixedAssignment */
	$ownerid = pg_escape_literal($owner[0]);
	$belongings = @pg_query($conn, "select amount,item from manifest where owner=$ownerid order by amount desc;");
	while ($stuff = pg_fetch_row($belongings)) {
		echo "<li class='pakastin-item' owner='".htmlspecialchars($owner[0])."' amount='".htmlspecialchars($stuff[0])."' item-name='".htmlspecialchars($stuff[1])."'>".htmlspecialchars($stuff[0])."x ".htmlspecialchars($stuff[1])."</li>";
	}
	echo "</ul></li>";
}
echo "</ul>";
?>
