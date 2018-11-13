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

class TableDetailSheet implements FromCollection, WithTitle, WithEvents, ShouldAutoSize
{
    private $connection;
    private $table;
    private $headingTitleCellRange;
    private $titleCellRanges;
    private $dataCellRanges;
    private $alignCenterCellRanges;

    public function __construct($connection, $table)
    {
        $this->connection = $connection;
        $this->table = $table;
        $this->headingTitleCellRange = 'A1';
        $this->titleCellRanges = [];
        $this->dataCellRanges = [];
        $this->alignCenterCellRanges = [];
    }

    public function collection()
    {
        $table = $this->connection->getDoctrineSchemaManager()->listTableDetails($this->table);
        $data = [
            [''],
            ['テーブル名', $table->getName()],
            ['スキーマ', 'public'],
            ['備考', str_beautifier($table->getName())],
            [''],
            ['カラム情報'],
            ['No', '項目名', 'データ型', '主キー', '必須', 'ユニック', '自動インクリメント', 'インデックス', '初期値', '備考'],
        ];
        $this->titleCellRanges[] = 'A2:A4';
        $this->dataCellRanges[] = 'B2:B4';
        $this->titleCellRanges[] = 'A7:J7';
        $this->alignCenterCellRanges[] = 'A7:J7';
        $indexes = $table->getIndexes();
        $count = 0;
        $rowCount = 7;
        $beginCell = 'A' . (++$rowCount);
        $columns = $table->getColumns();
        foreach ($columns as $column) {
            $data[] = [
                ++$count,
                $column->getName(),
                ($length = $column->getLength()) ? $column->getType() . ' (' . $length . ')' : $column->getType(),
                $this->isPrimary($indexes, $column->getName()) ? 'Y' : '',
                $column->getNotNull() ? 'Y' : '',
                $this->isUnique($indexes, $column->getName()) ? 'Y' : '',
                $column->getAutoincrement() ? 'Y' : '',
                $this->isSimpleIndex($indexes, $column->getName()) ? 'Y' : '',
                $column->getDefault(),
                ($comment = $column->getComment()) ? $comment : str_beautifier($column->getName()),
                // $column->getUnsigned() ? 'YES' : '',
            ];
        }
        if (count($columns) === 0) {
            $data = [''];
        }
        $rowCount = count($data);
        $this->dataCellRanges[] = $beginCell . ':J' . $rowCount;
        $this->alignCenterCellRanges[] = $beginCell . ':A' . $rowCount;
        $this->alignCenterCellRanges[] = 'D' . substr($beginCell, 1) . ':I' . $rowCount;
        $data[] = [''];
        $data[] = ['インデックス情報'];
        $data[] = ['No', 'インデックス名', 'カラムリスト'];
        $rowCount = $rowCount + 3;
        $this->titleCellRanges[] = 'A' . $rowCount . ':C' . $rowCount;
        $this->alignCenterCellRanges[] = 'A' . $rowCount . ':C' . $rowCount;
        $beginCell = 'A' . (++$rowCount);
        foreach ($indexes as $index) {
            $columns = $index->getColumns();
            // $type = $index->isPrimary() ? 'PRIMARY' : ($index->isUnique() ? 'UNIQUE' : 'INDEX');
            foreach ($columns as $i => $column) {
                $data[] = [
                    $i + 1,
                    $index->getName(),
                    // $type,
                    $column,
                ];
            }
        }
        if (count($indexes) === 0) {
            $data = [''];
        }
        $rowCount = count($data);
        $this->dataCellRanges[] = $beginCell . ':C' . $rowCount;
        $this->alignCenterCellRanges[] = $beginCell . ':A' . $rowCount;
        $data[] = [''];
        $data[] = ['外部キー情報'];
        $data[] = ['No', '外部キー名', 'カラム名', '参照先テーブル名', '参照先カラム名'];
        $rowCount = $rowCount + 3;
        $this->titleCellRanges[] = 'A' . $rowCount . ':E' . $rowCount;
        $this->alignCenterCellRanges[] = 'A' . $rowCount . ':E' . $rowCount;
        $count = 0;
        $beginCell = 'A' . (++$rowCount);
        $foreignKeys = $table->getForeignKeys();
        foreach ($foreignKeys as $foreignKey) {
            $columns = $foreignKey->getColumns();
            $foreignColumns = $foreignKey->getForeignColumns();
            foreach ($columns as $i => $column) {
                $data[] = [
                    ++$count,
                    $foreignKey->getName(),
                    $column,
                    $foreignKey->getForeignTableName(),
                    $foreignColumns[$i],
                ];
            }
        }
        if (count($foreignKeys) === 0) {
            $data[] = [''];
        }
        $rowCount = count($data);
        $this->dataCellRanges[] = $beginCell . ':E' . $rowCount;
        $this->alignCenterCellRanges[] = $beginCell . ':A' . $rowCount;
        return new Collection($data);
    }

    public function title(): string
    {
        return str_beautifier($this->table);
    }

    private function isPrimary($indexes, $column)
    {
        foreach ($indexes as $index) {
            if (in_array($column, $index->getColumns())) {
                return $index->isPrimary();
            }
        }
        return false;
    }

    private function isUnique($indexes, $column)
    {
        foreach ($indexes as $index) {
            if (in_array($column, $index->getColumns())) {
                return $index->isUnique();
            }
        }
        return false;
    }

    private function isSimpleIndex($indexes, $column)
    {
        foreach ($indexes as $index) {
            if (in_array($column, $index->getColumns())) {
                return $index->isSimpleIndex();
            }
        }
        return false;
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
