<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit="generateReport" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-1">
                    <x-filament::input.wrapper id="tanggalMulai" label="Tanggal Mulai">
                        <x-filament::input.date
                            wire:model="tanggalMulai"
                        />
                    </x-filament::input.wrapper>
                </div>
                <div class="col-span-1">
                    <x-filament::input.wrapper id="tanggalSelesai" label="Tanggal Selesai">
                        <x-filament::input.date
                            wire:model="tanggalSelesai"
                        />
                    </x-filament::input.wrapper>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <x-filament::button type="submit" icon="heroicon-m-arrow-path">
                    Refresh Data
                </x-filament::button>

                @if(count($rekapPerKelas) > 0)
                    <div>{{ $rekapPerKelas->getHeaderActions()[0] }}</div>
                @endif
            </div>
        </form>
    </x-filament::section>

    @if(count($rekapPerKelas) > 0)
        <!-- Statistik Global -->
        <x-filament::section>
            <h2 class="text-xl font-bold mb-4">Statistik Presensi Sekolah</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Siswa</h3>
                    <p class="text-2xl font-bold">{{ $rekapData['total_siswa'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Kelas</h3>
                    <p class="text-2xl font-bold">{{ $rekapData['total_kelas'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Periode</h3>
                    <p class="text-md font-bold">{{ $rekapData['periode']['mulai'] }} - {{ $rekapData['periode']['selesai'] }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">({{ $rekapData['periode']['total_hari'] }} hari)</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Persentase Hadir</h3>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $rekapData['persentase']['hadir'] }}%</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Hadir</h3>
                            <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ $rekapData['total']['hadir'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                            <span class="text-green-600 dark:text-green-400 text-lg font-bold">H</span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                            <div class="bg-green-600 dark:bg-green-500 h-2.5 rounded-full" style="width: {{ $rekapData['persentase']['hadir'] }}%"></div>
                        </div>
                        <p class="text-xs text-right mt-1">{{ $rekapData['persentase']['hadir'] }}%</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Sakit</h3>
                            <p class="text-xl font-bold text-yellow-600 dark:text-yellow-400">{{ $rekapData['total']['sakit'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-yellow-100 dark:bg-yellow-900 flex items-center justify-center">
                            <span class="text-yellow-600 dark:text-yellow-400 text-lg font-bold">S</span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                            <div class="bg-yellow-600 dark:bg-yellow-500 h-2.5 rounded-full" style="width: {{ $rekapData['persentase']['sakit'] }}%"></div>
                        </div>
                        <p class="text-xs text-right mt-1">{{ $rekapData['persentase']['sakit'] }}%</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Izin</h3>
                            <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ $rekapData['total']['izin'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                            <span class="text-blue-600 dark:text-blue-400 text-lg font-bold">I</span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                            <div class="bg-blue-600 dark:bg-blue-500 h-2.5 rounded-full" style="width: {{ $rekapData['persentase']['izin'] }}%"></div>
                        </div>
                        <p class="text-xs text-right mt-1">{{ $rekapData['persentase']['izin'] }}%</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Alpa</h3>
                            <p class="text-xl font-bold text-red-600 dark:text-red-400">{{ $rekapData['total']['alpa'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center">
                            <span class="text-red-600 dark:text-red-400 text-lg font-bold">A</span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                            <div class="bg-red-600 dark:bg-red-500 h-2.5 rounded-full" style="width: {{ $rekapData['persentase']['alpa'] }}%"></div>
                        </div>
                        <p class="text-xs text-right mt-1">{{ $rekapData['persentase']['alpa'] }}%</p>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Tabel Per Kelas -->
        <x-filament::section>
            <h2 class="text-xl font-bold mb-4">Rekap Presensi Per Kelas</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">No</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Kelas</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Wali Kelas</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jumlah Siswa</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Hadir</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sakit</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Izin</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Alpa</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">% Hadir</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">% Tidak Hadir</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($rekapPerKelas as $index => $kelas)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-200">{{ $kelas['nama_kelas'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $kelas['wali_kelas'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $kelas['jumlah_siswa'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-green-600 dark:text-green-400 font-medium">{{ $kelas['total']['hadir'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-yellow-600 dark:text-yellow-400 font-medium">{{ $kelas['total']['sakit'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-blue-600 dark:text-blue-400 font-medium">{{ $kelas['total']['izin'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-red-600 dark:text-red-400 font-medium">{{ $kelas['total']['alpa'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium">
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mb-1">
                                    <div class="bg-green-600 dark:bg-green-500 h-2.5 rounded-full" style="width: {{ $kelas['persentase']['hadir'] }}%"></div>
                                </div>
                                <span>{{ $kelas['persentase']['hadir'] }}%</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium">
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mb-1">
                                    <div class="bg-red-600 dark:bg-red-500 h-2.5 rounded-full" style="width: {{ $kelas['persentase']['sakit'] + $kelas['persentase']['izin'] + $kelas['persentase']['alpa'] }}%"></div>
                                </div>
                                <span>{{ $kelas['persentase']['sakit'] + $kelas['persentase']['izin'] + $kelas['persentase']['alpa'] }}%</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @else
        <x-filament::section>
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p>Tidak ada data presensi untuk periode yang dipilih.</p>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
