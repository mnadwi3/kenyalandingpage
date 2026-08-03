<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\View;
use App\Services\HospitalExportService;
use App\Services\HospitalService;
use RuntimeException;

final class HospitalController extends Controller
{
    private HospitalService $hospitals;
    private HospitalExportService $exporter;

    public function __construct(?HospitalService $hospitals = null, ?HospitalExportService $exporter = null)
    {
        $this->hospitals = $hospitals ?? new HospitalService();
        $this->exporter = $exporter ?? new HospitalExportService();
    }

    public function index(): void
    {
        $result = $this->hospitals->list($_GET);
        View::renderInLayout('hospitals/index', 'admin', [
            'title' => 'Hospitals',
            'activeNav' => 'hospitals',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => url('/dashboard')],
                ['label' => 'Hospitals'],
            ],
            'user' => Auth::user(),
            'csrf' => Csrf::field(),
            'hospitals' => $result['hospitals'],
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
            $id = $this->hospitals->create($_POST, $_FILES['logo'] ?? null, $_FILES['cover_image'] ?? null);
            flash('success', 'Hospital created successfully.');
            redirect('/hospitals/' . $id . '/edit');
        } catch (RuntimeException $e) {
            Session::flash('old', $_POST);
            flash('error', $e->getMessage());
            redirect('/hospitals/create');
        }
    }

    public function edit(string $id): void
    {
        $hospital = $this->hospitals->find((int) $id, true);
        if ($hospital === null) {
            flash('error', 'Hospital not found.');
            redirect('/hospitals');
        }
        $old = Session::getFlash('old');
        $this->renderForm($hospital, is_array($old) ? $old : $hospital);
    }

    public function update(string $id): void
    {
        $this->validateCsrf();
        $hospitalId = (int) $id;
        try {
            $this->hospitals->update($hospitalId, $_POST, $_FILES['logo'] ?? null, $_FILES['cover_image'] ?? null);
            flash('success', 'Hospital updated successfully.');
            redirect('/hospitals/' . $hospitalId . '/edit');
        } catch (RuntimeException $e) {
            Session::flash('old', $_POST);
            flash('error', $e->getMessage());
            redirect('/hospitals/' . $hospitalId . '/edit');
        }
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();
        $this->hospitals->softDelete((int) $id);
        flash('success', 'Hospital moved to trash.');
        redirect('/hospitals');
    }

    public function restore(string $id): void
    {
        $this->validateCsrf();
        $this->hospitals->restore((int) $id);
        flash('success', 'Hospital restored.');
        redirect('/hospitals?trashed=only');
    }

    public function duplicate(string $id): void
    {
        $this->validateCsrf();
        try {
            $newId = $this->hospitals->duplicate((int) $id);
            flash('success', 'Hospital duplicated.');
            redirect('/hospitals/' . $newId . '/edit');
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/hospitals');
        }
    }

    public function bulk(): void
    {
        $this->validateCsrf();
        $ids = array_map('intval', (array) ($_POST['ids'] ?? []));
        $action = (string) ($_POST['bulk_action'] ?? '');
        $count = match ($action) {
            'delete' => $this->hospitals->bulkSoftDelete($ids),
            'restore' => $this->hospitals->bulkRestore($ids),
            'active', 'inactive', 'draft', 'archived' => $this->hospitals->bulkStatus($ids, $action),
            default => 0,
        };
        flash($count > 0 ? 'success' : 'error', $count > 0 ? "Updated {$count} hospital(s)." : 'No hospitals updated.');
        redirect('/hospitals');
    }

    public function exportCsv(): void
    {
        $this->download('hospitals.csv', 'text/csv; charset=utf-8', $this->exporter->toCsv($this->hospitals->exportRows($this->exportFilters())));
    }

    public function exportJson(): void
    {
        $this->download('hospitals.json', 'application/json; charset=utf-8', $this->exporter->toJson($this->hospitals->exportRows($this->exportFilters())));
    }

    public function exportExcel(): void
    {
        $this->download('hospitals.xls', 'application/vnd.ms-excel; charset=utf-8', $this->exporter->toExcelXml($this->hospitals->exportRows($this->exportFilters())));
    }

    public function importForm(): void
    {
        View::renderInLayout('hospitals/import', 'admin', [
            'title' => 'Import Hospitals',
            'activeNav' => 'hospitals',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => url('/dashboard')],
                ['label' => 'Hospitals', 'href' => url('/hospitals')],
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
            redirect('/hospitals/import');
        }
        $rows = $this->parseCsv((string) $file['tmp_name']);
        $result = $this->hospitals->importRows($rows);
        $message = "Imported {$result['created']} hospital(s).";
        if ($result['failed'] > 0) {
            $message .= " {$result['failed']} failed.";
            if ($result['errors'] !== []) {
                Session::flash('import_errors', array_slice($result['errors'], 0, 10));
            }
        }
        flash($result['failed'] > 0 ? 'error' : 'success', $message);
        redirect('/hospitals');
    }

    private function renderForm(?array $hospital, array $values): void
    {
        View::renderInLayout('hospitals/form', 'admin', [
            'title' => $hospital ? 'Edit Hospital' : 'Add Hospital',
            'activeNav' => 'hospitals',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => url('/dashboard')],
                ['label' => 'Hospitals', 'href' => url('/hospitals')],
                ['label' => $hospital ? 'Edit' : 'Add'],
            ],
            'user' => Auth::user(),
            'csrf' => Csrf::field(),
            'hospital' => $hospital,
            'values' => $values,
            'options' => $this->hospitals->formOptions(),
            'notifications' => [],
        ]);
    }

    private function exportFilters(): array
    {
        return [
            'q' => trim($this->string('q')),
            'status' => $this->string('status'),
            'is_featured' => $_GET['is_featured'] ?? '',
            'is_verified' => $_GET['is_verified'] ?? '',
            'hospital_type' => trim($this->string('hospital_type')),
            'country' => trim($this->string('country')),
            'city' => trim($this->string('city')),
            'accreditation' => $this->string('accreditation'),
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
