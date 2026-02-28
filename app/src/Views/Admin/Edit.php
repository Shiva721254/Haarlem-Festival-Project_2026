<?php
declare(strict_types=1);

use App\Middleware\AuthMiddleware;

function e($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function decodeJson(string $raw): array
{
    $d = json_decode($raw, true);
    return is_array($d) ? $d : [];
}

function flattenJson(array $data, string $prefix = ''): array
{
    $out = [];
    foreach ($data as $k => $v) {
        $key = $prefix === '' ? (string)$k : $prefix . '.' . (string)$k;
        if (is_array($v)) {
            $out += flattenJson($v, $key);
        } else {
            $out[$key] = $v;
        }
    }
    return $out;
}

$csrf = AuthMiddleware::generateCsrfToken();

$groups = [];
foreach ($blocks as $b) {
    $groups[$b->Section][] = $b;
}

$saveUrl = '/admin/save';

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit page: <?= e($page) ?></title>
    <link rel="stylesheet" href="/assets/css/admin-edit.css">
</head>
<body>

<form method="POST" action="<?= e($saveUrl) ?>">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="page" value="<?= e($page) ?>">

    <div class="topbar">
        <div class="topbarInner">
            <div class="titleWrap">
                <h1 class="h1">Edit page: <?= e($page) ?></h1>
                <div class="metaLine">
                    <span class="chip"><span class="dot"></span><?= e($page) ?></span>
                    <span class="chip"><?= count($blocks) ?> blocks</span>
                </div>
            </div>
            <div class="actions">
                <button class="btn" type="button" onclick="window.location.reload()">Refresh</button>
                <button class="btn btnPrimary" type="submit">Save</button>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="layout">
            <aside class="sidebar">
                <div class="sidebarHead">Sections</div>
                <div class="nav">
                    <?php foreach (array_keys($groups) as $sec): ?>
                        <a href="#sec-<?= e($sec) ?>"><?= e($sec) ?></a>
                    <?php endforeach; ?>
                </div>
            </aside>

            <main class="main">
                <?php foreach ($groups as $sectionName => $items): ?>
                    <section class="section" id="sec-<?= e($sectionName) ?>">
                        <div class="sectionHead">
                            <div class="sectionTitle"><?= e($sectionName) ?></div>
                            <div class="sectionCount"><?= count($items) ?> items</div>
                        </div>

                        <div class="sectionBody">
                            <?php foreach ($items as $b): ?>
                                <div class="row">
                                    <?php if ($b->Type === 'text'): ?>
                                        <div class="fieldLabel"><?= e($b->KeyName) ?></div>
                                        <input class="input" type="text" name="value[<?= (int)$b->Id ?>]" value="<?= e($b->Value) ?>">
                                    <?php elseif ($b->Type === 'json'): ?>
                                        <?php $flat = flattenJson(decodeJson($b->Value)); ?>
                                        <?php foreach ($flat as $k => $v): ?>
                                            <div class="pairs">
                                                <div class="keyBox"><?= e(str_replace('.', ' → ', (string)$k)) ?></div>
                                                <input class="input" type="text" name="json[<?= (int)$b->Id ?>][<?= e((string)$k) ?>]" value="<?= e($v) ?>">
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="help">Edits values only (no JSON shown).</div>
                                    <?php else: ?>
                                        <div class="fieldLabel"><?= e($b->KeyName) ?></div>
                                        <textarea class="textarea" name="value[<?= (int)$b->Id ?>]"><?= e($b->Value) ?></textarea>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </main>
        </div>
    </div>

    <div class="footerSave">
        <div class="footerSaveInner">
            <div class="footerHint">Save before leaving.</div>
            <button class="btn btnPrimary" type="submit">Save</button>
        </div>
    </div>
</form>

</body>
</html>
