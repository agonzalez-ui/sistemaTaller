<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExcelExporter
{
    public function download(string $type, string $title, string $description, Builder $query, array $totals, User $user): BinaryFileResponse
    {
        [$headers, $map] = $this->definition($type);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($title, 0, 31));
        $this->header($sheet, $title, $description, $user, count($headers));
        $sheet->fromArray($headers, null, 'A7');
        $row = 8;
        $query->reorder()->orderBy('id')->chunkById(500, function ($records) use ($sheet, $map, &$row) {
            foreach ($records as $record) {
                foreach ($map($record) as $column => $value) {
                    $coordinate = chr(65 + $column).$row;
                    if (is_string($value)) {
                        $sheet->setCellValueExplicit($coordinate, $value, DataType::TYPE_STRING);
                    } else {
                        $sheet->setCellValue($coordinate, $value);
                    }
                }
                $row++;
            }
        });
        $this->footer($sheet, $totals, $row, count($headers));

        $path = tempnam(storage_path('app'), 'simrh-report-').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return response()->download($path, 'SIMRH-'.str($type)->slug().'-'.now()->format('Ymd-His').'.xlsx')->deleteFileAfterSend(true);
    }

    private function definition(string $type): array
    {
        return match ($type) {
            'billing' => [['Factura', 'Fecha', 'Cliente', 'Orden', 'Estado', 'Total'], fn ($row) => [$row->number, $row->date->format('d/m/Y H:i'), $row->customer->name, $row->order->number, $row->status === 'ISSUED' ? 'Emitida' : 'Anulada', (float) $row->total]],
            'orders' => [['Orden', 'Recepción', 'Cliente', 'Placa', 'Mecánico', 'Estado', 'Valor'], fn ($row) => [$row->number, $row->received_at->format('d/m/Y H:i'), $row->customer->name, $row->vehicle->license_plate, $row->mechanic?->name ?? 'Sin asignar', $row->status->name, (float) $row->labor_cost + (float) $row->items_sum_line_total]],
            'inventory' => [['Código', 'Repuesto', 'Marca', 'Stock', 'Mínimo', 'Precio', 'Valor', 'Estado'], fn ($row) => [$row->code, $row->name, $row->brand?->name ?? 'Sin marca', $row->stock_quantity, $row->minimum_quantity, (float) $row->price, $row->stock_quantity * (float) $row->price, $row->active ? 'Activo' : 'Inactivo']],
            'access' => [['Código', 'Usuario', 'Ingreso', 'Salida', 'Tipo de salida', 'Dirección IP'], fn ($row) => [$row->id, $row->user->name, $row->logged_in_at->format('d/m/Y H:i:s'), $row->logged_out_at?->format('d/m/Y H:i:s') ?? 'Sesión abierta', $row->logout_type ?? '', $row->ip_address ?? '']],
            'activity' => [['Código', 'Usuario', 'Fecha y hora', 'Movimiento', 'Tabla', 'Registro', 'Detalle'], fn ($row) => [$row->id, $row->user->name, $row->occurred_at->format('d/m/Y H:i:s'), $row->action, $row->table_name, $row->record_id ?? '', $row->details]],
        };
    }

    private function header($sheet, string $title, string $description, User $user, int $columns): void
    {
        $last = chr(64 + $columns);
        $drawing = new Drawing;
        $drawing->setName('SIMRH')->setPath(public_path('img/logo.png'))->setHeight(65)->setCoordinates('A1')->setWorksheet($sheet);
        $sheet->mergeCells("C1:{$last}2")->setCellValue('C1', $title);
        $sheet->mergeCells("C3:{$last}3")->setCellValue('C3', $description);
        $sheet->mergeCells("A5:{$last}5")->setCellValue('A5', 'Generado el '.now()->format('d/m/Y H:i').' por '.$user->name);
        $sheet->getStyle("A1:{$last}5")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('172F4F');
        $sheet->getStyle("A1:{$last}5")->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('C1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(35);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(6)->setRowHeight(8);
    }

    private function footer($sheet, array $totals, int $row, int $columns): void
    {
        $last = chr(64 + $columns);
        $sheet->getStyle("A7:{$last}7")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FBBF24');
        $sheet->getStyle("A7:{$last}7")->getFont()->setBold(true)->getColor()->setRGB('172F4F');
        $sheet->getStyle("A7:{$last}".max(7, $row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D1D5DB');
        $summary = collect($totals)->map(fn ($value, $label) => "$label: $value")->implode('   |   ');
        $sheet->mergeCells("A{$row}:{$last}{$row}")->setCellValue("A{$row}", $summary);
        $sheet->getStyle("A{$row}:{$last}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF3C7');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->setAutoFilter("A7:{$last}7");
        $sheet->freezePane('A8');
        foreach (range('A', $last) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        $sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(.4)->setRight(.3)->setBottom(.4)->setLeft(.3);
        $sheet->getHeaderFooter()->setOddFooter('&LSIMRH&CReporte generado por el sistema&R&P / &N');
    }
}
