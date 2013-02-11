<?php
/**
 * @package php-font-lib
 * @link    https://github.com/PhenX/php-font-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

require_once dirname(__FILE__) . "/Font_TrueType.php";
require_once dirname(__FILE__) . "/Font_EOT_Header.php";

/**
 * EOT font file.
 * 
 * @package php-font-lib
 */
class Font_EOT extends Font_TrueType {
  /**
   * @var Font_EOT_Header
   */
  public $header;

  function parseHeader(){
    if (!empty($this->header)) {
      return;
    }

    $this->header = new Font_EOT_Header($this);
    $this->header->parse();
  }
  
  function parse() {
    $this->parseHeader();

    // TODO
  }

  /**
   * Little endian version of the read method
   */
  public function read($n) {
    if ($n < 1) {
      return "";
    }

    $string = fread($this->f, $n);
    $chunks = str_split($string, 2);
    $chunks = array_map("strrev", $chunks);

    return implode("", $chunks);
  }

  public function readUInt32(){
    $uint32 = parent::readUInt32();
    return $uint32 >> 16 & 0x0000FFFF | $uint32 << 16 & 0xFFFF0000;
  }

  /**
   * Get font copyright
   *
   * @return string|null
   */
  function getFontCopyright(){
    return null;
  }

  /**
   * Get font name
   *
   * @return string|null
   */
  function getFontName(){
    return $this->header->data["FullName"];
  }

  /**
   * Get font subfamily
   *
   * @return string|null
   */
  function getFontSubfamily(){
    return $this->header->data["FamilyName"];
  }

  /**
   * Get font subfamily ID
   *
   * @return string|null
   */
  function getFontSubfamilyID(){
    return $this->header->data["FamilyName"];
  }

  /**
   * Get font full name
   *
   * @return string|null
   */
  function getFontFullName(){
    return $this->header->data["FullName"];
  }

  /**
   * Get font version
   *
   * @return string|null
   */
  function getFontVersion(){
    return $this->header->data["VersionName"];
  }

  /**
   * Get font Postscript name
   *
   * @return string|null
   */
  function getFontPostscriptName(){
    return null;
  }
}
