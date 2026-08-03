<?php

declare(strict_types=1);

namespace App\Services;

final class DoctorExportService
{
    public function toCsv(array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            return '';
        }
        fputcsv($handle, $this->headers());
        foreach ($rows as $row) {
            fputcsv($handle, $this->mapRow($row));
        }
        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return "\xEF\xBB\xBF" . $csv;
    }

    public function toJson(array $rows): string
    {
        $payload = array_map(fn (array $row): array => array_combine($this->headers(), $this->mapRow($row)) ?: [], $rows);

        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
    }

    public function toExcelXml(array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
        $xml .= '<Worksheet ss:Name="Doctors"><Table><Row>';
        foreach ($this->headers() as $header) {
            $xml .= '<Cell><Data ss:Type="String">' . $this->xml($header) . '</Data></Cell>';
        }
        $xml .= '</Row>';
        foreach ($rows as $row) {
            $xml .= '<Row>';
            foreach ($this->mapRow($row) as $value) {
                $type = is_numeric($value) ? 'Number' : 'String';
                $xml .= '<Cell><Data ss:Type="' . $type . '">' . $this->xml((string) $value) . '</Data></Cell>';
            }
            $xml .= '</Row>';
        }
        $xml .= '</Table></Worksheet></Workbook>';

        return $xml;
    }

    public function headers(): array
    {
        return [
            'uuid', 'slug', 'name', 'qualification', 'expertise', 'education',
            'registration_number', 'status', 'is_featured',
            'seo_title', 'seo_description', 'public_url', 'created_at',
        ];
    }

    private function mapRow(array $row): array
    {
        $slug = (string) ($row['slug'] ?? '');

        return [
            $row['uuid'] ?? '',
            $slug,
            $row['name'] ?? '',
            $row['qualification'] ?? '',
            $row['expertise'] ?? $row['experience_summary'] ?? '',
            $row['education'] ?? '',
            $row['registration_number'] ?? '',
            $row['status'] ?? '',
            (int) ($row['is_featured'] ?? 0),
            $row['seo_title'] ?? '',
            $row['seo_description'] ?? '',
            '/doctors/' . $slug,
            $row['created_at'] ?? '',
        ];
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
