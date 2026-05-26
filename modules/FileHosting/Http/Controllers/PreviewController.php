<?php

namespace App\Modules\FileHosting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\FileHosting\Models\File;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpSpreadsheet\IOFactory as ExcelIOFactory;

class PreviewController extends Controller
{
    public function previewWord(Request $request, int $id)
    {
        $file = File::findOrFail($id);
        $zoom = $request->get('zoom', 100);
        
        $filePath = storage_path('app/public/' . $file->path);
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        try {
            $phpWord = WordIOFactory::load($filePath);
            $htmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
            
            $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            padding: 40px; 
            background: #f8f9fa; 
            zoom: ' . $zoom . '%;
        }
        .doc-content { background: white; padding: 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { border-collapse: collapse; width: 100%; }
        td, th { border: 1px solid #ddd; padding: 8px; }
    </style>
</head>
<body>
    <div class="doc-content">
        ' . $htmlWriter->getContent() . '
    </div>
</body>
</html>';

            return response($html, 200, ['Content-Type' => 'text/html']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Cannot preview Word file: ' . $e->getMessage()], 500);
        }
    }

    public function previewExcel(Request $request, int $id)
    {
        $file = File::findOrFail($id);
        $zoom = $request->get('zoom', 100);
        
        $filePath = storage_path('app/public/' . $file->path);
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        try {
            $spreadsheet = ExcelIOFactory::load($filePath);
            
            $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background: #f8f9fa; zoom: ' . $zoom . '%; }
        .excel-content { background: white; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; font-size: 12px; }
        td, th { border: 1px solid #dee2e6; padding: 6px 10px; }
        th { background: #f8f9fa; font-weight: 600; }
    </style>
</head>
<body>
    <div class="excel-content">
        <table class="table table-bordered table-striped">
            <thead>';

            foreach ($spreadsheet->getAllSheets() as $sheetIndex => $sheet) {
                $sheetName = $sheet->getTitle();
                $html .= '<tr><th colspan="100" class="table-primary">Sheet: ' . htmlspecialchars($sheetName) . '</th></tr>';
                
                $html .= '<thead><tbody>';
                
                $rows = $sheet->toArray();
                foreach ($rows as $rowIndex => $row) {
                    $html .= '<tr>';
                    $html .= '<td>' . ($rowIndex + 1) . '</td>';
                    foreach ($row as $cell) {
                        $html .= '<td>' . htmlspecialchars($cell ?? '') . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</tbody>';
            }

            $html .= '</table>
    </div>
</body>
</html>';

            return response($html, 200, ['Content-Type' => 'text/html']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Cannot preview Excel file: ' . $e->getMessage()], 500);
        }
    }
}
