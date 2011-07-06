<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
<?php 

require_once "../classes/font.cls.php";

$fontfile = $_GET["fontfile"];

$t = microtime(true);

$font = Font::load($fontfile);
$font->parse();

if ($font instanceof Font_TrueType_Collection) {
  $font = $font->getFont(0);
  $font->parse();
}

//$font->saveAdobeFontMetrics("$fontfile.ufm");

?>

<span style="float: right;">
  Memory: <?php echo (memory_get_peak_usage(true) / 1024); ?>KB &mdash;
  Time: <?php echo round(microtime(true) - $t, 4); ?>s
</span>

<h1>
<?php echo $font->data["name"]["nameRecord"][3]; ?>
</h1>
<h3>
<?php echo $font->data["name"]["nameRecord"][5]; ?>
</h3>
<hr />

<?php /*
<div class="unicode-map">
  <?php 
        
  $subtable = null;
  foreach($font->data["cmap"]["subtables"] as $_subtable) {
    if ($_subtable["platformID"] == 3 && $_subtable["platformSpecificID"] == 1) {
      $subtable = $_subtable;
      break;
    }
  }
  
  $empty = 0;
  for($c = 0; $c <= 0xFFFF; $c++) { 
    if (($c % 256 == 0 || $c == 0xFFFF) && $empty > 0) {
      echo "<b style=\"width:{$empty}px\"></b>";
      $empty = 0;
    }
    
    if (isset($subtable["glyphIndexArray"][$c])) {
      if ($empty > 0) {
        echo "<b style=\"width:{$empty}px\"></b>";
        $empty = 0;
      }
      echo "<i><s>&#$c;</s></i>";
    }
    else {
      $empty++;
    }
  } ?>
</div>
*/
?>
<ul>
  <?php foreach($font->data as $tag => $data) { ?>
    <li><a href="#<?php echo $tag; ?>"><?php echo $tag; ?></a></li>
  <?php } ?>
</ul>

<?php foreach($font->data as $tag => $data) { ?>
  <h2 id="<?php echo $tag; ?>">
    <?php echo $tag; ?>
  </h2>
  <pre><?php var_export($data); ?></pre>
<?php } ?>

</body>
</html>