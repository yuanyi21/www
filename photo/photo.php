<!doctype html>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <link href="photo.css" rel="stylesheet" type="text/css"/>
    <script src="jquery.js"></script>
    <script language="javascript" type="text/javascript">
      var c_MaxImageSize = 1600;
      var images = new Array();
      var current = 0;
      function Image(url, ratio, caption, exifModel, exifAperture, exifExposure, exifFocal, exifISO, exifDate)
      {
        this.url = url;
        this.ratio = ratio;
        this.caption = caption;
        this.exifModel = exifModel;
        this.exifAperture = eval(exifAperture);
        this.exifExposure = exifExposure + "s";
        this.exifFocal = eval(exifFocal) + "mm";
        this.exifISO = exifISO;
        this.exifDate = exifDate;
        
        // another
      }
    
      function setImageSize(imageData)
      {
        var imgMaxWidth = Math.min($("#left-section").width() - 40, c_MaxImageSize);
        var imgMaxHeight = Math.min(window.innerHeight - 150, c_MaxImageSize);

        var containerRatio = imgMaxWidth / imgMaxHeight;
        var imageW;

        if (imageData.ratio > containerRatio)
        {
          imageW = imgMaxWidth;
          $("#image").width(imgMaxWidth);
          $("#image").height("auto");
        }
        else
        {
          imageW = imgMaxHeight * imageData.ratio;
          $("#image").width("auto");
          $("#image").height(imgMaxHeight);
        }
        
        var margin = ($("#left-section").width() - imageW - 32) / 2;
        $("#media-div").css("marginLeft", margin + "px");
      }
      
      function setMedia(index)
      {
        current = index;
        if (images[index].url.toLowerCase().indexOf(".jpg") > 0)
        {
          $("#image").attr("src", images[index].url);
          setImageSize(images[index]);

          $("#image").show();
          video.pause();
          $("#video").hide();
        }
        else
        {
          $("#video").attr("src", images[index].url);
          $("#image").hide();
          $("#video").show();
        }

        $("#captionText").text(images[index].caption);
        $("#Exif-Model").text(images[index].exifModel);
        $("#Exif-Aperture").text(images[index].exifAperture);
        $("#Exif-Exposure").text(images[index].exifExposure);
        $("#Exif-Focal").text(images[index].exifFocal);
        $("#Exif-ISO").text(images[index].exifISO);
        $("#Exif-Date").text(images[index].exifDate);
      };
      
      function goPrev()
      {
        if (current > 0)
        {
          setMedia(current - 1);
        }
      }
      
      function goNext()
      {
        if (current < images.length - 1)
        {
          setMedia(current + 1);
        }
      }
      
      var divWidth, divHeight;
      var timeoutId = 0;

      function slideShowStart()
      {
        divWidth = $("#slide").width();
        divHeight = $("#slide").height();
        $("#slide").show();
        animate();
      }

      function slideShowStop()
      {
        window.clearTimeout(timeoutId);
        $("#slide").hide();
      }

