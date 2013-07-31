<!doctype html>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <link href="photo.css" rel="stylesheet" type="text/css"/>
  </head>

<?php
function enumDir($dir)
{
  $subdirs = array();
  $i = 0;
  
  if (is_dir($dir))
  {
    if ($handle = opendir($dir))
    {
      while (($file = readdir($handle)) !== false)
      {
        if ($file != "." && $file != ".." && is_dir($dir . $file))
        {
          $subdirs[$i] = $file;
          $i++;
        }
      }
      closedir($handle);
    }
  }
  
  asort($subdirs);
  return $subdirs;
}

function outputImage($linkUrl, $imageUrl, $caption)
{
  echo "<figure>";
  echo "<a href=\"$linkUrl\">";
  echo "<img src=\"$imageUrl\" alt=\"$caption\" class=\"grow\">";
  echo "</a>\n";
  echo "<figcaption>$caption</figcaption>";
  echo "</figure>\n";
}
?>
  
<body>
  <table id="container">
    <tr>
      <td class="header">

        <?php
$request = 0;
foreach ($_REQUEST as $key => $value)
{
  if ($key == "kid" || $key == "year")
  {
    $request++;
  }
}

switch($request)
{
case 0:
  echo "<header>albums</header><hr></td></tr><tr><td>";

  $subdirs = enumDir("./", false);
  foreach ($subdirs as $dir)
  {
    if ($dir == "emily" || $dir == "willie")
    {
      outputImage("index.php?kid=$dir", "$dir/$dir.jpg", $dir);
    }
  }
  echo "</td></tr><tr><td>\n";
  foreach ($subdirs as $dir)
  {
    if ($dir != "emily" && $dir != "willie")
    {
      outputImage("index.php?kid=$dir", "$dir/$dir.jpg", $dir);
    }
  }
  break;

case 1:
  $kid = $_REQUEST['kid'];

  echo "<header><a href='index.php'>home</a> / $kid</header><hr></td></tr><tr><td>";
    
  $subdirs = enumDir("./$kid/");
  foreach ($subdirs as $dir)
  {
    outputImage("index.php?kid=$kid&year=$dir", "./$kid/$dir/$dir.jpg", $dir);
  }
  break;

case 2:
  $kid = $_REQUEST['kid'];
  $year = $_REQUEST['year'];

  echo "<header><a href='index.php'>home</a> / <a href='index.php?kid=$kid'>$kid</a> / $year</header><hr></td></tr><tr><td>";
      
  $subdirs = enumDir("./$kid/$year/");
  foreach ($subdirs as $dir)
  {
    outputImage("photo.php?kid=$kid&year=$year&month=$dir", "./$kid/$year/$dir/$dir.jpg", $dir);
  }
  break;
}
?>
      </td>
    </tr>
  </table>
</body>
</html>