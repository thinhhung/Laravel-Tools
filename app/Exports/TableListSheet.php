<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TableListSheet implements FromCollection, WithTitle, WithEvents, ShouldAutoSize
{
    private $connection;
    private $headingTitleCellRange;
    private $titleCellRanges;
    private $dataCellRanges;
    private $alignCenterCellRanges;

    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->headingTitleCellRange = 'A1';
        $this->titleCellRanges = [];
        $this->dataCellRanges = [];
        $this->alignCenterCellRanges = [];
    }

    public function collection()
    {
        $tables = $this->connection->getDoctrineSchemaManager()->listTableNames();
        $data = [
            ['テーブル一覧'],
            ['No', '論理テーブル名', '物理テーブル名', '備考'],
        ];
        $this->titleCellRanges[] = 'A2:D2';
        $this->alignCenterCellRanges[] = 'A2:D2';
        foreach ($tables as $index => $table) {
            $data[] = [$index + 1, str_beautifier($table), $table];
        }
        if (count($tables) === 0) {
            $data = [''];
        }
        $count = count($data);
        $this->dataCellRanges[] = 'A3:D' . $count;
        $this->alignCenterCellRanges[] = 'A3:A' . $count;
        return new Collection($data);
    }

    public function title(): string
    {
        return 'テーブル一覧';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                foreach ($this->alignCenterCellRanges as $cellRange) {
                    $event->sheet->getDelegate()->getStyle($cellRange)->getAlignment()->applyFromArray([
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ]);
                }
                $event->sheet->getDelegate()->getStyle($this->headingTitleCellRange)->applyFromArray([
                    'font' => [
                        'size' => 14,
                    ],
                ]);
                foreach ($this->titleCellRanges as $cellRange) {
                    $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => [
                                'rgb' => '8DB4E2',
                            ],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);
                }
                foreach ($this->dataCellRanges as $cellRange) {
                    $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);
                }
            },
        ];
    }
}
