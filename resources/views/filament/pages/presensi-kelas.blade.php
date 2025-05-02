<x-filament-panels::page>
    <form wire:submit="simpanPresensi">
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                @if(auth()->user()->hasRole('admin'))
                <x-filament::input.wrapper id="kelas_id" label="Kelas">
                    <x-filament::input.select
                        wire:model.live="kelas_id"
                        placeholder="Pilih Kelas"
                    >
                        @foreach(\App\Models\Kelas::all() as $kelas)
                            <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }} ({{ $kelas->tahun_ajaran }})</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                @else
                <div>
                    <x-filament::input.wrapper label="Kelas">
                        <div class="px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-700">
                            @if($kelas_id)
                                {{ \App\Models\Kelas::find($kelas_id)->nama_kelas }} ({{ \App\Models\Kelas::find($kelas_id)->tahun_ajaran }})
                            @else
                                <span class="text-gray-500">Tidak ada kelas yang diampu</span>
                            @endif
                        </div>
                    </x-filament::input.wrapper>
                </div>
                @endif

                <x-filament::input.wrapper id="tanggal" label="Tanggal">
                    <x-filament::input.date
                        wire:model.live="tanggal"
                    />
                </x-filament::input.wrapper>
            </div>

            @if($isTodayHoliday)
                <div class="bg-amber-100 dark:bg-amber-900 border border-amber-300 dark:border-amber-700 p-4 rounded-lg text-amber-800 dark:text-amber-300 mb-4">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <span class="font-medium">Hari Libur: {{ $holidayInfo }}</span>
                    </div>
                    <p class="mt-2">Semua siswa akan otomatis diberi status Izin dengan keterangan Hari Libur.</p>
                </div>
            @endif

            @if($kelas_id && $tanggal)
                @if(count($siswaList) > 0)
                    <div class="mb-4">
                        <div class="flex flex-wrap gap-2">
                            <x-filament::button
                                type="button"
                                color="success"
                                size="sm"
                                wire:click="setAllStatus('hadir')"
                            >
                                Semua Hadir
                            </x-filament::button>

                            <x-filament::button
                                type="button"
                                color="info"
                                size="sm"
                                wire:click="setAllStatus('sakit')"
                            >
                                Semua Sakit
                            </x-filament::button>

                            <x-filament::button
                                type="button"
                                color="warning"
                                size="sm"
                                wire:click="setAllStatus('izin')"
                            >
                                Semua Izin
                            </x-filament::button>

                            <x-filament::button
                                type="button"
                                color="danger"
                                size="sm"
                                wire:click="setAllStatus('alpa')"
                            >
                                Semua Alpa
                            </x-filament::button>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
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
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Keterangan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($siswaList as $index => $siswa)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                            {{ $siswa['nis'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                            {{ $siswa['nama'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                            <select
                                                wire:model="siswaList.{{ $index }}.status"
                                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 rounded-md shadow-sm w-full"
                                                {{ $isTodayHoliday ? 'disabled' : '' }}
                                            >
                                                <option value="">Pilih Status</option>
                                                <option value="hadir">Hadir</option>
                                                <option value="izin">Izin</option>
                                                <option value="sakit">Sakit</option>
                                                <option value="alpa">Alpa</option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                            <input
                                                type="text"
                                                wire:model="siswaList.{{ $index }}.keterangan"
                                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-500 rounded-md shadow-sm w-full"
                                                {{ $isTodayHoliday ? 'disabled' : '' }}
                                            >
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end">
                        <x-filament::button type="submit">
                            Simpan Presensi
                        </x-filament::button>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 rounded-lg shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p>Tidak ada data siswa untuk kelas ini</p>
                    </div>
                @endif
            @else
                <div class="text-center py-8 text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 rounded-lg shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p>Silakan pilih kelas dan tanggal untuk melakukan presensi</p>
                </div>
            @endif
        </div>
    </form>
</x-filament-panels::page>
