<?php
/**
 * @package php-font-lib
 * @link    http://php-font-lib.googlecode.com/
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id$
 */

require_once dirname(__FILE__)."/font_table_directory_entry.cls.php";

/**
 * WOFF font file table directory entry.
 * 
 * @package php-font-lib
 */
class Font_WOFF_Table_Directory_Entry extends Font_Table_Directory_Entry {
  function __construct(Font_WOFF $font) {
    parent::__construct($font);
    $this->offset = $this->readUInt32();
    $this->length = $this->readUInt32();
    $this->origLength = $this->readUInt32();
    $this->checksum = $this->readUInt32();
  }
  
  function startRead(){
    parent::startRead();
    
    if ($this->length == $this->origLength) {
      return true;
    }
    
    $font = $this->font;
    $font->fileOffset = $font->pos();
    
    $data = $font->read($this->length);
    
    $f = self::getTempFile();
    fwrite($f, gzuncompress($data));
    rewind($f);
    
    $font->origF = $font->f;
    $font->f = $f;
  }
  
  function endRead(){
    parent::endRead();
    
    $font = $this->font;
    
    if ($font->origF) {
      fclose($font->f);
      $font->f = $font->origF;
      $font->origF = null;
      $font->fileOffset = 0;
    }
  }
}
