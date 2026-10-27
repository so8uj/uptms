<?php

namespace App\Services;

/**
 * Makes a real Excel file (.xlsx) with one sheet, without any library (the PHP zip extension is not needed).
 *
 *   $file = XlsxWriter::build('Research', ['Code', 'Title', 'Year'], [['RS-2026-0001', 'My thesis', 2026]]);
 *   // $file is the content of the .xlsx: send it with the right headers, or save it
 *
 * An .xlsx is a zip file with a few small XML files inside. They are written here and zipped without compression
 * (a zip may store its files as they are, and Excel opens it).
 * The first row is the header: bold white text on blue, frozen and with a filter. A number stays a number, the rest is text.
 */
class XlsxWriter
{
    public static function build(string $sheetName, array $headers, array $rows): string
    {
        $files = [
            '[Content_Types].xml'        => self::contentTypes(),
            '_rels/.rels'                => self::rootRels(),
            'xl/workbook.xml'            => self::workbook($sheetName),
            'xl/_rels/workbook.xml.rels' => self::workbookRels(),
            'xl/styles.xml'              => self::styles(),
            'xl/worksheets/sheet1.xml'   => self::sheet($headers, $rows),
        ];

        return self::zip($files);
    }

    // ---------------------------------------------------------------------------------
    // The sheet
    // ---------------------------------------------------------------------------------

    private static function sheet(array $headers, array $rows): string
    {
        $columns = count($headers);
        $widths = array_map(fn($header) => mb_strlen((string) $header) + 2, $headers);

        $xml = '';

        // the header row
        $cells = '';
        foreach ($headers as $i => $header) {
            $cells .= self::textCell(self::ref($i, 1), (string) $header, 1);
        }
        $xml .= '<row r="1">' . $cells . '</row>';

        // the data rows
        foreach (array_values($rows) as $r => $row) {
            $number = $r + 2;
            $cells = '';

            foreach (array_values($row) as $i => $value) {
                if ($i >= $columns) {
                    break;
                }

                if (is_int($value) || is_float($value)) {
                    $cells .= '<c r="' . self::ref($i, $number) . '"><v>' . $value . '</v></c>';
                    $widths[$i] = max($widths[$i], strlen((string) $value) + 2);
                } elseif ($value !== null && $value !== '') {
                    $text = (string) $value;
                    $cells .= self::textCell(self::ref($i, $number), $text, 0);
                    $widths[$i] = max($widths[$i], min(60, mb_strlen($text) + 2));
                }
            }

            $xml .= '<row r="' . $number . '">' . $cells . '</row>';
        }

        $cols = '';
        foreach ($widths as $i => $width) {
            $cols .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . min(60, max(8, $width)) . '" customWidth="1"/>';
        }

        $last = self::ref(max(0, $columns - 1), max(1, count($rows) + 1));

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<cols>' . $cols . '</cols>'
            . '<sheetData>' . $xml . '</sheetData>'
            . '<autoFilter ref="A1:' . $last . '"/>'
            . '</worksheet>';
    }

    private static function textCell(string $ref, string $text, int $style): string
    {
        return '<c r="' . $ref . '" t="inlineStr" s="' . $style . '"><is><t xml:space="preserve">' . self::escape($text) . '</t></is></c>';
    }

    // A1, B1 ... Z1, AA1 ...
    private static function ref(int $column, int $row): string
    {
        $letters = '';
        $n = $column + 1;

        while ($n > 0) {
            $n--;
            $letters = chr(65 + $n % 26) . $letters;
            $n = intdiv($n, 26);
        }

        return $letters . $row;
    }

    // Text for XML: no markup, and no character that XML does not allow.
    private static function escape(string $text): string
    {
        $text = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $text) ?? '';

        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    // ---------------------------------------------------------------------------------
    // The small files of the package
    // ---------------------------------------------------------------------------------

    private static function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private static function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private static function workbook(string $sheetName): string
    {
        // a sheet name has at most 31 characters and none of  \ / ? * [ ] :
        $name = mb_substr(preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $sheetName), 0, 31);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . self::escape($name) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private static function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    // style 0 = normal, style 1 = the header (bold, white, on blue)
    private static function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts>'
            . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF0158AC"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs>'
            . '</styleSheet>';
    }

    // ---------------------------------------------------------------------------------
    // The zip (files stored without compression)
    // ---------------------------------------------------------------------------------

    private static function zip(array $files): string
    {
        $time = (int) date('H') << 11 | (int) date('i') << 5 | (int) ((int) date('s') / 2);
        $date = ((int) date('Y') - 1980) << 9 | (int) date('n') << 5 | (int) date('j');

        $body = '';
        $directory = '';
        $count = 0;

        foreach ($files as $name => $content) {
            $crc = crc32($content);
            $size = strlen($content);
            $offset = strlen($body);

            // the header of the file, then the file itself
            $body .= pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, $time, $date, $crc, $size, $size, strlen($name), 0) . $name . $content;

            // the entry of the same file in the list at the end of the zip
            $directory .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 0, $time, $date, $crc, $size, $size, strlen($name), 0, 0, 0, 0, 0, $offset) . $name;
            $count++;
        }

        $end = pack('VvvvvVVv', 0x06054b50, 0, 0, $count, $count, strlen($directory), strlen($body), 0);

        return $body . $directory . $end;
    }
}
