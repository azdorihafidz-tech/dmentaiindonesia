<?php

namespace Tests\Unit;

use App\Services\PageContentSanitizer;
use Tests\TestCase;

class PageContentSanitizerTest extends TestCase
{
    public function test_untrusted_page_content_is_reduced_to_safe_editor_markup(): void
    {
        $content = app(PageContentSanitizer::class)->sanitize(
            '<h2 onclick="alert(1)">Tentang</h2><script>alert(1)</script><a href="javascript:alert(1)">tautan</a>'
        );

        $this->assertSame('<h2>Tentang</h2><a rel="noopener noreferrer">tautan</a>', $content);
    }
}
