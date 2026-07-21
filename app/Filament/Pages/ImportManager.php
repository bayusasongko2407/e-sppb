<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ImportProgressWidget;
use App\Models\Asset;
use App\Models\AssetImportCompletion;
use App\Models\Department;
use App\Models\Item;
use App\Models\Location;
use App\Models\Plant;
use App\Models\Unit;
use App\Services\DataImportService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportManager extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $title = 'Import Excel Master Data';

    protected static ?string $navigationLabel = 'Import Master Data';

    protected static \UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.import-manager';

    public function getHeaderWidgets(): array
    {
        return [
            ImportProgressWidget::class,
        ];
    }

    public ?array $data = [];

    // URL parameters to display progress/logs of a specific import
    #[Url(as: 'job')]
    public ?string $activeImportUuid = null;

    public bool $isProcessing = false;

    public int $totalRows = 0;

    public int $processedRows = 0;

    public int $successfulRows = 0;

    public int $failedRows = 0;

    public array $importLogs = [];

    public ?string $errorMessage = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('import_type')
                    ->label('Pilih Tipe Data *')
                    ->options([
                        'plants' => 'Plant',
                        'departments' => 'Departemen',
                        'locations' => 'Lokasi',
                        'units' => 'Satuan (Unit)',
                        'items' => 'Barang (Item)',
                        'assets' => 'Aset',
                    ])
                    ->required(),
                FileUpload::make('file')
                    ->label('File Template XLSX *')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/wps-office.xlsx',
                    ])
                    ->required()
                    ->disk('local')
                    ->directory('imports')
                    ->visibility('private')
                    ->storeFileNamesIn('stored_name'),
            ])
            ->statePath('data');
    }

    public function startImport(): void
    {
        $data = $this->form->getState();
        $importType = $data['import_type'];
        $filePath = Storage::disk('local')->path($data['file']);

        $this->isProcessing = true;
        $this->errorMessage = null;
        $this->importLogs = [];
        $this->processedRows = 0;
        $this->successfulRows = 0;
        $this->failedRows = 0;

        try {
            // Load Spreadsheet Sheet 1 only
            $reader = IOFactory::createReader('Xlsx');
            $sheetNames = $reader->listWorksheetNames($filePath);
            if (empty($sheetNames)) {
                throw new \Exception('File excel kosong atau tidak valid.');
            }
            $reader->setLoadSheetsOnly($sheetNames[0]);
            $spreadsheet = $reader->load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (count($rows) <= 1) {
                throw new \Exception('File tidak memiliki data baris untuk diproses.');
            }

            // Exclude header row (row 1)
            $header = array_shift($rows);
            $this->totalRows = count($rows);

            // Register Import record
            $dataImportService = app(DataImportService::class);
            $importRecord = $dataImportService->uploadImport(
                importType: $importType,
                requestedById: auth()->id(),
                originalName: basename($filePath),
                disk: 'local',
                path: $data['file'],
                mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                extension: 'xlsx',
                fileSize: filesize($filePath),
                checksum: hash_file('sha256', $filePath)
            );

            $this->activeImportUuid = $importRecord->uuid;

            $barcodesInUpload = [];

            // Execute each row inside a database transaction to keep integrity
            foreach ($rows as $rowIndex => $row) {
                $actualRowNumber = $rowIndex + 1; // plus headers
                try {
                    DB::transaction(function () use ($importType, $row, &$barcodesInUpload) {
                        $this->processRow($importType, $row, $barcodesInUpload);
                    });

                    $this->successfulRows++;
                    $this->importLogs[] = [
                        'row' => $actualRowNumber,
                        'status' => 'SUCCESS',
                        'message' => 'Baris berhasil di-proses/di-update.',
                    ];
                } catch (\Throwable $e) {
                    $this->failedRows++;
                    $this->importLogs[] = [
                        'row' => $actualRowNumber,
                        'status' => 'FAILED',
                        'message' => $e->getMessage(),
                    ];
                }

                $this->processedRows++;
                $importRecord->update([
                    'processed_rows' => $this->processedRows,
                    'successful_rows' => $this->successfulRows,
                    'failed_rows' => $this->failedRows,
                ]);
            }

            // Aset Specific Logic: Barcode checks
            if ($importType === 'assets') {
                $allDbBarcodes = Asset::pluck('barcode')->toArray();
                $missingBarcodes = array_diff($allDbBarcodes, $barcodesInUpload);

                if (! empty($missingBarcodes)) {
                    $completion = AssetImportCompletion::create([
                        'uuid' => Str::uuid()->toString(),
                        'requested_by_id' => auth()->id(),
                        'stored_name' => $importRecord->stored_name,
                        'original_name' => $importRecord->original_name,
                        'missing_barcodes' => array_values($missingBarcodes),
                        'status' => 'PENDING',
                    ]);

                    $importRecord->update(['status' => 'COMPLETED']);
                    $this->isProcessing = false;

                    Notification::make()
                        ->title('Import Sukses dengan Mismatch Barcode')
                        ->warning()
                        ->body('Beberapa barcode tidak ditemukan dalam data excel baru. Mengalihkan ke halaman konfirmasi...')
                        ->send();

                    $this->redirectRoute('filament.admin.pages.import-review', ['id' => $completion->uuid]);

                    return;
                }
            }

            $importRecord->update(['status' => 'COMPLETED']);
            $this->isProcessing = false;

            Notification::make()
                ->title('Proses Import Selesai!')
                ->success()
                ->body("Berhasil memproses {$this->successfulRows} data dari total {$this->totalRows} baris.")
                ->send();

        } catch (\Throwable $e) {
            $this->isProcessing = false;
            $this->errorMessage = $e->getMessage();

            if (isset($importRecord)) {
                $importRecord->update([
                    'status' => 'FAILED',
                    'error_message' => $e->getMessage(),
                ]);
            }

            Notification::make()
                ->title('Gagal Memproses File Import')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    private function processRow(string $importType, array $row, array &$barcodesInUpload): void
    {
        switch ($importType) {
            case 'plants':
                $code = trim($row['A'] ?? '');
                $name = trim($row['B'] ?? '');
                $address = trim($row['C'] ?? '');
                $isActive = trim($row['D'] ?? '') === '1';

                if (empty($code) || empty($name)) {
                    throw new \Exception('Kolom Code dan Name tidak boleh kosong.');
                }

                Plant::updateOrCreate(
                    ['code' => $code],
                    ['name' => $name, 'address' => $address, 'is_active' => $isActive]
                );
                break;

            case 'departments':
                $plantCode = trim($row['A'] ?? '');
                $code = trim($row['B'] ?? '');
                $name = trim($row['C'] ?? '');
                $isActive = trim($row['D'] ?? '') === '1';

                if (empty($code) || empty($name) || empty($plantCode)) {
                    throw new \Exception('Kolom Plant Code, Department Code, dan Name tidak boleh kosong.');
                }

                $plant = Plant::where('code', $plantCode)->first();
                if (! $plant) {
                    throw new \Exception("Plant dengan kode '{$plantCode}' tidak ditemukan.");
                }

                Department::updateOrCreate(
                    ['code' => $code],
                    ['plant_id' => $plant->id, 'name' => $name, 'is_active' => $isActive]
                );
                break;

            case 'locations':
                $plantCode = trim($row['A'] ?? '');
                $code = trim($row['B'] ?? '');
                $name = trim($row['C'] ?? '');
                $address = trim($row['D'] ?? '');
                $isActive = trim($row['E'] ?? '') === '1';

                if (empty($code) || empty($name) || empty($plantCode)) {
                    throw new \Exception('Kolom Plant Code, Location Code, dan Name tidak boleh kosong.');
                }

                $plant = Plant::where('code', $plantCode)->first();
                if (! $plant) {
                    throw new \Exception("Plant dengan kode '{$plantCode}' tidak ditemukan.");
                }

                Location::updateOrCreate(
                    ['code' => $code],
                    ['plant_id' => $plant->id, 'name' => $name, 'address' => $address, 'is_active' => $isActive]
                );
                break;

            case 'units':
                $code = trim($row['A'] ?? '');
                $name = trim($row['B'] ?? '');
                $category = trim($row['C'] ?? '');
                $isActive = trim($row['D'] ?? '') === '1';

                if (empty($code) || empty($name)) {
                    throw new \Exception('Kolom Code dan Name tidak boleh kosong.');
                }

                Unit::updateOrCreate(
                    ['code' => $code],
                    ['name' => $name, 'category' => $category, 'is_active' => $isActive]
                );
                break;

            case 'items':
                $code = trim($row['A'] ?? '');
                $name = trim($row['B'] ?? '');
                $specification = trim($row['C'] ?? '');
                $unitCode = trim($row['D'] ?? '');
                $itemCategory = trim($row['E'] ?? '');
                $isActive = trim($row['F'] ?? '') === '1';

                if (empty($code) || empty($name) || empty($unitCode)) {
                    throw new \Exception('Kolom Code, Name, dan Unit Code tidak boleh kosong.');
                }

                $unit = Unit::where('code', $unitCode)->first();
                if (! $unit) {
                    throw new \Exception("Unit dengan kode '{$unitCode}' tidak ditemukan.");
                }

                Item::updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => $name,
                        'specification' => $specification,
                        'unit_id' => $unit->id,
                        'item_category' => $itemCategory,
                        'is_active' => $isActive,
                    ]
                );
                break;

            case 'assets':
                $plantCode = trim($row['A'] ?? '');
                $locationCode = trim($row['B'] ?? '');
                $assetName = trim($row['C'] ?? '');
                $assetLocationData = trim($row['D'] ?? '');
                $barcode = trim($row['E'] ?? '');
                $condition = trim($row['F'] ?? '');
                $status = trim($row['G'] ?? '');
                $unitCode = trim($row['H'] ?? '');
                $notes = trim($row['I'] ?? '');
                $isActive = trim($row['J'] ?? '') === '1';

                if (empty($barcode) || empty($assetName) || empty($condition) || empty($status) || empty($unitCode)) {
                    throw new \Exception('Barcode, Nama Aset, Kondisi, Status, dan Unit Code tidak boleh kosong.');
                }

                $plant = ! empty($plantCode) ? Plant::where('code', $plantCode)->first() : null;
                $location = ! empty($locationCode) ? Location::where('code', $locationCode)->first() : null;
                $unit = Unit::where('code', $unitCode)->first();

                if (! $unit) {
                    throw new \Exception("Unit dengan kode '{$unitCode}' tidak ditemukan.");
                }

                Asset::updateOrCreate(
                    ['barcode' => $barcode],
                    [
                        'plant_id' => $plant?->id,
                        'location_id' => $location?->id,
                        'asset_name' => $assetName,
                        'asset_location_data' => $assetLocationData,
                        'condition' => $condition,
                        'status' => $status,
                        'unit_id' => $unit->id,
                        'notes' => $notes,
                        'is_active' => $isActive,
                    ]
                );

                $barcodesInUpload[] = $barcode;
                break;
        }
    }

    public function downloadSelectedTemplate(?string $type = null)
    {
        $selectedType = $type ?? ($this->data['import_type'] ?? 'assets');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        switch ($selectedType) {
            case 'plants':
                $sheet->setCellValue('A1', 'code');
                $sheet->setCellValue('B1', 'name');
                $sheet->setCellValue('C1', 'address');
                $sheet->setCellValue('D1', 'is_active');

                $sheet->getComment('A1')->setAuthor('Sistem')->getText()->createTextRun("KODE PLANT (Unik, maks 20 karakter).\nContoh: PL-SJA1");
                $sheet->getComment('B1')->setAuthor('Sistem')->getText()->createTextRun("NAMA PLANT (Wajib, maks 150 karakter).\nContoh: SJA I - Sidoarjo");
                $sheet->getComment('C1')->setAuthor('Sistem')->getText()->createTextRun("ALAMAT PLANT (Opsional).\nContoh: Jl. Raya Sidoarjo No. 123");
                $sheet->getComment('D1')->setAuthor('Sistem')->getText()->createTextRun('STATUS AKTIF (Wajib, terisi 1 untuk aktif, atau 0 untuk non-aktif).');

                $sheet->setCellValue('A2', 'PL-SJA1');
                $sheet->setCellValue('B2', 'SJA I - Sidoarjo');
                $sheet->setCellValue('C2', 'Jl. Raya Sidoarjo No. 123');
                $sheet->setCellValue('D2', '1');
                $fileName = 'template-plants.xlsx';
                break;

            case 'departments':
                $sheet->setCellValue('A1', 'plant');
                $sheet->setCellValue('B1', 'code');
                $sheet->setCellValue('C1', 'name');
                $sheet->setCellValue('D1', 'is_active');

                $sheet->getComment('A1')->setAuthor('Sistem')->getText()->createTextRun("KODE PLANT (Wajib, harus ada di database master Plant).\nContoh: PL-SJA1");
                $sheet->getComment('B1')->setAuthor('Sistem')->getText()->createTextRun("KODE DEPARTEMEN (Unik, maks 20 karakter).\nContoh: DEP-HRD");
                $sheet->getComment('C1')->setAuthor('Sistem')->getText()->createTextRun("NAMA DEPARTEMEN (Wajib, maks 150 karakter).\nContoh: Human Resources Development");
                $sheet->getComment('D1')->setAuthor('Sistem')->getText()->createTextRun('STATUS AKTIF (Wajib, terisi 1 untuk aktif, atau 0 untuk non-aktif).');

                $sheet->setCellValue('A2', 'PL-SJA1');
                $sheet->setCellValue('B2', 'DEP-HRD');
                $sheet->setCellValue('C2', 'Human Resources Development');
                $sheet->setCellValue('D2', '1');
                $fileName = 'template-departments.xlsx';
                break;

            case 'locations':
                $sheet->setCellValue('A1', 'plant');
                $sheet->setCellValue('B1', 'code');
                $sheet->setCellValue('C1', 'name');
                $sheet->setCellValue('D1', 'address');
                $sheet->setCellValue('E1', 'is_active');

                $sheet->getComment('A1')->setAuthor('Sistem')->getText()->createTextRun("KODE PLANT (Wajib, harus ada di database master Plant).\nContoh: PL-SJA1");
                $sheet->getComment('B1')->setAuthor('Sistem')->getText()->createTextRun("KODE LOKASI (Unik, maks 20 karakter).\nContoh: LOC-GUD1");
                $sheet->getComment('C1')->setAuthor('Sistem')->getText()->createTextRun("NAMA LOKASI (Wajib, maks 150 karakter).\nContoh: Gudang Bahan Baku A");
                $sheet->getComment('D1')->setAuthor('Sistem')->getText()->createTextRun("ALAMAT LOKASI (Opsional).\nContoh: Jl. Gudang Baru No. 8");
                $sheet->getComment('E1')->setAuthor('Sistem')->getText()->createTextRun('STATUS AKTIF (Wajib, terisi 1 untuk aktif, atau 0 untuk non-aktif).');

                $sheet->setCellValue('A2', 'PL-SJA1');
                $sheet->setCellValue('B2', 'LOC-GUD1');
                $sheet->setCellValue('C2', 'Gudang Bahan Baku A');
                $sheet->setCellValue('D2', 'Jl. Gudang Baru No. 8');
                $sheet->setCellValue('E2', '1');
                $fileName = 'template-locations.xlsx';
                break;

            case 'units':
                $sheet->setCellValue('A1', 'code');
                $sheet->setCellValue('B1', 'name');
                $sheet->setCellValue('C1', 'category');
                $sheet->setCellValue('D1', 'is_active');

                $sheet->getComment('A1')->setAuthor('Sistem')->getText()->createTextRun("KODE SATUAN (Unik, maks 20 karakter).\nContoh: KG");
                $sheet->getComment('B1')->setAuthor('Sistem')->getText()->createTextRun("NAMA SATUAN (Wajib, maks 150 karakter).\nContoh: Kilogram");
                $sheet->getComment('C1')->setAuthor('Sistem')->getText()->createTextRun("KATEGORI SATUAN (Opsional).\nContoh: WEIGHT, VOLUME, PIECE");
                $sheet->getComment('D1')->setAuthor('Sistem')->getText()->createTextRun('STATUS AKTIF (Wajib, terisi 1 untuk aktif, atau 0 untuk non-aktif).');

                $sheet->setCellValue('A2', 'KG');
                $sheet->setCellValue('B2', 'Kilogram');
                $sheet->setCellValue('C2', 'WEIGHT');
                $sheet->setCellValue('D2', '1');
                $fileName = 'template-units.xlsx';
                break;

            case 'items':
                $sheet->setCellValue('A1', 'code');
                $sheet->setCellValue('B1', 'name');
                $sheet->setCellValue('C1', 'specification');
                $sheet->setCellValue('D1', 'unit');
                $sheet->setCellValue('E1', 'item_category');
                $sheet->setCellValue('F1', 'is_active');

                $sheet->getComment('A1')->setAuthor('Sistem')->getText()->createTextRun("KODE BARANG (Unik, maks 50 karakter).\nContoh: BRG-001");
                $sheet->getComment('B1')->setAuthor('Sistem')->getText()->createTextRun("NAMA BARANG (Wajib, maks 255 karakter).\nContoh: Kabel Tembaga 2.5mm");
                $sheet->getComment('C1')->setAuthor('Sistem')->getText()->createTextRun("SPESIFIKASI BARANG (Opsional).\nContoh: Tipe NYM");
                $sheet->getComment('D1')->setAuthor('Sistem')->getText()->createTextRun("KODE SATUAN (Wajib, harus ada di master Satuan).\nContoh: PCS");
                $sheet->getComment('E1')->setAuthor('Sistem')->getText()->createTextRun("KATEGORI BARANG (Opsional).\nContoh: SPARE_PART, RAW_MATERIAL, ASSET_SUPPORT");
                $sheet->getComment('F1')->setAuthor('Sistem')->getText()->createTextRun('STATUS AKTIF (Wajib, terisi 1 untuk aktif, atau 0 untuk non-aktif).');

                $sheet->setCellValue('A2', 'BRG-001');
                $sheet->setCellValue('B2', 'Kabel Tembaga 2.5mm');
                $sheet->setCellValue('C2', 'Tipe NYM');
                $sheet->setCellValue('D2', 'PCS');
                $sheet->setCellValue('E2', 'SPARE_PART');
                $sheet->setCellValue('F2', '1');
                $fileName = 'template-items.xlsx';
                break;

            case 'assets':
            default:
                $sheet->setCellValue('A1', 'plant');
                $sheet->setCellValue('B1', 'location');
                $sheet->setCellValue('C1', 'asset_name');
                $sheet->setCellValue('D1', 'asset_location_data');
                $sheet->setCellValue('E1', 'barcode');
                $sheet->setCellValue('F1', 'condition');
                $sheet->setCellValue('G1', 'status');
                $sheet->setCellValue('H1', 'unit');
                $sheet->setCellValue('I1', 'notes');
                $sheet->setCellValue('J1', 'is_active');

                $sheet->getComment('A1')->setAuthor('Sistem')->getText()->createTextRun("KODE PLANT (Opsional, harus ada di master Plant).\nContoh: PL-SJA1");
                $sheet->getComment('B1')->setAuthor('Sistem')->getText()->createTextRun("KODE LOKASI (Opsional, harus ada di master Lokasi).\nContoh: LOC-GUD1");
                $sheet->getComment('C1')->setAuthor('Sistem')->getText()->createTextRun("NAMA ASET (Wajib, maks 255 karakter).\nContoh: Laptop Dell Latitude");
                $sheet->getComment('D1')->setAuthor('Sistem')->getText()->createTextRun("DATA DETAIL LOKASI ASET (Opsional).\nContoh: Ruang IT Lantai 2");
                $sheet->getComment('E1')->setAuthor('Sistem')->getText()->createTextRun("BARCODE (Wajib, Unik, maks 100 karakter).\nContoh: AST-00001");
                $sheet->getComment('F1')->setAuthor('Sistem')->getText()->createTextRun("KONDISI (Wajib, terdaftar di EnumControl).\nContoh: GOOD, BROKEN");
                $sheet->getComment('G1')->setAuthor('Sistem')->getText()->createTextRun("STATUS (Wajib, terdaftar di EnumControl).\nContoh: AVAILABLE, IN_USE, REPAIR");
                $sheet->getComment('H1')->setAuthor('Sistem')->getText()->createTextRun("KODE SATUAN (Wajib, harus ada di master Satuan).\nContoh: PCS");
                $sheet->getComment('I1')->setAuthor('Sistem')->getText()->createTextRun('CATATAN ASET (Opsional).');
                $sheet->getComment('J1')->setAuthor('Sistem')->getText()->createTextRun('STATUS AKTIF (Wajib, terisi 1 untuk aktif, atau 0 untuk non-aktif).');

                $sheet->setCellValue('A2', 'PL-SJA1');
                $sheet->setCellValue('B2', 'LOC-GUD1');
                $sheet->setCellValue('C2', 'Laptop Dell Latitude');
                $sheet->setCellValue('D2', 'Ruang IT Lantai 2');
                $sheet->setCellValue('E2', 'AST-00001');
                $sheet->setCellValue('F2', 'GOOD');
                $sheet->setCellValue('G2', 'AVAILABLE');
                $sheet->setCellValue('H2', 'PCS');
                $sheet->setCellValue('I2', 'Kondisi baik');
                $sheet->setCellValue('J2', '1');
                $fileName = 'template-assets.xlsx';
                break;
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
