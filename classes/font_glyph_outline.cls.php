<?php
/**
 * @package php-font-lib
 * @link    http://php-font-lib.googlecode.com/
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id: font_table_glyf.cls.php 46 2012-04-02 20:22:38Z fabien.menager $
 */

require_once dirname(__FILE__)."/font_glyph_outline_simple.cls.php";
require_once dirname(__FILE__)."/font_glyph_outline_composite.cls.php";

/**
 * `glyf` font table.
 * 
 * @package php-font-lib
 */
class Font_Glyph_Outline extends Font_Binary_Stream {
  const ARG_1_AND_2_ARE_WORDS    = 0x0001;
  const ARGS_ARE_XY_VALUES       = 0x0002;
  const ROUND_XY_TO_GRID         = 0x0004;
  const WE_HAVE_A_SCALE          = 0x0008;
  const MORE_COMPONENTS          = 0x0020;
  const WE_HAVE_AN_X_AND_Y_SCALE = 0x0040;
  const WE_HAVE_A_TWO_BY_TWO     = 0x0080;
  const WE_HAVE_INSTRUCTIONS     = 0x0100;
  const USE_MY_METRICS           = 0x0200;
  const OVERLAP_COMPOUND         = 0x0400;
  
  /**
   * @var Font_Table_glyf
   */
  protected $table;
  
  protected $offset;
  protected $size;
  
  // Data
  public $numberOfContours;
  public $xMin;
  public $yMin;
  public $xMax;
  public $yMax;
  
  public $raw;
  
  /**
   * @return Font_Glyph_Outline
   */
  static function init(Font_Table_glyf $table, $offset, $size) {
    $font = $table->getFont();
    $font->seek($offset);
    
    /**
     * @var Font_Glyph_Outline
     */
    $glyph;
    
    if ($font->readInt16() > -1) {
      $glyph = new Font_Glyph_Outline_Simple($table, $offset, $size);
    }
    else {
      $glyph = new Font_Glyph_Outline_Composite($table, $offset, $size);
    }
    
    $glyph->parse();
    return $glyph;
  }
  
  /**
   * @return Font_TrueType
   */
  function getFont() {
    return $this->table->getFont();
  }
  
  function __construct(Font_Table_glyf $table, $offset = null, $size = null) {
    $this->table  = $table;
    $this->offset = $offset;
    $this->size   = $size;
  }
  
  function parse() {
    $font = $this->getFont();
    $font->seek($this->offset);
  
    if (!$this->size) {
      return;
    }
    
    $this->raw = $font->read($this->size);
  }
  
  function parseData(){
    $font = $this->getFont();
    $font->seek($this->offset);
    
    $this->numberOfContours = $font->readInt16();
    $this->xMin = $font->readFWord();
    $this->yMin = $font->readFWord();
    $this->xMax = $font->readFWord();
    $this->yMax = $font->readFWord();
  }

  function encode(){
    $font = $this->getFont();
    return $font->write($this->raw, strlen($this->raw));
  }
}