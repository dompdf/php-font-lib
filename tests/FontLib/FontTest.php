<?php

namespace FontLib\Tests;

use FontLib\Font;
use PHPUnit\Framework\TestCase;

class FontTest extends TestCase
{
    public function testLoadFileNotFound()
    {
        // @todo when PHP 5.4 support is dropped, uncomment line below and drop
        //       the try...catch block.
        // $this->expectException('\Fontlib\Exception\FontNotFoundException');
        try {
            Font::load('non-existing/font.ttf');
            $this->fail('Load should have failed.');
        }
        catch (\Fontlib\Exception\FontNotFoundException $e) {
            // Avoid throwing a risky test error.
            $this->assertTrue(true);
        }
    }

    public function testLoadTTFFont()
    {
        $trueTypeFont = Font::load('tests/resources/fonts/ahem/ahem.ttf');

        $this->assertInstanceOf('FontLib\TrueType\File', $trueTypeFont);
    }

    public function testGetFontInfoTTF()
    {
        $font = Font::load('tests/resources/fonts/ahem/ahem.ttf');
        $font->parse();
        $this->assertSame('Ahem', $font->getFontName());
        $this->assertSame('Regular', $font->getFontSubfamily());
        $this->assertSame('Version 1.50 Ahem', $font->getFontSubfamilyID());
        $this->assertSame('Ahem', $font->getFontFullName());
        $this->assertSame('Version 1.50', $font->getFontVersion());
        $this->assertSame(400, $font->getFontWeight());
        $this->assertSame('Ahem', $font->getFontPostscriptName());
        $this->assertTrue($font->close());
    }

    public function testTTFCmap()
    {
        $trueTypeFont = Font::load('tests/resources/fonts/noto/NotoSansShavian-Regular.ttf');

        $trueTypeFont->parse();

        $cmapTable = $trueTypeFont->getData("cmap", "subtables");

        $cmapFormat4Table = $cmapTable[0];

        $this->assertEquals(4, $cmapFormat4Table['format']);
        $this->assertEquals(51, $cmapFormat4Table['segCount']);
        $this->assertEquals($cmapFormat4Table['segCount'], count($cmapFormat4Table['startCode']));
        $this->assertEquals($cmapFormat4Table['segCount'], count($cmapFormat4Table['endCode']));

        $cmapFormat12Table = $cmapTable[1];

        $this->assertEquals(12, $cmapFormat12Table['format']);
        $this->assertEquals(294, $cmapFormat12Table['ngroups']);
        $this->assertEquals(294, count($cmapFormat12Table['startCode']));
        $this->assertEquals(294, count($cmapFormat12Table['endCode']));
        $this->assertEquals(383, count($cmapFormat12Table['glyphIndexArray']));
    }
}
