<?php
namespace App\Services;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\UserModel;
use App\Repositories\Interfaces\IOrderRepository;
use App\Repositories\Interfaces\ITicketTypeRepository;
use App\Repositories\Interfaces\IUserRepository;
use App\Services\Interfaces\IOrderService;
use App\Services\Interfaces\ICartService;
use App\Services\Interfaces\ITicketPdfService;
use App\Services\Interfaces\IMailService;
use App\Enums\OrderStatus;

class OrderService implements IOrderService
{
    private const PAY_LATER_SECONDS  = 24 * 60 * 60;
    private const INVOICE_PREFIX     = 'HF-';
    private const INVOICE_PAD_LENGTH = 6;
    private const ADMIN_STATUSES = [
        OrderStatus::Pending->value,
        OrderStatus::Paid->value,
        OrderStatus::Failed->value,
        OrderStatus::Cancelled->value,
    ];
    private const EXPORT_COLUMNS = [
        'id' => 'Order ID',
        'invoice_number' => 'Invoice number',
        'status' => 'Status',
        'customer_name' => 'Customer name',
        'customer_email' => 'Customer email',
        'item_count' => 'Items',
        'subtotal' => 'Subtotal',
        'vat_total' => 'VAT',
        'total' => 'Total',
        'created_at' => 'Created at',
        'paid_at' => 'Paid at',
        'payment_intent_id' => 'Payment reference',
    ];

    private IOrderRepository $orderRepo;
    private ITicketTypeRepository $ticketRepo;
    private IUserRepository $userRepo;
    private ICartService $cartService;
    private ITicketPdfService $pdfService;
    private IMailService $mailService;

    public function __construct(
        IOrderRepository $orderRepo,
        ITicketTypeRepository $ticketRepo,
        IUserRepository $userRepo,
        ICartService $cartService,
        ITicketPdfService $pdfService,
        IMailService $mailService
    ) {
        $this->orderRepo = $orderRepo;
        $this->ticketRepo = $ticketRepo;
        $this->userRepo = $userRepo;
        $this->cartService = $cartService;
        $this->pdfService = $pdfService;
        $this->mailService = $mailService;
    }

    public function createFromCart(int $userId, string $sessionId): array
    {
        $items = $this->cartService->getItems($userId, $sessionId);
        $error = $this->orderBlocker($items);
        if ($error !== null) {
            return ['ok' => false, 'order' => null, 'message' => $error];
        }
        $order = $this->buildOrderFromCart($userId, $items, $this->cartService->totals($userId, $sessionId));
        $order->id = $this->orderRepo->create($order);
        return ['ok' => true, 'order' => $order, 'message' => 'Order created.'];
    }

    /** Reason the cart cannot be ordered (empty or out of stock), or null. */
    private function orderBlocker(array $items): ?string
    {
        if (empty($items)) {
            return 'Your cart is empty.';
        }
        return $this->unavailableMessage($items);
    }

    /**
     * Re-check availability (stock may have changed). Returns the first problem
     * message, or null when every line is still purchasable. Shared by ordering
     * and pay-later validation.
     */
    private function unavailableMessage(array $items): ?string
    {
        foreach ($items as $item) {
            $ticket = $this->ticketRepo->getById($item->ticket_type_id);
            if ($ticket === null || !$ticket->is_active) {
                return "\"{$item->ticket_type_name}\" is no longer available.";
            }
            if ($item->quantity > $ticket->available()) {
                return "Only {$ticket->available()} left for \"{$ticket->name}\".";
            }
        }
        return null;
    }

    /** Build a pending order (with its priced lines) from the current cart. */
    private function buildOrderFromCart(int $userId, array $items, array $totals): OrderModel
    {
        $order = new OrderModel();
        $order->user_id = $userId;
        $order->status = OrderStatus::Pending;
        $order->subtotal = $totals['subtotal'];
        $order->vat_total = $totals['vat'];
        $order->total = $totals['total'];
        $order->pay_later_until = date('Y-m-d H:i:s', time() + self::PAY_LATER_SECONDS);
        $order->items = array_map(fn($item) => $this->toOrderItem($item), $items);
        return $order;
    }

