<?php include('core.config.php');
class coreLIB
  {     var $dc;
	var $deBug;
	var $fast_levels;
	var $fasts;
	var $current_year=false;
	var $year=false;
	var $do_jump=0;

function __construct()
  { GLOBAL $core_var;
	if ($core_var['do_lucan_jump']) {$this->do_jump=1;}

	date_default_timezone_set($core_var['timezone']);
	$this->dc = new mysqli($core_var['mysql_host'], $core_var['mysql_user'], $core_var['mysql_password'], $core_var['mysql_database']);

	$this->fast_levels=array();
	foreach ($core_var['fast_levels'] as $k=>$v)  {$this->fast_levels[$k]=$v;}
	$this->fasts=array();
	foreach ($core_var['fasts'] as $k=>$v)  {$this->fasts[$k]=$v;}

	unset($core_var); }

// mysqli functions
// if you are using a different sql syntax you can make all changes here
  function query($q)
    { return $this->dc->query($q); }
  function query_fetch($q)
    { $r = $this->query($q); return $r->fetch_array(); }
  function fetch($r)
    { return $r->fetch_array(); }
  function num_rows($r)
    { return $r->num_rows; }


// returns jd (Julian Day Number) of Pascha for a given year
// jd is not to be confused with Julian Calendar!
  function calculatePascha($y)
    { $jg=0;
	if ($y>1582)
	{ $jg=10; $x = floor($y/100) -16;
	  if ($x>0) { $jg += (floor($x/4)*3) + $x % 4; } }
	$a = $y % 4; $b = $y % 7; $c = $y % 19; $z = ((19 * $c) + 15) % 30;
	$e = ((2 * $a) + (4 * $b) - $z + 34) % 7;
	$m = floor(($z + $e + 114) / 31); $d = (($z + $e + 114) % 31) + 1;
	return $jg+gregoriantojd($m,$d,$y); }

// get day of week (0-6) from pday
  function dow($pday)
    { return (7 + ($pday % 7)) % 7; }

// get saturday/sunday before/after
  function ssba($pday, $wday)
    { $arr = array();
	$arr[0] = $pday - $wday - 1;
	$arr[1] = $pday - 7 + ((7 - $wday) % 7);
	$arr[2] = $pday + 7 - (($wday + 1) % 7);
	$arr[3] = $pday + 7 - $wday;
	return $arr; }

  function retrieveXceptions($m, $d, $y)
    { $arr=array($m, $d, "", 0);
      $q="select * from xceptions where xcYear='$y' and xcMonth='$m' and xcDay='$d'";
      $r=$this->query($q);
      if ($w = $r->fetch_array())
	{ $arr[0]=$w['xcNewMonth']; $arr[1]=$w['xcNewDay']; $arr[2]=$w['xcNote'];
	  if ($arr[1] < 99)
	  { $q = "select * from days where daMonth='{$arr[0]}' and daDay='{$arr[1]}'";
	    $w=$this->query_fetch($q);
	    $arr[3] = $w['daFexc']; } }
      return $arr; }

  function calculatePent($p)
    { $w = floor($p/7)-6; $d = $p % 7; if ($d==0) {--$w;}
	$o = $w % 10;
	if ($o==1) { if ($w>10 && $w<20) {$s=$w."th";} else {$s=$w."st";} }
	elseif ($o==2) { if ($w>10 && $w<20) {$s=$w."th";} else {$s=$w."nd";} }
	elseif ($o==3) { if ($w>10 && $w<20) {$s=$w."th";} else {$s=$w."rd";} }
	else { $s=$w."th"; }
	if ($d==0) {$s = "$s Sunday after Pentecost";}
	elseif ($d==1) {$s = "Monday of the $s week after Pentecost";}
	elseif ($d==2) {$s = "Tuesday of the $s week after Pentecost";}
	elseif ($d==3) {$s = "Wednesday of the $s week after Pentecost";}
	elseif ($d==4) {$s = "Thursday of the $s week after Pentecost";}
	elseif ($d==5) {$s = "Friday of the $s week after Pentecost";}
	elseif ($d==6) {$s = "Saturday of the $s week after Pentecost";}
	return $s; }

  function calculateFasting($fast, $level, $feast_level, $dow, $pday, $year)
    { $arr=array(); $arr[0]=$fast; $arr[1]=$level; $arr[2]="";
	if ($level == 11) { $arr[0] = 0; $arr[1] = 11; return $arr; }
// Lent remove fish for minor feasts
	if ($fast == 2)
	  { if ($level == 2) { $arr[1] = 1; return $arr; } }
// Dormition 
	if ($fast == 4)
	  { if (($dow==0 || $dow==6) && $level == 0) { $arr[1] = 1; return $arr; } }
// are we in apostles fast
	if ($pday > 56 && $pday < $year['peterpaul'])
	  { $fast=3; $arr[0]=3;
	    if ($pday == 57 && $pday < $year['peterpaul'])
	      { $arr[2] = "Beginning of Apostles&rsquo; Fast"; } }
// Apostles & Nativity
	if ($fast == 3 || $fast == 5)
	  { if ($dow==2 || $dow==4)
	    { if (!$level) { $level = 1; } }
	    elseif ($dow==3 || $dow==5)
	    { if ($feast_level < 4 && $level > 1) { $level = 1; } }
	    elseif ($dow==0 || $dow==6) { $level = 2; }
	    if ( $pday > ($year['nativity']-6) && $pday < ($year['nativity']-1) && $level > 1)
	      { $level = 1; } }

	if (($pday == ($year['nativity']-1) || $pday == ($year['theophany']-1)) && ($dow == 0 || $dow == 6))
	  { $level = 1; }
	$arr[1]=$level; return $arr; }


  function calculateDay($m=0, $d=0, $y=0)
    { $arr=array(); // this will hold all return values
	if (!$m || !$d || !$y)
	{ $x = getDate(); $y=$x['year']; $m=$x['mon']; $d=$x['mday']; }
// get pday of current year
// if < -77 use previous year and get new pday
	$pyear = $y;
	$jd = gregoriantojd($m,$d,$y);
        $pday = $jd - $this->calculatePascha($y);
	if ($pday < -77)
	{ --$pyear; $pday = $jd - $this->calculatePascha($y-1); }
	$dow = $this->dow($pday);
// get year variables
	if ($this->current_year != $pyear)
	  { $this->year = $this->calculateYear($pyear);
	    $this->current_year = $pyear; }
	$year = $this->year;
// nday is pday relative to next pascha, used for theophany stepback
// vday is pday relative to last pascha, for tones and matins gospels before Palm Sunday
	$nday = $jd - $year['next_pascha_jd'];
	$vday = $jd - $year['previous_pascha_jd'];
// gday is adjusted pday for gospel reading, eday for epistle
	if ($pday>$year['sun_aft_elevation'] && $this->do_jump) { $jump=$year['lucan_jump']; } else { $jump=0; }

	$ld=$pday + $jump;
	if ($ld > 168 && $ld < 274 && $pday < $year['nativity'] && $dow == 0)
	  { $x=$ld-168; $ofluke=floor($x/7); } else { $ofluke=0; }

	if (in_array($pday, $year['nodaily']))
	{ $gday=499; $eday=499; }
	else
	{ $limit=272;
	  if ($pday==252) { $eday = $year['forefathers']; } // forefathers swap
	    elseif ($pday > $limit) { $eday = $nday; } // theophany stepback
	    else { $eday = $pday; }
	  if ($year['theophany_weekday']<2) { $limit=279; }
	  if ($pday==(245-$year['lucan_jump'])) { $gday = $year['forefathers']+$year['lucan_jump']; }
	    elseif ($pday > $year['sun_aft_theophany'] && $dow==0 && $year['extra_sundays'] > 1)
	      { $i = ($pday - $year['sun_aft_theophany']) / 7;
		$gday=$year['reserves'][$i-1]; }
	    elseif ($pday+$jump > $limit) { $gday = $nday; } // theophany stepback
	    else { $gday = $pday+$jump; } }
// if today is a floating feast
	$fday = array_search($pday, $year['floats']);
	if ($m==1 && $d>24 && $dow==0) {$fday=1031;}
// if today is is involved in a lenten paremia swap
	if (array_search($pday, $year['noparemias'])) {$noparemias=true;} else {$noparemias=false;}
	if (array_search($pday, $year['getparemias'])) {$getparemias=true;} else {$getparemias=false;}
// if 2/28 in non-leapyear we need John Cassian (but rubrics book does not add readings !!!)
	if ($m==2 && $d==28 && date('L', mktime(0,0,0,2,28,$y))==0) {$twentynine=true;}
	  else {$twentynine=false;}
// check if memorial saturday is cancelled
	if (($pday == -36 || $pday == -29 || $pday == -22) && $m==3 && ($d==9 || $d==24 || $d==25 || $d==26)) {$nomem=true;} else {$nomem=false;}
// check if feast is arbitrarily moved
	$xa = $this->retrieveXceptions($m, $d, $y);
	$menaion_month=$xa[0];
	$menaion_day=$xa[1];
	$menaion_note=$xa[2];
	$menaion_fexc=$xa[3];
// get records for pday and date from days table
	if ($fday && $fday != 499) {$xf = "or daPday='$fday'";} else {$xf="";}
	$q = "select * from days where (daPday='$pday' $xf) or (daMonth='$m' and daDay='$d')";

// $this->deBug=$q;

	$r = $this->query($q);
	$xp=array(); $xf=array(); $xn=array(); $xs=array();
	$pname=""; $fname=""; $snote=""; $kata="";
	$service=0; $feast_level=-2; $saint_level=0; $fast=0; $fast_level=0;
	while ($w=$this->fetch($r))
	{ if ($w['daPsub']) { $w['daPname'] .= ": " . $w['daPsub']; }
	  if ($w['daPname']) { $xp[]=$w['daPname']; }
	  if ($w['daFname'] && (!$nomem || $w['daFname'] != "Memorial Saturday")) { $xf[]=$w['daFname']; }
	  if ($w['daFlevel'] > $feast_level) { $feast_level=$w['daFlevel']; }
	  if ($w['daSlevel'] > $saint_level) { $saint_level=$w['daSlevel']; }
	  if ($w['daService'] > $service) { $service=$w['daService']; }
	  if ($w['daSnote'] && $pday != $year['annunciation']) { $xn[]=$w['daSnote']; }
	  if ($w['daSaint']) { $xs[]=$w['daSaint']; }
	  if ($w['daFast'] > $fast) { $fast=$w['daFast']; }
	  if ($w['daFexc'] > $fast_level) { $fast_level=$w['daFexc']; }
	  if (!$kata) { $kata=$w['daKatavasia']; } }
	if ($menaion_fexc > $fast_level) { $fast_level=$menaion_fexc; }
	if ($noparemias && ($dow==2 || $dow==4)) { $xn[]="Presanctified Liturgy"; }
	if (count($xp)>1) { $pname=implode("; ", $xp); } elseif (count($xp)) { $pname=$xp[0]; }
	if (count($xf)>1) { $fname=implode("; ", $xf); } elseif (count($xf)) { $fname=$xf[0]; }
	if (count($xn)>1) { $snote=implode("; ", $xn); } elseif (count($xn)) { $snote=$xn[0]; }
	if (count($xs)>1) { $saint=implode("; ", $xs); } elseif (count($xs)) { $saint=$xs[0]; }
	if ($dow < 1 || $feast_level > 2 || $saint_level > 2)
	{ if (!$kata) { $kata="&ldquo;I will open my mouth...&rdquo;"; } }
	else { $kata=""; }
// calculate sunday tone
	if ($pday < 0) { $pbase=$vday; } else { $pbase=$pday; }
	if ($pday > -9 && $pday < 7) { $tone=0; }
	  else
	  { $x = $pbase % 56; if ($x==0) {$x=56;}
	    $tone = floor($x/7); }
// calculate sunday matins gospel
	$no_matins_gospel=false;
	$matins_gospel=0;
	if ($dow==0)
	{ if ($pday > -8 && $pday < 50) { $no_matins_gospel=true; }
	  elseif ($feast_level < 7)
	  { $no_matins_gospel=true;
	    $mbase = $pbase - 49;
	    $x = $mbase % 77; if ($x==0) {$x=77;}
	    $matins_gospel = floor($x/7); } }
// calculate pent out of range
	if ($pday < -48)
	{ $pent=$this->calculatePent($pbase);
	  if ($pname) { $pname = $pent . ": " . $pname; } else { $pname = $pent; } }
// get fasting
	$xa = $this->calculateFasting($fast, $fast_level, $feast_level, $dow, $pday, $year);
	$fast = $xa[0]; $fast_level = $xa[1];
	if ($xa[2]) { if ($snote) { $snote = $xa[2] . ": " . $snote; } else { $snote = $xa[2]; } }
// load vars into arr
	$arr['month'] = $m;
	$arr['day'] = $d;
	$arr['year'] = $y;
	$arr['pyear'] = $pyear;
	$arr['jd'] = $jd;
	$arr['pday'] = $pday;
	$arr['dow'] = $dow;
	$arr['nday'] = $nday;
	$arr['vday'] = $vday;
	$arr['gday'] = $gday;
	$arr['eday'] = $eday;
	$arr['jump'] = $jump;
	$arr['of_luke'] = $ofluke;
	$arr['fday'] = $fday;
	$arr['twentynine'] = $twentynine;
	$arr['no_memorial'] = $nomem;
	$arr['menaion_month'] = $menaion_month;
	$arr['menaion_day'] = $menaion_day;
	$arr['menaion_note'] = $menaion_note;
	$arr['pname'] = $pname;
	$arr['fname'] = $fname;
	$arr['snote'] = $snote;
	$arr['saint'] = $saint;
	$arr['service'] = $service;
	$arr['feast_level'] = $feast_level;
	$arr['saint_level'] = $saint_level;
	$arr['fast'] = $fast;
	$arr['fast_level'] = $fast_level;
	$arr['pbase'] = $pbase;
	$arr['tone'] = $tone;
	$arr['no_matins_gospel'] = $no_matins_gospel;
	$arr['matins_gospel'] = $matins_gospel;
	$arr['no_paremias'] = $noparemias;
	$arr['get_paremias'] = $getparemias;
	$arr['katavasia'] = $kata;
	return $arr; }

/*
The heart of this system is the concept of a "paschal year," which begins with Zacchaeus Sunday and continues to the next one. Within the paschal year, every day has an integer value based on its relation to Pascha, which we call the "pday." The pday of Pascha is 0, the pday of Palm Sunday is -7, the pday of Ascension is 39, the pday of Pentecost is 49, etc. This function will return pdays (and other info) of significant days of the paschal year.
*/
  function calculateYear($y)
    { $arr=array(); // this will hold all return values
	$reserves=array(); // Sunday gospels held for Theophany stepback
	$floats=array(); // "floating" feasts
	$nodaily=array("499"); // daily readings are suppressed
	$noparemias=array("499"); // see below
	$getparemias=array("499"); // see below
	$arr['id'] = $y;
	$pascha = $this->calculatePascha($y); $arr['pascha_jd'] = $pascha;

// get pdays
	$arr['finding'] = gregoriantojd(2,24,$y) - $pascha;
	$arr['annunciation'] = gregoriantojd(3,25,$y) - $pascha;
	  $arr['annunciation_weekday'] = $this->dow($arr['annunciation']);
	$arr['peterpaul'] = gregoriantojd(6,29,$y) - $pascha;
	$arr['beheading'] = gregoriantojd(8,29,$y) - $pascha;
	$arr['nativity_theotokos'] = gregoriantojd(9,8,$y) - $pascha;
	$arr['elevation'] = gregoriantojd(9,14,$y) - $pascha;
	  $arr['elevation_weekday'] = $this->dow($arr['elevation']);
	$xa = $this->ssba($arr['elevation'], $arr['elevation_weekday']);
	  $arr['sat_bef_elevation'] = $xa[0];
	  $arr['sun_bef_elevation'] = $xa[1];
	  $arr['sat_aft_elevation'] = $xa[2];
	  $arr['sun_aft_elevation'] = $xa[3];
	$arr['lucan_jump'] = 168 - $arr['sun_aft_elevation'];
	$j = gregoriantojd(7,16,$y) - $pascha; $wd = $this->dow($j);
	  if ($wd < 4) { $j -= $wd; } else { $j += 7 - $wd; }
	  $arr['fathers_six'] = $j;
	$j = gregoriantojd(10,11,$y) - $pascha; $wd = $this->dow($j);
	  if ($wd > 0) { $j += 7 - $wd; }
	  $arr['fathers_seven'] = $j;
	$j = gregoriantojd(10,26,$y) - $pascha; $wd = $this->dow($j);
	  $arr['demetrius_saturday'] = $j - $wd - 1;
	$j = gregoriantojd(11,1,$y) - $pascha; $wd = $this->dow($j);
	  $arr['synaxis_unmercenaries'] = $j + 7 - $wd;
	$arr['nativity'] = gregoriantojd(12,25,$y) - $pascha;
	  $arr['nativity_weekday'] = $this->dow($arr['nativity']);
	$arr['forefathers'] = $arr['nativity'] - 14 + ((7 - $arr['nativity_weekday']) % 7);
	$xa = $this->ssba($arr['nativity'], $arr['nativity_weekday']);
	  $arr['sat_bef_nativity'] = $xa[0];
	  $arr['sun_bef_nativity'] = $xa[1];
	  $arr['sat_aft_nativity'] = $xa[2];
	  $arr['sun_aft_nativity'] = $xa[3];
	$arr['theophany'] = gregoriantojd(1,6,$y+1) - $pascha;
	  $arr['theophany_weekday'] = $this->dow($arr['theophany']);
	$xa = $this->ssba($arr['theophany'], $arr['theophany_weekday']);
	  $arr['sat_bef_theophany'] = $xa[0];
	  $arr['sun_bef_theophany'] = $xa[1];
	  $arr['sat_aft_theophany'] = $xa[2];
	  $arr['sun_aft_theophany'] = $xa[3];

// assemble floats ***see reference for index numbers
	for ($i=1001; $i<1038; $i++) {$floats[$i]=499;}
	$floats[1001] = $arr['fathers_six'];
	$floats[1002] = $arr['fathers_seven'];
	$floats[1003] = $arr['demetrius_saturday'];
	$floats[1004] = $arr['synaxis_unmercenaries'];
	if ($arr['sat_bef_elevation']==$arr['nativity_theotokos']) {$floats[1005] = $arr['elevation'] - 1;}
	  else {$floats[1006] = $arr['sat_bef_elevation'];}
	$floats[1007] = $arr['sun_bef_elevation'];
	$floats[1008] = $arr['sat_aft_elevation'];
	$floats[1009] = $arr['sun_aft_elevation'];
	$floats[1010] = $arr['forefathers'];
	if ($arr['sat_bef_nativity']==$arr['nativity']-1)
	  { $floats[1013]=$arr['nativity']-2;
	    $floats[1012]=$arr['sun_bef_nativity'];
	    $floats[1015]=$arr['nativity']-1; }
	  elseif ($arr['sun_bef_nativity']==$arr['nativity']-1)
	  { $floats[1013]=$arr['nativity']-3;
	    $floats[1011]=$arr['sat_bef_nativity'];
	    $floats[1016]=$arr['nativity']-1; }
	  else
	  { $floats[1014]=$arr['nativity']-1;
	    $floats[1011]=$arr['sat_bef_nativity'];
	    $floats[1012]=$arr['sun_bef_nativity']; }
	if ($arr['nativity_weekday']==0)
	  { $floats[1017]=$arr['sat_aft_nativity'];
	    $floats[1020]=$arr['nativity']+1;
	    $floats[1024]=$arr['sun_bef_theophany'];
	    $floats[1026]=$arr['theophany']-1; }
	  elseif ($arr['nativity_weekday']==1)
	  { $floats[1017]=$arr['sat_aft_nativity'];
	    $floats[1021]=$arr['sun_aft_nativity'];
	    $floats[1023]=$arr['theophany']-5;
	    $floats[1026]=$arr['theophany']-1; }
	  elseif ($arr['nativity_weekday']==2)
	  { $floats[1019]=$arr['sat_aft_nativity'];
	    $floats[1021]=$arr['sun_aft_nativity'];
	    $floats[1027]=$arr['sat_bef_theophany'];
	    $floats[1023]=$arr['theophany']-5;
	    $floats[1025]=$arr['theophany']-2; }
	  elseif ($arr['nativity_weekday']==3)
	  { $floats[1019]=$arr['sat_aft_nativity'];
	    $floats[1021]=$arr['sun_aft_nativity'];
	    $floats[1022]=$arr['sat_bef_theophany'];
	    $floats[1028]=$arr['sun_bef_theophany'];
	    $floats[1025]=$arr['theophany']-3; }
	  elseif ($arr['nativity_weekday']==4 || $arr['nativity_weekday']==5)
	  { $floats[1019]=$arr['sat_aft_nativity'];
	    $floats[1021]=$arr['sun_aft_nativity'];
	    $floats[1022]=$arr['sat_bef_theophany'];
	    $floats[1024]=$arr['sun_bef_theophany'];
	    $floats[1026]=$arr['theophany']-1; }
	  elseif ($arr['nativity_weekday']==6)
	  { $floats[1018]=$arr['nativity']+6;
	    $floats[1021]=$arr['sun_aft_nativity'];
	    $floats[1022]=$arr['sat_bef_theophany'];
	    $floats[1024]=$arr['sun_bef_theophany'];
	    $floats[1026]=$arr['theophany']-1; }
	$floats[1029] = $arr['sat_aft_theophany'];
	$floats[1030] = $arr['sun_aft_theophany'];
	$no_daily_ann=false;
	if ($arr['annunciation_weekday']==6)
	  { $floats[1032]=$arr['annunciation']-1;
	    $floats[1033]=$arr['annunciation'];
	    $no_daily_ann=true; }
	elseif ($arr['annunciation_weekday']==0)
	  { $floats[1034]=$arr['annunciation']; }
	elseif ($arr['annunciation_weekday']==1)
	  { $floats[1035]=$arr['annunciation']; }
	else
	  { $floats[1036]=$arr['annunciation']-1;
	    $floats[1037]=$arr['annunciation']; }

// assemble nodaily
	$nodaily[]=$arr['sun_bef_theophany'];
	$nodaily[]=$arr['sun_aft_theophany'];
	$nodaily[]=$arr['theophany']-5;

	if ($arr['sat_bef_theophany'] != ($arr['theophany']-1))
	{ $nodaily[]=$arr['theophany']-1; }

	$nodaily[]=$arr['theophany'];
	if ($arr['sat_aft_theophany']==$arr['theophany']+1) {$nodaily[]=$arr['theophany']+1;}
//	if ($arr['finding']==-43) {$nodaily[]=$arr['finding'];}
	$nodaily[]=$arr['forefathers'];
	$nodaily[]=$arr['sun_bef_nativity'];
	$nodaily[]=$arr['nativity']-1;
	$nodaily[]=$arr['nativity'];
	$nodaily[]=$arr['nativity']+1;
	$nodaily[]=$arr['sun_aft_nativity'];
	if ($no_daily_ann) {$nodaily[]=$arr['annunciation'];}

// assemble reserves, first get previous and next paschas
	$arr['previous_pascha_jd'] = $this->calculatePascha($y-1);
	$arr['previous_pascha'] = $arr['previous_pascha_jd'] - $pascha;
	$arr['next_pascha_jd'] = $this->calculatePascha($y+1);
	$arr['next_pascha'] = $arr['next_pascha_jd'] - $pascha;
	$x = $arr['next_pascha'] - 84;
	$i = floor(($x - $arr['sun_aft_theophany']) / 7); $arr['extra_sundays'] = $i;
	if ($i)
	{ $z = $arr['forefathers'] + $arr['lucan_jump'] + 7;
	  for ($x=$z; $x<=266; $x+=7) {$reserves[]=$x;}
	  $i -= count($reserves);
	  if ($i>0)
	  { $x = 175 - ($i*7); for ($i=$x; $i<169; $i+=7) {$reserves[]=$i;} } }

// minor feasts on weekdays in lent have their paremias moved to previous day 
	$pamonths=array(2, 2, 3, 3, 4, 4, 4, 4);
	$padays=array(24, 27, 9, 31, 7, 23, 25, 30);
	foreach ($pamonths as $k=>$v)
	  { $xp = gregoriantojd($v,$padays[$k],$y) - $pascha; $xd=$this->dow($xp);
	    if ($xp > -44 && $xp < -7 && $xd > 1)
	      { $noparemias[] = $xp; $getparemias[] = $xp - 1; } }

// add other arrays
	$arr['reserves']=$reserves;
	$arr['floats']=$floats;
	$arr['nodaily']=$nodaily;
	$arr['noparemias']=$noparemias;
	$arr['getparemias']=$getparemias;
	return $arr; }

  function retrieveReadings($day)
    { $arr=array();
	$conditions=array();
	$types=array();
	$descs=array();
	$books=array();
	$nums=array();
	$idx=array();
// assemble conditions for sql
	if ($day['no_memorial']) {$nomem=" and reDesc != 'Departed'";} else {$nomem="";}
	if ($day['gday'] != 499)
	  { $conditions[]="(rePday = {$day['gday']} and reType = 'Gospel' $nomem)"; }
	if ($day['eday'] != 499)
	  { $conditions[]="(rePday = {$day['eday']} and reType = 'Epistle' $nomem)"; }
	$conditions[]="(rePday = {$day['pday']} and reType != 'Epistle' and reType !='Gospel')";
	if ($day['fday'] && $day['fday'] != 499)
	  { $conditions[]="(rePday = {$day['fday']})"; }
	if ($day['matins_gospel'])
	  { $mg = $day['matins_gospel']+700; $conditions[]="(rePday = $mg)"; }
	if ($day['no_matins_gospel']) {$x="and reType != 'Matins Gospel'";} else {$x="";}
	if ($day['no_paremias']) {$y="and reType != 'Vespers'";} else {$y="";}
// no readings for leavetaking annunciation on non-liturgy day
	if ($day['month']==3 && $day['day']==26 && ($day['dow']==1 || $day['dow']==2 || $day['dow']==4))
	{$z="and reDesc != 'Theotokos'";} else {$z="";}
	  $conditions[]="((reMonth = {$day['menaion_month']} and reDay = {$day['menaion_day']}) $y $x $z)"; 
	if ($day['get_paremias'])
	  { $pa=getdate(mktime(0, 0, 0, $day['month'], $day['day']+1, $day['year']));
	    $conditions[]="(reMonth = {$pa['mon']} and reDay = {$pa['mday']} and reType = 'Vespers')"; }
// make sql
	$conds = implode(" or ", $conditions);
	$q = "select readings.*, zachalos.zaDisplay as display, zachalos.zaSdisplay as sdisplay from readings left join zachalos on (zachalos.zaBook=readings.reBook and zachalos.zaNum=readings.reNum) where $conds order by reIndex";

// $this->deBug=$q;

	$r = $this->query($q);
	while ($w = $this->fetch($r))
	  { $types[] = $w['reType'];
	    $descs[] = $w['reDesc'];
	    $books[] = $w['reBook'];
	    $displays[] = $w['display'];
	    $sdisplays[] = $w['sdisplay'];
	    $nums[] = $w['reNum'];
	    $idx[] = $w['reIndex']; }
// if Saturday bef Theophany is Eve, move daily readings
	if ($day['fday'] == 1027)
	  { $i = array_search("811", $idx);
	    $j = array_search("911", $idx);
	        $xt=array($types[0], $types[1], $types[$i], $types[$j]);
	        $xd=array($descs[0], $descs[1], $descs[$i], $descs[$j]);
	        $xb=array($books[0], $books[1], $books[$i], $books[$j]);
	        $xy=array($displays[0], $displays[1], $displays[$i], $displays[$j]);
	        $xs=array($sdisplays[0], $sdisplays[1], $sdisplays[$i], $sdisplays[$j]);
	        $xn=array($nums[0], $nums[1], $nums[$i], $nums[$j]);
	        $xi=array($idx[0], $idx[1], $idx[$i], $idx[$j]); 
		foreach ($types as $k=>$v)
		{ if ($k > 1 && $k != $i && $k != $j)
		  { $xt[]=$types[$k];
		    $xd[]=$descs[$k];
		    $xb[]=$books[$k];
		    $xy[]=$displays[$k];
		    $xs[]=$sdisplays[$k];
		    $xn[]=$nums[$k];
		    $xi[]=$idx[$k]; } }
		$types=$xt;
	        $descs=$xd;
	        $books=$xb;
	        $displays=$xy;
	        $sdisplays=$xs;
	        $nums=$xn;
	        $idx=$xi; }
// if lent, matins gospel moves to top
	if ($day['pday'] > -42 && $day['pday'] < -7 && $day['feast_level'] < 7)
	  { $i = array_search("Matins Gospel", $types);
	    if ($i)
	      { $xt=array($types[$i]);
	        $xd=array($descs[$i]);
	        $xb=array($books[$i]);
	        $xy=array($displays[$i]);
	        $xs=array($sdisplays[$i]);
	        $xn=array($nums[$i]);
	        $xi=array($idx[$i]); 
		foreach ($types as $k=>$v)
		{ if ($k != $i)
		  { $xt[]=$types[$k];
		    $xd[]=$descs[$k];
		    $xb[]=$books[$k];
		    $xy[]=$displays[$k];
		    $xs[]=$sdisplays[$k];
		    $xn[]=$nums[$k];
		    $xi[]=$idx[$k]; } }
		$types=$xt;
	        $descs=$xd;
	        $books=$xb;
	        $displays=$xy;
	        $sdisplays=$xs;
	        $nums=$xn;
	        $idx=$xi; } }
// load into return array
	$arr['types']=$types;
	$arr['descs']=$descs;
	$arr['books']=$books;
	$arr['displays']=$displays;
	$arr['sdisplays']=$sdisplays;
	$arr['nums']=$nums;
	$arr['idx']=$idx;
	return $arr; }

  function retrievePericope($book, $num)
    { $q="select * from zachalos where zaBook='$book' and zaNum='$num' ";
	$r = $this->query($q);
	$arr=array(); // this will hold all return values
	$xv=array(); // this will hold verse numbers
	$xt=array(); // this will hold verse texts
	$w=$this->fetch($r);
	$arr['display']=$w['zaDisplay'];
	$arr['sdisplay']=$w['zaSdisplay'];
	$arr['desc']=$w['zaDesc'];
// If there is a Preverse it means we are overwriting the first verse with the Prefix
// then we empty the prefix so it is not added to first block
	if ($w['zaPreverse']) {$xv[]=$w['zaPreverse']; $xt[]=$w['zaPrefix']; $prefix="";}
	else {$prefix=$w['zaPrefix'];}
	$prefixb=$w['zaPrefixb']; // second block may have its own prefix
	$suffix=$w['zaSuffix'];
// separate discontinuous readings into blocks
	$blocks = explode("|", $w['zaVerses']);
	$num_blocks=count($blocks);
	$current_block=0;
	foreach($blocks as $v)
	{ ++$current_block; $current_verse=0; $a = explode("_", $v);
	  $q = "select scVerse, scText from scriptures where scBook='$a[0]' and scVerse >= $a[1] and scVerse <= $a[2] order by scVerse";
	  $r = $this->query($q);
	  $num_verses=$this->num_rows($r);
	  while ($w=$this->fetch($r))
	  { ++$current_verse; $x=$w['scText'];
// if first verse of block, strip everything through an asterisk
// otherwise delete asterisk
	    $i=strpos($x, "*");
	    if ($i)
	    { if ($current_verse==1) {$x=substr($x, $i+1);}
	      else {$x=str_replace("*", "", $x);} }
// if beginning of first or second block add prefix
	    if ($current_block==1 && $current_verse==1) {$x=$prefix.$x;}
	    elseif ($current_block==2 && $current_verse==1) {$x=$prefixb.$x;}
// if last verse of block strip everything beginning with "|"
// otherwise delete vertical bar
	    $i=strpos($x, "|");
	    if ($i)
	    { if ($current_verse==$num_verses) {$x=substr($x, 0, $i);}
	      else {$x=str_replace("|", "", $x);} }
// if last verse of block, but not last block add ellipsis
	    if ($current_block!=$num_blocks && $current_verse==$num_verses) {$x=$x." .&nbsp;.&nbsp;.";}
// if last verse of last block add suffix
	    if ($current_block==$num_blocks && $current_verse==$num_verses) {$x=$x.$suffix;}
// add numbers and texts to arrays
	    $xv[] = $w['scVerse']; $xt[] = $x; } }
	$arr['numbers']=$xv; $arr['texts']=$xt;
	return $arr; }

}
?>
