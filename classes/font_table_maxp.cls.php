<?php
/**
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this library in the file LICENSE.LGPL; if not, write to the
 * Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 * 02111-1307 USA
 *
 * Alternatively, you may distribute this software under the terms of the
 * PHP License, version 3.0 or later.  A copy of this license should have
 * been distributed with this file in the file LICENSE.PHP .  If this is not
 * the case, you can obtain a copy at http://www.php.net/license/3_0.txt.
 *
 * @link http://php-font-lib.googlecode.com/
 * @author Fabien Ménager
 */

/* $Id$ */

class Font_Table_maxp extends Font_Table {
  protected $def = array(
    "version"               => self::Fixed,
    "numGlyphs"             => self::uint16,
    "maxPoints"             => self::uint16,
    "maxContours"           => self::uint16,
    "maxComponentPoints"    => self::uint16,
    "maxComponentContours"  => self::uint16,
    "maxZones"              => self::uint16,
    "maxTwilightPoints"     => self::uint16,
    "maxStorage"            => self::uint16,
    "maxFunctionDefs"       => self::uint16,
    "maxInstructionDefs"    => self::uint16,
    "maxStackElements"      => self::uint16,
    "maxSizeOfInstructions" => self::uint16,
    "maxComponentElements"  => self::uint16,
    "maxComponentDepth"     => self::uint16,
  );
}