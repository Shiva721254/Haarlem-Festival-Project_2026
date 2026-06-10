<?php
/**
 * PDF invoice (rendered to a string and fed to dompdf).
 *
 * @var \App\Models\OrderModel $order
 * @var string  $customerName
 * @var string  $customerEmail
 * @var ?string $customerPhone
 * @var ?string $customerAddress
 */
$accent = '#361883';
// Seller (festival) details; edit these placeholders for production.
$seller = [
    'name'    => 'The Festival - Haarlem',
    'address' => 'Zijlweg 7, 2013 DK Haarlem, Netherlands',
    'vat'     => 'NL0012 3456 B01',
    'coc'     => '34 56 78 90',
    'tel'     => '+31 23 512 3456',
    'email'   => 'info@thefestival-haarlem.nl',
    'iban'    => 'NL12 INGB 0001 2345 67',
];
$date = $order->paid_at ? date('j F Y', strtotime($order->paid_at)) : date('j F Y');
$invoiceNo = htmlspecialchars($order->invoice_number ?? (string) $order->id);

$box = static function (string $label, string $value) use ($accent): string {
    return '<td style="width:33%; padding:0 6px 0 0; vertical-align:top;">
        <div style="border:1px solid #e2ddd0; border-radius:4px; padding:8px 10px;">
            <div style="color:#9a9485; font-size:10px; text-transform:uppercase; letter-spacing:.5px;">' . $label . '</div>
            <div style="color:' . $accent . '; font-weight:bold; font-size:13px;">' . $value . '</div>
        </div></td>';
};
?>
<html><head><style>
    body { font-family: DejaVu Sans, sans-serif; color:#2b2b2b; font-size:12px; }
</style></head><body>

<table style="width:100%; margin-bottom:22px;">
    <tr>
        <td style="width:38%; vertical-align:top;">
            <div style="font-size:30px; font-weight:bold; color:<?= $accent ?>;">Invoice</div>
            <div style="color:#777; margin-top:2px;">No. <?= $invoiceNo ?></div>
        </td>
        <td style="width:37%; vertical-align:top; font-size:11px; color:#555; line-height:1.5;">
            <strong><?= htmlspecialchars($seller['name']) ?></strong><br>
            <?= htmlspecialchars($seller['address']) ?><br>
            VAT-ID: <?= htmlspecialchars($seller['vat']) ?> &nbsp; CoC: <?= htmlspecialchars($seller['coc']) ?><br>
            Tel: <?= htmlspecialchars($seller['tel']) ?><br>
            <?= htmlspecialchars($seller['email']) ?>
        </td>
        <td style="width:25%; vertical-align:top; text-align:right;">
            <div style="border:1px solid #ddd; border-radius:6px; color:#b8b3a4; padding:20px 8px; text-align:center; font-weight:bold; letter-spacing:1px;">THE&nbsp;FESTIVAL</div>
        </td>
    </tr>
</table>

<div style="margin-bottom:14px;">
    <div style="color:#9a9485; font-size:11px; text-transform:uppercase; letter-spacing:.5px;">Invoice to</div>
    <div style="font-size:13px; line-height:1.5;">
        <strong><?= htmlspecialchars($customerName) ?></strong>
        <?php if (!empty($customerAddress)): ?><br><?= htmlspecialchars($customerAddress) ?><?php endif; ?>
        <?php if (!empty($customerPhone)): ?><br>Tel: <?= htmlspecialchars($customerPhone) ?><?php endif; ?>
        <br><?= htmlspecialchars($customerEmail) ?>
    </div>
</div>

<table style="width:100%; margin-bottom:8px;"><tr>
    <?= $box('Invoice number', $invoiceNo) ?>
    <?= $box('Invoice date', htmlspecialchars($date)) ?>
    <?= $box('Payment date', htmlspecialchars($date)) ?>
</tr></table>
<table style="width:100%; margin-bottom:20px;"><tr>
    <?= $box('Amount paid', '&euro;' . number_format($order->total, 2)) ?>
    <?= $box('Payment', 'Paid online') ?>
    <?= $box('IBAN', htmlspecialchars($seller['iban'])) ?>
</tr></table>

<table style="width:100%; border-collapse:collapse;">
    <thead>
        <tr style="background:<?= $accent ?>; color:#fff;">
            <th style="text-align:left; padding:9px;">Description</th>
            <th style="text-align:center; padding:9px;">Qty</th>
            <th style="text-align:right; padding:9px;">Unit price</th>
            <th style="text-align:center; padding:9px;">VAT</th>
            <th style="text-align:right; padding:9px;">Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 0; foreach ($order->items as $item): $bg = (++$i % 2 === 0) ? '#faf9f6' : '#ffffff'; ?>
            <tr style="background:<?= $bg ?>;">
                <td style="padding:8px;"><?= htmlspecialchars(($item->ticket_type_name ?? 'Ticket') . ' — ' . ($item->event_title ?? '')) ?></td>
                <td style="padding:8px; text-align:center;"><?= (int) $item->quantity ?></td>
                <td style="padding:8px; text-align:right;">&euro;<?= number_format($item->unit_price, 2) ?></td>
                <td style="padding:8px; text-align:center;"><?= rtrim(rtrim(number_format($item->vat_rate, 2), '0'), '.') ?>%</td>
                <td style="padding:8px; text-align:right;">&euro;<?= number_format($item->lineTotal(), 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<table style="width:46%; float:right; border-collapse:collapse; margin-top:14px;">
    <tr><td style="padding:5px 10px; color:#555;">Subtotal (excl. VAT)</td><td style="padding:5px 10px; text-align:right;">&euro;<?= number_format($order->subtotal, 2) ?></td></tr>
    <tr><td style="padding:5px 10px; color:#555;">VAT</td><td style="padding:5px 10px; text-align:right;">&euro;<?= number_format($order->vat_total, 2) ?></td></tr>
    <tr style="background:<?= $accent ?>; color:#fff;"><td style="padding:9px 10px; font-weight:bold;">Total paid</td><td style="padding:9px 10px; text-align:right; font-weight:bold;">&euro;<?= number_format($order->total, 2) ?></td></tr>
</table>

<div style="clear:both;"></div>
<div style="margin-top:48px; border-top:1px solid #eee; padding-top:10px; font-size:10px; color:#888; line-height:1.5;">
    Paid online &mdash; thank you for your order. This invoice serves as proof of payment (reference <?= $invoiceNo ?>).
    <?= htmlspecialchars($seller['name']) ?>, <?= htmlspecialchars($seller['address']) ?>.
</div>

</body></html>
