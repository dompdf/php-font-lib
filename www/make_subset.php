<?php
/**
 * @package php-font-lib
 * @link    https://github.com/PhenX/php-font-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

use FontLib\Font;
use FontLib\Binary_Stream;

$fontfile = null;
if (isset($_GET["fontfile"])) {
  $fontfile = basename($_GET["fontfile"]);
  $fontfile = "../fonts/$fontfile";
}

if (!file_exists($fontfile)) {
  return;
}

$name = isset($_GET["name"]) ? $_GET["name"] : null;

if (isset($_POST["subset"])) {
  $subset = $_POST["subset"];
  
  ob_start();

  require_once "../src/FontLib/Autoloader.php";
  
  $font = Font::load($fontfile);
  $font->parse();
  
  $font->setSubset($subset);
  $font->reduce();

  $new_filename = basename($fontfile);
  $dot = strpos($new_filename, ".");
  $new_filename = substr($new_filename, 0, $dot)."-subset".substr($new_filename, $dot);
  
  header("Content-Type: font/truetype");
  header("Content-Disposition: attachment; filename=\"$new_filename\"");
  
  $tmp = tempnam(sys_get_temp_dir(), "fnt");
  $font->open($tmp, Binary_Stream::modeWrite);
  $font->encode(array("OS/2"));
  $font->close();
  
  ob_end_clean();
  
  readfile($tmp);
  unlink($tmp);
  
  return;
} ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Subset maker</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
  <h1><?php echo htmlentities($name); ?></h1>
  <form name="make-subset" method="post" action="?fontfile=<?php echo $fontfile; ?>">
    <label>
      Insert the text from which you want the glyphs in the subsetted font: <br />
      <textarea name="subset" cols="50" rows="20"></textarea>
    </label>
    <br />
    <button type="submit">Make subset!</button>
  </form>
</body>
</html>