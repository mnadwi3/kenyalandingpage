<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\View;
use App\Services\DoctorExportService;
use App\Services\DoctorService;
use RuntimeException;

final class DoctorController extends Controller
{
    private DoctorService $doctors;
    private DoctorExportService $exporter;

    public function __construct(?DoctorService $doctors = null, ?DoctorExportService $exporter = null)
    {
        $this->doctors = $doctors ?? new DoctorService();
        $this->exporter = $exporter ?? new DoctorExportService();
    }

    public function index(): void
    {
        $result = $this->doctors->list($_GET);
        View::renderInLayout('doctors/index', 'admin', [
            'title' => 'Doctors',
            'activeNav' => 'doctors',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => url('/dashboard')],
                ['label' => 'Doctors'],
            ],
            'user' => Auth::user(),
            'csrf' => Csrf::field(),
            'doctors' => $result['doctors'],
            'paginator' => $result['paginator'],
            'filters' => $result['filters'],
            'sort' => $result['sort'],
            'direction' => $result['direction'],
            'options' => $result['options'],
            'notifications' => [],
        ]);
    }

    public function create(): void
    {
        $this->renderForm(null, Session::getFlash('old') ?? []);
    }

    public function store(): void
    {
        $this->validateCsrf();
        try {
            $id = $this->doctors->create($_POST, $_FILES['photo'] ?? null);
            flash('success', 'Doctor created successfully.');
            redirect('/doctors/' . $id . '/edit');
        } catch (RuntimeException $e) {
            Session::flash('old', $_POST);
            flash('error', $e->getMessage());
            redirect('/doctors/create');
        }
    }

    public function edit(string $id): void
    {
        $doctor = $this->doctors->find((int) $id, true);
        if ($doctor === null) {
            flash('error', 'Doctor not found.');
            redirect('/doctors');
        }
        $old = Session::getFlash('old');
        $this->renderForm($doctor, is_array($old) ? $old : $doctor);
    }

    public function update(string $id): void
    {
        $this->validateCsrf();
        $doctorId = (int) $id;
        try {
            $this->doctors->update($doctorId, $_POST, $_FILES['photo'] ?? null);
            flash('success', 'Doctor updated successfully.');
            redirect('/doctors/' . $doctorId . '/edit');
        } catch (RuntimeException $e) {
            Session::flash('old', $_POST);
            flash('error', $e->getMessage());
            redirect('/doctors/' . $doctorId . '/edit');
        }
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();
        $this->doctors->softDelete((int) $id);
        flash('success', 'Doctor moved to trash.');
        redirect('/doctors');
    }

    public function restore(string $id): void
    {
        $this->validateCsrf();
        $this->doctors->restore((int) $id);
        flash('success', 'Doctor restored.');
        redirect('/doctors?trashed=only');
    }

    public function duplicate(string $id): void
    {
        $this->validateCsrf();
        try {
            $newId = $this->doctors->duplicate((int) $id);
            flash('success', 'Doctor duplicated.');
            redirect('/doctors/' . $newId . '/edit');
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/doctors');
        }
    }

    public function bulk(): void
    {
        $this->validateCsrf();
        $ids = array_map('intval', (array) ($_POST['ids'] ?? []));
        $action = (string) ($_POST['bulk_action'] ?? '');
        $count = match ($action) {
            'delete' => $this->doctors->bulkSoftDelete($ids),
            'restore' => $this->doctors->bulkRestore($ids),
            'active', 'inactive', 'draft', 'archived' => $this->doctors->bulkStatus($ids, $action),
            default => 0,
        };
        flash($count > 0 ? 'success' : 'error', $count > 0 ? "Updated {$count} doctor(s)." : 'No doctors updated.');
        redirect('/doctors');
    }

    public function exportCsv(): void
    {
        $this->download('doctors.csv', 'text/csv; charset=utf-8', $this->exporter->toCsv($this->doctors->exportRows($this->exportFilters())));
    }

    public function exportJson(): void
    {
        $this->download('doctors.json', 'application/json; charset=utf-8', $this->exporter->toJson($this->doctors->exportRows($this->exportFilters())));
    }

    public function exportExcel(): void
    {
        $this->download('doctors.xls', 'application/vnd.ms-excel; charset=utf-8', $this->exporter->toExcelXml($this->doctors->exportRows($this->exportFilters())));
    }

    public function importForm(): void
    {
        View::renderInLayout('doctors/import', 'admin', [
            'title' => 'Import Doctors',
            'activeNav' => 'doctors',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => url('/dashboard')],
                ['label' => 'Doctors', 'href' => url('/doctors')],
                ['label' => 'Import'],
            ],
            'user' => Auth::user(),
            'csrf' => Csrf::field(),
            'notifications' => [],
        ]);
    }

    public function import(): void
    {
        $this->validateCsrf();
        $file = $_FILES['csv_file'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            flash('error', 'Please upload a CSV file.');
            redirect('/doctors/import');
        }
        $rows = $this->parseCsv((string) $file['tmp_name']);
        $result = $this->doctors->importRows($rows);
        $message = "Imported {$result['created']} doctor(s).";
        if ($result['failed'] > 0) {
            $message .= " {$result['failed']} failed.";
            if ($result['errors'] !== []) {
                Session::flash('import_errors', array_slice($result['errors'], 0, 10));
            }
        }
        flash($result['failed'] > 0 ? 'error' : 'success', $message);
        redirect('/doctors');
    }

    private function renderForm(?array $doctor, array $values): void
    {
        View::renderInLayout('doctors/form', 'admin', [
            'title' => $doctor ? 'Edit Doctor' : 'Add Doctor',
            'activeNav' => 'doctors',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => url('/dashboard')],
                ['label' => 'Doctors', 'href' => url('/doctors')],
                ['label' => $doctor ? 'Edit' : 'Add'],
            ],
            'user' => Auth::user(),
            'csrf' => Csrf::field(),
            'doctor' => $doctor,
            'values' => $values,
            'options' => $this->doctors->formOptions(),
            'notifications' => [],
        ]);
    }

    private function exportFilters(): array
    {
        return [
            'q' => trim($this->string('q')),
            'status' => $this->string('status'),
            'gender' => $this->string('gender'),
            'is_featured' => $_GET['is_featured'] ?? '',
            'specialty_id' => $_GET['specialty_id'] ?? '',
            'hospital_id' => $_GET['hospital_id'] ?? '',
            'treatment_id' => $_GET['treatment_id'] ?? '',
            'trashed' => $this->string('trashed', 'active'),
        ];
    }

    private function download(string $filename, string $contentType, string $content): never
    {
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }

    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('Unable to read CSV.');
        }
        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            return [];
        }
        $headers = array_map(static function ($h): string {
            $h = strtolower(trim((string) $h));
            $h = preg_replace('/[^a-z0-9_]+/', '_', $h) ?? $h;

            return trim($h, '_');
        }, $headers);
        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) === 1 && trim((string) $data[0]) === '') {
                continue;
            }
            $row = [];
            foreach ($headers as $i => $header) {
                $row[$header] = trim((string) ($data[$i] ?? ''));
            }
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }
}
