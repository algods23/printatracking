<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Pcv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExcelExporter
{
    private const SHEET_TITLES = [
        'sales'          => 'Sales Report',
        'expenses'       => 'Disbursement Report',
        'pcv'            => 'PCV Report',
        'tasks'          => 'Task Report',
        'billing'        => 'Billing Report',
        'productivity'   => 'Productivity',
        'monthly'        => 'Monthly Summary',
        'daily_expenses' => 'Daily Disbursement',
        'daily_report'   => 'Daily Report',
    ];

    public function download(string $type, array $data, string $startDate, string $endDate): StreamedResponse
    {
        return $this->downloadMultiple([$type => $data], $startDate, $endDate);
    }

    /**
     * @param array<string, array> $reportsByType
     */
    public function downloadMultiple(array $reportsByType, string $startDate, string $endDate): StreamedResponse
    {
        if ($reportsByType === []) {
            throw new \InvalidArgumentException('At least one report type is required.');
        }

        $spreadsheet = new Spreadsheet();
        $sheetIndex = 0;

        foreach ($reportsByType as $type => $data) {
            if ($sheetIndex === 0) {
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $spreadsheet->createSheet($sheetIndex);
            }

            $sheet->setTitle($this->sheetTitle($type));
            $this->populateSheet($sheet, $this->rowsForType($type, $data));
            $sheetIndex++;
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = count($reportsByType) === 1
            ? str_replace(' ', '-', strtolower(self::SHEET_TITLES[array_key_first($reportsByType)]))
                . '-' . $startDate . '-to-' . $endDate . '.xlsx'
            : 'reports-' . $startDate . '-to-' . $endDate . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function rowsForType(string $type, array $data): array
    {
        return match ($type) {
            'sales'          => $this->salesRows($data),
            'expenses'       => $this->expenseRows($data),
            'pcv'            => $this->pcvRows($data),
            'tasks'          => $this->taskRows($data),
            'billing'        => $this->billingRows($data),
            'productivity'   => $this->productivityRows($data),
            'monthly'        => $this->monthlyRows($data),
            'daily_expenses' => $this->dailyExpensesRows($data),
            'daily_report'   => $this->dailyReportRows($data),
            default          => throw new \InvalidArgumentException("Unknown report type: {$type}"),
        };
    }

    private function sheetTitle(string $type): string
    {
        $title = self::SHEET_TITLES[$type] ?? 'Report';

        return mb_substr($title, 0, 31);
    }

    /**
     * @return array{headers: string[], body: array<int, array>, moneyColumns: int[], dateColumns: int[], totalRow: array|null}
     */
    private function salesRows(array $data): array
    {
        $body = [];
        $totalAmount = 0.0;

        foreach ($data['receipts'] as $receipt) {
            $amount = (float) $receipt->cash_received;
            $totalAmount += $amount;
            $body[] = [
                $receipt->receipt_number,
                $receipt->task?->task_id ?? '—',
                $receipt->customer_name,
                $receipt->payment_method,
                $amount,
                $this->excelDate($receipt->created_at),
            ];
        }

        return [
            'headers'      => ['Receipt #', 'Task', 'Customer', 'Method', 'Amount', 'Date'],
            'body'         => $body,
            'moneyColumns' => [5],
            'dateColumns'  => [6],
            'totalRow'     => ['', '', '', 'TOTAL', $totalAmount, ''],
        ];
    }

    private function expenseRows(array $data): array
    {
        $body = [];
        $totalAmount = 0.0;

        foreach ($data['expenses'] as $expense) {
            $amount = (float) $expense->amount;
            $totalAmount += $amount;
            $body[] = [
                $expense->expense_name,
                $expense->category,
                $amount,
                $this->excelDate($expense->date),
                $expense->recordedBy?->name ?? '—',
            ];
        }

        return [
            'headers'      => ['Name', 'Category', 'Amount', 'Date', 'Recorded by'],
            'body'         => $body,
            'moneyColumns' => [3],
            'dateColumns'  => [4],
            'totalRow'     => ['', 'TOTAL', $totalAmount, '', ''],
        ];
    }

    private function taskRows(array $data): array
    {
        $body = [];
        $totalAmount = 0.0;

        foreach ($data['tasks'] as $task) {
            $amount = (float) $task->amount;
            $totalAmount += $amount;
            $body[] = [
                $task->task_id,
                $task->customer_name,
                $task->assignedTo?->name ?? 'Unassigned',
                $task->status,
                $task->priority,
                $amount,
                $this->excelDate($task->created_at),
            ];
        }

        return [
            'headers'      => ['Task ID', 'Customer', 'Assigned to', 'Status', 'Priority', 'Amount', 'Created'],
            'body'         => $body,
            'moneyColumns' => [6],
            'dateColumns'  => [7],
            'totalRow'     => ['TOTAL', $data['totalTasks'] . ' tasks', '', '', '', $totalAmount, ''],
        ];
    }

    private function productivityRows(array $data): array
    {
        $body = [];
        $sumTotal = 0;
        $sumCompleted = 0;
        $sumPending = 0;

        foreach ($data['staffProductivity'] as $row) {
            $rate = $row['total'] > 0 ? round(($row['completed'] / $row['total']) * 100, 1) : 0;
            $sumTotal += $row['total'];
            $sumCompleted += $row['completed'];
            $sumPending += $row['pending'];
            $body[] = [
                $row['name'],
                $row['total'],
                $row['completed'],
                $row['pending'],
                $rate / 100,
            ];
        }

        $overallRate = $sumTotal > 0 ? round(($sumCompleted / $sumTotal) * 100, 1) / 100 : 0;

        return [
            'headers'      => ['Staff', 'Total tasks', 'Completed', 'Pending', 'Completion %'],
            'body'         => $body,
            'moneyColumns' => [],
            'dateColumns'  => [],
            'percentColumns' => [5],
            'totalRow'     => ['TOTAL', $sumTotal, $sumCompleted, $sumPending, $overallRate],
        ];
    }

    private function monthlyRows(array $data): array
    {
        $body = [];

        foreach ($data['monthlyRows'] as $row) {
            $body[] = [
                $row['label'],
                (float) $row['sales'],
                (float) $row['expenses'],
                (float) $row['pcv'],
                (float) $row['total'],
            ];
        }

        return [
            'headers'      => ['Month', 'Sales', 'Expenses', 'PCV', 'Total'],
            'body'         => $body,
            'moneyColumns' => [2, 3, 4, 5],
            'dateColumns'  => [],
            'totalRow'     => [
                'TOTAL',
                (float) $data['totalSales'],
                (float) $data['totalExpenses'],
                (float) $data['totalPcv'],
                (float) $data['netProfit'],
            ],
        ];
    }

    private function pcvRows(array $data): array
    {
        $body = [];
        $totalAmount = 0.0;

        foreach ($data['pcvs'] as $pcv) {
            $amount = (float) $pcv->amount;
            $totalAmount += $amount;
            $body[] = [
                $pcv->pcv_name,
                $pcv->category === 'Other' && $pcv->other_category ? $pcv->other_category : $pcv->category,
                $amount,
                $this->excelDate($pcv->date),
                $pcv->recordedBy?->name ?? '—',
            ];
        }

        return [
            'headers'      => ['Name', 'Category', 'Amount', 'Date', 'Recorded by'],
            'body'         => $body,
            'moneyColumns' => [3],
            'dateColumns'  => [4],
            'totalRow'     => ['', 'TOTAL', $totalAmount, '', ''],
        ];
    }

    /**
     * @param array{headers: string[], body: array<int, array>, moneyColumns: int[], dateColumns: int[], totalRow: array|null, percentColumns?: int[]} $config
     */
    private function populateSheet(Worksheet $sheet, array $config): void
    {
        $headers = $config['headers'];
        $body = $config['body'];
        $moneyColumns = $config['moneyColumns'] ?? [];
        $dateColumns = $config['dateColumns'] ?? [];
        $percentColumns = $config['percentColumns'] ?? [];
        $totalRow = $config['totalRow'] ?? null;
        $colCount = count($headers);

        foreach ($headers as $colIndex => $header) {
            $sheet->setCellValue([$colIndex + 1, 1], $header);
        }

        $rowIndex = 2;
        foreach ($body as $row) {
            foreach ($row as $colIndex => $value) {
                $sheet->setCellValue([$colIndex + 1, $rowIndex], $value);
            }
            $rowIndex++;
        }

        if ($totalRow !== null) {
            foreach ($totalRow as $colIndex => $value) {
                $sheet->setCellValue([$colIndex + 1, $rowIndex], $value);
            }
            $totalRowIndex = $rowIndex;
        } else {
            $totalRowIndex = null;
        }

        $lastRow = $totalRowIndex ?? max(1, $rowIndex - 1);
        $dataEndRow = $totalRowIndex ? $totalRowIndex - 1 : $lastRow;

        $this->applyFormatting(
            $sheet,
            $colCount,
            $dataEndRow,
            $totalRowIndex,
            $moneyColumns,
            $dateColumns,
            $percentColumns
        );
    }

    private function applyFormatting(
        Worksheet $sheet,
        int $colCount,
        int $dataEndRow,
        ?int $totalRowIndex,
        array $moneyColumns,
        array $dateColumns,
        array $percentColumns
    ): void {
        $lastCol = $this->columnLetter($colCount);
        $thinBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'B0B0B0'],
                ],
            ],
        ];

        $headerRange = "A1:{$lastCol}1";
        $sheet->getStyle($headerRange)->applyFromArray(array_merge($thinBorder, [
            'font'      => ['bold' => true, 'color' => ['rgb' => '1F2937']],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]));

        if ($dataEndRow >= 2) {
            for ($row = 2; $row <= $dataEndRow; $row++) {
                $range = "A{$row}:{$lastCol}{$row}";
                $style = $thinBorder;
                if (($row - 2) % 2 === 1) {
                    $style['fill'] = [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F2F2F2'],
                    ];
                }
                $sheet->getStyle($range)->applyFromArray($style);
            }
        }

        foreach ($moneyColumns as $col) {
            $letter = $this->columnLetter($col);
            $end = $totalRowIndex ?? $dataEndRow;
            if ($end >= 2) {
                $sheet->getStyle("{$letter}2:{$letter}{$end}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            }
        }

        foreach ($dateColumns as $col) {
            $letter = $this->columnLetter($col);
            $end = $totalRowIndex ? $totalRowIndex - 1 : $dataEndRow;
            if ($end >= 2) {
                $sheet->getStyle("{$letter}2:{$letter}{$end}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_DATE_XLSX14);
            }
        }

        foreach ($percentColumns as $col) {
            $letter = $this->columnLetter($col);
            $end = $totalRowIndex ?? $dataEndRow;
            if ($end >= 2) {
                $sheet->getStyle("{$letter}2:{$letter}{$end}")
                    ->getNumberFormat()
                    ->setFormatCode('0.0%');
            }
        }

        if ($totalRowIndex !== null) {
            $totalRange = "A{$totalRowIndex}:{$lastCol}{$totalRowIndex}";
            $sheet->getStyle($totalRange)->applyFromArray(array_merge($thinBorder, [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8E8E8'],
                ],
            ]));

            foreach ($moneyColumns as $col) {
                $letter = $this->columnLetter($col);
                $sheet->getStyle("{$letter}{$totalRowIndex}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            }

            foreach ($percentColumns as $col) {
                $letter = $this->columnLetter($col);
                $sheet->getStyle("{$letter}{$totalRowIndex}")
                    ->getNumberFormat()
                    ->setFormatCode('0.0%');
            }
        }

        for ($col = 1; $col <= $colCount; $col++) {
            $sheet->getColumnDimension($this->columnLetter($col))->setAutoSize(true);
        }

        $sheet->freezePane('A2');
        $sheet->setSelectedCell('A2');
    }

    private function dailyExpensesRows(array $data): array
    {
        $body = [];
        $totalAmount = 0.0;

        foreach ($data['dailySummary'] as $day) {
            $amount = (float) $day['total'];
            $totalAmount += $amount;
            $body[] = [
                $this->excelDate($day['date']),
                $amount,
                $day['count'],
            ];
        }

        return [
            'headers'      => ['Date', 'Total Expenses', 'Number of Entries'],
            'body'         => $body,
            'moneyColumns' => [2],
            'dateColumns'  => [1],
            'totalRow'     => ['TOTAL', $totalAmount, ''],
        ];
    }

    private function dailyReportRows(array $data): array
    {
        $body = [];

        foreach ($data['dailyRows'] as $row) {
            $body[] = [
                $this->excelDate($row['date']),
                (float) $row['sales'],
                (float) $row['expenses'],
                (float) $row['profit'],
            ];
        }

        return [
            'headers'      => ['Date', 'Sales', 'Expenses', 'Profit'],
            'body'         => $body,
            'moneyColumns' => [2, 3, 4],
            'dateColumns'  => [1],
            'totalRow'     => [
                'TOTAL',
                (float) $data['totalSales'],
                (float) $data['totalExpenses'],
                (float) $data['netProfit'],
            ],
        ];
    }

    private function excelDate(Carbon|\DateTimeInterface|string $value): float
    {
        $date = $value instanceof Carbon
            ? $value
            : Carbon::parse($value);

        return \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($date);
    }

    private function columnLetter(int $columnIndex): string
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
    }

    private function billingRows(array $data): array
    {
        $body = [];
        $totalAmount = 0.0;
        $totalDeposit = 0.0;
        $totalBalance = 0.0;

        foreach ($data['tasks'] as $task) {
            $amount = (float) $task->amount;
            $deposit = (float) $task->receipts->sum('cash_received');
            $balance = (float) $task->balance;

            $totalAmount += $amount;
            $totalDeposit += $deposit;
            $totalBalance += $balance;

            $body[] = [
                $this->excelDate($task->created_at),
                $task->task_id,
                $task->customer_name,
                $task->contact_number ?? '—',
                $amount,
                $deposit,
                $balance,
                $task->payment_status,
            ];
        }

        return [
            'headers'      => ['Date', 'Billing ID', 'Customer', 'Contact', 'Total', 'Deposit', 'Balance', 'Status'],
            'body'         => $body,
            'moneyColumns' => [5, 6, 7],
            'dateColumns'  => [1],
            'totalRow'     => ['TOTAL', '', '', '', $totalAmount, $totalDeposit, $totalBalance, ''],
        ];
    }
}
