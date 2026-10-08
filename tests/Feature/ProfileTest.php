<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk()
            ->assertSee('Mi perfil')->assertSee('Guardar cambios')
            ->assertSee('Actualizar contraseña')->assertDontSee('Profile Information');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_profile_rejects_another_accounts_email(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($user)->from('/profile')->patch('/profile', [
            'name' => 'Nuevo nombre',
            'email' => $other->email,
        ])->assertSessionHasErrors(['email' => 'Este correo electrónico ya está registrado en otra cuenta.'])->assertRedirect('/profile');
        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_profile_success_message_is_displayed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['status' => 'profile-updated'])->get('/profile')
            ->assertOk()->assertSee('Tus datos se guardaron correctamente.')
            ->assertSee('data-success-notice', false);
    }

    public function test_profile_renders_field_errors_in_spanish(): void
    {
        $user = User::factory()->create();
        $errors = (new \Illuminate\Support\ViewErrorBag)
            ->put('default', new \Illuminate\Support\MessageBag(['email' => 'Este correo ya está registrado.']))
            ->put('updatePassword', new \Illuminate\Support\MessageBag(['password' => 'La confirmación no coincide.']));
        // The application uses JSON sessions; seed the stored error-bag format.
        $storedErrors = collect($errors->getBags())->map(fn ($bag) => [
            'messages' => $bag->getMessages(),
            'format' => $bag->getFormat(),
        ])->all();
        $this->actingAs($user)->withSession(['errors' => $storedErrors])->get('/profile')
            ->assertOk()->assertSee('Este correo ya está registrado.')
            ->assertSee('La confirmación no coincide.')->assertSee('is-invalid');
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_cannot_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response->assertMethodNotAllowed();
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh());
    }

    public function test_profile_page_does_not_offer_account_deletion(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk()->assertDontSee('Delete Account');
    }
}
