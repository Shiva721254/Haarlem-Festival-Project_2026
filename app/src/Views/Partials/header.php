<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'User Management System' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <header class="sticky-top text-white pb-2 shadow-sm" style="background-color: #5c2379ff;">
        <div class="container">
            <div class="row align-items-center py-3">
                <div class="col-md-3">
                    <h1 class="h3 mb-0 fw-bold text-uppercase">Webstore</h1>
                </div>

                <div class="col-md-6">
                <form action="/products" method="GET">
                        <div class="input-group">
                            <input type="text" name="q" class="form-control border-0" 
                                placeholder="What are you looking for?" 
                                value="<?= htmlspecialchars($vm->searchTerm ?? '') ?>"> <button class="btn btn-light" type="submit">
                                <i class="bi bi-search text-danger"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="col-md-3 d-flex justify-content-end gap-3">
                    <?php if (isset($_SESSION['UserId'])): ?>
                        <a href="/user/<?= $_SESSION['UserId'] ?>" class="btn btn-outline-light rounded-circle border-0 p-2" title="Account Information">
                            <i class="bi bi-person-circle fs-4"></i>
                            <small class="d-block" style="font-size: 0.6rem;"><?= htmlspecialchars($_SESSION['FirstName'] ?? 'User') ?></small>
                        </a>
                    <?php else: ?>
                        <a href="/showLogin" class="btn btn-outline-light rounded-circle border-0 p-2" title="Login">
                            <i class="bi bi-box-arrow-in-right fs-4"></i>
                        </a>
                    <?php endif; ?>
                    <a href="/shoppingCart" class="btn btn-outline-light rounded-circle border-0 p-2 position-relative" title="Shopping Cart">
                        <i class="bi bi-cart3 fs-4"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark" style="font-size: 0.6rem;"></span>
                    </a>
                </div>
            </div>

            <nav class="nav">
                <?php 
                    // Defensive checks to see if the current $vm has the properties we need
                    $activeCat = (isset($vm) && property_exists($vm, 'category')) ? $vm->category : null; 
                    $searchTerm = (isset($vm) && property_exists($vm, 'searchTerm')) ? $vm->searchTerm : null;
                    
                    $linkClass = "nav-link text-white fw-semibold pe-4";
                    
                    // Helper to build URLs safely without triggering null-pointer warnings
                    $buildUrl = function($cat = null) use ($searchTerm) {
                        $params = [];
                        if ($cat) $params['category'] = $cat;
                        if ($searchTerm) $params['q'] = $searchTerm;
                        return '/products' . (!empty($params) ? '?' . http_build_query($params) : '');
                    };
                ?>
                
                <a class="<?= $linkClass ?> <?= $activeCat === 'computers' ? 'text-decoration-underline' : '' ?>" 
                href="<?= $buildUrl('computers') ?>">Computers</a>
                
                <a class="<?= $linkClass ?> <?= $activeCat === 'home_entertainment' ? 'text-decoration-underline' : '' ?>" 
                href="<?= $buildUrl('home_entertainment') ?>">Home Entertainment</a>

                <a class="<?= $linkClass ?> <?= $activeCat === 'wearables' ? 'text-decoration-underline' : '' ?>" 
                href="<?= $buildUrl('wearables') ?>">Wearables</a>

                <a class="<?= $linkClass ?> <?= $activeCat === 'appliances' ? 'text-decoration-underline' : '' ?>" 
                href="<?= $buildUrl('appliances') ?>">Appliances</a>
                
                <span class="text-white-50 py-2">|</span>
                
                <a class="<?= $linkClass ?> <?= (empty($activeCat) && empty($searchTerm)) ? 'text-decoration-underline' : '' ?>" 
                href="/products">ALL PRODUCTS</a>
            </nav>
        </div>
    </header>