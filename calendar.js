function rexec(d, s)
{ document.exec.cday.value = d;
  document.exec.rdng.value = s;
  document.exec.submit();
  return false; }

function cexec()
{ document.exec.action = "calendar.php";
  document.exec.submit();
  return false; }

function verYear(f)
{ var strx=/[\s\-]/g ;
  var numb=/^\d{4}$/ ;
  f.sY.value = f.sY.value.replace(strx, "");
  if (!numb.test(f.sY.value))
  { f.sY.value = ""; return false; }
  return true; }
