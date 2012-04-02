<?php
/**
 * @package php-font-lib
 * @link    http://php-font-lib.googlecode.com/
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id$
 */

$fontfile = $_GET["fontfile"];
$name     = isset($_GET["name"]) ? $_GET["name"] : null;

if (isset($_POST["subset"])) {
  $subset = $_POST["subset"];
  
  ob_start();
  
  require_once "../classes/font.cls.php";
  
  $font = Font::load($fontfile);
  $font->parse();
  
  $font->setSubset($subset);
  $font->reduce();
  
  header('Content-Type: font/truetype');
  header('Content-Disposition: attachment; filename="subset.ttf"');
  
  $tmp = tempnam(sys_get_temp_dir(), "fnt");
  $font->open($tmp, Font_Binary_Stream::modeWrite);
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
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
  <h1><?php echo $name; ?></h1>
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