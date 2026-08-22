<?php

namespace App\Exceptions;

use Exception;

/**
 * A cart operation the shopper isn't allowed to complete — out of stock,
 * product no longer for sale, quantity out of range.
 *
 * The message is written for the shopper (Spanish, no internals) because
 * CartController renders it straight into the JSON response.
 */
class CartException extends Exception
{
}
