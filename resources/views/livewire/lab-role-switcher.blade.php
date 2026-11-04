<div class="px-2 pt-1 pb-3 mb-2 border-b border-gray-200 dark:border-gray-800">
    <!-- Header Pilih Peran -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
        <span style="font-size: 10px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; color: #6b7280;">
            PILIH PERAN
        </span>
        @if($currentRole === 'toolman')
            <span style="font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 9999px; background: #fef3c7; color: #92400e; border: 1px solid #fcd34d;">
                Toolman Aktif
            </span>
        @else
            <span style="font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 9999px; background: #ccfbf1; color: #115e59; border: 1px solid #5eead4;">
                Kalab Aktif
            </span>
        @endif
    </div>

    <!-- Container Kiri - Kanan (Side-by-side flexbox) -->
    <div style="display: flex; flex-direction: row; gap: 6px; padding: 4px; background: rgba(150, 150, 150, 0.12); border-radius: 12px; border: 1px solid rgba(150, 150, 150, 0.2);">
        <!-- KIRI: Toolman -->
        <button
            type="button"
            wire:click="switchRole('toolman')"
            style="flex: 1; min-width: 0; display: flex; align-items: center; justify-content: center; gap: 5px; padding: 8px 6px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer; transition: all 0.2s ease; border: {{ $currentRole === 'toolman' ? '1px solid #f59e0b' : '1px solid transparent' }}; background: {{ $currentRole === 'toolman' ? '#ffffff' : 'transparent' }}; color: {{ $currentRole === 'toolman' ? '#b45309' : '#6b7280' }}; box-shadow: {{ $currentRole === 'toolman' ? '0 1px 3px rgba(0,0,0,0.1)' : 'none' }};"
        >
            <svg style="width: 14px; height: 14px; flex-shrink: 0; color: {{ $currentRole === 'toolman' ? '#f59e0b' : '#9ca3af' }};" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 004.486-6.32l-3.276 3.277a3.004 3.004 0 01-2.25 2.25l-3.276-3.276a4.5 4.5 0 00-6.32 4.486c.118.58.094 1.193-.07 1.742m10.963 1.163l-2.496 3.03" />
            </svg>
            <span style="white-space: nowrap;">Toolman</span>
        </button>

        <!-- KANAN: Kepala Lab -->
        <button
            type="button"
            wire:click="switchRole('kepala_lab')"
            style="flex: 1; min-width: 0; display: flex; align-items: center; justify-content: center; gap: 5px; padding: 8px 6px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer; transition: all 0.2s ease; border: {{ $currentRole === 'kepala_lab' ? '1px solid #14b8a6' : '1px solid transparent' }}; background: {{ $currentRole === 'kepala_lab' ? '#ffffff' : 'transparent' }}; color: {{ $currentRole === 'kepala_lab' ? '#0f766e' : '#6b7280' }}; box-shadow: {{ $currentRole === 'kepala_lab' ? '0 1px 3px rgba(0,0,0,0.1)' : 'none' }};"
        >
            <svg style="width: 14px; height: 14px; flex-shrink: 0; color: {{ $currentRole === 'kepala_lab' ? '#0d9488' : '#9ca3af' }};" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 01-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-5.25 6.557c0 1.875 1.5 3.75 5.25 3.75s5.25-1.875 5.25-3.75" />
            </svg>
            <span style="white-space: nowrap;">Kepala Lab</span>
        </button>
    </div>

    <!-- Deskripsi Tugas Aktif -->
    <div style="margin-top: 8px; font-size: 11px; line-height: 1.4; color: #6b7280; padding: 0 4px;">
        @if($currentRole === 'toolman')
            <span style="font-weight: 600; color: #b45309;">Mode Toolman:</span> Input barang masuk, keluar & peminjaman praktikum.
        @else
            <span style="font-weight: 600; color: #0f766e;">Mode Kepala Lab:</span> Buat daftar master barang & usulan pengadaan lab.
        @endif
    </div>

    @if(auth()->user()?->hasRole('super_admin') || !auth()->user()?->unit_id)
        <div style="margin-top: 10px; padding: 6px 8px; background: rgba(59, 130, 246, 0.08); border-radius: 8px; border: 1px solid rgba(59, 130, 246, 0.2);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 3px;">
                <span style="font-size: 9px; font-weight: 700; color: #2563eb; text-transform: uppercase;">Pilih Jurusan (Super Admin):</span>
            </div>
            <select
                wire:change="setLabUnit($event.target.value)"
                style="width: 100%; font-size: 11px; font-weight: 600; padding: 4px 6px; border-radius: 6px; border: 1px solid #93c5fd; background: #ffffff; color: #1e3a8a;"
                class="dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600"
            >
                @foreach(\App\Models\Unit::where('tipe', 'jurusan')->orderBy('urutan')->get() as $u)
                    <option value="{{ $u->id }}" {{ $selectedUnitId == $u->id ? 'selected' : '' }}>
                        {{ $u->nama }} ({{ $u->kode }})
                    </option>
                @endforeach
            </select>
        </div>
    @endif
</div>
