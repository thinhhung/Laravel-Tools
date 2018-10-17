<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class TableListSheet implements FromCollection, WithTitle
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function collection()
    {
        $tables = $this->connection->getDoctrineSchemaManager()->listTableNames();
        $data = [
            ['テーブル一覧'],
            ['No', '論理テーブル名', '物理テーブル名', '備考'],
        ];
        foreach ($tables as $index => $table) {
            $data[] = [$index + 1, str_beautifier($table), $table];
        }
        return new Collection($data);
    }

    public function title(): string
    {
        return 'テーブル一覧';
    }
}
