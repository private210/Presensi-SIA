<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DownloadTemplateController extends Controller
{
    public function downloadUserTemplate()
    {
        // Buat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Tambahkan catatan penggunaan di bagian atas
        $sheet->setCellValue('A1', 'TEMPLATE IMPORT USER');
        $sheet->mergeCells('A1:D1');

        $sheet->setCellValue('A2', 'Catatan Penggunaan:');
        $sheet->mergeCells('A2:D2');

        $sheet->setCellValue('A3', '1. Kolom nama: Isi dengan nama lengkap pengguna');
        $sheet->mergeCells('A3:D3');

        $sheet->setCellValue('A4', '2. Kolom email: Isi dengan alamat email unik (belum terdaftar)');
        $sheet->mergeCells('A4:D4');

        $sheet->setCellValue('A5', '3. Kolom password: Isi dengan password pengguna (min. 8 karakter). Jika kosong, password default "password123" akan digunakan');
        $sheet->mergeCells('A5:D5');

        $sheet->setCellValue('A6', '4. Kolom role: Isi dengan nama role yang sudah ada di sistem (super_admin, Wali Kelas, Wali Murid, Kepala Sekolah)');
        $sheet->mergeCells('A6:D6');

        $sheet->setCellValue('A8', 'JANGAN UBAH BARIS HEADER!');
        $sheet->mergeCells('A8:D8');
        // Tambahkan catatan di bawah tabel contoh

        $sheet->setCellValue('A7', 'Silakan hapus data contoh di atas dan isi dengan data pengguna yang ingin diimport.');
        $sheet->mergeCells('A7:D7' );

        // Format header catatan
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A8')->getFont()->setBold(true);
        $sheet->getStyle('A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A8')->getFont()->getColor()->setRGB('FF0000');

        // Berikan sedikit jarak
        $sheet->setCellValue('A9', '');

        // Tambahkan header untuk data
        $headers = ['nama', 'email', 'password', 'roles'];
        $headerRow = 10;
        foreach ($headers as $index => $header) {
            $column = chr(65 + $index); // A, B, C, D
            $sheet->setCellValue($column . $headerRow, $header);
            $sheet->getStyle($column . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle($column . $headerRow)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('DDDDDD');
            $sheet->getStyle($column . $headerRow)->getBorders()
                ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        // Tambahkan data contoh
        $dataRow = 11;
        $exampleData = [
            ['Budi Santoso', 'budi@example.com', 'rahasia123', 'Wali Kelas'],
            ['Ani Wijaya', 'ani@example.com', 'pass123', 'Wali Murid'],
            ['Siti Aminah', 'siti@example.com', '', 'Kepala Sekolah'],
        ];

        foreach ($exampleData as $rowIndex => $rowData) {
            $currentRow = $dataRow + $rowIndex;
            foreach ($rowData as $colIndex => $cellValue) {
                $column = chr(65 + $colIndex); // A, B, C, D
                $sheet->setCellValue($column . $currentRow, $cellValue);
                $sheet->getStyle($column . $currentRow)->getBorders()
                    ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
        }

        // Set lebar kolom agar sesuai dengan isi
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);

        // Beri warna berbeda untuk contoh data
        $sheet->getStyle('A11:D' . ($dataRow + count($exampleData) - 1))->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFCCC');



        // Create writer and output
        $writer = new Xlsx($spreadsheet);

        $filename = 'template_import_user.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
