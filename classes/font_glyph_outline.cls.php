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
  const ARG_1_AND_2_ARE_WORDS    = 1;
  const ARGS_ARE_XY_VALUES       = 2;
  const ROUND_XY_TO_GRID         = 4;
  const WE_HAVE_A_SCALE          = 8;
  const MORE_COMPONENTS          = 32;
  const WE_HAVE_AN_X_AND_Y_SCALE = 64;
  const WE_HAVE_A_TWO_BY_TWO     = 128;
  const WE_HAVE_INSTRUCTIONS     = 256;
  const USE_MY_METRICS           = 512;
  const OVERLAP_COMPOUND         = 1024;
  
  /**
   * @var Font_Table_glyf
   */
  protected $table;
  
  protected $offset;
  protected $size;
  
  protected $data;
  
  static function init(Font_Table_glyf $table, $offset, $size) {
    $font = $table->getFont();
    $font->seek($offset);
    
    if ($font->readInt16() > -1) {
      return new Font_Glyph_Outline_Simple($table, $offset, $size);
    }
    else {
      return new Font_Glyph_Outline_Composite($table, $offset, $size);
    }
  }
  
  /**
   * @return Font_TrueType
   */
  function getFont() {
    return $this->table->getFont();
  }
  
  function getGlyphData(){
    if (empty($this->data)) {
      $this->parse();
    }
    
    return $this->data;
  }
  
  function __construct(Font_Table_glyf $table, $offset = null, $size = null) {
    $this->table  = $table;
    $this->offset = $offset;
    $this->size   = $size;
  }
  
  function parse() {
    $font = $this->getFont();
    $font->seek($this->offset);
    
    $data = $font->unpack(array(
      "numberOfContours" => self::int16,
      "xMin" => self::FWord,
      "yMin" => self::FWord,
      "xMax" => self::FWord,
      "yMax" => self::FWord,
    ));
    
    //$data["outline"] = $font->read($this->size - 10);
    
    return $this->data = $data;
  }
}