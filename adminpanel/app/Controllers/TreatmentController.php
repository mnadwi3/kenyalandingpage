<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\View;
use App\Services\TreatmentExportService;
use App\Services\TreatmentService;
use RuntimeException;

final class TreatmentController extends Controller
{
    private TreatmentService $treatments;
    private TreatmentExportService $exporter;

    public function __construct(?TreatmentService $treatments = null, ?TreatmentExportService $exporter = null)
    {
        $this->treatments = $treatments ?? new TreatmentService();
        $this->exporter = $exporter ?? new TreatmentExportService();
    }

    public function index(): void
    {
        $result = $this->treatments->list($_GET);
        View::renderInLayout('treatments/index', 'admin', [
            'title' => 'Treatments',
            'activeNav' => 'treatments',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => url('/dashboard')],
                ['label' => 'Treatments'],
            ],
            'user' => Auth::user(),
            'csrf' => Csrf::field(),
            'treatments' => $result['treatments'],
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
            $id = $this->treatments->create($_POST, $_FILES['featured_image'] ?? null);
            flash('success', 'Treatment created successfully.');
            redirect('/treatments/' . $id . '/edit');
        } catch (RuntimeException $e) {
            Session::flash('old', $_POST);
            flash('error', $e->getMessage());
            redirect('/treatments/create');
        }
    }

    public function edit(string $id): void
    {
        $treatment = $this->treatments->find((int) $id, true);
        if ($treatment === null) {
            flash('error', 'Treatment not found.');
            redirect('/treatments');
        }
        $old = Session::getFlash('old');
        $this->renderForm($treatment, is_array($old) ? $old : $treatment);
    }

    public function update(string $id): void
    {
        $this->validateCsrf();
        $treatmentId = (int) $id;
        try {
            $this->treatments->update($treatmentId, $_POST, $_FILES['featured_image'] ?? null);
            flash('success', 'Treatment updated successfully.');
            redirect('/treatments/' . $treatmentId . '/edit');
        } catch (RuntimeException $e) {
            Session::flash('old', $_POST);
            flash('error', $e->getMessage());
            redirect('/treatments/' . $treatmentId . '/edit');
        }
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();
        $this->treatments->softDelete((int) $id);
        flash('success', 'Treatment moved to trash.');
        redirect('/treatments');
    }

    public function restore(string $id): void
    {
        $this->validateCsrf();
        $this->treatments->restore((int) $id);
        flash('success', 'Treatment restored.');
        redirect('/treatments?trashed=only');
    }

    public function duplicate(string $id): void
    {
        $this->validateCsrf();
        try {
            $newId = $this->treatments->duplicate((int) $id);
            flash('success', 'Treatment duplicated.');
            redirect('/treatments/' . $newId . '/edit');
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/treatments');
        }
    }

    public function bulk(): void
    {
        $this->validateCsrf();
        $ids = array_map('intval', (array) ($_POST['ids'] ?? []));
        $action = (string) ($_POST['bulk_action'] ?? '');
        $count = match ($action) {
            'delete' => $this->treatments->bulkSoftDelete($ids),
            'restore' => $this->treatments->bulkRestore($ids),
            'active', 'inactive', 'draft', 'archived' => $this->treatments->bulkStatus($ids, $action),
            default => 0,
        };
        flash($count > 0 ? 'success' : 'error', $count > 0 ? "Updated {$count} treatment(s)." : 'No treatments updated.');
        redirect('/treatments');
    }

    public function exportCsv(): void
    {
        $this->download('treatments.csv', 'text/csv; charset=utf-8', $this->exporter->toCsv($this->treatments->exportRows($this->exportFilters())));
    }

    public function exportJson(): void
    {
        $this->download('treatments.json', 'application/json; charset=utf-8', $this->exporter->toJson($this->treatments->exportRows($this->exportFilters())));
    }

    public function exportExcel(): void
    {
        $this->download('treatments.xls', 'application/vnd.ms-excel; charset=utf-8', $this->exporter->toExcelXml($this->treatments->exportRows($this->exportFilters())));
    }

    public function importForm(): void
    {
        View::renderInLayout('treatments/import', 'admin', [
            'title' => 'Import Treatments',
            'activeNav' => 'treatments',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => url('/dashboard')],
                ['label' => 'Treatments', 'href' => url('/treatments')],
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
            redirect('/treatments/import');
        }
        $rows = $this->parseCsv((string) $file['tmp_name']);
        $result = $this->treatments->importRows($rows);
        $message = "Imported {$result['created']} treatment(s).";
        if ($result['failed'] > 0) {
            $message .= " {$result['failed']} failed.";
            if ($result['errors'] !== []) {
                Session::flash('import_errors', array_slice($result['errors'], 0, 10));
            }
        }
        flash($result['failed'] > 0 ? 'error' : 'success', $message);
        redirect('/treatments');
    }

    private function renderForm(?array $treatment, array $values): void
    {
        View::renderInLayout('treatments/form', 'admin', [
            'title' => $treatment ? 'Edit Treatment' : 'Add Treatment',
            'activeNav' => 'treatments',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => url('/dashboard')],
                ['label' => 'Treatments', 'href' => url('/treatments')],
                ['label' => $treatment ? 'Edit' : 'Add'],
            ],
            'user' => Auth::user(),
            'csrf' => Csrf::field(),
            'treatment' => $treatment,
            'values' => $values,
            'options' => $this->treatments->formOptions(),
            'notifications' => [],
        ]);
    }

    private function exportFilters(): array
    {
        return [
            'q' => trim($this->string('q')),
            'status' => $this->string('status'),
            'is_featured' => $_GET['is_featured'] ?? '',
            'category' => trim($this->string('category')),
            'specialty_id' => $_GET['specialty_id'] ?? '',
            'hospital_id' => $_GET['hospital_id'] ?? '',
            'doctor_id' => $_GET['doctor_id'] ?? '',
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
