<?php
/** @psalm-suppress PossiblyUndefinedArrayOffset */
if ($_SERVER["REQUEST_METHOD"]==="OPTIONS") {
	exit;
}

$conn = @pg_connect("dbname=pakastin");
if (!$conn || !array_key_exists("key",$_GET)) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}
/** @psalm-suppress MixedArgument */
$hash = hash("sha256", htmlspecialchars($_GET["key"]));
/** @psalm-suppress MixedAssignment */
$hash1 = pg_escape_literal($hash);
$access = pg_query($conn, "select * from access where \"key\"=$hash1;");
if (!(pg_fetch_row($access)[0])) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}
/** @psalm-suppress MixedAssignment */
$owner = pg_escape_literal($_GET["name"]);
/** @psalm-suppress MixedAssignment */
$item = pg_escape_literal($_GET["item"]);
if (!is_numeric($_GET["amount"])) {
	exit;
}
$existing = pg_query($conn, "select * from manifest where lower(owner)=lower($owner) and lower(item)=lower($item);");
/** @psalm-suppress MixedAssignment */
$amount = pg_escape_literal($_GET["amount"]);
if ($_GET["amount"]==0) {
	pg_query($conn, "delete from manifest where lower(owner)=lower($owner) and lower(item)=lower($item);");
}
elseif (!$existing || !pg_fetch_row($existing)[0]) {
	pg_query($conn, "insert into manifest values ($owner,$item,$amount);");
}
else {
	/** @psalm-suppress MixedAssignment */
	$stuff = pg_fetch_row($existing)[0];
	pg_query($conn, "update manifest set amount=$amount where lower(owner)=lower($owner) and lower(item)=lower($item);");
	pg_query($conn, "update manifest set owner=$owner where lower(owner)=lower($owner);");
}
include "index.php";
?>
