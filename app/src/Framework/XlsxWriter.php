<?php
namespace App\Framework;

/**
 * Minimal native .xlsx (Office Open XML) writer — no external library.
 * Builds a single worksheet from a header row and data rows using inline
 * strings (text) and numeric cells, and packages it with ZipArchive.
 */
class XlsxWriter
{
    /**
     * @param string[]              $headers
     * @param array<int,array<int|string,mixed>> $rows
     * @return string the .xlsx file bytes
     */
    public static function build(array $headers, array $rows, string $sheetName = 'Sheet1'): string
    {
        $sheetData = self::rowXml(1, array_values($headers));
        $r = 2;
        foreach ($rows as $row) {
            $sheetData .= self::rowXml($r++, array_values($row));
        }

        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>' . $sheetData . '</sheetData></worksheet>';

        $parts = [
            '[Content_Types].xml' =>
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                . '<Default Extension="xml" ContentType="application/xml"/>'
                . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                . '</Types>',
            '_rels/.rels' =>
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                . '</Relationships>',
            'xl/workbook.xml' =>
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                . '<sheets><sheet name="' . self::esc($sheetName) . '" sheetId="1" r:id="rId1"/></sheets>'
                . '</workbook>',
            'xl/_rels/workbook.xml.rels' =>
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                . '</Relationships>',
            'xl/worksheets/sheet1.xml' => $sheet,
        ];

        return self::zip($parts);
    }

    /**
     * Build a ZIP archive (stored / no compression) from name => content,
     * without the zip extension.
     *
     * @param array<string,string> $files
     */
    private static function zip(array $files): string
    {
        $local = '';
        $central = '';
        $offset = 0;
        foreach ($files as $name => $data) {
            $crc = crc32($data);
            $len = strlen($data);
            $nameLen = strlen($name);
            $lfh = pack('V', 0x04034b50) . pack('v', 20) . pack('v', 0) . pack('v', 0)
                 . pack('v', 0) . pack('v', 0)
                 . pack('V', $crc) . pack('V', $len) . pack('V', $len)
                 . pack('v', $nameLen) . pack('v', 0) . $name;
            $local .= $lfh . $data;
            $central .= pack('V', 0x02014b50) . pack('v', 20) . pack('v', 20) . pack('v', 0)
                 . pack('v', 0) . pack('v', 0) . pack('v', 0)
                 . pack('V', $crc) . pack('V', $len) . pack('V', $len)
                 . pack('v', $nameLen) . pack('v', 0) . pack('v', 0)
                 . pack('v', 0) . pack('v', 0) . pack('V', 0)
                 . pack('V', $offset) . $name;
            $offset += strlen($lfh) + $len;
        }
        $count = count($files);
        $eocd = pack('V', 0x06054b50) . pack('v', 0) . pack('v', 0)
              . pack('v', $count) . pack('v', $count)
              . pack('V', strlen($central)) . pack('V', $offset) . pack('v', 0);
        return $local . $central . $eocd;
    }

    /** @param array<int,mixed> $cells */
    private static function rowXml(int $r, array $cells): string
    {
        $xml = '<row r="' . $r . '">';
        $c = 0;
        foreach ($cells as $val) {
            $ref = self::colLetter($c++) . $r;
            // Treat clean numbers as numeric; everything else as inline text.
            if (is_int($val) || is_float($val) || (is_string($val) && $val !== '' && preg_match('/^-?\d+(\.\d+)?$/', $val))) {
                $xml .= '<c r="' . $ref . '" t="n"><v>' . $val . '</v></c>';
            } else {
                $xml .= '<c r="' . $ref . '" t="inlineStr"><is><t xml:space="preserve">' . self::esc((string) $val) . '</t></is></c>';
            }
        }
        return $xml . '</row>';
    }

    private static function colLetter(int $i): string
    {
        $s = '';
        $i++;
        while ($i > 0) {
            $i--;
            $s = chr(65 + ($i % 26)) . $s;
            $i = intdiv($i, 26);
        }
        return $s;
    }

    private static function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
