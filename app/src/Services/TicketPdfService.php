<?php
namespace App\Services;

use App\Models\OrderModel;
use App\Services\Interfaces\ITicketPdfService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class TicketPdfService implements ITicketPdfService
{
    public function renderTickets(OrderModel $order, array $tickets): string
    {
        $blocks = '';
        foreach ($tickets as $t) {
            $qr = $this->qrDataUri((string) $t['qr_code']);
            $when = !empty($t['starts_at']) ? date('l j F Y, H:i', strtotime($t['starts_at'])) : '';
            $venue = !empty($t['venue_name']) ? htmlspecialchars($t['venue_name']) : '';

            $blocks .= '
                <div class="ticket">
                    <div class="ticket-info">
                        <div class="event">' . htmlspecialchars($t['event_title'] ?? '') . '</div>
                        <div class="type">' . htmlspecialchars($t['ticket_type_name'] ?? '') . '</div>
                        <div class="meta">' . htmlspecialchars($when) . ($venue ? ' &middot; ' . $venue : '') . '</div>
                        <div class="code">' . htmlspecialchars($t['qr_code']) . '</div>
                    </div>
                    <div class="ticket-qr"><img src="' . $qr . '" alt="QR"></div>
                </div>';
        }

        $html = '<html><head><style>
            body { font-family: DejaVu Sans, sans-serif; color:#222; }
            h1 { color:#361883; }
            .ticket { border:1px solid #ccc; border-radius:8px; padding:16px; margin-bottom:16px;
                      display:flex; }
            .ticket-info { width:70%; }
            .ticket-qr { width:30%; text-align:right; }
            .ticket-qr img { width:170px; height:170px; }
            .event { font-size:18px; font-weight:bold; }
            .type { color:#5c2379; font-weight:bold; margin-top:4px; }
            .meta { color:#555; margin-top:6px; font-size:12px; }
            .code { color:#888; margin-top:10px; font-size:10px; }
        </style></head><body>
            <h1>Haarlem Festival — Your tickets</h1>
            <p>Order ' . htmlspecialchars($order->invoice_number ?? (string)$order->id) . '. Present the QR code at the entrance.</p>
            ' . $blocks . '
        </body></html>';

        return $this->toPdf($html);
    }

    public function renderInvoice(OrderModel $order, string $customerName, string $customerEmail): string
    {
        $rows = '';
        foreach ($order->items as $item) {
            $rows .= '<tr>
                <td>' . htmlspecialchars(($item->ticket_type_name ?? 'Ticket') . ' — ' . ($item->event_title ?? '')) . '</td>
                <td style="text-align:center;">' . (int)$item->quantity . '</td>
                <td style="text-align:right;">&euro;' . number_format($item->unit_price, 2) . '</td>
                <td style="text-align:center;">' . rtrim(rtrim(number_format($item->vat_rate, 2), '0'), '.') . '%</td>
                <td style="text-align:right;">&euro;' . number_format($item->lineTotal(), 2) . '</td>
            </tr>';
        }

        $date = $order->paid_at ? date('j F Y', strtotime($order->paid_at)) : date('j F Y');

        $html = '<html><head><style>
            body { font-family: DejaVu Sans, sans-serif; color:#222; font-size:13px; }
            h1 { color:#361883; }
            table { width:100%; border-collapse:collapse; margin-top:16px; }
            th, td { border-bottom:1px solid #ddd; padding:8px; }
            th { text-align:left; background:#f4f2eb; }
            .totals { margin-top:16px; width:40%; float:right; }
            .totals td { border:none; padding:4px 8px; }
        </style></head><body>
            <h1>Invoice</h1>
            <p>
                <strong>Invoice number:</strong> ' . htmlspecialchars($order->invoice_number ?? '') . '<br>
                <strong>Date:</strong> ' . htmlspecialchars($date) . '<br>
                <strong>Billed to:</strong> ' . htmlspecialchars($customerName) . ' (' . htmlspecialchars($customerEmail) . ')
            </p>
            <table>
                <thead><tr><th>Item</th><th style="text-align:center;">Qty</th><th style="text-align:right;">Unit</th><th style="text-align:center;">VAT</th><th style="text-align:right;">Line total</th></tr></thead>
                <tbody>' . $rows . '</tbody>
            </table>
            <table class="totals">
                <tr><td>Subtotal (excl. VAT)</td><td style="text-align:right;">&euro;' . number_format($order->subtotal, 2) . '</td></tr>
                <tr><td>VAT</td><td style="text-align:right;">&euro;' . number_format($order->vat_total, 2) . '</td></tr>
                <tr><td><strong>Total paid</strong></td><td style="text-align:right;"><strong>&euro;' . number_format($order->total, 2) . '</strong></td></tr>
            </table>
        </body></html>';

        return $this->toPdf($html);
    }

    private function qrDataUri(string $data): string
    {
        $result = (new PngWriter())->write(new QrCode($data));
        return $result->getDataUri();
    }

    private function toPdf(string $html): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true); // allow data: URIs for the QR images
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();
        return $dompdf->output();
    }
}
