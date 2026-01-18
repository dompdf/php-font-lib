<?php
/**
 * @package php-font-lib
 * @link    https://github.com/dompdf/php-font-lib
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace FontLib\TrueType;

use Countable;
use FontLib\BinaryStream;
use Iterator;
use OutOfBoundsException;

/**
 * TrueType collection font file.
 *
 * @package php-font-lib
 */
class Collection extends BinaryStream implements Iterator, Countable {
  /**
   * Current iterator position.
   *
   * @var integer
   */
  private $position = 0;

  protected $collectionOffsets = array();
  protected $collection = array();
  protected $version;
  protected $numFonts;

  function parse() : void {
    if (isset($this->numFonts)) {
      return;
    }

    $this->read(4); // tag name

    $this->version  = $this->readFixed();
    $this->numFonts = $this->readUInt32();

    for ($i = 0; $i < $this->numFonts; $i++) {
      $this->collectionOffsets[] = $this->readUInt32();
    }
  }

  /**
   * @param int $fontId
   *
   * @throws OutOfBoundsException
   * @return File
   */
  function getFont($fontId) : File {
    $this->parse();

    if (!isset($this->collectionOffsets[$fontId])) {
      throw new OutOfBoundsException();
    }

    if (isset($this->collection[$fontId])) {
      return $this->collection[$fontId];
    }

    $font    = new File();
    $font->f = $this->f;
    $font->setTableOffset($this->collectionOffsets[$fontId]);

    return $this->collection[$fontId] = $font;
  }

  function current() : File {
    return $this->getFont($this->position);
  }

  function key() : int {
    return $this->position;
  }

  function next() : void {
    ++$this->position;
  }

  function rewind() : void {
    $this->position = 0;
  }

  function valid() : bool {
    $this->parse();

    return isset($this->collectionOffsets[$this->position]);
  }

  function count(): int {
    $this->parse();

    return $this->numFonts;
  }
}
