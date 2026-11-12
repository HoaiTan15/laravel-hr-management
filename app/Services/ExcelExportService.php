<?php

namespace App\Services;

use Phar;
use PharData;
use Symfony\Component\HttpFoundation\StreamedResponse;
use XMLWriter;

class ExcelExportService
{
    /**
     * @param  list<string>  $headings
     * @param  iterable<list<string|int|float|null>>  $rows
     */
    public function download(string $filename, array $headings, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $rows): void {
            $path = sys_get_temp_dir().'/hr-export-'.bin2hex(random_bytes(16)).'.zip';

            try {
                $archive = new PharData($path, 0, null, Phar::ZIP);
                $archive->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
                $archive->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
                $archive->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Du lieu" sheetId="1" r:id="rId1"/></sheets></workbook>');
                $archive->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');

                $xml = new XMLWriter;
                $xml->openMemory();
                $xml->startDocument('1.0', 'UTF-8');
                $xml->startElement('worksheet');
                $xml->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                $xml->startElement('sheetFormatPr');
                $xml->writeAttribute('defaultRowHeight', '15');
                $xml->writeAttribute('defaultColWidth', '24');
                $xml->endElement();
                $xml->startElement('sheetData');
                $this->writeRow($xml, $headings);
                foreach ($rows as $row) {
                    $this->writeRow($xml, $row);
                }
                $xml->endElement();
                $xml->endElement();
                $xml->endDocument();

                $archive->addFromString('xl/worksheets/sheet1.xml', $xml->outputMemory());
                unset($archive);
                readfile($path);
            } finally {
                unset($archive);
                if (is_file($path)) {
                    unlink($path);
                }
            }
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    /** @param list<string|int|float|null> $values */
    private function writeRow(XMLWriter $xml, array $values): void
    {
        $xml->startElement('row');
        foreach ($values as $value) {
            $xml->startElement('c');
            if (is_int($value) || is_float($value)) {
                $xml->writeElement('v', (string) $value);
            } else {
                $xml->writeAttribute('t', 'inlineStr');
                $xml->startElement('is');
                $xml->startElement('t');
                $xml->writeAttribute('xml:space', 'preserve');
                $xml->text(preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', (string) $value) ?? '');
                $xml->endElement();
                $xml->endElement();
            }
            $xml->endElement();
        }
        $xml->endElement();
    }
}
