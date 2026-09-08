<?php

declare(strict_types=1);

namespace PhpOffice\PhpSpreadsheetTests\Writer\Xlsx\Streaming;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\File;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Streaming\StreamedCell;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Streaming\StreamingWriter;
use PHPUnit\Framework\TestCase;

class StreamingStyleTest extends TestCase
{
    private string $file = '';

    protected function setUp(): void
    {
        $this->file = File::temporaryFilename();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->file)) {
            unlink($this->file);
        }
    }

    public function testBlankCellsKeepRowStylesAndCellOverrides(): void
    {
        $writer = new StreamingWriter($this->file);
        $rowStyle = $writer->registerStyle([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF0000']],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $cellStyle = $writer->registerStyle(['font' => ['bold' => true]]);
        $sheet = $writer->startSheet('Styles');
        $sheet->appendRow([null, new StreamedCell(null, $cellStyle), new StreamedCell(null, 0)], $rowStyle);
        $sheet->appendRow([null, new StreamedCell(null), new StreamedCell(null, $cellStyle)]);
        $writer->close();

        $spreadsheet = (new Xlsx())->load($this->file);
        $loaded = $spreadsheet->getActiveSheet();
        self::assertTrue($loaded->cellExists('A1'));
        self::assertNull($loaded->getCell('A1')->getValue());
        self::assertSame('FFFF0000', $loaded->getStyle('A1')->getFill()->getStartColor()->getARGB());
        self::assertSame(Border::BORDER_THIN, $loaded->getStyle('A1')->getBorders()->getBottom()->getBorderStyle());
        self::assertTrue($loaded->getStyle('B1')->getFont()->getBold());
        self::assertSame(Fill::FILL_NONE, $loaded->getStyle('B1')->getFill()->getFillType());
        self::assertFalse($loaded->cellExists('C1'));
        self::assertFalse($loaded->cellExists('A2'));
        self::assertFalse($loaded->cellExists('B2'));
        self::assertTrue($loaded->getStyle('C2')->getFont()->getBold());
        $spreadsheet->disconnectWorksheets();
    }
}
