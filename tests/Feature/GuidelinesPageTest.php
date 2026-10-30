<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The public /guidelines page.
 *
 * It 500'd in production from 2026-09-02 to 2026-10-31: the tea roster
 * contact line had "WhatsApp@endif", and Blade does not treat "@endif" as a
 * directive when a letter comes straight before it (it reads like an email
 * address), so the compiled view had an unclosed if. Nothing rendered the page
 * in tests, so these do, with and without the optional contact phone.
 */
class GuidelinesPageTest extends TestCase
{
    public function test_renders_with_the_contact_phone(): void
    {
        config([
            'custom.contacts.tea_roster_name' => 'Test Coordinator',
            'custom.contacts.tea_roster_phone' => '0400 000 000',
        ]);

        $this->get('/guidelines')
            ->assertOk()
            ->assertSee('please contact <strong>Test Coordinator</strong> on <strong>0400 000 000</strong> via SMS or WhatsApp.', false);
    }

    public function test_renders_without_the_contact_phone(): void
    {
        config([
            'custom.contacts.tea_roster_name' => 'Test Coordinator',
            'custom.contacts.tea_roster_phone' => null,
        ]);

        $this->get('/guidelines')
            ->assertOk()
            ->assertSee('please contact <strong>Test Coordinator</strong>.', false)
            ->assertDontSee('via SMS or WhatsApp');
    }

    public function test_escapes_the_contact_phone(): void
    {
        config(['custom.contacts.tea_roster_phone' => '<script>x</script>']);

        $this->get('/guidelines')
            ->assertOk()
            ->assertDontSee('<script>x</script>', false);
    }
}
