<?php include("lib/core.lib.php"); $core = new coreLIB;
if (isset($_GET['book']))
{ $book=$_GET['book'];
  $q="select * from zachalos where zaBook='$book' order by zaId";
  $r = $core->query($q);
  while ($w=$core->fetch($r))
  { $zz[]=$w['zaNum']."||".$w['zaSdisplay']; }
  $x=implode("@", $zz);
  print $x;
} ?>