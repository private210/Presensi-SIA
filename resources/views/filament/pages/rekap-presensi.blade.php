<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit="generateReport" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Kelas Dropdown -->
                <div class="col-span-1">
                    <x-filament::input.wrapper id="kelas_id" label="Kelas">
                        <x-filament::input.select id="kelas_id" wire:model="kelas_id">
                            <option value="">Pilih Kelas</option>
                            @foreach($kelasOptions as $id => $nama_kelas)
                                <option value="{{ $id }}">{{ $nama_kelas }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <!-- Siswa Dropdown (conditionally displayed) -->
                @if(auth()->user()->role !== 'wali_murid')
                <div class="col-span-1">
                    <x-filament::input.wrapper id="siswa_id" label="Siswa">
                        <x-filament::input.select id="siswa_id" wire:model="siswa_id">
                            <option value="">Semua Siswa</option>
                            @if($kelas_id)
                                @foreach(App\Models\Siswa::where('kelas_id', $kelas_id)->get() as $siswa)
                                    <option value="{{ $siswa->id }}">{{ $siswa->nama }}</option>
                                @endforeach
                            @endif
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
                @endif

                <!-- Bulan Dropdown -->
                <div class="col-span-1">
                    <x-filament::input.wrapper id="bulan" label="Bulan">
                        <x-filament::input.select id="bulan" wire:model="bulan">
                            @foreach($bulanOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <!-- Tahun Dropdown -->
                <div class="col-span-1">
                    <x-filament::input.wrapper id="tahun" label="Tahun">
                        <x-filament::input.select id="tahun" wire:model="tahun">
                            @foreach($tahunOptions as $value => $label)
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
                    {{ $exportAction }}
                @endif
            </div>
        </form>
    </x-filament::section>

    <!-- Results Section -->
    @if(count($rekapData) > 0)
        <x-filament::section>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                No
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                NIS
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nama Siswa
                            </th>
                            @foreach($tanggalList as $tanggal)
                                <th scope="col" class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ Carbon\Carbon::parse($tanggal)->format('d') }}
                                </th>
                            @endforeach
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                H
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                S
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                I
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                A
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($rekapData as $index => $siswa)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $siswa['nis'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $siswa['nama'] }}
                                </td>

                                @foreach($tanggalList as $tanggal)
                                    <td class="px-2 py-4 whitespace-nowrap text-sm text-center
                                        @if(isset($siswa['presensi'][$tanggal]))
                                            @if($siswa['presensi'][$tanggal] == 'H')
                                                text-green-600 font-medium
                                            @elseif($siswa['presensi'][$tanggal] == 'S')
                                                text-yellow-600 font-medium
                                            @elseif($siswa['presensi'][$tanggal] == 'I')
                                                text-blue-600 font-medium
                                            @elseif($siswa['presensi'][$tanggal] == 'A')
                                                text-red-600 font-medium
                                            @endif
                                        @else
                                            text-gray-300
                                        @endif
                                    ">
                                        {{ $siswa['presensi'][$tanggal] ?? '-' }}
                                    </td>
                                @endforeach

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-green-600 font-medium">
                                    {{ $siswa['total']['H'] ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-yellow-600 font-medium">
                                    {{ $siswa['total']['S'] ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-blue-600 font-medium">
                                    {{ $siswa['total']['I'] ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-red-600 font-medium">
                                    {{ $siswa['total']['A'] ?? 0 }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <div class="flex space-x-6">
                    <div class="flex items-center">
                        <span class="h-4 w-4 bg-green-600 rounded-full mr-2"></span>
                        <span class="text-sm text-gray-700">H: Hadir</span>
                    </div>
                    <div class="flex items-center">
                        <span class="h-4 w-4 bg-yellow-600 rounded-full mr-2"></span>
                        <span class="text-sm text-gray-700">S: Sakit</span>
                    </div>
                    <div class="flex items-center">
                        <span class="h-4 w-4 bg-blue-600 rounded-full mr-2"></span>
                        <span class="text-sm text-gray-700">I: Izin</span>
                    </div>
                    <div class="flex items-center">
                        <span class="h-4 w-4 bg-red-600 rounded-full mr-2"></span>
                        <span class="text-sm text-gray-700">A: Alpha (Tidak Hadir Tanpa Keterangan)</span>
                    </div>
                </div>
            </div>
        </x-filament::section>
    @elseif($kelas_id)
        <x-filament::section>
            <div class="text-center py-4 text-gray-500">
                Tidak ada data presensi untuk filter yang dipilih.
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
