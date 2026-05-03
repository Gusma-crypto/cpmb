<?php

namespace App\Modules\Registration\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationExportService
{
    public function excel(Collection $registrations): StreamedResponse
    {
        $filename = 'pendaftaran-pmb-' . now()->format('Ymd-His') . '.xls';

        return response()->streamDownload(function () use ($registrations): void {
            echo '<html><head><meta charset="UTF-8">';
            echo '<style>
                body { font-family: Arial, sans-serif; }
                table { border-collapse: collapse; width: 100%; }
                th { background: #1d4ed8; color: #ffffff; font-weight: bold; }
                th, td { border: 1px solid #cbd5e1; padding: 8px; font-size: 12px; vertical-align: top; }
                .header-table td { border: 0; padding: 4px 8px; }
                .logo { width: 48px; height: 48px; background: #1d4ed8; color: #ffffff; text-align: center; font-size: 24px; font-weight: bold; }
                .title { font-size: 18px; font-weight: bold; color: #111827; }
                .report-title { font-size: 18px; font-weight: bold; color: #111827; text-align: center; }
                .meta { font-size: 11px; color: #475569; text-align: right; }
            </style>';
            echo '</head><body>';
            echo '<table class="header-table">';
            echo '<tr>';
            echo '<td class="logo">U</td>';
            echo '<td>';
            echo '<div class="title">Universitas</div>';
            echo '</td>';
            echo '<td class="report-title">Daftar Calon Mahasiswa Baru</td>';
            echo '<td class="meta">Tanggal Export: ' . e(now()->format('d/m/Y H:i')) . '<br>Total Data: ' . e((string) $registrations->count()) . '</td>';
            echo '</td>';
            echo '</tr>';
            echo '</table>';
            echo '<br>';
            echo '<table>';
            echo '<thead><tr>';

            foreach ($this->headings() as $heading) {
                echo '<th>' . e($heading) . '</th>';
            }

            echo '</tr></thead><tbody>';

            foreach ($registrations as $registration) {
                echo '<tr>';

                foreach ($this->row($registration) as $value) {
                    echo '<td>' . e($value) . '</td>';
                }

                echo '</tr>';
            }

            echo '</tbody></table></body></html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function pdf(Collection $registrations): Response
    {
        $rows = $registrations->map(fn ($registration) => $this->row($registration))->values();
        $pages = $rows->isEmpty()
            ? collect([collect()])
            : $rows->chunk(18)->values();
        $objects = [];
        $pageIds = [];
        $nextId = 3;

        foreach ($pages as $pageIndex => $pageRows) {
            $contentId = $nextId++;
            $pageId = $nextId++;
            $pageIds[] = $pageId;

            $objects[$contentId] = $this->streamObject($this->pageContent($pageRows->all(), $pageIndex + 1));
            $objects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 1 0 R >> >> /Contents {$contentId} 0 R >>";
        }

        $objects[1] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', array_map(fn (int $id) => "{$id} 0 R", $pageIds)) . '] /Count ' . count($pageIds) . ' >>';
        $catalogId = $nextId++;
        $objects[$catalogId] = '<< /Type /Catalog /Pages 2 0 R >>';

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$object}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $xrefSize = $catalogId + 1;
        $pdf .= "xref\n0 {$xrefSize}\n";
        $pdf .= "0000000000 65535 f \n";

        for ($id = 1; $id <= $catalogId; $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id] ?? 0);
        }

        $pdf .= "trailer\n<< /Size {$xrefSize} /Root {$catalogId} 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="pendaftaran-pmb-' . now()->format('Ymd-His') . '.pdf"',
        ]);
    }

    private function headings(): array
    {
        return [
            'No Registrasi',
            'Nama Mahasiswa',
            'Email',
            'No HP',
            'Program Studi',
            'Status Pendaftaran',
            'Status Pembayaran',
            'Tanggal Daftar',
        ];
    }

    private function row($registration): array
    {
        return [
            $registration->registration_number,
            $registration->user?->name ?? '-',
            $registration->user?->email ?? '-',
            $registration->user?->phone ?? '-',
            $registration->program?->name ?? '-',
            $registration->status,
            $registration->payment_status ?? 'unpaid',
            optional($registration->created_at)->format('d/m/Y H:i') ?? '-',
        ];
    }

    private function pageContent(array $rows, int $page): string
    {
        $content = "BT\n";
        $content .= "/F1 26 Tf\n45 542 Td\n(" . $this->escapePdf('U') . ") Tj\n";
        $content .= "/F1 16 Tf\n75 548 Td\n(" . $this->escapePdf('Universitas') . ") Tj\n";
        $content .= "/F1 17 Tf\n245 548 Td\n(" . $this->escapePdf('Daftar Calon Mahasiswa Baru') . ") Tj\n";
        $content .= "/F1 9 Tf\n620 548 Td\n(" . $this->escapePdf('Tanggal Export: ' . now()->format('d/m/Y H:i')) . ") Tj\n";
        $content .= "0 -14 Td\n(" . $this->escapePdf("Halaman {$page}") . ") Tj\n";
        $content .= "ET\n";
        $content .= "0.2 w\n40 515 m 802 515 l S\n";

        $x = 35;
        $y = 485;
        $rowHeight = 22;
        $columnWidths = [86, 100, 135, 80, 125, 80, 80, 86];
        $tableWidth = array_sum($columnWidths);
        $headings = $this->headings();

        $content .= "0.92 g\n{$x} " . ($y - $rowHeight + 5) . " {$tableWidth} {$rowHeight} re f\n0 g\n";
        $content .= $this->drawTableGrid($x, $y, count($rows) + 1, $rowHeight, $columnWidths);
        $content .= $this->drawRowText($headings, $x, $y - 13, $columnWidths, 7, true);

        foreach ($rows as $index => $row) {
            $content .= $this->drawRowText($row, $x, $y - (($index + 1) * $rowHeight) - 13, $columnWidths, 7);
        }

        if (empty($rows)) {
            $content .= $this->text($x + 8, $y - 35, 'Tidak ada data pendaftaran.', 8);
        }

        return $content;
    }

    private function drawTableGrid(int $x, int $y, int $rows, int $rowHeight, array $columnWidths): string
    {
        $height = $rows * $rowHeight;
        $width = array_sum($columnWidths);
        $bottom = $y - $height + 5;
        $content = "0.35 w\n";

        for ($index = 0; $index <= $rows; $index++) {
            $lineY = $y - ($index * $rowHeight) + 5;
            $content .= "{$x} {$lineY} m " . ($x + $width) . " {$lineY} l S\n";
        }

        $currentX = $x;
        $content .= "{$currentX} {$y} m {$currentX} {$bottom} l S\n";

        foreach ($columnWidths as $columnWidth) {
            $currentX += $columnWidth;
            $content .= "{$currentX} {$y} m {$currentX} {$bottom} l S\n";
        }

        return $content;
    }

    private function drawRowText(array $row, int $x, int $y, array $columnWidths, int $fontSize, bool $bold = false): string
    {
        $content = '';
        $currentX = $x + 4;

        foreach ($row as $index => $value) {
            $maxCharacters = max(8, (int) floor(($columnWidths[$index] ?? 80) / 5.2));
            $text = Str::limit((string) $value, $maxCharacters, '');
            $content .= $this->text($currentX, $y, $text, $fontSize);
            $currentX += $columnWidths[$index] ?? 80;
        }

        return $content;
    }

    private function text(int $x, int $y, string $text, int $fontSize): string
    {
        return "BT\n/F1 {$fontSize} Tf\n{$x} {$y} Td\n(" . $this->escapePdf($text) . ") Tj\nET\n";
    }

    private function streamObject(string $content): string
    {
        return '<< /Length ' . strlen($content) . " >>\nstream\n{$content}endstream";
    }

    private function escapePdf(string $value): string
    {
        $value = Str::ascii($value);

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }
}
