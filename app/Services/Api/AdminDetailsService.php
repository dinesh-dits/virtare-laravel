<?php

namespace App\Services\Api;

use App\Transformers\AdminDetails\AdminDetailsTransformer;
use Exception;


class AdminDetailsService
{
    // Admin Details
    public function AdminDetails()
    {
        try {
            $ADMIN_NAME = $_ENV['ADMIN_NAME'];
            $ADMIN_EMAIL = $_ENV['ADMIN_EMAIL'];
            $data = ["email" => $ADMIN_EMAIL, "name" => $ADMIN_NAME];
            return fractal()->item($data)->transformWith(new AdminDetailsTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
