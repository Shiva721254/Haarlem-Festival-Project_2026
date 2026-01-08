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
    <header class="text-white pb-2 shadow-sm" style="background-color: #5c2379ff;">
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
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark" style="font-size: 0.6rem;">
                        0
                    </span>
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <nav class="nav">
                    <a class="nav-link text-white fw-semibold ps-0 pe-4" href="/products/computers">Computers</a>
                    <a class="nav-link text-white fw-semibold pe-4" href="/products/entertainment">Home Entertainment</a>
                    <a class="nav-link text-white fw-semibold pe-4" href="/products/wearables">Wearables</a>
                    <a class="nav-link text-white fw-semibold pe-4" href="/products/kitchen">Kitchen</a> |
                    <a class="nav-link text-white fw-semibold pe-4" href="/users">USERS</a> |
                    <a class="nav-link text-white fw-semibold pe-4" href="/products">PRODUCTS</a>
                </nav>
            </div>
        </div>
    </div>
    </header>