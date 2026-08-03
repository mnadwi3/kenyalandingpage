<?php

declare(strict_types=1);

namespace App\Services;

final class SpecialtyExportService
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
        $xml .= '<Worksheet ss:Name="Specialties"><Table><Row>';
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
        return ['uuid', 'slug', 'name', 'status', 'created_at', 'updated_at'];
    }

    private function mapRow(array $row): array
    {
        return [
            $row['uuid'] ?? '',
            $row['slug'] ?? '',
            $row['name'] ?? '',
            $row['status'] ?? '',
            $row['created_at'] ?? '',
            $row['updated_at'] ?? '',
        ];
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