    private function toOrderItem(object $item): OrderItemModel
    {
        $line = new OrderItemModel();
        $line->ticket_type_id = $item->ticket_type_id;
        $line->quantity = $item->quantity;
        $line->unit_price = $item->effectivePrice();
        $line->vat_rate = $item->vat_rate;
        $line->special_requests = $item->special_requests;
        return $line;
    }

    public function getById(int $id): ?OrderModel
    {
        return $this->orderRepo->getById($id);
    }

    public function getByUser(int $userId): array
    {
        return $this->orderRepo->getByUser($userId);
    }

    public function getPendingPayLaterForUser(int $userId): array
    {
        return array_filter($this->getByUser($userId), static fn($order) => $order->canPayLater());
    }

    public function getByIdForUser(int $orderId, int $userId): ?OrderModel
    {
        return $this->orderRepo->getByIdForUser($orderId, $userId);
    }

    /**
     * Validate a pending order before sending the customer back to Stripe.
     *
     * @return array{ok:bool,message:string}
     */
    public function canStartPayment(OrderModel $order): array
    {
        if (!$order->canPayLater()) {
            return ['ok' => false, 'message' => 'This order can no longer be paid. Please create a new order.'];
        }
        $error = $this->unavailableMessage($order->items);
        if ($error !== null) {
            return ['ok' => false, 'message' => $error];
        }
        return ['ok' => true, 'message' => 'Order can be paid.'];
    }

    public function getAllForAdmin(?string $status = null): array
    {
        return $this->orderRepo->getAllForAdmin($status);
    }

    public function getExportRows(?string $status = null): array
    {
        return $this->orderRepo->getExportRows($status);
    }

    public function adminStatuses(): array
    {
        return self::ADMIN_STATUSES;
    }

    public function exportColumns(): array
    {
        return self::EXPORT_COLUMNS;
    }

    public function normalizeAdminStatus(?string $status): ?string
    {
        return in_array($status, self::ADMIN_STATUSES, true) ? $status : null;
    }

    public function resolveExportColumns(array $selected): array
    {
        $columns = array_values(array_filter($selected, static fn($key) => isset(self::EXPORT_COLUMNS[$key])));
        return empty($columns) ? array_keys(self::EXPORT_COLUMNS) : $columns;
    }

    public function buildExport(?string $status, array $columns, string $format): array
    {
        $columns = $this->resolveExportColumns($columns);
        $headers = array_map(static fn(string $key) => self::EXPORT_COLUMNS[$key], $columns);
        $rows = $this->getExportRows($status);
        $base = 'orders-' . date('Ymd-His');
        return $format === 'xlsx'
            ? $this->xlsxExport($headers, $rows, $columns, $base)
            : $this->csvExport($headers, $rows, $columns, $base);
    }

    private function xlsxExport(array $headers, array $rows, array $columns, string $base): array
    {
        $data = array_map(
            static fn(array $row) => array_map(static fn(string $key) => $row[$key] ?? '', $columns),
            $rows
        );
        return [
            'filename' => $base . '.xlsx',
            'contentType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'body' => \App\Framework\XlsxWriter::build($headers, $data, 'Orders'),
        ];
    }

    private function csvExport(array $headers, array $rows, array $columns, string $base): array
    {
        $out = fopen('php://temp', 'r+');
        fputcsv($out, $headers, ',', '"', '');
        foreach ($rows as $row) {
            fputcsv($out, array_map(static fn(string $key) => $row[$key] ?? '', $columns), ',', '"', '');
        }
        rewind($out);
        $body = stream_get_contents($out) ?: '';
        fclose($out);
        return ['filename' => $base . '.csv', 'contentType' => 'text/csv; charset=utf-8', 'body' => $body];
    }

    public function setPaymentIntent(int $orderId, string $paymentIntentId): void
    {
        $this->orderRepo->setPaymentIntent($orderId, $paymentIntentId);
    }

    public function fulfill(OrderModel $order, ?string $sessionId = null): void
    {
        if ($order->isPaid()) {
            return; // guard against double-fulfilment (e.g. refresh of success page)
        }
        $this->orderRepo->markPaid($order->id, $this->invoiceNumber($order->id));
        $this->orderRepo->issueTickets($this->ticketCodesFor($order->id));
        $this->incrementSold($order);
        $this->clearCartForReturn($order, $sessionId);
        $this->sendConfirmation($order->id);
    }

