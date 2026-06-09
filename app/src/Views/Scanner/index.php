<?php
/**
 * @var array{ok:bool,level:string,message:string,ticket:?array<string,mixed>}|null $result
 * @var string $code
 */
use App\Middleware\AuthMiddleware;

$ticket = $result['ticket'] ?? null;
?>
<main class="scanner-page">
    <section class="container py-4">
        <div class="scanner-shell">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h1 class="scanner-title">Ticket scanner</h1>
                    <p class="text-muted mb-0">Scan a QR code or enter the ticket code manually.</p>
                </div>
                <a href="/" class="btn btn-outline-secondary btn-sm">Home</a>
            </div>

            <?php if ($result !== null): ?>
                <div class="alert alert-<?= htmlspecialchars($result['level']) ?> scanner-result" role="alert">
                    <strong><?= htmlspecialchars($result['message']) ?></strong>
                    <?php if ($ticket !== null): ?>
                        <div class="scanner-ticket-meta mt-2">
                            <div><?= htmlspecialchars($ticket['event_title'] ?? '') ?></div>
                            <div><?= htmlspecialchars($ticket['ticket_type_name'] ?? '') ?></div>
                            <div><?= htmlspecialchars($ticket['customer_name'] ?? '') ?> &middot; <?= htmlspecialchars($ticket['customer_email'] ?? '') ?></div>
                            <div>
                                <?= htmlspecialchars(!empty($ticket['starts_at']) ? date('j M Y, H:i', strtotime($ticket['starts_at'])) : '') ?>
                                <?php if (!empty($ticket['venue_name'])): ?>
                                    &middot; <?= htmlspecialchars($ticket['venue_name']) ?>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($ticket['scanned_at'])): ?>
                                <div>Scanned at <?= htmlspecialchars(date('j M Y, H:i:s', strtotime($ticket['scanned_at']))) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="scanner-camera mb-3">
                <div id="qr-reader"></div>
            </div>

            <form method="POST" action="/scanner/scan" id="scanner-form" class="card card-body scanner-form">
                <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken() ?>">
                <label class="form-label" for="ticket-code">Ticket code</label>
                <div class="input-group">
                    <input type="text" id="ticket-code" name="code" class="form-control"
                           value="<?= htmlspecialchars($code) ?>" autocomplete="off" autofocus>
                    <button class="btn btn-purple" type="submit">
                        <i class="bi bi-qr-code-scan"></i> Scan
                    </button>
                </div>
            </form>
        </div>
    </section>
</main>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const codeInput = document.getElementById('ticket-code');
const form = document.getElementById('scanner-form');

function submitCode(decodedText) {
    if (!decodedText || codeInput.value === decodedText) {
        return;
    }
    codeInput.value = decodedText;
    form.submit();
}

if (window.Html5QrcodeScanner) {
    const scanner = new Html5QrcodeScanner('qr-reader', {
        fps: 10,
        qrbox: { width: 240, height: 240 },
        rememberLastUsedCamera: true
    }, false);
    scanner.render(submitCode);
}
</script>
