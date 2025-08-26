<?php
/**
 * @package php-font-lib
 * @link    https://github.com/dompdf/php-font-lib
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace FontLib\Table\Type;
use FontLib\Table\Table;

/**
 * `GSUB` font table (Glyph Substitution).
 *
 * @package php-font-lib
 */
class gsub extends Table {
  private static $header_format = array(
    "majorVersion"  => self::uint16,
    "minorVersion"  => self::uint16,
    "scriptList"    => self::uint16,
    "featureList"   => self::uint16,
    "lookupList"    => self::uint16,
  );

  protected function _parse() {
    $font   = $this->getFont();
    $offset = $font->pos();

    $header = $font->unpack(self::$header_format);

    $data = array(
      "header"      => $header,
      "scriptList"  => null,
      "featureList" => null,
      "lookupList"  => null,
    );

    if ($header["scriptList"]) {
      $font->seek($offset + $header["scriptList"]);
      $data["scriptList"] = $this->parseScriptList($font, $offset + $header["scriptList"]);
    }

    if ($header["featureList"]) {
      $font->seek($offset + $header["featureList"]);
      $data["featureList"] = $this->parseFeatureList($font, $offset + $header["featureList"]);
    }

    if ($header["lookupList"]) {
      $font->seek($offset + $header["lookupList"]);
      $data["lookupList"] = $this->parseLookupList($font, $offset + $header["lookupList"]);
    }

    $this->data = $data;
  }

  private function parseScriptList($font, $baseOffset) {
    $scriptCount = $font->readUInt16();
    $records = [];
    for ($i = 0; $i < $scriptCount; $i++) {
      $tag = $font->read(4);
      $offset = $font->readUInt16();
      $records[] = ["tag" => $tag, "offset" => $offset];
    }
    return ["scriptCount" => $scriptCount, "records" => $records];
  }

  private function parseFeatureList($font, $baseOffset) {
    $featureCount = $font->readUInt16();
    $records = [];
    for ($i = 0; $i < $featureCount; $i++) {
      $tag = $font->read(4);
      $offset = $font->readUInt16();
      $records[] = ["tag" => $tag, "offset" => $offset];
    }
    return ["featureCount" => $featureCount, "records" => $records];
  }

  private function parseLookupList($font, $baseOffset) {
    $lookupCount = $font->readUInt16();
    $lookups = [];
    $offsets = $font->readUInt16Many($lookupCount);

    foreach ($offsets as $lookupOffset) {
      $font->seek($baseOffset + $lookupOffset);
      $lookups[] = $this->parseLookup($font, $baseOffset + $lookupOffset);
    }

    return ["lookupCount" => $lookupCount, "lookups" => $lookups];
  }

  private function parseLookup($font, $baseOffset) {
    $lookupType     = $font->readUInt16();
    $lookupFlag     = $font->readUInt16();
    $subtableCount  = $font->readUInt16();
    $subtableOffsets = $font->readUInt16Many($subtableCount);

    $subtables = [];
    foreach ($subtableOffsets as $off) {
      $font->seek($baseOffset + $off);
      $subtables[] = $this->parseSubtable($font, $baseOffset + $off, $lookupType);
    }

    return [
      "lookupType"    => $lookupType,
      "lookupFlag"    => $lookupFlag,
      "subtableCount" => $subtableCount,
      "subtables"     => $subtables,
    ];
  }

  private function parseSubtable($font, $baseOffset, $lookupType) {
    switch ($lookupType) {
      case 4:
        return $this->parseLigatureSubst($font, $baseOffset);
      default:
        return ["raw" => "Unsupported lookupType $lookupType"];
    }
  }

  private function parseLigatureSubst($font, $subtableBase) {
    $substFormat   = $font->readUInt16();
    if ($substFormat != 1) {
      return ["format" => $substFormat, "unsupported" => true];
    }

    $coverageOffset = $font->readUInt16();
    $ligSetCount    = $font->readUInt16();
    $ligSetOffsets  = $font->readUInt16Many($ligSetCount);

    $ligSets = [];
    foreach ($ligSetOffsets as $offset) {
      $font->seek($subtableBase + $offset);
      $ligSets[] = $this->parseLigatureSet($font, $subtableBase + $offset);
    }

    $font->seek($subtableBase + $coverageOffset);
    $coverageGlyphs = $this->parseCoverage($font);

    return [
      "format"         => $substFormat,
      "coverageOffset" => $coverageOffset,
      "coverageGlyphs" => $coverageGlyphs,
      "ligSetCount"    => $ligSetCount,
      "ligSets"        => $ligSets,
    ];
  }

  private function parseCoverage($font) {
    $coverageFormat = $font->readUInt16();
    $glyphs = [];
    if ($coverageFormat == 1) {
        $glyphCount = $font->readUInt16();
        for ($i = 0; $i < $glyphCount; $i++) {
            $glyphs[] = $font->readUInt16();
        }
    } elseif ($coverageFormat == 2) {
        $rangeCount = $font->readUInt16();
        for ($i = 0; $i < $rangeCount; $i++) {
            $startGlyph = $font->readUInt16();
            $endGlyph   = $font->readUInt16();
            $startCoverageIndex = $font->readUInt16(); // can be ignored for mapping
            for ($g = $startGlyph; $g <= $endGlyph; $g++) {
                $glyphs[] = $g;
            }
        }
    }
    return $glyphs;
}


  private function parseLigatureSet($font, $baseOffset) {
    $ligatureCount  = $font->readUInt16();
    $ligatureOffsets = $font->readUInt16Many($ligatureCount);

    $ligatures = [];
    foreach ($ligatureOffsets as $off) {
      $font->seek($baseOffset + $off);
      $ligatures[] = $this->parseLigature($font);
    }

    return [
      "ligatureCount"  => $ligatureCount,
      "ligatures"      => $ligatures,
    ];
  }

  private function parseLigature($font) {
    $ligGlyph   = $font->readUInt16();
    $compCount  = $font->readUInt16();
    $components = $font->readUInt16Many($compCount - 1);

    return [
      "ligatureGlyph" => $ligGlyph,
      "compCount"     => $compCount,
      "components"    => $components,
    ];
  }

  function _encode() {
    // This still needs to be implemented
    throw new \Exception("Encoding GSUB not implemented yet.");
  }
}
