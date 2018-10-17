<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DataDocExport implements WithMultipleSheets
{
    use Exportable;

    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [new TableListSheet($this->connection)];
        $tables = $this->connection->getDoctrineSchemaManager()->listTableNames();
        foreach ($tables as $table) {
            $sheets[] = new TableDetailSheet($this->connection, $table);
        }
        return $sheets;
    }
}
