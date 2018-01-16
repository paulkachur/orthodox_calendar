<?php include("lib/core.lib.php"); $core = new coreLIB;
$lday=0;
if (isset($_POST['lday']))
{ $lday=$_POST['lday']; $fday=$_POST['fday']; $weeks=$_POST['sWeeks'];
  $month=$_POST['sMonth']; $day=$_POST['sDay']; $year=$_POST['sYear']; }
elseif (isset($_POST['sWeeks']))
{ $weeks=$_POST['sWeeks'];
  $month=$_POST['sMonth']; $day=$_POST['sDay']; $year=$_POST['sYear']; }
else
{ $x = getDate(); $year=$x['year']; $month=$x['mon']; $day=$x['mday']; $weeks=6; }
if (!$lday)
{ $jd = gregoriantojd($month, $day, $year);
  $dow=date("w", mktime(0, 0, 0, $month, $day, $year));
  $w = ($weeks * 7) - 1;
  $fday = $jd - $dow;
  $lday = $fday + $w; }

$d = cal_from_jd($fday, CAL_GREGORIAN);
$month=$d['month']; $day=$d['day']; $year=$d['year'];
$ld = cal_from_jd($lday, CAL_GREGORIAN);
$xy=""; if ($d['year']<>$ld['year']) {$xy=", {$d['year']}";}
?><!DOCTYPE html>
<html lang="en">
<head>
<title>OCA :: Calendar</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="calendar.css" />
<script type="text/javascript" src="calendar.js"></script>
</head>
<body>
<form name="exec" method="post" action="readings.php">
<input type="hidden" name="fday" value="<?php print $fday; ?>" />
<input type="hidden" name="lday" value="<?php print $lday; ?>" />
<input type="hidden" name="sDay" value="<?php print $day; ?>" />
<input type="hidden" name="sMonth" value="<?php print $month; ?>" />
<input type="hidden" name="sYear" value="<?php print $year; ?>" />
<input type="hidden" name="sWeeks" value="<?php print $weeks; ?>" />
<input type="hidden" name="cday" value="" />
<input type="hidden" name="rdng" value="" />
</form>

<div style="float:right; text-align:right; width:540px;">
<form method="post" name="fatesearch" onsubmit="return verYear(this);" action="calendar.php">
<p>Start date: <select name="sMonth">
<?php $s=array("","","","","","","","","","","","","");
  $s[$month]=' selected="selected"';
  print "  <option value=\"1\"$s[1]>January</option>\n";
  print "  <option value=\"2\"$s[2]>February</option>\n";
  print "  <option value=\"3\"$s[3]>March</option>\n";
  print "  <option value=\"4\"$s[4]>April</option>\n";
  print "  <option value=\"5\"$s[5]>May</option>\n";
  print "  <option value=\"6\"$s[6]>June</option>\n";
  print "  <option value=\"7\"$s[7]>July</option>\n";
  print "  <option value=\"8\"$s[8]>August</option>\n";
  print "  <option value=\"9\"$s[9]>September</option>\n";
  print "  <option value=\"10\"$s[10]>October</option>\n";
  print "  <option value=\"11\"$s[11]>November</option>\n";
  print "  <option value=\"12\"$s[12]>December</option>\n";
?>
 </select> &nbsp; 
<select name="sDay">
<?php for ($i=1; $i<32; $i++)
  {print " <option value=\"$i\""; if ($i==$day) {print " selected=\"selected\"";} print ">$i</option>\n"; }
?>
 </select> &nbsp; 
<input type="text" name="sYear" value="<?php print $year; ?>" size="6" /> &nbsp;
 Weeks: <input type="text" name="sWeeks" value="<?php print $weeks; ?>" size="6" /> &nbsp;
<input type="submit" value="Go" /></p>
</form>
</div>

<h2><?php print "{$d['monthname']} {$d['day']}$xy - {$ld['monthname']} {$ld['day']}, {$ld['year']}"; ?></h2>