    private function ticketCodesFor(int $orderId): array
    {
        $codes = [];
        foreach ($this->orderRepo->getItemQuantities($orderId) as $itemId => $quantity) {
            $codes[$itemId] = $this->randomTicketCodes($quantity);
        }
        return $codes;
    }

    private function randomTicketCodes(int $quantity): array
    {
        return array_map(fn() => bin2hex(random_bytes(16)), range(1, $quantity));
    }

    private function clearCartForReturn(OrderModel $order, ?string $sessionId): void
    {
        if ($sessionId !== null) {
            $this->cartService->clear($order->user_id, $sessionId);
        }
    }

    public function cancelMessage(?OrderModel $order): string
    {
        if ($order !== null && $order->canPayLater()) {
            return 'Payment cancelled. You can still pay this order until ' . $this->deadline($order) . '.';
        }
        return 'Payment cancelled.';
    }

    private function deadline(OrderModel $order): string
    {
        return date('j M Y, H:i', strtotime($order->pay_later_until));
    }

    public function fulfillPaidCheckout(array $info): void
    {
        if (!$info['paid'] || empty($info['order_id'])) {
            return;
        }
        $order = $this->getById((int)$info['order_id']);
        if ($order === null) {
            return;
        }
        $this->recordPaymentIntent($order, $info['payment_intent']);
        $this->fulfill($order);
    }

    private function recordPaymentIntent(OrderModel $order, ?string $paymentIntent): void
    {
        if ($paymentIntent !== null) {
            $this->setPaymentIntent($order->id, $paymentIntent);
        }
    }

    private function incrementSold(OrderModel $order): void
    {
        foreach ($order->items as $item) {
            $this->ticketRepo->incrementSold($item->ticket_type_id, $item->quantity);
        }
    }

    private function invoiceNumber(int $orderId): string
    {
        return self::INVOICE_PREFIX . date('Y') . '-' . str_pad((string)$orderId, self::INVOICE_PAD_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Email the (now paid) order's tickets + invoice as PDF attachments.
     * Best-effort: a mail/PDF failure must not break order fulfilment.
     */
    private function sendConfirmation(int $orderId): void
    {
        try {
            $order = $this->orderRepo->getById($orderId); // reloaded: paid, with invoice + items
            $user = $order ? $this->userRepo->getById($order->user_id) : null;
            if ($order === null || $user === null) {
                return;
            }
            $this->mailConfirmation($order, $user);
        } catch (\Throwable $e) {
            // swallow — order is already fulfilled; email is best-effort
        }
    }

    private function mailConfirmation(OrderModel $order, UserModel $user): void
    {
        $name = trim($user->FirstName . ' ' . $user->LastName);
        $attachments = $this->confirmationAttachments($order, $user, $name);
        $subject = 'Your Haarlem Festival tickets';
        $this->mailService->sendWithAttachments($user->Email, $subject, $this->confirmationBody($order), $attachments);
    }

    /** @return array<int,array{name:string,content:string,type:string}> */
    private function confirmationAttachments(OrderModel $order, UserModel $user, string $name): array
    {
        $tickets = $this->orderRepo->getIssuedTickets($order->id);
        $ticketsPdf = $this->pdfService->renderTickets($order, $tickets, $name);
        $invoicePdf = $this->pdfService->renderInvoice($order, $name, $user->Email, $user->phone ?? null, $user->address ?? null);
        return [
            ['name' => 'tickets.pdf', 'content' => $ticketsPdf, 'type' => 'application/pdf'],
            ['name' => 'invoice-' . ($order->invoice_number ?? $order->id) . '.pdf', 'content' => $invoicePdf, 'type' => 'application/pdf'],
        ];
    }

    private function confirmationBody(OrderModel $order): string
    {
        return '<h2>Thank you for your order!</h2>'
            . '<p>Your tickets and invoice are attached (invoice '
            . htmlspecialchars($order->invoice_number ?? '') . ').</p>'
            . '<p>Present the QR code on each ticket at the entrance.</p>';
    }
}
