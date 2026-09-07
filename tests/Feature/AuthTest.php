<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_self_registration_is_disabled(): void
    {
        // Les comptes ne sont créés que par un administrateur depuis
        // "Les Utilisateurs" ; l'auto-inscription publique donnerait de fait
        // un accès complet au Dépôt (NNI, téléphone, adresse des citoyens)
        // à n'importe quel visiteur non authentifié.
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Intrus',
            'email' => 'intrus@example.com',
            'password' => 'motdepasse123',
            'password_confirmation' => 'motdepasse123',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'intrus@example.com']);
    }

    public function test_self_service_password_reset_by_email_is_disabled(): void
    {
        // Aucun serveur SMTP réel n'est configuré (MAIL_MAILER=log) : la
        // réinitialisation par email ne peut de toute façon jamais aboutir.
        // Un administrateur réinitialise le mot de passe depuis "Les Utilisateurs".
        $this->get('/password/reset')->assertNotFound();
        $this->post('/password/email', ['email' => 'quelqu-un@example.com'])->assertNotFound();
    }

    public function test_login_page_does_not_advertise_the_disabled_links(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertDontSee('Créer un compte');
        $response->assertDontSee('Mot de passe oublié');
    }
}
