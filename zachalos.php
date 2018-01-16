<?php include("lib/core.lib.php"); $core = new coreLIB;
if (isset($_POST['book'])) {$book=$_POST['book'];} else {$book="Matthew";}
if (isset($_POST['zach'])) {$zach=$_POST['zach'];} else {$zach="";}
$err=""; ?>
<html>
<head>
<title>Zachalo test</title>
<style type="text/css">
dt { float: left; clear: left; width:3.33em; }
dd { margin: 0 0 0 3.5em; }
.controls {font-size: 18px; line-height:120%;}
.symbols {font-size: 24px; line-height:120%;}
.typikon {height:1.12em; vertical-align: -.24em;}
</style>
<script type="text/javascript">
var xmlr = false;
var msxmlhttp = new Array(
	'Msxml2.XMLHTTP',
	'Microsoft.XMLHTTP');
for (var i = 0; i < msxmlhttp.length; i++)
{ try { xmlr = new ActiveXObject(msxmlhttp[i]); }
  catch (e) { xmlr = null; } }
if(!xmlr && typeof XMLHttpRequest != "undefined")
	xmlr = new XMLHttpRequest();
if (!xmlr) alert("Could not create connection object.");

function reDrop(b)
{ var url = "zachsget.php?book=" + b;
  xmlr.open("GET", url, true);
  xmlr.onreadystatechange = reZach;
  xmlr.send(null); return false; }	 

function reZach()
{ if (xmlr.readyState == 4)
  { var ll = xmlr.responseText.split("@");
    var oz=document.getElementById("zdrop");
    oz.length=0;
    for (i=0; i<ll.length; i++)
    { var a=ll[i].split("||");
      var s = a[0] + " (" + a[1] + ")";
      var nOpt = new Option(s);
      oz.options[i] = nOpt;
      oz.options[i].value = a[0]; } } }
</script>
</head>
<body>

<form method="post" action="<?php print $_SERVER['PHP_SELF']; ?>">
<select name="book" class="controls" onchange="reDrop(this.value);">
  <option value="Matthew"<?php if ($book=="Matthew") {print " selected";} ?>>Matthew</option>
  <option value="Mark"<?php if ($book=="Mark") {print " selected";} ?>>Mark</option>
  <option value="Luke"<?php if ($book=="Luke") {print " selected";} ?>>Luke</option>
  <option value="John"<?php if ($book=="John") {print " selected";} ?>>John</option>
  <option value="Apostol"<?php if ($book=="Apostol") {print " selected";} ?>>Apostol</option>
  <option value="OT"<?php if ($book=="OT") {print " selected";} ?>>OT</option>
</select> &nbsp; &nbsp;
<select name="zach" class="controls" id="zdrop" onchange="this.form.submit();">
<?php $q="select * from zachalos where zaBook='$book' order by zaId";
  $r = $core->query($q);
  while ($w=$core->fetch($r))
  { print "  <option value=\"$w[zaNum]\"";
    if ($w['zaNum']==$zach) {print " selected";}
    print ">$w[zaNum] ($w[zaSdisplay])</option>\n"; }
?></select> &nbsp; &nbsp; <input type="submit" class="controls" value="Go" /> 
</form>

<?php
if ($book && $zach)
{ $xa=$core->retrievePericope($book, $zach);
  print "<h2>($zach) $book : $xa[display] ($xa[desc])</h2>";
  $arrv = $xa['numbers']; $arrt = $xa['texts'];
  print "<dl>\n";
  foreach($arrv as $k=>$v)
  { print "<dt>$v</dt><dd>".$arrt[$k]."</dd>"; }
  print "</dl>\n"; }
?>

<pre>
<?php // print_r($xa);
 ?>
</pre>

<!--div class="symbols">
<p>This is a <img src="lib/typikon2.svg" class="typikon" /> level two!</p>

<p>This is a <img src="lib/typikon3.svg" class="typikon" /> level three!</p>

<p>This is a <img src="lib/typikon4.svg" class="typikon" /> level four!</p>

<p>This is a <img src="lib/typikon5.svg" class="typikon" /> level five!</p>

<p>This is a <img src="lib/typikon6.svg" class="typikon" /> level six!</p>
</div>

</body-->
</html>
