<?php

namespace App\Filament\Admin\Pages;

use App\Models\Category;
use App\Models\Unit;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class ManageCategories extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Monitoring Kategori';
    protected static ?string $title           = 'Monitoring Kategori (Seluruh Unit)';
    protected static ?string $slug            = 'manage-categories';
    protected static ?int    $navigationSort  = 3;

    protected static string $view = 'filament.admin.pages.manage-categories';

    public function getHeading(): string
    {
        return '';
    }

    /** ID unit yang sedang aktif/dipilih di Card 1 */
    public ?int $selectedUnitId = null;

    /** Form data untuk Card Paling Atas (Create / Edit Kategori) */
    public array $categoryForm = [];

    /** Kata kunci pencarian unit di Card 1 */
    public string $searchUnit = '';

    /** Daftar card kategori baru yang sedang ditambahkan di Card 2 */
    public array $newCategories = [];

    /** ID kategori yang sedang diedit inline di Card 2 */
    public ?int $editingCategoryId = null;

    /** Data kategori yang sedang diedit inline */
    public array $editingCategoryData = [];

    public function mount(): void
    {
        $this->initCategoryForm();

        // Default: pilih unit pertama jika ada agar langsung menampilkan kategorinya
        $firstUnit = Unit::orderBy('urutan')->orderBy('nama')->first();
        if ($firstUnit) {
            $this->selectUnit($firstUnit->id);
        }
    }

    /** Reset form kategori ke default kosong (Mode Tambah Baru) */
    public function initCategoryForm(): void
    {
        $firstUnitId = $this->selectedUnitId ?? Unit::orderBy('urutan')->value('id');

        $this->categoryForm = [
            'id'      => null,
            'unit_id' => $firstUnitId,
            'nama'    => '',
            'urutan'  => 0,
        ];
    }

    /**
     * Saat user mengklik unit di Card 1:
     * - Unit menjadi terpilih (biru)
     * - Form atas diarahkan ke unit tersebut
     * - Card 2 memuat daftar kategori di unit tersebut
     */
    public function selectUnit(int $id): void
    {
        $unit = Unit::find($id);
        if (! $unit) {
            return;
        }

        $this->selectedUnitId = $id;

        $this->categoryForm = [
            'id'      => null,
            'unit_id' => $unit->id,
            'nama'    => '',
            'urutan'  => $unit->categories()->count(),
        ];

        $this->newCategories       = [];
        $this->editingCategoryId   = null;
        $this->editingCategoryData = [];
    }

    /**
     * Beralih ke mode tambah kategori baru pada form paling atas
     */
    public function startCreateCategory(): void
    {
        $this->initCategoryForm();
        $this->newCategories       = [];
        $this->editingCategoryId   = null;
        $this->editingCategoryData = [];
    }

    /**
     * Mulai edit kategori di form paling atas
     */
    public function selectCategoryForTopEdit(int $categoryId): void
    {
        $category = Category::find($categoryId);
        if (! $category) {
            return;
        }

        $this->selectedUnitId = $category->unit_id;
        $this->categoryForm = [
            'id'      => $category->id,
            'unit_id' => $category->unit_id,
            'nama'    => $category->nama,
            'urutan'  => (int) $category->urutan,
        ];

        $this->editingCategoryId   = null;
        $this->editingCategoryData = [];
    }

    /**
     * Menyimpan data kategori dari Card Atas (baik Tambah Baru maupun Update)
     */
    public function saveCategory(): void
    {
        $this->validate([
            'categoryForm.unit_id' => 'required|exists:units,id',
            'categoryForm.nama'    => 'required|string|max:100',
            'categoryForm.urutan'  => 'nullable|numeric|min:0',
        ], [
            'categoryForm.unit_id.required' => 'Pilih Jurusan / Unit terlebih dahulu.',
            'categoryForm.nama.required'    => 'Nama Kategori wajib diisi.',
        ]);

        $data = $this->categoryForm;
        $data['nama'] = trim($data['nama']);
        $data['urutan'] = (int) ($data['urutan'] ?? 0);

        if (! empty($data['id'])) {
            $cat = Category::findOrFail($data['id']);
            $cat->update([
                'unit_id' => $data['unit_id'],
                'nama'    => $data['nama'],
                'urutan'  => $data['urutan'],
            ]);

            $this->selectUnit($cat->unit_id);

            Notification::make()
                ->title("Kategori '{$cat->nama}' berhasil diperbarui")
                ->success()
                ->send();
        } else {
            $cat = Category::create([
                'unit_id' => $data['unit_id'],
                'nama'    => $data['nama'],
                'urutan'  => $data['urutan'],
            ]);

            $this->selectUnit($cat->unit_id);

            Notification::make()
                ->title("Kategori baru '{$cat->nama}' berhasil ditambahkan")
                ->success()
                ->send();
        }
    }

    /**
     * Menghapus kategori dari card atas
     */
    public function deleteCategoryFromTop(): void
    {
        if (empty($this->categoryForm['id'])) {
            return;
        }

        $this->deleteCategory((int) $this->categoryForm['id']);
        $this->startCreateCategory();
    }

    /**
     * Menambahkan card input kategori baru di Card 2
     */
    public function addNewCategoryCard(): void
    {
        if (! $this->selectedUnitId) {
            Notification::make()
                ->title('Pilih Jurusan / Unit terlebih dahulu di sebelah kiri')
                ->warning()
                ->send();
            return;
        }

        $unit = Unit::find($this->selectedUnitId);
        $nextUrutan = $unit ? $unit->categories()->count() + count($this->newCategories) : 0;

        $this->newCategories[] = [
            'temp_id' => uniqid('cat_'),
            'nama'    => '',
            'urutan'  => $nextUrutan,
        ];
    }

    /**
     * Menghapus salah satu card input kategori baru menggunakan tanda silang (✕)
     */
    public function removeNewCategoryCard(int $index): void
    {
        if (isset($this->newCategories[$index])) {
            array_splice($this->newCategories, $index, 1);
        }
    }

    /**
     * Menyimpan kategori dari salah satu card baru
     */
    public function saveNewCategory(int $index): void
    {
        if (! $this->selectedUnitId || ! isset($this->newCategories[$index])) {
            return;
        }

        $catData = $this->newCategories[$index];
        $nama = trim($catData['nama'] ?? '');

        if ($nama === '') {
            Notification::make()
                ->title('Nama kategori wajib diisi')
                ->danger()
                ->send();
            return;
        }

        $unit = Unit::find($this->selectedUnitId);
        if (! $unit) {
            return;
        }

        $unit->categories()->create([
            'nama'   => $nama,
            'urutan' => (int) ($catData['urutan'] ?? $unit->categories()->count()),
        ]);

        array_splice($this->newCategories, $index, 1);

        Notification::make()
            ->title("Kategori '{$nama}' berhasil ditambahkan ke {$unit->nama}")
            ->success()
            ->send();
    }

    /**
     * Mulai edit inline kategori yang sudah ada
     */
    public function editCategory(int $catId): void
    {
        $cat = Category::find($catId);
        if (! $cat) {
            return;
        }

        $this->editingCategoryId = $catId;
        $this->editingCategoryData = [
            'nama'   => $cat->nama,
            'urutan' => $cat->urutan ?? 0,
        ];
    }

    /**
     * Batal edit inline kategori
     */
    public function cancelEditCategory(): void
    {
        $this->editingCategoryId   = null;
        $this->editingCategoryData = [];
    }

    /**
     * Simpan perubahan edit inline kategori
     */
    public function updateCategory(int $catId): void
    {
        $cat = Category::find($catId);
        if (! $cat) {
            return;
        }

        $nama = trim($this->editingCategoryData['nama'] ?? '');
        if ($nama === '') {
            Notification::make()
                ->title('Nama kategori wajib diisi')
                ->danger()
                ->send();
            return;
        }

        $cat->update([
            'nama'   => $nama,
            'urutan' => (int) ($this->editingCategoryData['urutan'] ?? 0),
        ]);

        $this->editingCategoryId   = null;
        $this->editingCategoryData = [];

        Notification::make()
            ->title("Kategori '{$cat->nama}' berhasil diperbarui")
            ->success()
            ->send();
    }

    /**
     * Hapus kategori yang sudah ada
     */
    public function deleteCategory(int $catId): void
    {
        $cat = Category::find($catId);
        if ($cat) {
            $nama = $cat->nama;
            $itemsCount = $cat->items()->count();

            if ($itemsCount > 0) {
                // Set category_id di items jadi null sebelum hapus kategori
                $cat->items()->update(['category_id' => null]);
            }

            $cat->delete();

            Notification::make()
                ->title("Kategori '{$nama}' berhasil dihapus")
                ->success()
                ->send();
        }
    }

    /**
     * Daftar unit terfilter untuk Card 1
     */
    public function getUnitsProperty(): Collection
    {
        $query = Unit::query()
            ->withCount(['categories', 'items'])
            ->orderBy('urutan')
            ->orderBy('nama');

        if (! empty(trim($this->searchUnit))) {
            $s = '%' . trim($this->searchUnit) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('nama', 'like', $s)
                  ->orWhere('kode', 'like', $s)
                  ->orWhere('kepala_nama', 'like', $s);
            });
        }

        return $query->get();
    }

    /**
     * Unit terpilih saat ini
     */
    public function getSelectedUnitProperty(): ?Unit
    {
        if (! $this->selectedUnitId) {
            return null;
        }

        return Unit::with(['categories' => function ($q) {
            $q->withCount('items')->orderBy('urutan')->orderBy('nama');
        }])->find($this->selectedUnitId);
    }

    /**
     * Pilihan unit untuk dropdown select
     */
    public function getUnitOptionsProperty(): array
    {
        return Unit::where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->toArray();
    }
}
