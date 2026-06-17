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
        return self::zip(self::parts($headers, $rows, $sheetName));
    }

    private static function parts(array $headers, array $rows, string $sheetName): array
    {
        return [
            '[Content_Types].xml' => self::contentTypesXml(),
            '_rels/.rels' => self::packageRelsXml(),
            'xl/workbook.xml' => self::workbookXml($sheetName),
            'xl/_rels/workbook.xml.rels' => self::workbookRelsXml(),
            'xl/worksheets/sheet1.xml' => self::worksheetXml($headers, $rows),
        ];
    }

    private static function worksheetXml(array $headers, array $rows): string
    {
        return self::xmlHeader()
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>' . self::sheetDataXml($headers, $rows) . '</sheetData></worksheet>';
    }

    private static function sheetDataXml(array $headers, array $rows): string
    {
        $sheetData = self::rowXml(1, array_values($headers));
        foreach ($rows as $i => $row) {
            $sheetData .= self::rowXml($i + 2, array_values($row));
        }
        return $sheetData;
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
            $meta = self::zipMeta($name, $data, $offset);
            $local .= $meta['local'] . $data;
            $central .= $meta['central'];
            $offset += strlen($meta['local']) + strlen($data);
        }
        return $local . $central . self::eocd(count($files), strlen($central), $offset);
    }

    private static function zipMeta(string $name, string $data, int $offset): array
    {
        $crc = crc32($data);
        $len = strlen($data);
        $nameLen = strlen($name);
        return [
            'local' => self::localHeader($crc, $len, $nameLen, $name),
            'central' => self::centralHeader($crc, $len, $nameLen, $offset, $name),
        ];
    }

    private static function localHeader(int $crc, int $len, int $nameLen, string $name): string
    {
        return pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, 0, 0, $crc, $len, $len, $nameLen, 0) . $name;
    }

    private static function centralHeader(int $crc, int $len, int $nameLen, int $offset, string $name): string
    {
        return pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 0, 0, 0, $crc, $len, $len, $nameLen, 0, 0, 0, 0, 0, $offset) . $name;
    }

    private static function eocd(int $count, int $centralLen, int $offset): string
    {
        return pack('VvvvvVVv', 0x06054b50, 0, 0, $count, $count, $centralLen, $offset, 0);
    }

    private static function xmlHeader(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    }

    private static function contentTypesXml(): string
    {
        return self::xmlHeader() . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '</Types>';
    }

    private static function packageRelsXml(): string
    {
        return self::xmlHeader() . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private static function workbookXml(string $sheetName): string
    {
        return self::xmlHeader()
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . self::esc($sheetName) . '" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private static function workbookRelsXml(): string
    {
        return self::xmlHeader() . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>';
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
