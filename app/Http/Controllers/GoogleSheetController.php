<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client;
use Google\Service\Sheets;
use Carbon\Carbon;

class GoogleSheetController extends Controller
{
    private $client;
    private $googleSheetService;
    private $spreadsheetId;
    public function __construct()
    {
        // Khởi tạo Google Client
        $this->client = new Client();
        $this->client->setApplicationName('Google Sheets API Laravel');
        $this->client->setScopes(Sheets::SPREADSHEETS);
        $this->client->setAuthConfig(storage_path('tto-sh-8ceca283a64e.json')); // Đường dẫn đến file JSON
        $this->client->setAccessType('offline');
        $this->googleSheetService = new Sheets($this->client);
        $this->spreadsheetId = '1YS4DriBzADKh8L6QXquBkJyOtsgOEJr3pbP9PMPtICg';
    }

    public function readGoogleSheet()
    {
        // $dimension = $this->getDimensions($this->spreadsheetId);
        // $range = 'Sheet1!A1:'.$dimension['colCount'];
        // $data = $this->googleSheetService->spreadsheets_values->batchGet($this->spreadsheetId, ['ranges' => $range]);

        try {
            // $sheets = new Sheets($this->client);

            // $range = 'Sheet1!A1:D10'; // Phạm vi dữ liệu

            // $response = $sheets->spreadsheets_values->get($spreadsheetId, $range);
            // $values = $response->getValues();
            $dimension = $this->getDimensions($this->spreadsheetId);
            $range = 'Sheet1!A2:' . $dimension['colCount'];
            $data = $this->googleSheetService->spreadsheets_values->batchGet($this->spreadsheetId, ['ranges' => $range]);
            $values = $data[0]->values;
            if (empty($values)) {
                return view('googleSeet', ['message' => 'No data found.', 'data' => []]);
            }

            return view('googleSeet', ['message' => 'Data loaded successfully.', 'data' =>  $values]);
        } catch (\Exception $e) {
            return view('googleSeet', ['message' => 'Error: ' . $e->getMessage(), 'data' => []]);
        }
    }
    public function showForm()
    {
        return view('addGoogleSeet', ['message' => null, 'data' => []]);
    }
    public function writeGoogleSheet()
    {
        try {
            $sheets = new Sheets($this->client);

            // Chỉ định tên sheet, không cần xác định vị trí cụ thể
            $range = "Sheet1";

            $values = [
                ["3", "Alice", "alice@example.com", "content34"],
                ["4", "Bob", "bob@example.com", "content35"],
            ];

            $body = new Sheets\ValueRange([
                'values' => $values
            ]);

            $params = [
                'valueInputOption' => 'RAW',          // Dữ liệu thêm vào theo dạng thô
                'insertDataOption' => 'INSERT_ROWS'  // Thêm dòng mới, không ghi đè
            ];

            // Sử dụng phương thức append để thêm vào cuối
            $sheets->spreadsheets_values->append(
                $this->spreadsheetId,
                $range,
                $body,
                $params
            );

            return view('googleSeet', ['message' => 'Data appended successfully.', 'data' => $values]);
        } catch (\Exception $e) {
            return view('googleSeet', ['message' => 'Error: ' . $e->getMessage(), 'data' => []]);
        }
    }
    public function addData(Request $request)
    {
        try {
            $sheets = new Sheets($this->client);

            $range = "Sheet1";
            // Lấy dữ liệu hiện có từ Google Sheets để tìm ID lớn nhất
            $existingData = $sheets->spreadsheets_values->get($this->spreadsheetId, 'Sheet1');
            $rows = $existingData->getValues();
            $lastId = isset($rows) && count($rows) > 1 ? intval($rows[count($rows) - 1][0]) : 0;

            // Tạo dữ liệu mới
            $newId = $lastId + 1;
            $currentTime = Carbon::now()->format('Y-m-d H:i:s'); // Lấy thời gian hiện tại

            $values = [
                [$newId, $request->input('name'), $request->input('email'), $request->input('content'), $currentTime]
            ];

            $body = new Sheets\ValueRange([
                'values' => $values
            ]);
            $params = [
                'valueInputOption' => 'RAW',
                'insertDataOption' => 'INSERT_ROWS'
            ];

            $sheets->spreadsheets_values->append(
                $this->spreadsheetId,
                $range,
                $body,
                $params
            );

            return redirect()->route('showForm')->with('message', 'Dữ liệu đã được thêm thành công!')->with('data', $values);
        } catch (\Exception $e) {
            return redirect()->route('showForm')->with('message', 'Lỗi: ' . $e->getMessage())->with('data', []);
        }
    }
    private function getDimensions($spreadSheetId)
    {
        $rowDimensions = $this->googleSheetService->spreadsheets_values->batchGet(
            $spreadSheetId,
            ['ranges' => 'Sheet1!A2:A2', 'majorDimension' => 'COLUMNS']
        );

        //if data is present at nth row, it will return array till nth row
        //if all column values are empty, it returns null
        $rowMeta = $rowDimensions->getValueRanges()[0]->values;
        if (! $rowMeta) {
            return [
                'error' => true,
                'message' => 'missing row data'
            ];
        }

        $colDimensions = $this->googleSheetService->spreadsheets_values->batchGet(
            $spreadSheetId,
            ['ranges' => 'Sheet1!2:2', 'majorDimension' => 'ROWS']
        );

        //if data is present at nth col, it will return array till nth col
        //if all column values are empty, it returns null
        $colMeta = $colDimensions->getValueRanges()[0]->values;
        if (! $colMeta) {
            return [
                'error' => true,
                'message' => 'missing row data'
            ];
        }

        return [
            'error' => false,
            'rowCount' => count($rowMeta[0]),
            'colCount' => $this->colLengthToColumnAddress(count($colMeta[0]))
        ];
    }

    public  function colLengthToColumnAddress($number)
    {
        if ($number <= 0) return null;

        $letter = '';
        while ($number > 0) {
            $temp = ($number - 1) % 26;
            $letter = chr($temp + 65) . $letter;
            $number = ($number - $temp - 1) / 26;
        }
        return $letter;
    }
}
