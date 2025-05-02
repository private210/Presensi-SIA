<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit="generateReport" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Kelas Dropdown - Tampilkan hanya jika bukan Wali Murid -->
                <div class="col-span-1">
                    <x-filament::input.wrapper id="kelas_id" label="Kelas">
                        @if(auth()->user()->hasRole('Wali Murid'))
                            <div class="px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-700">
                                @if($kelas_id)
                                    {{ \App\Models\Kelas::find($kelas_id)->nama_kelas }}
                                @else
                                    <span class="text-gray-500">Tidak ada kelas</span>
                                @endif
                            </div>
                        @else
                            <x-filament::input.select id="kelas_id" wire:model="kelas_id">
                                <option value="">Pilih Kelas</option>
                                @foreach($kelasOptions as $id => $nama_kelas)
                                    <option value="{{ $id }}">{{ $nama_kelas }}</option>
                                @endforeach
                            </x-filament::input.select>
                        @endif
                    </x-filament::input.wrapper>
                </div>

                <!-- Siswa Dropdown - Tampilkan hanya jika bukan Wali Murid atau Wali Murid dengan lebih dari 1 anak -->
                @if(!auth()->user()->hasRole('Wali Murid') || count($siswaOptions) > 1)
                <div class="col-span-1">
                    <x-filament::input.wrapper id="siswa_id" label="Siswa">
                        <x-filament::input.select id="siswa_id" wire:model="siswa_id">
                            <option value="">Semua Siswa</option>
                            @foreach($siswaOptions as $id => $nama)
                                <option value="{{ $id }}">{{ $nama }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
                @endif

                <!-- Bulan Dropdown -->
                <div class="col-span-1">
                    <x-filament::input.wrapper id="bulan" label="Bulan">
                        <x-filament::input.select id="bulan" wire:model="bulan">
                            @foreach($bulanOptions() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <!-- Tahun Dropdown -->
                <div class="col-span-1">
                    <x-filament::input.wrapper id="tahun" label="Tahun">
                        <x-filament::input.select id="tahun" wire:model="tahun">
                            @foreach($tahunOptions() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <x-filament::button type="submit">
                    Tampilkan Rekap
                </x-filament::button>

                @if(count($rekapData) > 0)
                    <div>{{ $rekapData->getHeaderActions()[0] }}</div>
                @endif
            </div>
        </form>
    </x-filament::section>

    <!-- Results Section -->
    @if(count($rekapData) > 0)
        <x-filament::section>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                No
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                NIS
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Nama Siswa
                            </th>
                            @foreach($tanggalList as $tanggal)
                                <th scope="col" class="px-2 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ Carbon\Carbon::parse($tanggal)->format('d') }}
                                </th>
                            @endforeach
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                H
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                S
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                I
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                A
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($rekapData as $index => $siswa)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $siswa['nis'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">
                                    {{ $siswa['nama'] }}
                                </td>

                                @foreach($tanggalList as $tanggal)
                                    <td class="px-2 py-4 whitespace-nowrap text-sm text-center
                                        @if(isset($siswa['presensi'][$tanggal]))
                                            @if($siswa['presensi'][$tanggal] == 'hadir')
                                                text-green-600 dark:text-green-400 font-medium
                                            @elseif($siswa['presensi'][$tanggal] == 'sakit')
                                                text-yellow-600 dark:text-yellow-400 font-medium
                                            @elseif($siswa['presensi'][$tanggal] == 'izin')
                                                text-blue-600 dark:text-blue-400 font-medium
                                            @elseif($siswa['presensi'][$tanggal] == 'alpa')
                                                text-red-600 dark:text-red-400 font-medium
                                            @endif
                                        @else
                                            text-gray-300 dark:text-gray-600
                                        @endif
                                    ">
                                        @if(isset($siswa['presensi'][$tanggal]))
                                            @if($siswa['presensi'][$tanggal] == 'hadir')
                                                H
                                            @elseif($siswa['presensi'][$tanggal] == 'sakit')
                                                S
                                            @elseif($siswa['presensi'][$tanggal] == 'izin')
                                                I
                                            @elseif($siswa['presensi'][$tanggal] == 'alpa')
                                                A
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endforeach

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-green-600 dark:text-green-400 font-medium">
                                    {{ $siswa['total']['hadir'] ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-yellow-600 dark:text-yellow-400 font-medium">
                                    {{ $siswa['total']['sakit'] ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-blue-600 dark:text-blue-400 font-medium">
                                    {{ $siswa['total']['izin'] ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-red-600 dark:text-red-400 font-medium">
                                    {{ $siswa['total']['alpa'] ?? 0 }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <div class="flex flex-wrap space-x-6">
                    <div class="flex items-center">
                        <span class="h-4 w-4 bg-green-600 dark:bg-green-500 rounded-full mr-2"></span>
                        <span class="text-sm text-gray-700 dark:text-gray-300">H: Hadir</span>
                    </div>
                    <div class="flex items-center">
                        <span class="h-4 w-4 bg-yellow-600 dark:bg-yellow-500 rounded-full mr-2"></span>
                        <span class="text-sm text-gray-700 dark:text-gray-300">S: Sakit</span>
                    </div>
                    <div class="flex items-center">
                        <span class="h-4 w-4 bg-blue-600 dark:bg-blue-500 rounded-full mr-2"></span>
                        <span class="text-sm text-gray-700 dark:text-gray-300">I: Izin</span>
                    </div>
                    <div class="flex items-center">
                        <span class="h-4 w-4 bg-red-600 dark:bg-red-500 rounded-full mr-2"></span>
                        <span class="text-sm text-gray-700 dark:text-gray-300">A: Alpa (Tidak Hadir Tanpa Keterangan)</span>
                    </div>
                </div>
            </div>
        </x-filament::section>
    @elseif($kelas_id)
        <x-filament::section>
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p>Tidak ada data presensi untuk filter yang dipilih.</p>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
