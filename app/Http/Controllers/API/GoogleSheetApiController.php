<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\MailGoogleSheet;
use Illuminate\Http\Request;
use Google\Client;
use Google\Service\Sheets;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class GoogleSheetApiController extends Controller
{
    private $client;
    private $googleSheetService;
    private $spreadsheetId;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName('Google Sheets API Laravel');
        $this->client->setScopes(Sheets::SPREADSHEETS);
        $this->client->setAuthConfig(storage_path('tto-sh-8ceca283a64e.json'));
        $this->client->setAccessType('offline');
        $this->googleSheetService = new Sheets($this->client);
        $this->spreadsheetId = '1YS4DriBzADKh8L6QXquBkJyOtsgOEJr3pbP9PMPtICg';
    }

    public function readGoogleSheet()
    {
        try {
            $dimension = $this->getDimensions($this->spreadsheetId);
            $range = 'Sheet1!A2:' . $dimension['colCount'];
            $response = $this->googleSheetService->spreadsheets_values->batchGet($this->spreadsheetId, ['ranges' => $range]);
            $values = $response->getValueRanges()[0]->values ?? [];

            return response()->json([
                'success' => true,
                'message' => 'Lấy dữ liệu thành công.',
                'data' => $values
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    public function postdminGoogleSheet(Request $request)
    {
        try {
            $id = $request->input('id');
            $reply = $request->input('reply');

            if (!$id || !$reply) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng cung cấp ID và câu trả lời.'
                ]);
            }

            // Lấy dữ liệu từ Google Sheets
            $dimension = $this->getDimensions($this->spreadsheetId);
            $range = 'Sheet1!A2:' . $dimension['colCount'];
            $response = $this->googleSheetService->spreadsheets_values->batchGet($this->spreadsheetId, ['ranges' => $range]);
            $values = $response->getValueRanges()[0]->values ?? [];

            // Tìm dòng khớp với ID
            $updated = false;
            $updatedRow = null;
            foreach ($values as $rowIndex => $row) {
                if (isset($row[0]) && $row[0] == $id) { // Giả sử cột ID là cột A (index = 0)
                    // Cập nhật câu trả lời và trạng thái
                    $values[$rowIndex][6] = $reply; // Cột câu trả lời (giả sử index = 5)
                    $updated = true;
                    $updatedRow = $values[$rowIndex];
                    Mail::to($values[$rowIndex][2])->send(new MailGoogleSheet($reply, $values[$rowIndex][1]));
                    break;
                }
            }

            if ($updated) {
                // Cập nhật lại Google Sheets
                $body = new \Google\Service\Sheets\ValueRange(['values' => $values]);
                $params = ['valueInputOption' => 'RAW'];
                $this->googleSheetService->spreadsheets_values->update($this->spreadsheetId, $range, $body, $params);

                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật trạng thái thành công.',
                    'data' => $updatedRow // Trả về thông tin dòng đã cập nhật
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy dòng với ID đã cung cấp.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    public function getSortedData($status = 'noReply', $sortOrder = 'desc')
    {
        try {
            // Lấy dữ liệu từ Google Sheets
            $dimension = $this->getDimensions($this->spreadsheetId);
            $range = 'Sheet1!A3:' . $dimension['colCount'];
            $response = $this->googleSheetService->spreadsheets_values->batchGet($this->spreadsheetId, ['ranges' => $range]);
            $values = $response->getValueRanges()[0]->values ?? [];

            // Lọc dữ liệu theo trạng thái nếu có
            if ($status == 'reply') {
                $values = array_filter($values, function ($row) use ($status) {
                    return isset($row[6]) && $row[6] !== 'Chưa trả lời'; // Giả sử cột trạng thái là cột G (index = 6)
                });
            } else {
                $values;
            }
            // Sắp xếp theo ngày (mới nhất trước hoặc cũ nhất sau)
            usort($values, function ($a, $b) use ($sortOrder) {
                $dateA = strtotime($a[5] ?? ''); // Giả sử cột thời gian là cột F (index = 5)
                $dateB = strtotime($b[5] ?? '');
                return $sortOrder === 'desc' ? $dateB - $dateA : $dateA - $dateB;
            });
            // Kết hợp tiêu đề với dữ liệu đã lọc và sắp xếp

            return response()->json([
                'success' => true,
                'message' => 'Lấy dữ liệu thành công.',
                'data' => $values
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    public function addData(Request $request)
    {
        try {
            $range = "Sheet1";
            $existingData = $this->googleSheetService->spreadsheets_values->get($this->spreadsheetId, $range);
            $rows = $existingData->getValues();
            $lastId = isset($rows) && count($rows) > 1 ? intval($rows[count($rows) - 1][0]) : 0;

            $newId = $lastId + 1;
            $currentTime = Carbon::now()->format('Y-m-d H:i:s');

            $values = [
                [$newId, $request->input('name'), $request->input('email'), $request->input('content'),  $request->input('name_course') ?? '', $currentTime, 'Chưa trả lời']
            ];

            $body = new Sheets\ValueRange(['values' => $values]);
            $params = [
                'valueInputOption' => 'RAW',
                'insertDataOption' => 'INSERT_ROWS'
            ];

            $this->googleSheetService->spreadsheets_values->append($this->spreadsheetId, $range, $body, $params);

            return response()->json([
                'success' => true,
                'message' => 'Bạn đã gửi thông tin thành công.',
                'data' => $values
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    private function getDimensions($spreadSheetId)
    {
        $rowDimensions = $this->googleSheetService->spreadsheets_values->batchGet(
            $spreadSheetId,
            ['ranges' => 'Sheet1!A2:A2', 'majorDimension' => 'COLUMNS']
        );
        $rowMeta = $rowDimensions->getValueRanges()[0]->values;

        $colDimensions = $this->googleSheetService->spreadsheets_values->batchGet(
            $spreadSheetId,
            ['ranges' => 'Sheet1!2:2', 'majorDimension' => 'ROWS']
        );
        $colMeta = $colDimensions->getValueRanges()[0]->values;

        return [
            'rowCount' => count($rowMeta[0] ?? []),
            'colCount' => $this->colLengthToColumnAddress(count($colMeta[0] ?? []))
        ];
    }

    private function colLengthToColumnAddress($number)
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