<table>
 <tr>
  <td style="width:19%;" class="dow">SUNDAY</td>
  <td style="width:13%;" class="dow">MONDAY</td>
  <td style="width:13%;" class="dow">TUESDAY</td>
  <td style="width:13%;" class="dow">WEDNESDAY</td>
  <td style="width:13%;" class="dow">THURSDAY</td>
  <td style="width:13%;" class="dow">FRIDAY</td>
  <td style="width:16%;" class="dow">SATURDAY</td>
  </tr>
<?php $c=0;
for ($i=$fday; $i<=$lday; $i++)
{ ++$c; $s=""; if ($c==1) {print "<tr>\n";}
  $d = cal_from_jd($i, CAL_GREGORIAN);
  $a = $core->calculateDay($d['month'], $d['day'], $d['year']);

  if ($a['fast']) {$s=" class=\"fast\"";}
  print "<td$s><p><span class=\"date\">{$d['monthname']} {$d['day']}</span>\n";
  if ($a['fast'] && $a['fast_level'] && $a['fast_level']!=10 || $a['fast_level']==11)
    {print "<br /><span class=\"fnote\">{$core->fast_levels[$a['fast_level']]}</span></p> ";}
  if ($a['snote']) {print "<p class=\"snote\">{$a['snote']}</p>";}
  print "<p>";
  if (!$a['tone']) {$tone="";} else {$tone=" &mdash; Tone {$a['tone']}";}
  if ($d['dow']==0 || ($a['pday'] > -9 && $a['pday'] < 7)) {print "<b>{$a['pname']}$tone.</b> ";}
  if ($a['fname']) {print "<b>{$a['fname']}.</b> ";}
  if ($a['saint']) {print "{$a['saint']}";}
  print "</p>\n";

  $xr=$core->retrieveReadings($a);

  foreach ($xr['sdisplays'] as $k=>$v)
    { $xa[]="<span class=\"rdg\" onclick=\"rexec($i,$k);\">$v</span>"; }
  $x=implode("<br />\n", $xa); unset($xa);
  print "<p>$x</p>\n";

  print "</td>\n";
  if ($c==7) {print "</tr>\n"; $c=0;} 
 } ?>
</table>

<form method="post" name="datesearch" onsubmit="return verYear(this);" action="calendar.php">
<p>Start date: <select name="sMonth">
<?php $s=array("","","","","","","","","","","","","");
  $s[$month]=' selected="selected"';
  print "  <option value=\"1\"$s[1]>January</option>\n";
  print "  <option value=\"2\"$s[2]>February</option>\n";
  print "  <option value=\"3\"$s[3]>March</option>\n";
  print "  <option value=\"4\"$s[4]>April</option>\n";
  print "  <option value=\"5\"$s[5]>May</option>\n";
  print "  <option value=\"6\"$s[6]>June</option>\n";
  print "  <option value=\"7\"$s[7]>July</option>\n";
  print "  <option value=\"8\"$s[8]>August</option>\n";
  print "  <option value=\"9\"$s[9]>September</option>\n";
  print "  <option value=\"10\"$s[10]>October</option>\n";
  print "  <option value=\"11\"$s[11]>November</option>\n";
  print "  <option value=\"12\"$s[12]>December</option>\n";
?>
 </select> &nbsp; 
<select name="sDay">
<?php for ($i=1; $i<32; $i++)
  {print " <option value=\"$i\""; if ($i==$day) {print " selected=\"selected\"";} print ">$i</option>\n"; }
?>
 </select> &nbsp; 
<input type="text" name="sYear" value="<?php print $year; ?>" size="6" /> &nbsp;
 Weeks: <input type="text" name="sWeeks" value="<?php print $weeks; ?>" size="6" /> &nbsp;
<input type="submit" value="Go" /></p>
</form>


</body>
</html>
