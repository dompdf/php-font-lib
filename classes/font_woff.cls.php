<?php
/**
 * @package php-font-lib
 * @link    http://php-font-lib.googlecode.com/
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id$
 */

require_once dirname(__FILE__)."/font_truetype.cls.php";
require_once dirname(__FILE__)."/font_woff_table_directory_entry.cls.php";
require_once dirname(__FILE__)."/font_woff_header.cls.php";

/**
 * WOFF font file.
 * 
 * @package php-font-lib
 */
class Font_WOFF extends Font_TrueType {
  public $origF;
  public $fileOffset = 0;
  
  function parseHeader(){
    if (!empty($this->header)) {
      return;
    }
    
    $this->header = new Font_WOFF_Header($this);
    $this->header->parse();
  }
  
  public function seek($offset) {
    return fseek($this->f, $offset - $this->fileOffset, SEEK_SET) == 0;
  }
}