<?php
function enumFile($dir)
{
  $subdirs = array();
  
  // Read captions
  $captions = array();
  $captionFilename = "$dir/caption.txt";
  if (file_exists($captionFilename))
  {
    $handle = fopen($captionFilename, "r");
    while (($buffer = fgets($handle, 4096)) !== false) 
    {
      $tokens = explode("|", $buffer);
      if (count($tokens) >= 2)
      {
        $caption = trim($tokens[1]);
        if (strlen($caption) > 0)
        {
          $captions[$tokens[0]] = $caption;
        }
      }
    }
  }
  
  // Read images
  if (is_dir($dir))
  {
    if ($handle = opendir($dir))
    {
      while (($file = readdir($handle)) !== false)
      {
        if (!is_dir($dir . $file))
        {
          $extension = strtolower(substr($file, -4));
          $name = substr($file, 0, strlen($file) - 4);
          if (($extension  == ".jpg") || ($extension  == ".mp4") || ($extension  == ".mp3"))
          {
            $caption = $captions[$file];
            $properties = array();
            if (strlen($caption) > 0)
            {
              $properties["caption"] = $captions[$file];
            }
            else
            {
              $properties["caption"] = $file;
            }
            $properties["extension"] = $extension;
            if ($extension  == ".jpg")
            {
              $exif = exif_read_data("./$dir/thumbnail/$name.jpg", "IFD0,EXIF");
              $properties["Model"] = $exif["Model"];
              $properties["Aperture"] = $exif["FNumber"];
              $properties["Exposure"] = $exif["ExposureTime"];
              $properties["Focal"] = $exif["FocalLength"];
              $properties["ISO"] = $exif["ISOSpeedRatings"];
              $properties["Date"] = $exif["DateTimeOriginal"];    
            }
            $subdirs[$name] = $properties;
          }
        }
      }
      closedir($handle);
    }
  }
  
  return $subdirs;
}

function cmpDate($a, $b)
{
  if (!array_key_exists('Date', $a))
  {
    return 1;
  }
  elseif (!array_key_exists('Date', $b))
  {
    return -1;
  }
  else
  {
    return ($a["Date"] < $b["Date"]) ? -1 : 1;
  }
}

function processImages(&$files, $month)
{
  foreach ($files as $name => $properties)
  {
    if ($name == $month)
    {
      unset($files[$name]);
    }
  }
  
  uasort($files, 'cmpDate');
}

$kid = $_REQUEST['kid'];
$year = $_REQUEST['year'];
$month = $_REQUEST['month'];

$dir = "./$kid/$year/$month";
$files = enumFile($dir);
processImages($files, $month);

$i = 0;
foreach ($files as $name => $properties)
{
  $caption = $properties["caption"];
  $size = getimagesize("$dir/thumbnail/$name.jpg");
  $ratio = $size[0] / $size[1];
  $extension = $properties["extension"];
  $exifModel = $properties["Model"];
  $exifAperture = $properties["Aperture"];
  $exifExposure = $properties["Exposure"];
  $exifFocal = $properties["Focal"];
  $exifISO = $properties["ISO"];
  $exifDate = $properties["Date"];

  echo "      images[$i] = new Image('$dir/$name$extension', '$ratio', '$caption', '$exifModel', '$exifAperture', '$exifExposure', '$exifFocal', '$exifISO', '$exifDate');\n";
  $i++;
}
?>
      var slideIndex = 0;

      function animate() 
      {
        transition();
        //nontransition();
        timeoutId = window.setTimeout(animate, 3500);
      }
      
      function setSlideSize(element)
      {
        var imgMaxWidth = Math.min(divWidth - 20, c_MaxImageSize);
        var imgMaxHeight = Math.min(divHeight - 20, c_MaxImageSize);
        var divRatio = imgMaxWidth / imgMaxHeight;
            
        var imgWidth, imgHeight;
        if (images[slideIndex].ratio > divRatio)
        {
          imgWidth = imgMaxWidth;
          imgHeight = imgMaxWidth / images[slideIndex].ratio;
        }
        else
        {
          imgWidth = imgMaxHeight * images[slideIndex].ratio;
          imgHeight = imgMaxHeight;
        }
            
        var divMarginLR = (divWidth - imgWidth) / 2;

        element.width(imgWidth);
        element.height(imgHeight);
        element.css("marginLeft", divMarginLR);
      }
      
      function getNextSlide()
      {
        do
        {
          slideIndex = (slideIndex + 1) % images.length;
        } while (images[slideIndex].url.toLowerCase().indexOf(".jpg") < 0);
      }

      function nontransition() 
      {
        setSlideSize($("#outgoing"));
        $("#outgoing").attr("src", images[slideIndex].url);
        getNextSlide();
      }

      function transition() 
      {
        //$("#incoming").fadeIn();
        //$("#dissolve").addClass("active");
        
        $("#outgoing").attr("src", images[slideIndex].url);
        setSlideSize($("#outgoing"));
        //$("#outgoing").fadeIn();

        //$("#dissolve").removeClass("active");

        getNextSlide();
        
        $("#incoming").attr("src", images[slideIndex].url);
        setSlideSize($("#incoming"));
      }
    </script>
  </head>

