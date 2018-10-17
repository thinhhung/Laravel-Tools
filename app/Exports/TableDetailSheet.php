<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;
use Str;

class TableDetailSheet implements FromCollection, WithTitle
{
    private $connection;

    private $table;

    public function __construct($connection, $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    public function collection()
    {
        $table = $this->connection->getDoctrineSchemaManager()->listTableDetails($this->table);
        $data = [
            ['テーブル定義書'],
            ['名称', str_beautifier($table->getName())],
            ['テーブル名', $table->getName()],
            ['No', '論理テーブルの列名', '物理テーブルの列名', 'データ型', '桁', 'PRIMARY KEY', 'NOT NULL', 'UNIQUE', 'UNSIGNED', 'AUTO INCREMENT', 'INDEX', '初期値', '備考'],
        ];
        $indexes = $table->getIndexes();
        $count = 0;
        foreach ($table->getColumns() as $column) {
            $data[] = [
                ++$count,
                str_beautifier($column->getName()),
                $column->getName(),
                $column->getType(),
                $column->getLength(),
                $this->isPrimary($indexes, $column->getName()) ? 'YES' : '',
                $column->getNotNull() ? 'YES' : '',
                $this->isUnique($indexes, $column->getName()) ? 'YES' : '',
                $column->getUnsigned() ? 'YES' : '',
                $column->getAutoincrement() ? 'YES' : '',
                $this->isSimpleIndex($indexes, $column->getName()) ? 'YES' : '',
                $column->getDefault(),
                $column->getComment(),
            ];
        }
        $data[] = ['No', 'Index', 'Type', 'Column'];
        foreach ($indexes as $index) {
            $columns = $index->getColumns();
            $type = $index->isPrimary() ? 'PRIMARY' : ($index->isUnique() ? 'UNIQUE' : 'INDEX');
            foreach ($columns as $i => $column) {
                $data[] = [
                    $i + 1,
                    $index->getName(),
                    $type,
                    $column,
                ];
            }
        }
        $data[] = ['No', 'Foreign Key', 'Column', 'Referenced Table', 'Referenced Column'];
        $count = 0;
        foreach ($table->getForeignKeys() as $foreignKey) {
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
}
