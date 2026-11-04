<?php

namespace App\Livewire;

use Livewire\Component;

class LabRoleSwitcher extends Component
{
    public string $currentRole = 'toolman';
    public ?int $selectedUnitId = null;

    public function mount(): void
    {
        $this->currentRole = session('lab_active_role', 'toolman');
        $this->selectedUnitId = auth()->user()?->getEffectiveUnitId();
    }

    public function setLabUnit($unitId): mixed
    {
        $unitId = (int) $unitId;
        session(['lab_selected_unit_id' => $unitId]);
        $this->selectedUnitId = $unitId;

        // Reload current page to refresh data according to selected unit
        return redirect(request()->header('Referer', route('filament.kepalab.pages.dashboard')));
    }

    public function switchRole(string $role): mixed
    {
        if (!in_array($role, ['toolman', 'kepala_lab'], true)) {
            return null;
        }

        session(['lab_active_role' => $role]);
        $this->currentRole = $role;

        if ($role === 'toolman') {
            return redirect()->route('filament.kepalab.pages.barang-masuk');
        }

        return redirect()->route('filament.kepalab.pages.daftar-barang');
    }

    public function render()
    {
        return view('livewire.lab-role-switcher');
    }
}

