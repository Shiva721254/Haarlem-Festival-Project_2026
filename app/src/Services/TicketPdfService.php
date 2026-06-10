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
    public function renderTickets(OrderModel $order, array $tickets, string $customerName = ''): string
    {
        foreach ($tickets as &$t) {
            $t['qr_uri'] = $this->qrDataUri((string) $t['qr_code']);
        }
        unset($t);

        return $this->toPdf($this->renderTemplate('tickets', [
            'order'        => $order,
            'tickets'      => $tickets,
            'customerName' => $customerName,
        ]));
    }

    public function renderInvoice(OrderModel $order, string $customerName, string $customerEmail, ?string $customerPhone = null, ?string $customerAddress = null): string
    {
        return $this->toPdf($this->renderTemplate('invoice', [
            'order'           => $order,
            'customerName'    => $customerName,
            'customerEmail'   => $customerEmail,
            'customerPhone'   => $customerPhone,
            'customerAddress' => $customerAddress,
        ]));
    }

    /**
     * Render a PDF view template (app/src/Views/Pdf/{name}.php) to an HTML string.
     *
     * @param array<string,mixed> $data
     */
    private function renderTemplate(string $name, array $data): string
    {
        extract($data, EXTR_OVERWRITE);
        ob_start();
        include __DIR__ . '/../Views/Pdf/' . $name . '.php';
        return (string) ob_get_clean();
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
