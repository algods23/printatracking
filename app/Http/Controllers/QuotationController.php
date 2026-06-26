<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
       

        $query = Task::query()
            ->with(['assignedTo', 'receipts'])
            ->where('status', '!=', 'Cancelled')
            ->where('payment_status', '!=', 'Paid');

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('task_id', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });

            $query->orderByRaw(
                'CASE WHEN customer_name LIKE ? THEN 0 WHEN customer_name LIKE ? THEN 1 ELSE 2 END',
                ["{$search}%", "%{$search}%"]
            );
        }

        $quotations = $query->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $summaryQuery = Task::query()
            ->where('status', '!=', 'Cancelled')
            ->where('payment_status', '!=', 'Paid');

        if ($request->filled('q')) {
            $search = $request->input('q');
            $summaryQuery->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('task_id', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        $summary = $summaryQuery
            ->withSum('receipts as paid_amount', 'cash_received')
            ->get();

        $totalAmount = (float) $summary->sum('amount');
        $totalDeposit = (float) $summary->sum(fn ($task) => (float) ($task->paid_amount ?? 0));
        $totalBalance = (float) $summary->sum(fn ($task) => (float) $task->balance);

        return view('quotation.index', compact(
            'quotations',
            'totalAmount',
            'totalDeposit',
            'totalBalance'
        ));
    }

    public function download(Request $request)
    {
     
        $customerName = $request->input('customer');
        $selectedIds = array_values(array_filter((array) $request->input('ids', [])));
        $query = Task::query()
            ->with(['assignedTo', 'receipts', 'items'])
            ->where('status', '!=', 'Cancelled')
            ->where('payment_status', '!=', 'Paid');

        if (! empty($selectedIds)) {
            $query->whereIn('id', $selectedIds);
        } elseif ($customerName) {
            $query->where('customer_name', $customerName);
        } elseif ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('task_id', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        $quotations = $query->orderByDesc('created_at')->get();

        $billingRows = collect();

        foreach ($quotations as $task) {
            if ($task->items->isNotEmpty()) {
                foreach ($task->items as $item) {
                    $billingRows->push([
                        'date' => $task->created_at->format('M d, Y'),
                        'job_order' => $task->task_id,
                        'quantity' => (int) $item->quantity,
                        'unit' => 'pc',
                        'details' => $item->job_order,
                        'price' => (float) $item->price,
                        'amount' => (float) $item->total,
                    ]);
                }

                continue;
            }

            $billingRows->push([
                'date' => $task->created_at->format('M d, Y'),
                'job_order' => $task->task_id,
                'quantity' => 1,
                'unit' => 'job',
                'details' => $task->notes ? Str::limit($task->notes, 60) : ($task->product_type ?: $task->customer_name),
                'price' => (float) $task->amount,
                'amount' => (float) $task->amount,
            ]);
        }

        $totalAmount = (float) $quotations->sum('amount');
        $totalDeposit = (float) $quotations->sum(fn ($task) => (float) ($task->paid_amount ?? $task->receipts->sum('cash_received')));
        $totalBalance = (float) $quotations->sum(fn ($task) => (float) $task->balance);

        $customerNames = $quotations
            ->pluck('customer_name')
            ->filter()
            ->map(fn ($name) => trim((string) $name))
            ->unique(fn ($name) => mb_strtolower($name))
            ->values();

        $contactNumbers = $quotations
            ->pluck('contact_number')
            ->filter()
            ->map(fn ($number) => trim((string) $number))
            ->unique(fn ($number) => mb_strtolower($number))
            ->values();

        $displayCustomerName = $customerNames->count() === 1
            ? $customerNames->first()
            : ($customerName ?: null);
        $displayContactNumber = $contactNumbers->count() === 1 ? $contactNumbers->first() : null;
        $multipleCustomers = $customerNames->count() > 1;

        $companyName = Setting::get('company_name', 'PRINTA SIGNAGES & STICKERS');
        $companyAddress = Setting::get('company_address', 'KUMINTANG ST., MINTAL, DAVAO CITY');
        $companyPhone = Setting::get('company_phone', '09667550044');
        $logoPath = Setting::get('company_logo');
        $selectedLogoPath = null;

        $logoCandidates = array_filter([
            $logoPath ? public_path('storage/' . $logoPath) : null,
            public_path('images/printa-3color.png'),
        ]);

        foreach ($logoCandidates as $logoFile) {
            if (! is_file($logoFile)) {
                continue;
            }

            $extension = strtolower(pathinfo($logoFile, PATHINFO_EXTENSION));
            $mimeType = match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/png',
            };

            $selectedLogoPath = $logoFile;
            break;
        }

        $billingReference = str_pad((string) ($quotations->first()?->id ?? 1), 3, '0', STR_PAD_LEFT);

        $jpg = $this->renderBillingJpg([
            'billingRows' => $billingRows,
            'totalAmount' => $totalAmount,
            'totalDeposit' => $totalDeposit,
            'totalBalance' => $totalBalance,
            'customerName' => $displayCustomerName,
            'customerContact' => $displayContactNumber,
            'billingReference' => $billingReference,
            'companyName' => $companyName,
            'companyAddress' => $companyAddress,
            'companyPhone' => $companyPhone,
            'logoPath' => $selectedLogoPath,
            'dueDate' => \Carbon\Carbon::parse($request->input('due_date', now()->addDays(14)))->format('M d, Y'),
            'authRep' => $request->input('auth_rep', 'Jelian Fernandez'),
        ]);

        $filename = 'billing-' . ($displayCustomerName ? Str::slug($displayCustomerName) . '-' : '') . now()->format('Ymd-His') . '.jpg';

        return response($jpg, 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function renderBillingJpg(array $data): string
    {
        $width = 1240;
        $height = max(1754, 760 + ($data['billingRows']->count() * 86));
        $image = imagecreatetruecolor($width, $height);

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $gray = imagecolorallocate($image, 85, 85, 85);
        $softGray = imagecolorallocate($image, 250, 248, 243);
        $yellow = imagecolorallocate($image, 251, 225, 2);
        $paleYellow = imagecolorallocate($image, 255, 253, 240);
        $gold = imagecolorallocate($image, 184, 134, 11);

        imagefilledrectangle($image, 0, 0, $width, $height, $white);

        $font = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');
        $bold = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');

        imagefilledrectangle($image, 0, 0, $width, 170, $yellow);
        $this->drawLogo($image, $data['logoPath'], 34, 22, 430, 126);
        $this->drawText($image, $data['companyName'], 25, $width - 34, 48, $black, $bold, 'right');
        $this->drawText($image, $data['companyAddress'], 17, $width - 34, 86, $black, $font, 'right');
        $this->drawText($image, $data['companyPhone'], 17, $width - 34, 116, $black, $font, 'right');

        $this->drawText($image, 'BILLING STATEMENT', 31, $width / 2, 220, $black, $bold, 'center');

        $x = 34;
        $y = 272;
        $gap = 22;
        $boxW = (int) (($width - ($x * 2) - $gap) / 2);
        $boxH = 172;
        $this->drawInfoBox($image, $x, $y, $boxW, $boxH, 'Bill To:', [
            $data['customerName'] ?: 'Multiple Customers',
            $data['customerContact'] ?: '',
        ], $font, $bold, $yellow, $black, $gray);

        $this->drawInfoBox($image, $x + $boxW + $gap, $y, $boxW, $boxH, 'Statement Details', [
            'Statement No. : BS-' . now()->format('Y-m-d') . '-' . $data['billingReference'],
            'Date : ' . now()->format('M d, Y'),
            'Payment Terms : 50% Downpayment, 50% Upon Receipt',
            'Due Date : ' . $data['dueDate'],
        ], $font, $bold, $yellow, $black, $gray, 18);

        $y = 486;
        $cols = [140, 470, 170, 100, 250];
        $headers = ['Date', 'Product', 'Unit Price', 'Qty', 'Amount'];
        $rowX = $x;
        foreach ($headers as $index => $header) {
            imagefilledrectangle($image, $rowX, $y, $rowX + $cols[$index], $y + 52, $yellow);
            imagerectangle($image, $rowX, $y, $rowX + $cols[$index], $y + 52, $gray);
            $this->drawText($image, $header, 16, $rowX + 12, $y + 16, $black, $bold);
            $rowX += $cols[$index];
        }

        $y += 52;
        foreach ($data['billingRows'] as $index => $row) {
            $rowH = max(64, $this->wrappedHeight($image, $row['details'], 15, $cols[1] - 22, $font) + 30);
            $fill = $index % 2 === 0 ? $white : $softGray;
            imagefilledrectangle($image, $x, $y, $x + array_sum($cols), $y + $rowH, $fill);

            $rowX = $x;
            foreach ($cols as $colW) {
                imagerectangle($image, $rowX, $y, $rowX + $colW, $y + $rowH, $gray);
                $rowX += $colW;
            }

            $this->drawText($image, $row['date'] ?? '', 13, $x + 70, $y + 22, $black, $font, 'center');
            $this->drawWrappedText($image, $row['details'], 15, $x + $cols[0] + 12, $y + 16, $cols[1] - 22, $black, $bold);
            $this->drawText($image, number_format($row['price'], 2), 14, $x + $cols[0] + $cols[1] + $cols[2] - 12, $y + 22, $black, $font, 'right');
            $this->drawText($image, (string) $row['quantity'], 14, $x + $cols[0] + $cols[1] + $cols[2] + 50, $y + 22, $black, $font, 'center');
            $this->drawText($image, number_format($row['amount'], 2), 14, $x + array_sum($cols) - 12, $y + 22, $black, $font, 'right');
            $y += $rowH;
        }

        $y += 24;
        imagefilledrectangle($image, $x, $y, $x + 330, $y + 128, $paleYellow);
        imagedashedline($image, $x, $y, $x + 330, $y, $gray);
        imagedashedline($image, $x, $y + 128, $x + 330, $y + 128, $gray);
        imagedashedline($image, $x, $y, $x, $y + 128, $gray);
        imagedashedline($image, $x + 330, $y, $x + 330, $y + 128, $gray);
        $this->drawText($image, 'Payment Instructions:', 14, $x + 16, $y + 14, $gold, $bold);
        $this->drawWrappedText($image, 'a.) For check payment, make it payable to: KRISTINE PANTASTICO', 13, $x + 16, $y + 42, 298, $black, $font);
        $this->drawWrappedText($image, 'b.) For GCash payment, send proof of payment for verification.', 13, $x + 16, $y + 88, 298, $black, $font);

        $totalX = $width - 34 - 360;
        $labels = [
            ['Subtotal', $data['totalAmount'], false],
            ['Deposit', $data['totalDeposit'], false],
            ['Balance', $data['totalBalance'], false],
            ['TOTAL AMOUNT DUE', $data['totalBalance'], true],
        ];
        foreach ($labels as $i => [$label, $amount, $highlight]) {
            $rowY = $y + ($i * 46);
            imagefilledrectangle($image, $totalX, $rowY, $totalX + 360, $rowY + 46, $highlight ? $yellow : ($i % 2 ? $white : $softGray));
            imagerectangle($image, $totalX, $rowY, $totalX + 360, $rowY + 46, $gray);
            $this->drawText($image, $label, 15, $totalX + 14, $rowY + 14, $black, $highlight ? $bold : $font);
            $this->drawText($image, 'PHP ' . number_format($amount, 2), 15, $totalX + 346, $rowY + 14, $black, $bold, 'right');
        }

        $y += 204;
        imagefilledrectangle($image, $x, $y, $width - 34, $y + 56, $yellow);
        imagerectangle($image, $x, $y, $width - 34, $y + 56, $gray);
        $this->drawText($image, 'NOTE: Must deposit 50% of the total amount of orders, and remaining 50% upon receipt of items.', 16, $x + 14, $y + 18, $black, $bold);

        $y += 105;
        $this->drawText($image, 'Thank you for choosing', 18, $x, $y, $black, $font);
        $this->drawText($image, $data['companyName'], 18, $x, $y + 32, $gold, $bold);
        $this->drawText($image, $data['authRep'] ?: 'Jelian Fernandez', 19, $width - 120, $y + 8, $black, $bold, 'right');
        imageline($image, $width - 430, $y + 42, $width - 120, $y + 42, $black);
        $this->drawText($image, 'Authorized Representative', 16, $width - 120, $y + 54, $gray, $font, 'right');

        ob_start();
        imagejpeg($image, null, 92);
        $jpg = ob_get_clean();
        imagedestroy($image);

        return $jpg;
    }

    private function drawInfoBox($image, int $x, int $y, int $w, int $h, string $title, array $lines, string $font, string $bold, int $yellow, int $black, int $gray, int $lineSize = 22): void
    {
        imagefilledrectangle($image, $x, $y, $x + $w, $y + $h, imagecolorallocate($image, 255, 255, 255));
        imagerectangle($image, $x, $y, $x + $w, $y + $h, $gray);
        imagefilledrectangle($image, $x, $y, $x + $w, $y + 44, $yellow);
        imagerectangle($image, $x, $y, $x + $w, $y + 44, $gray);
        $this->drawText($image, $title, 17, $x + 12, $y + 12, $black, $bold);

        $lineY = $y + 66;
        foreach (array_filter($lines) as $index => $line) {
            $this->drawWrappedText($image, $line, $lineSize, $x + 16, $lineY, $w - 32, $black, $index === 0 ? $bold : $font);
            $lineY += $lineSize + 16;
        }
    }

    private function drawLogo($image, ?string $path, int $x, int $y, int $maxW, int $maxH): void
    {
        if (! $path || ! is_file($path)) {
            return;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $logo = match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'gif' => @imagecreatefromgif($path),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => @imagecreatefrompng($path),
        };

        if (! $logo) {
            return;
        }

        $sourceW = imagesx($logo);
        $sourceH = imagesy($logo);
        $scale = min($maxW / $sourceW, $maxH / $sourceH);
        $targetW = (int) ($sourceW * $scale);
        $targetH = (int) ($sourceH * $scale);

        imagecopyresampled($image, $logo, $x, $y, 0, 0, $targetW, $targetH, $sourceW, $sourceH);
        imagedestroy($logo);
    }

    private function drawText($image, string $text, int $size, int|float $x, int $y, int $color, string $font, string $align = 'left'): void
    {
        $box = imagettfbbox($size, 0, $font, $text);
        $textW = $box[2] - $box[0];

        if ($align === 'right') {
            $x -= $textW;
        } elseif ($align === 'center') {
            $x -= $textW / 2;
        }

        imagettftext($image, $size, 0, (int) $x, $y + $size, $color, $font, $text);
    }

    private function drawWrappedText($image, string $text, int $size, int $x, int $y, int $maxWidth, int $color, string $font): int
    {
        $lineHeight = (int) round($size * 1.45);
        $startY = $y;

        foreach ($this->wrapText($image, $text, $size, $maxWidth, $font) as $line) {
            $this->drawText($image, $line, $size, $x, $y, $color, $font);
            $y += $lineHeight;
        }

        return $y - $startY;
    }

    private function wrappedHeight($image, string $text, int $size, int $maxWidth, string $font): int
    {
        return count($this->wrapText($image, $text, $size, $maxWidth, $font)) * (int) round($size * 1.45);
    }

    private function wrapText($image, string $text, int $size, int $maxWidth, string $font): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $candidate = trim($line . ' ' . $word);
            $box = imagettfbbox($size, 0, $font, $candidate);
            $candidateWidth = $box[2] - $box[0];

            if ($candidateWidth <= $maxWidth || $line === '') {
                $line = $candidate;
                continue;
            }

            $lines[] = $line;
            $line = $word;
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines ?: [''];
    }
}
