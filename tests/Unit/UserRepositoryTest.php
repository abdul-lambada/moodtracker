<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\UserRepository;
use PHPUnit\Framework\TestCase;

final class UserRepositoryTest extends TestCase
{
    public function testCountAllReturnsInteger(): void
    {
        self::markTestIncomplete('Sediakan database khusus testing sebelum menjalankan test ini.');

        $total = UserRepository::countAll();
        self::assertIsInt($total);
        self::assertGreaterThanOrEqual(0, $total);
    }
}
