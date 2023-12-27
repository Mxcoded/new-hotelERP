<?php

namespace App\Services;

use App\Models\Guest;

class GuestService
{
    public function createGuest($data)
    {
        // Here you'll handle the logic to create a new guest
        $guest = Guest::create($data);
        return $guest;
    }
};
