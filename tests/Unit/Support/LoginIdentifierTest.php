<?php

namespace Tests\Unit\Support;

use App\Support\LoginIdentifier;
use Tests\TestCase;

class LoginIdentifierTest extends TestCase
{
    public function test_appends_domain_to_username_when_domain_is_set(): void
    {
        $this->assertSame('user.name@example.com', LoginIdentifier::normalize('user.name', 'example.com'));
    }

    public function test_leaves_full_email_untouched_when_domain_is_set(): void
    {
        $this->assertSame('user.name@other.com', LoginIdentifier::normalize('user.name@other.com', 'example.com'));
    }

    public function test_returns_input_unchanged_when_domain_is_null(): void
    {
        $this->assertSame('user.name', LoginIdentifier::normalize('user.name', null));
    }

    public function test_returns_input_unchanged_when_domain_is_empty_string(): void
    {
        $this->assertSame('user.name', LoginIdentifier::normalize('user.name', ''));
    }

    public function test_appends_domain_to_plain_username(): void
    {
        $this->assertSame('myuser@example.com', LoginIdentifier::normalize('myuser', 'example.com'));
    }
}
