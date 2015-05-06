<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/blitzer/jquery-ui-1.8.14.custom.css" />
</head>
<body>
<pre>
<?php

use FontLib\Font;
use FontLib\BinaryStream;

require_once "../src/FontLib/Autoloader.php";

$t = microtime(true);

// RW methods
$values = array(
  array(BinaryStream::uint8, 9),
  array(BinaryStream::int8, 9),
  array(BinaryStream::uint16, 5040),
  array(BinaryStream::int16, -5040),
  array(BinaryStream::uint32, 8400245),
  array(BinaryStream::int32, 8400245),
  array(BinaryStream::shortFrac, 1.0),
  array(BinaryStream::Fixed, -155.54),
  array(BinaryStream::FWord, -5040),
  array(BinaryStream::uFWord, 5040),
  array(BinaryStream::F2Dot14, -56.54),
  array(BinaryStream::longDateTime, "2011-07-21 21:37:00"),
  array(BinaryStream::char, "A"),
);
/*
$stream = new Font_Binary_Stream();
foreach($values as $value) {
  list($type, $data) = $value;
  
  $stream = new Font_Binary_Stream();
  $stream->setFile(Font_Binary_Stream::getTempFile());
  
  $stream->w($type, $data);
  $stream->seek(0);
  $new_data = $stream->r($type);
  
  if ($new_data !== $data) {
    echo "NOT OK \t $data \t => $new_data<br />";
  }
  else {
    echo "OK $type<br />";
  }
}*/

// font RW
$filename = "../fonts/DejaVuSansMono.ttf";
$filename_out = "$filename.2.ttf";

Font::$debug = true;
$font = Font::load($filename);
$font->parse();


$font->setSubset("(.apbiI,mn");
$font->reduce();

$font->open($filename_out, BinaryStream::modeWrite);
$font->encode(array("OS/2"));

?>

File size: <?php echo number_format(filesize($filename_out), 0, ".", " "); ?> bytes
Memory: <?php echo (memory_get_peak_usage(true) / 1024); ?>KB
Time: <?php echo round(microtime(true) - $t, 4); ?>s
</pre>
</body>
</html>