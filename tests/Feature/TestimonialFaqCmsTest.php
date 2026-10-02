<?php

namespace Tests\Feature;

use App\Filament\Resources\Faqs\Pages\ManageFaqs;
use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Models\Faq;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TestimonialFaqCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_testimonials_can_be_created_updated_and_only_active_items_show_on_homepage(): void
    {
        $testimonial = Testimonial::query()->create([
            'customer_name' => 'Pelanggan Uji', 'content' => 'Konten testimoni dari automated test.', 'status' => 'active', 'sort_order' => 2,
        ]);
        $testimonial->update(['content' => 'Konten testimoni yang diperbarui.']);
        Testimonial::query()->create(['customer_name' => 'Tidak tampil', 'content' => 'Inactive fixture.', 'status' => 'inactive']);
        $this->assertDatabaseHas('testimonials', ['id' => $testimonial->id, 'content' => 'Konten testimoni yang diperbarui.']);

        $this->get('/')->assertOk()->assertSee('Pelanggan Uji')->assertSee('Konten testimoni yang diperbarui.')->assertDontSee('Tidak tampil');
    }

    public function test_faqs_can_be_created_updated_ordered_and_inactive_items_are_hidden(): void
    {
        $later = Faq::query()->create(['question' => 'Pertanyaan kedua?', 'answer' => 'Jawaban kedua.', 'category' => 'Menu', 'status' => 'active', 'sort_order' => 2]);
        Faq::query()->create(['question' => 'Pertanyaan pertama?', 'answer' => 'Jawaban pertama.', 'category' => 'Menu', 'status' => 'active', 'sort_order' => 1]);
        Faq::query()->create(['question' => 'Pertanyaan privat?', 'answer' => 'Jangan tampil.', 'status' => 'inactive']);
        $later->update(['answer' => 'Jawaban yang diperbarui.']);
        $this->assertDatabaseHas('faqs', ['id' => $later->id, 'answer' => 'Jawaban yang diperbarui.']);

        $this->get('/faq')->assertOk()
            ->assertSeeInOrder(['Pertanyaan pertama?', 'Pertanyaan kedua?'])
            ->assertSee('Jawaban yang diperbarui.')
            ->assertDontSee('Pertanyaan privat?');
    }

    public function test_faq_output_escapes_html_and_does_not_inject_unsafe_markup(): void
    {
        Faq::query()->create([
            'question' => '<script>alert(1)</script>Question',
            'answer' => '<img src=x onerror=alert(1)> plain answer',
            'status' => 'active',
        ]);

        $this->get('/faq')->assertOk()->assertSee('&lt;script&gt;', false)->assertSee('&lt;img', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_admin_can_manage_testimonials_and_faqs_with_required_fields(): void
    {
        $this->get('/admin/testimonials')->assertRedirect('/admin/login');
        $this->get('/admin/faqs')->assertRedirect('/admin/login');
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/admin/testimonials')->assertOk();
        $this->actingAs($admin)->get('/admin/faqs')->assertOk();

        Livewire::actingAs($admin)->test(ManageTestimonials::class)
            ->mountAction('create')
            ->setActionData(['customer_name' => 'Nama Test', 'content' => 'Testimoni jelas dari test.', 'status' => 'active'])
            ->callMountedAction()->assertHasNoErrors();
        Livewire::actingAs($admin)->test(ManageFaqs::class)
            ->mountAction('create')
            ->setActionData(['question' => 'Pertanyaan admin?', 'answer' => 'Jawaban admin.', 'status' => 'active'])
            ->callMountedAction()->assertHasNoErrors();
        Livewire::actingAs($admin)->test(ManageFaqs::class)
            ->mountAction('create')
            ->setActionData(['question' => '', 'answer' => '', 'status' => 'active'])
            ->callMountedAction()->assertHasActionErrors(['question', 'answer']);
    }

    public function test_sitemap_contains_public_content_listing_routes_including_faq(): void
    {
        $response = $this->get('/sitemap.xml')->assertOk();
        foreach (['products.index', 'promotions.index', 'outlets.index', 'articles.index', 'gallery.index', 'faqs.index'] as $route) {
            $response->assertSee(route($route));
        }
    }
}
