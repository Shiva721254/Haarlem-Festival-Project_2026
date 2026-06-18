<?php
namespace App\Enums;

enum TicketStatus: string
{
    case Valid   = 'valid';
    case Scanned = 'scanned';
    case Void    = 'void';
}
