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