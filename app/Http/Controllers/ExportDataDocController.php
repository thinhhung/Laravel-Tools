<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Excel;
use App\Http\Requests\ExportDataDoc\Store;
use App\Exports\DataDocExport;
use Carbon\Carbon;
use DB;
use Config;

class ExportDataDocController extends Controller
{
    private $excel;

    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
    }

    public function index()
    {
        return view('export-data-doc.index');
    }

    public function store(Store $request)
    {
        $date = Carbon::now()->format('Ymd');
        Config::set('database.connections.export_connection', [
            'driver' => 'mysql',
            'host' => $request->input('host'),
            'database' => $request->input('database'),
            'username' => $request->input('username'),
            'password' => $request->input('password'),
        ]);
        return $this->excel->download(new DataDocExport(DB::connection('export_connection')), 'テーブル定義書_' . $date . '.xlsx');
    }
}
