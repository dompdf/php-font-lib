<?php
/**
 * @package php-font-lib
 * @link    http://php-font-lib.googlecode.com/
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id$
 */
?><!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<?php 

$fonts = glob("../fonts/*.{ttf,TTF,otf,OTF,ttc,TTC,eot,EOT,woff,WOFF}", GLOB_BRACE);
sort($fonts);

echo "<ul>";
foreach($fonts as $font) {
  echo "<li><a href=\"font_info.php?fontfile=$font\" target=\"font-info\">".basename($font)."</a></li>";
}
echo "</ul>";

?>
</body>
</html>