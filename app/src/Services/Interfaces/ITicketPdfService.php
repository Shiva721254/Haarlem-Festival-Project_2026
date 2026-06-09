<?php
namespace App\Services\Interfaces;

use App\Models\OrderModel;

interface ITicketPdfService
{
    /**
     * Render the issued tickets (with QR codes) as a single PDF.
     *
     * @param array<int,array<string,mixed>> $tickets
     */
    public function renderTickets(OrderModel $order, array $tickets): string;

    /**
     * Render an invoice for the order as a PDF.
     */
    public function renderInvoice(OrderModel $order, string $customerName, string $customerEmail): string;
}
