<?php include("lib/core.lib.php"); $core = new coreLIB;
if (isset($_POST['cday']))
{ $reading=$_POST['rdng']; $cday=$_POST['cday']; 

$d = cal_from_jd($cday, CAL_GREGORIAN);
$head=date("l, F j, Y", mktime(0, 0, 0, $d['month'], $d['day'], $d['year']));
$day=$core->calculateDay($d['month'], $d['day'], $d['year']);
if ($day['of_luke']) { $head .= " [{$day['of_luke']} of Luke]"; }

if ($day['fast'] || $day['fast_level']==11)
{ $head .= " <span class=\"half\">";
  if ($day['fast_level']==11) {$f=$core->fast_levels[11];}
  else
  { if ($day['fast']) {$head .= $core->fasts[$day['fast']];}
    if ($day['fast_level'] && $day['fast_level']!=10)
    { $head .= " ({$core->fast_levels[$day['fast_level']]})"; } }
  $head .= "$f</span>"; }
?><!DOCTYPE html>
<html lang="en">
<head>
<title>OCA :: Readings</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="calendar.css" />
<script type="text/javascript" src="calendar.js"></script>
</head>
<body>
<form name="exec" method="post" action="readings.php">
<input type="hidden" name="fday" value="<?php print $_POST['fday']; ?>" />
<input type="hidden" name="lday" value="<?php print $_POST['lday']; ?>" />
<input type="hidden" name="sDay" value="<?php print $_POST['sDay']; ?>" />
<input type="hidden" name="sMonth" value="<?php print $_POST['sMonth']; ?>" />
<input type="hidden" name="sYear" value="<?php print $_POST['sYear']; ?>" />
<input type="hidden" name="sWeeks" value="<?php print $_POST['sWeeks']; ?>" />
<input type="hidden" name="cday" value="" />
<input type="hidden" name="rdng" value="" />
</form>
<p><span class="rdg" onclick="cexec();">Return to calendar</span></p>

<h1<?php if ($day['fast']) {print " class=\"fast\"";} ?>><?php print $head; ?></h1>

<?php
  if (!$day['tone']) {$tone="";} else {$tone=" &mdash; Tone {$day['tone']}";}
  $pname=$day['pname'];
  $fname=$day['fname'];
  $snote=$day['snote'];
  $saint=$day['saint'];
  if ($day['tone']) {$pname .= " &mdash; Tone {$day['tone']}";}

  if ($day['feast_level']>1)
  { $f=$day['feast_level']; $img="<img src=\"lib/typikon$f.svg\" alt=\"$f\" class=\"typikon\" /> ";
    if ($saint)
    { $xa=explode("; ", $saint);
      if (count($xa)>1) {$saint=$xa[0] . "; " . $img . $xa[1];}
      else {$saint=$img . $saint;} }
    else {$fname=$img . $fname;} }

  print "<p><b>$pname.</b> ";
  if ($snote) {print "$snote. ";}
  if ($fname) {print "<b>$fname</b>. ";}
  if ($saint) {print "$saint. ";}
  if ($day['menaion_note']) {print $day['menaion_note'];}
  print "</p>\n"; ?>

<div style="font-size:18px; line-height:150%; text-align:center; padding-top: .33em;">

<?php
  $readings_list=$core->retrieveReadings($day);
  $xs=array();
  foreach ($readings_list['displays'] as $k=>$v)
  { if ($k==$reading) {$aa=""; $zz="";} else {$aa="<span class=\"rdg\" onclick=\"rexec($cday,$k);\">"; $zz="</span>";}
    if ($readings_list['descs'][$k]) {$desc=" (".$readings_list['descs'][$k].")";} else {$desc="";}
    $xs[]="$aa(" . $readings_list['nums'][$k] . ") $v$zz " . $readings_list['types'][$k] . $desc; }
  $x=implode("<br />\n", $xs); unset($xs);
  print "<p>$x</p>\n";

?>
</div>

<hr />

<h2><?php print $readings_list['displays'][$reading] . " (" . $readings_list['types'][$reading];
if ($readings_list['descs'][$reading]) {print " &ndash; ".$readings_list['descs'][$reading];}
print ")"; ?></h2>

<dl>
<?php $pericope=$core->retrievePericope($readings_list['books'][$reading], $readings_list['nums'][$reading]);
foreach ($pericope['numbers'] as $k=>$v)
{ print "<dt>$v</dt><dd>{$pericope['texts'][$k]}</dd>"; } ?>
</dl>

<hr />

<h3>Day and Paschal year:</h3>
<pre>
<?php print_r($day);
 print "\nYear:\n"; print_r($core->year);
 print "\nReadings\n"; print_r($readings_list);
 print "\nPericope\n"; print_r($pericope);
 print "\n".$core->deBug; ?>
</pre>

<p><span class="rdg" onclick="cexec();">Return to calendar</span></p>

</body>
</html>
<?php } else {print "<h3>You must come here through the proper channels!</h3>";
print_r($_POST); } ?>