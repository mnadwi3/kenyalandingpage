<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\View;
use App\Services\SpecialtyExportService;
use App\Services\SpecialtyService;
use RuntimeException;

final class SpecialtyController extends Controller
{
    private SpecialtyService $specialties;
    private SpecialtyExportService $exporter;

    public function __construct(?SpecialtyService $specialties = null, ?SpecialtyExportService $exporter = null)
    {
        $this->specialties = $specialties ?? new SpecialtyService();
        $this->exporter = $exporter ?? new SpecialtyExportService();
    }

    public function index(): void
    {
        $result = $this->specialties->list($_GET);
        View::renderInLayout('specialties/index', 'admin', [
            'title' => 'Specialties',
            'activeNav' => 'specialties',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => url('/dashboard')],
                ['label' => 'Specialties'],
            ],
            'user' => Auth::user(),
            'csrf' => Csrf::field(),
            'specialties' => $result['specialties'],
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
            $id = $this->specialties->create($_POST);
            flash('success', 'Specialty created successfully.');
            redirect('/specialties/' . $id . '/edit');
        } catch (RuntimeException $e) {
            Session::flash('old', $_POST);
            flash('error', $e->getMessage());
            redirect('/specialties/create');
        }
    }

    public function edit(string $id): void
    {
        $specialty = $this->specialties->find((int) $id, true);
        if ($specialty === null) {
            flash('error', 'Specialty not found.');
            redirect('/specialties');
        }
        $old = Session::getFlash('old');
        $this->renderForm($specialty, is_array($old) ? $old : $specialty);
    }

    public function update(string $id): void
    {
        $this->validateCsrf();
        $specialtyId = (int) $id;
        try {
            $this->specialties->update($specialtyId, $_POST);
            flash('success', 'Specialty updated successfully.');
            redirect('/specialties/' . $specialtyId . '/edit');
        } catch (RuntimeException $e) {
            Session::flash('old', $_POST);
            flash('error', $e->getMessage());
            redirect('/specialties/' . $specialtyId . '/edit');
        }
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();
        $this->specialties->softDelete((int) $id);
        flash('success', 'Specialty moved to trash.');
        redirect('/specialties');
    }

    public function restore(string $id): void
    {
        $this->validateCsrf();
        $this->specialties->restore((int) $id);
        flash('success', 'Specialty restored.');
        redirect('/specialties?trashed=only');
    }

    public function bulk(): void
    {
        $this->validateCsrf();
        $ids = array_map('intval', (array) ($_POST['ids'] ?? []));
        $action = (string) ($_POST['bulk_action'] ?? '');
        $count = match ($action) {
            'delete' => $this->specialties->bulkSoftDelete($ids),
            'restore' => $this->specialties->bulkRestore($ids),
            'active', 'inactive', 'draft', 'archived' => $this->specialties->bulkStatus($ids, $action),
            default => 0,
        };
        flash($count > 0 ? 'success' : 'error', $count > 0 ? "Updated {$count} specialty(ies)." : 'No specialties updated.');
        redirect('/specialties');
    }

    public function exportCsv(): void
    {
        $this->download('specialties.csv', 'text/csv; charset=utf-8', $this->exporter->toCsv($this->specialties->exportRows($this->exportFilters())));
    }

    public function exportJson(): void
    {
        $this->download('specialties.json', 'application/json; charset=utf-8', $this->exporter->toJson($this->specialties->exportRows($this->exportFilters())));
    }

    public function exportExcel(): void
    {
        $this->download('specialties.xls', 'application/vnd.ms-excel; charset=utf-8', $this->exporter->toExcelXml($this->specialties->exportRows($this->exportFilters())));
    }

    public function importForm(): void
    {
        View::renderInLayout('specialties/import', 'admin', [
            'title' => 'Import Specialties',
            'activeNav' => 'specialties',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => url('/dashboard')],
                ['label' => 'Specialties', 'href' => url('/specialties')],
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
            redirect('/specialties/import');
        }
        $rows = $this->parseCsv((string) $file['tmp_name']);
        $result = $this->specialties->importRows($rows);
        $message = "Imported {$result['created']} specialty(ies).";
        if ($result['failed'] > 0) {
            $message .= " {$result['failed']} failed.";
            if ($result['errors'] !== []) {
                Session::flash('import_errors', array_slice($result['errors'], 0, 10));
            }
        }
        flash($result['failed'] > 0 ? 'error' : 'success', $message);
        redirect('/specialties');
    }

    private function renderForm(?array $specialty, array $values): void
    {
        View::renderInLayout('specialties/form', 'admin', [
            'title' => $specialty ? 'Edit Specialty' : 'Add Specialty',
            'activeNav' => 'specialties',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => url('/dashboard')],
                ['label' => 'Specialties', 'href' => url('/specialties')],
                ['label' => $specialty ? 'Edit' : 'Add'],
            ],
            'user' => Auth::user(),
            'csrf' => Csrf::field(),
            'specialty' => $specialty,
            'values' => $values,
            'options' => ['statuses' => \App\Models\Specialty::STATUSES],
            'notifications' => [],
        ]);
    }

    private function exportFilters(): array
    {
        return [
            'q' => trim($this->string('q')),
            'status' => $this->string('status'),
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
