<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Plat;
use App\Http\Resources\PlatResource;
use App\Http\Controllers\RestResponseController;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Ramsey\Uuid\Uuid as Generator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PlatController extends Controller
{
    public function __construct() 
    {
        $this->response = new RestResponseController();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name'     => 'required',
            'check_in' => 'required'
        ]);

        $exist = Plat::where('name', $request->name)
                    ->whereNull('check_out')
                    ->first();

        if ($exist) {
            return $this->response->json(409, 'A vehicle with plate number '.$exist->name.' is in the parking lot');
        }

        $store = new Plat;
        $store->name        = $request->name;
        $store->check_in    = $request->check_in;
        $store->unique_code = $this->generateUniqCode();
        $store->check_out   = NULL;
        $store->cost        = NULL;
        $store->save();

        return $this->response->json(200, 'Data saved successfully', $store);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'unique_code' => 'required',
            'check_out'   => 'required'
        ]);

        $plat = Plat::where('unique_code', $request->unique_code)
                ->whereNull('check_out')
                ->first();

        if (!empty($plat)) {
            $startTime     = Carbon::parse($plat->check_in);
            $finishTime    = Carbon::parse($request->check_out);
            $totalDuration = $finishTime->diffInHours($startTime);
            if ($totalDuration <= 0) {
                $totalDuration = 1;
            }
            $cost = $totalDuration * 3000;
            $data['cost']      = $cost;
            $data['check_out'] = $finishTime;
            $update = Plat::where('unique_code', $request->unique_code)->update($data);

            return $this->response->json(200, 'Data update successfully', $update);
        }

        return $this->response->json(404, 'No updated data at check out');
    }

    public function generateUniqCode()
    {
        try {
            return Generator::uuid4()->toString();
        } catch (UnsatisfiedDependencyException $e) {
            return $this->response->json(500, $e->getMessage());
        }
    }

    public function getAll(Request $request)
    {
        if (!empty($request->start_check_in) && !empty($request->to_check_in)) {
            $startDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->start_check_in.' 00:00:00');
            $endDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->to_check_in.' 23:59:59');

            $plat = Plat::whereBetween('check_in', [$startDate, $endDate])->orderBy('id', 'DESC')->get();
        } else {
            $plat = Plat::orderBy('id', 'DESC')->get();
        }

        $listPlat = PlatResource::collection($plat);

        return $this->response->json(200, 'Get data success.', $listPlat);
    }

    public function getReport(Request $request)
    {
        if (!empty($request->start_check_in) && !empty($request->to_check_in)) {
            $startDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->start_check_in.' 00:00:00');
            $endDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->to_check_in.' 23:59:59');

            $plat = Plat::whereBetween('check_in', [$startDate, $endDate])->get();
        } else {
            $plat = Plat::get();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Kode Unik');
        $sheet->setCellValue('C1', 'Nama');
        $sheet->setCellValue('D1', 'Check In');
        $sheet->setCellValue('E1', 'Check Out');
        $sheet->setCellValue('F1', 'Cost');

        $rows = 2;
        foreach($plat as $item)
        {   
            $sheet->setCellValue('A' . $rows, ($rows-1));
            $sheet->setCellValue('B' . $rows, $item['unique_code']);
            $sheet->setCellValue('C' . $rows, $item['name']);
            $sheet->setCellValue('D' . $rows, $item['check_in']);
            $sheet->setCellValue('E' . $rows, $item['check_out']);
            $sheet->setCellValue('F' . $rows, $item['cost']);
            $rows++;
        }

        $tableHead = array(
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            )
        );
        $tableBody = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle("A1:F1")->applyFromArray($tableHead);
        $sheet->getStyle('A1:F'.($rows-1))->applyFromArray($tableBody);

        foreach(range('A','F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $sheet->setTitle("Report");
        
        $fileName = "report.xlsx";
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename='.$fileName); // Set nama file excel nya
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        // $writer->save($fileName);
        // if(file_exists($fileName)){
        //     echo json_encode(array('error'=>false, 'export_path'=>'download_excel/' . 'excel-report.xlsx')); //my angular project is at D:\wamp64\www\angular6-app\client\
        // }
    }
}
