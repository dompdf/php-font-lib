<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/blitzer/jquery-ui-1.8.14.custom.css" />
  <script type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
  <script type="text/javascript" src="js/jquery-ui-1.8.14.custom.min.js"></script>
	<script type="text/javascript">
		$(function() {
	    $("#tabs").tabs({
				select: function(event, ui) {
					if (ui.panel.id == "tabs-unicode-map") {
						$(ui.panel).load("<?php echo $_SERVER['REQUEST_URI']; ?>&unicodemap=1");
					}
				}
			});
	  });
	</script>
</head>
<body>
<?php 

require_once "../classes/font.cls.php";

$fontfile   = @$_GET["fontfile"];
$unicodemap = @$_GET["unicodemap"];

$t = microtime(true);

$font = Font::load($fontfile);

if ($font instanceof Font_TrueType_Collection) {
  $font = $font->getFont(0);
}

//$font->saveAdobeFontMetrics("$fontfile.ufm");

if ($unicodemap) { 
  $font->parseCMAP();
  $font->parsePOST();
  
  ?>

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
	$names = $font->data["post"]["names"];
	
  for($c = 0; $c <= 0xFFFF; $c++) { 
    if (($c % 256 == 0 || $c == 0xFFFF) && $empty > 0) {
      echo "<b style=\"width:".($empty*2)."px\"></b>";
      $empty = 0;
    }
    
    if (isset($subtable["glyphIndexArray"][$c])) {
    	$g = $subtable["glyphIndexArray"][$c];
			
      if ($empty > 0) {
        echo "<b style=\"width:".($empty*2)."px\"></b>";
        $empty = 0;
      }
      echo "<i><s>&#$c;<br /><div class=\"info\">$c<br />".(isset($names[$g]) ? $names[$g] : sprintf("uni%04x", $c))."</div></s></i>";
    }
    else {
      $empty++;
    }
  } ?>
</div>

<?php
} 
else { 
  $font->parse();
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

<div id="tabs">
	<ul>
	  <?php foreach($font->data as $tag => $data) { ?>
	    <li><a href="#tabs-<?php echo preg_replace("/[^a-z0-9]/i", "_", $tag); ?>"><?php echo $tag; ?></a></li>
	  <?php } ?>
		<li><a href="#tabs-unicode-map">Unicode map</a></li>
	</ul>
	
	<?php foreach($font->data as $tag => $data) { ?>
	  <div id="tabs-<?php echo preg_replace("/[^a-z0-9]/i", "_", $tag); ?>">
		  <pre><?php var_export($data); ?></pre>
		</div>
		
    <div id="tabs-unicode-map"></div>
	<?php } ?>
</div>

<?php } ?>
</body>
</html>