<?php
namespace App\CustomException;

/**
 * Thrown when an attempt is made to create a database record that violates
 * a unique constraint (e.g., duplicate email during user registration).
 */
final class DuplicateEntryException extends \Exception
{
}