<body>
  <table id="container" width="95%">
    <tr>
      <td class="header">
<?php  
echo "        <a href='index.php'>home</a> / <a href='index.php?kid=$kid'>$kid</a> / <a href='index.php?kid=$kid&year=$year'>$year</a> / $month\n";
?>
      </td>
      <td class="header">
        <div style="float:right;"><a herf="#" onclick="slideShowStart()">Slide Show</a></div>
      </td>
    </tr>
    <tr><td colspan="2"><hr/></td></tr>
    <tr>
      <td id="left-section" width="90%">
        <table width="100%">
          <tr>
            <td>
              <a href="#" class="prev" onclick="goPrev()">Prev</a>
            </td>
            <td>
              <p id="captionText">1</p>
            </td>
            <td>
              <a href="#" class="next" onclick="goNext()">Next</a>
            </td>
          </tr>
          <tr>
            <td colspan="3">
              <div id="media-div" class="drop-shadow">
                <img id="image"/>
                <video id="video" controls="controls" autoplay="autoplay">
                  Your browser does not support the video tag.
                </video>
              </div>
            </td>
          </tr>
        </table>
      </td>
      <td id="right-section">
        <table id="thumbnail-table">
<?php  
$i = 0;
$hasTr = false;
foreach ($files as $name => $properties)
{
  if (($i % 3) == 0)
  {
    echo "<tr>\n";
    $hasTr = true;
  }
  echo "<td class=\"grow\"><div>";

  $size = getimagesize("$dir/thumbnail/$name.jpg");
  if ($size[0] >= $size[1])
  {
    $margin = (1.0 - $size[0] / $size[1]) * 75 / 2;
    echo "<img class=\"thumbnail-landscape\" style=\"margin-left: " . number_format($margin, 2) . "px;\" ";
  }
  else
  {
    $margin = (1.0 - $size[1] / $size[0]) * 75 / 2;
    echo "<img class=\"thumbnail-portrait\" style=\"margin-top: " . number_format($margin, 2) . "px;\" ";
  }
  echo "src=\"$dir/thumbnail/$name.jpg\" onClick=\"setMedia($i);\">";

  echo "</div></td>\n";
  if (($i % 3) == 2)
  {
    echo "</tr>\n";
    $hasTr = false;;
  }
  $i++;
}

if (hasTr)
{
  echo "</tr>\n";
}
?>
        </table>
        <table id="exif-table" width="240">
          <tr><td class="exifName">Model</td><td id="Exif-Model" class="exifValue"></td></tr>
          <tr><td class="exifName">Aperture</td><td id="Exif-Aperture" class="exifValue"></td></tr>
          <tr><td class="exifName">Exposure</td><td id="Exif-Exposure" class="exifValue"></td></tr>
          <tr><td class="exifName">Focal</td><td id="Exif-Focal" class="exifValue"></td></tr>
          <tr><td class="exifName">ISO</td><td id="Exif-ISO" class="exifValue"></td></tr>
          <tr><td class="exifName">Date</td><td id="Exif-Date" class="exifValue"></td></tr>
        </table>
      </td>
    </tr>
  </table>
  <div id="slide">
    <div id="dissolve">
      <img id="incoming" class="incoming"/>
      <img id="outgoing" class="outgoing"/>
    </div>
    <div style="float:right; margin: 10px;"><a href="#" onclick="slideShowStop()"> Close </a></div>
  </div>
<?php  
echo "  <script>setMedia(0);</script>\n";
?>
</body>
</html>