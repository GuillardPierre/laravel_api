<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    public function test_uses_professional_email_returns_true(): void
    {
        $user = new User();
        $user->email = 'john@entreprise.com';

        $this->assertTrue($user->usesProfessionalEmail());
    }

    public function test_uses_professional_email_returns_false(): void
    {
        $user = new User();
        $user->email = 'john@gmail.com';

        $this->assertFalse($user->usesProfessionalEmail());
    }
}
