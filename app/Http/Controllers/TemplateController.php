<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TemplateController extends Controller
{
    public function downloadSiswaTemplate()
    {
        // Membuat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header kolom
        $columns = ['nis', 'nama_siswa', 'kelas', 'wali_murid', 'jenis_kelamin', 'alamat', 'no_telp'];
        foreach ($columns as $key => $column) {
            $sheet->setCellValue(chr(65 + $key) . '1', $column);
        }

        // Tambahkan contoh data
        $sheet->setCellValue('A2', '123456');
        $sheet->setCellValue('B2', 'Nama Siswa');
        $sheet->setCellValue('C2', 'X IPA 1'); // Nama kelas yang ada di database
        $sheet->setCellValue('D2', 'Nama Wali Murid'); // Nama wali murid yang ada di database
        $sheet->setCellValue('E2', 'L'); // L atau P
        $sheet->setCellValue('F2', 'Alamat Siswa');
        $sheet->setCellValue('G2', '08123456789');

        // Tambahkan penjelasan format
        $sheet->setCellValue('A4', 'Catatan:');
        $sheet->setCellValue('A5', '- NIS: Harus unik dan minimal 6 digit');
        $sheet->setCellValue('A6', '- Kelas: Harus sama persis dengan nama kelas yang ada di sistem');
        $sheet->setCellValue('A7', '- Wali Murid: Harus sama persis dengan nama wali murid yang ada di sistem');
        $sheet->setCellValue('A8', '- Jenis Kelamin: Gunakan L untuk Laki-laki atau P untuk Perempuan');

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
