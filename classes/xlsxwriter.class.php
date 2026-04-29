<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

/**
 * Minimal XLSX (Office Open XML SpreadsheetML) writer.
 *
 * Single-sheet workbooks, no third-party dependencies — uses the built-in
 * ZipArchive + libxml. Cell types: string (inline), number, currency,
 * date, datetime. Date values may be passed as a unix timestamp or any
 * string accepted by strtotime(); they are converted to Excel date
 * serials in the host timezone so the displayed date matches what
 * users see elsewhere in the admin.
 *
 * Usage:
 *   $xlsx = new XLSXWriter();
 *   $xlsx->setSheetName('Sales')
 *        ->setHeaders(array('Order #', 'Total', 'Date'))
 *        ->setColumnTypes(array(XLSXWriter::TYPE_STRING, XLSXWriter::TYPE_CURRENCY, XLSXWriter::TYPE_DATETIME))
 *        ->addRow(array('250001', 19.99, time()));
 *   $xlsx->download('sales-report.xlsx');   // streams to browser
 *   // or: $bytes = $xlsx->build();         // returns binary string
 */
class XLSXWriter
{
    const TYPE_STRING   = 'string';
    const TYPE_NUMBER   = 'number';
    const TYPE_CURRENCY = 'currency';
    const TYPE_DATE     = 'date';
    const TYPE_DATETIME = 'datetime';

    private $sheet_name = 'Sheet1';
    private $headers    = array();
    private $col_types  = array();
    private $rows       = array();

    public function setSheetName($name)
    {
        // Excel sheet names: max 31 chars; reserved chars [ ] : * ? / \ stripped.
        $name = preg_replace('/[\[\]:\*\?\/\\\\]/', '_', (string)$name);
        $this->sheet_name = mb_substr($name, 0, 31) ?: 'Sheet1';
        return $this;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = array_values($headers);
        return $this;
    }

    public function setColumnTypes(array $types)
    {
        $this->col_types = array_values($types);
        return $this;
    }

    public function addRow(array $row)
    {
        $this->rows[] = array_values($row);
        return $this;
    }

    /**
     * Build the workbook and return the binary XLSX as a string.
     */
    public function build()
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('XLSXWriter: unable to open temp zip');
        }
        $zip->addFromString('[Content_Types].xml',         $this->contentTypes());
        $zip->addFromString('_rels/.rels',                 $this->rootRels());
        $zip->addFromString('xl/workbook.xml',             $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels',  $this->workbookRels());
        $zip->addFromString('xl/styles.xml',               $this->styles());
        $zip->addFromString('xl/worksheets/sheet1.xml',    $this->sheet());
        $zip->close();
        $data = file_get_contents($tmp);
        @unlink($tmp);
        return $data;
    }

    /**
     * Stream the workbook to the browser as an attachment.
     */
    public function download($filename)
    {
        $data = $this->build();
        if (!preg_match('/\.xlsx$/i', $filename)) {
            $filename .= '.xlsx';
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.basename($filename).'"');
        header('Content-Length: '.strlen($data));
        header('Cache-Control: no-store');
        echo $data;
    }

    private function contentTypes()
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

    private function rootRels()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbook()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="'.$this->xmlEscape($this->sheet_name).'" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function workbookRels()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    /**
     * cellXfs indices used in cell `s` attribute:
     *   0 = default (string / general)
     *   1 = number       (numFmtId 2  = "0.00")
     *   2 = currency     (numFmtId 164 custom = "#,##0.00;(#,##0.00)" — accounting parens for negatives)
     *   3 = date         (numFmtId 14 = locale short date)
     *   4 = datetime     (numFmtId 22 = locale short date + time)
     */
    private function styles()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="1"><numFmt numFmtId="164" formatCode="#,##0.00;(#,##0.00)"/></numFmts>'
            . '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            . '<borders count="1"><border/></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="5">'
            .   '<xf numFmtId="0"   fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .   '<xf numFmtId="2"   fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            .   '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            .   '<xf numFmtId="14"  fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            .   '<xf numFmtId="22"  fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '</cellXfs>'
            . '</styleSheet>';
    }

    private function sheet()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
             . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
             . '<sheetData>';
        $r = 0;
        if ($this->headers) {
            $r++;
            $header_types = array_fill(0, count($this->headers), self::TYPE_STRING);
            $xml .= $this->writeRow($r, $this->headers, $header_types);
        }
        foreach ($this->rows as $row) {
            $r++;
            $xml .= $this->writeRow($r, $row, $this->col_types);
        }
        $xml .= '</sheetData></worksheet>';
        return $xml;
    }

    private function writeRow($r, array $cells, array $types)
    {
        $xml = '<row r="'.$r.'">';
        foreach ($cells as $i => $val) {
            $type = isset($types[$i]) ? $types[$i] : self::TYPE_STRING;
            $ref  = $this->colLetter($i + 1) . $r;
            $xml .= $this->writeCell($ref, $val, $type);
        }
        $xml .= '</row>';
        return $xml;
    }

    private function writeCell($ref, $val, $type)
    {
        if ($val === null || $val === '') {
            return '<c r="'.$ref.'"/>';
        }
        switch ($type) {
            case self::TYPE_NUMBER:
                return '<c r="'.$ref.'" s="1"><v>'.$this->numericValue($val).'</v></c>';
            case self::TYPE_CURRENCY:
                return '<c r="'.$ref.'" s="2"><v>'.$this->numericValue($val).'</v></c>';
            case self::TYPE_DATE:
                $serial = $this->dateSerial($val);
                return ($serial === null)
                    ? $this->stringCell($ref, $val)
                    : '<c r="'.$ref.'" s="3"><v>'.$serial.'</v></c>';
            case self::TYPE_DATETIME:
                $serial = $this->dateSerial($val);
                return ($serial === null)
                    ? $this->stringCell($ref, $val)
                    : '<c r="'.$ref.'" s="4"><v>'.$serial.'</v></c>';
            case self::TYPE_STRING:
            default:
                return $this->stringCell($ref, $val);
        }
    }

    private function stringCell($ref, $val)
    {
        return '<c r="'.$ref.'" t="inlineStr"><is><t xml:space="preserve">'.$this->xmlEscape($val).'</t></is></c>';
    }

    /**
     * Coerce $val to a float and emit it without locale formatting (always '.' decimal).
     */
    private function numericValue($val)
    {
        if (is_numeric($val)) {
            return rtrim(rtrim(sprintf('%.10F', (float)$val), '0'), '.');
        }
        // Best-effort: strip currency symbols and grouping for "$1,234.56" style strings.
        $clean = preg_replace('/[^0-9eE\.\-\+]/', '', (string)$val);
        return is_numeric($clean) ? rtrim(rtrim(sprintf('%.10F', (float)$clean), '0'), '.') : '0';
    }

    /**
     * Convert a unix timestamp or strtotime-able string into an Excel date serial
     * (days since 1899-12-30, accounting for the 1900 leap year bug). The host
     * timezone offset is applied so the displayed date matches local time.
     * Returns null if the value can't be parsed.
     */
    private function dateSerial($val)
    {
        $ts = is_numeric($val) ? (int)$val : strtotime((string)$val);
        if (!$ts) {
            return null;
        }
        $shifted = $ts + (int)date('Z', $ts);
        return ($shifted / 86400) + 25569;
    }

    private function colLetter($n)
    {
        $s = '';
        while ($n > 0) {
            $n--;
            $s = chr(65 + ($n % 26)) . $s;
            $n = intdiv($n, 26);
        }
        return $s;
    }

    private function xmlEscape($v)
    {
        return htmlspecialchars((string)$v, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
