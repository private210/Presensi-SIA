<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TemplateController extends Controller
{
    public function downloadSiswaTemplate()
    {
        // Membuat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Tambahkan header informasi
        $sheet->setCellValue('A1', 'TEMPLATE IMPORT SISWA');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Catatan penggunaan
        $sheet->setCellValue('A2', 'Catatan Penggunaan:');
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getFont()->setBold(true);

        $sheet->setCellValue('A3', '1. NIS: Wajib unik dan minimal 6 digit. Jika mengedit data siswa, gunakan NIS yang sama.');
        $sheet->mergeCells('A3:G3');

        $sheet->setCellValue('A4', '2. Kelas: Harus sama persis dengan nama kelas yang sudah ada di sistem.');
        $sheet->mergeCells('A4:G4');

        $sheet->setCellValue('A5', '3. Wali Murid: Harus sama persis dengan nama wali murid yang terdaftar di sistem dan memiliki role "Wali Murid".');
        $sheet->mergeCells('A5:G5');

        $sheet->setCellValue('A6', '4. Jenis Kelamin: Gunakan L untuk Laki-laki atau P untuk Perempuan.');
        $sheet->mergeCells('A6:G6');

        $sheet->setCellValue('A7', 'PERHATIAN: Jangan mengubah baris header (baris ke-9)!');
        $sheet->mergeCells('A7:G7');
        $sheet->getStyle('A7')->getFont()->setBold(true);
        $sheet->getStyle('A7')->getFont()->getColor()->setRGB('FF0000');
        $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Tambahkan baris kosong sebagai pemisah
        $sheet->setCellValue('A8', '');

        // Set header kolom
        $headers = ['nis', 'nama_siswa', 'kelas', 'wali_murid', 'jenis_kelamin', 'alamat', 'no_telp'];
        $headerRow = 9;

        foreach ($headers as $key => $column) {
            $colLetter = chr(65 + $key); // A, B, C, dst
            $sheet->setCellValue($colLetter . $headerRow, $column);

            // Format header
            $sheet->getStyle($colLetter . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle($colLetter . $headerRow)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('DDDDDD');
            $sheet->getStyle($colLetter . $headerRow)->getBorders()
                ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        // Tambahkan contoh data
        $dataRow = 10;
        $exampleData = [
            ['123456', 'Nama Siswa 1', 'X IPA 1', 'Nama Wali Murid 1', 'L', 'Alamat Siswa 1', '08123456789'],
            ['234567', 'Nama Siswa 2', 'X IPA 1', 'Nama Wali Murid 2', 'P', 'Alamat Siswa 2', '08234567890'],
            ['345678', 'Nama Siswa 3', 'X IPA 2', 'Nama Wali Murid 3', 'L', 'Alamat Siswa 3', '08345678901'],
        ];

        foreach ($exampleData as $rowIndex => $rowData) {
            foreach ($rowData as $colIndex => $cellValue) {
                $colLetter = chr(65 + $colIndex);
                $sheet->setCellValue($colLetter . ($dataRow + $rowIndex), $cellValue);
                $sheet->getStyle($colLetter . ($dataRow + $rowIndex))->getBorders()
                    ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
        }

        // Beri warna berbeda untuk contoh data
        $sheet->getStyle('A10:G' . ($dataRow + count($exampleData) - 1))->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFCCC');

        // Set lebar kolom agar sesuai dengan isi
        for ($i = 0; $i < count($headers); $i++) {
            $colLetter = chr(65 + $i);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Simpan ke file temporary
        $writer = new Xlsx($spreadsheet);
        $filename = 'template_import_siswa.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($temp_file);

        // Download file
        return response()->download($temp_file, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
