<?php
/**
 * Builds the application container: maps every service interface to its
 * concrete implementation so controllers can depend on abstractions and the
 * router can autowire them. Register new services here.
 */

use App\Framework\Container;
use App\Services;
use App\Services\Interfaces as SI;
use App\Repositories;
use App\Repositories\Interfaces as RI;

$container = new Container();

// Service interface -> implementation.
$serviceBindings = [
    SI\IAccountService::class    => Services\AccountService::class,
    SI\IAdminService::class      => Services\AdminService::class,
    SI\IArtistService::class     => Services\ArtistService::class,
    SI\ICartService::class       => Services\CartService::class,
    SI\IContentService::class    => Services\ContentService::class,
    SI\IEventService::class      => Services\EventService::class,
    SI\IMailService::class       => Services\MailService::class,
    SI\IOrderService::class      => Services\OrderService::class,
    SI\IPaymentService::class    => Services\PaymentService::class,
    SI\IProgramService::class    => Services\ProgramService::class,
    SI\IRestaurantService::class => Services\RestaurantService::class,
    SI\ITicketPdfService::class  => Services\TicketPdfService::class,
    SI\ITicketScanService::class => Services\TicketScanService::class,
    SI\ITicketTypeService::class => Services\TicketTypeService::class,
    SI\IUserService::class       => Services\UserService::class,
    SI\IVenueService::class      => Services\VenueService::class,
];

// Repository interface -> implementation.
$repositoryBindings = [
    RI\IAccountRepository::class    => Repositories\AccountRepository::class,
    RI\IAdminRepository::class      => Repositories\AdminRepository::class,
    RI\IArtistRepository::class     => Repositories\ArtistRepository::class,
    RI\ICartRepository::class       => Repositories\CartRepository::class,
    RI\IContentRepository::class    => Repositories\ContentRepository::class,
    RI\IEventRepository::class      => Repositories\EventRepository::class,
    RI\IOrderRepository::class      => Repositories\OrderRepository::class,
    RI\IProgramRepository::class    => Repositories\ProgramRepository::class,
    RI\IRestaurantRepository::class => Repositories\RestaurantRepository::class,
    RI\ITicketRepository::class     => Repositories\TicketRepository::class,
    RI\ITicketTypeRepository::class => Repositories\TicketTypeRepository::class,
    RI\IUserRepository::class       => Repositories\UserRepository::class,
    RI\IVenueRepository::class      => Repositories\VenueRepository::class,
];

foreach ($serviceBindings + $repositoryBindings as $interface => $concrete) {
    $container->bind($interface, fn(Container $c) => $c->make($concrete));
}

// Make the configured container available to the shared view layer.
Container::setInstance($container);

return $container;
