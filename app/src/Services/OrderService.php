<?php
namespace App\Services;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Repositories\OrderRepository;
use App\Repositories\TicketTypeRepository;
use App\Repositories\UserRepository;
use App\Repositories\Interfaces\IOrderRepository;
use App\Repositories\Interfaces\ITicketTypeRepository;
use App\Repositories\Interfaces\IUserRepository;
use App\Services\Interfaces\IOrderService;
use App\Services\Interfaces\ICartService;
use App\Services\Interfaces\ITicketPdfService;

class OrderService implements IOrderService
{
    private IOrderRepository $orderRepo;
    private ITicketTypeRepository $ticketRepo;
    private IUserRepository $userRepo;
    private ICartService $cartService;
    private ITicketPdfService $pdfService;
    private MailService $mailService;

    public function __construct()
    {
        $this->orderRepo = new OrderRepository();
        $this->ticketRepo = new TicketTypeRepository();
        $this->userRepo = new UserRepository();
        $this->cartService = new CartService();
        $this->pdfService = new TicketPdfService();
        $this->mailService = new MailService();
    }

    public function createFromCart(int $userId): array
    {
        $items = $this->cartService->getItems();
        if (empty($items)) {
            return ['ok' => false, 'order' => null, 'message' => 'Your cart is empty.'];
        }

        // Re-check availability at order time (stock may have changed).
        foreach ($items as $item) {
            $ticket = $this->ticketRepo->getById($item->ticket_type_id);
            if ($ticket === null || !$ticket->is_active) {
                return ['ok' => false, 'order' => null, 'message' => "“{$item->ticket_type_name}” is no longer available."];
            }
            if ($item->quantity > $ticket->available()) {
                return ['ok' => false, 'order' => null, 'message' => "Only {$ticket->available()} left for “{$ticket->name}”."];
            }
        }

        $totals = $this->cartService->totals();

        $order = new OrderModel();
        $order->user_id = $userId;
        $order->status = 'pending';
        $order->subtotal = $totals['subtotal'];
        $order->vat_total = $totals['vat'];
        $order->total = $totals['total'];

        foreach ($items as $item) {
            $line = new OrderItemModel();
            $line->ticket_type_id = $item->ticket_type_id;
            $line->quantity = $item->quantity;
            $line->unit_price = $item->price;
            $line->vat_rate = $item->vat_rate;
            $order->items[] = $line;
        }

        $order->id = $this->orderRepo->create($order);
        return ['ok' => true, 'order' => $order, 'message' => 'Order created.'];
    }

    public function getById(int $id): ?OrderModel
    {
        return $this->orderRepo->getById($id);
    }

    public function getByUser(int $userId): array
    {
        return $this->orderRepo->getByUser($userId);
    }

    public function getAllForAdmin(?string $status = null): array
    {
        return $this->orderRepo->getAllForAdmin($status);
    }

    public function getExportRows(?string $status = null): array
    {
        return $this->orderRepo->getExportRows($status);
    }

    public function setPaymentIntent(int $orderId, string $paymentIntentId): void
    {
        $this->orderRepo->setPaymentIntent($orderId, $paymentIntentId);
    }

    public function fulfill(OrderModel $order): void
    {
        // Guard against double-fulfilment (e.g. refresh of the success page).
        if ($order->isPaid()) {
            return;
        }

        $this->orderRepo->markPaid($order->id, $this->invoiceNumber($order->id));
        $this->orderRepo->issueTickets($order->id);

        foreach ($order->items as $item) {
            $this->ticketRepo->incrementSold($item->ticket_type_id, $item->quantity);
        }

        $this->cartService->clear();
        $this->sendConfirmation($order->id);
    }

    private function invoiceNumber(int $orderId): string
    {
        return 'HF-' . date('Y') . '-' . str_pad((string)$orderId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Email the (now paid) order's tickets + invoice as PDF attachments.
     * Best-effort: a mail/PDF failure must not break order fulfilment.
     */
    private function sendConfirmation(int $orderId): void
    {
        try {
            $order = $this->orderRepo->getById($orderId); // reloaded: paid, with invoice + items
            if ($order === null) {
                return;
            }
            $user = $this->userRepo->getById($order->user_id);
            if ($user === null) {
                return;
            }

            $tickets = $this->orderRepo->getIssuedTickets($orderId);
            $name = trim($user->FirstName . ' ' . $user->LastName);

            $ticketsPdf = $this->pdfService->renderTickets($order, $tickets);
            $invoicePdf = $this->pdfService->renderInvoice($order, $name, $user->Email);

            $body = '<h2>Thank you for your order!</h2>'
                . '<p>Your tickets and invoice are attached (invoice '
                . htmlspecialchars($order->invoice_number ?? '') . ').</p>'
                . '<p>Present the QR code on each ticket at the entrance.</p>';

            $this->mailService->sendWithAttachments($user->Email, 'Your Haarlem Festival tickets', $body, [
                ['name' => 'tickets.pdf', 'content' => $ticketsPdf, 'type' => 'application/pdf'],
                ['name' => 'invoice-' . ($order->invoice_number ?? $orderId) . '.pdf', 'content' => $invoicePdf, 'type' => 'application/pdf'],
            ]);
        } catch (\Throwable $e) {
            // swallow — order is already fulfilled; email is best-effort
        }
    }
}
