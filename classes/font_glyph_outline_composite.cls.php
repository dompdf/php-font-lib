<?php
/**
 * @package php-font-lib
 * @link    http://php-font-lib.googlecode.com/
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id: font_table_glyf.cls.php 46 2012-04-02 20:22:38Z fabien.menager $
 */

/**
 * `glyf` font table.
 * 
 * @package php-font-lib
 */
class Font_Glyph_Outline_Composite extends Font_Glyph_Outline {
  public $flags;
  public $glyphIndex;
  
  function parseData(){
    parent::parseData();
    
    $font = $this->getFont();
    
    $this->flags      = $font->readUInt16();
    $this->glyphIndex = $font->readUInt16();
  }
}