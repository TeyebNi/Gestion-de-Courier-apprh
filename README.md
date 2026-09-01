# Gestion de Courrier

Application Laravel de gestion et de suivi du courrier/des demandes administratives d'une commune (Mauritanie). Chaque demande déposée par un citoyen est suivie de l'accueil jusqu'à sa clôture à travers un circuit de validation impliquant plusieurs intervenants.

## Circuit d'une demande

```
Accueil → Fatou (coordination) → Maire (décision) → Fatou → Service concerné (ou clôture)
```

- **Accueil** : enregistre la demande (`Tabdepot`), imprime un reçu, l'envoie à Fatou.
- **Fatou** : réceptionne et transmet au Maire ; après décision, oriente vers un service ou clôture.
  Affiché "Cabinet" dans l'interface (le rôle interne reste `fatou`, voir ci-dessous).
- **Maire** : accepte ou refuse la demande, avec remarque éventuelle et service de destination.
- **Service** : consulte les demandes qui lui sont assignées.

Chaque changement d'étape est journalisé dans `DemandeHistorique` (visible via `circuit/{id}/historique` et `circuit/suivi`).

## Rôles

Définis dans `App\Enums\UserRole` et appliqués via les middlewares `admin`, `fatou`, `maire` (`app/Http/Middleware`) :

| Rôle | Accès |
|---|---|
| `admin` | Tout le système, y compris configuration (Orientation, Types de demande) et gestion des utilisateurs |
| `user` | Dépôt, suivi, notifications ; accès au module Affectation seulement si `can_affectation = true` |
| `fatou` | Pages de coordination (`circuit/fatou`), affichées "Cabinet" dans l'interface |
| `maire` | Pages de décision (`circuit/maire`) |

## Stack technique

- Laravel 12 / PHP 8.2, MySQL
- `laravel/ui` pour l'authentification
- Export PDF : `barryvdh/laravel-dompdf` (rapports/factures) et `mpdf/mpdf` + `mpdf/qrcode` (reçu de dépôt avec QR code)
- SMS : Twilio, via `App\Services\SmsService` (notifie le citoyen à l'enregistrement de sa demande)

## Installation

```bash
composer install
cp .env.example .env   # configurer DB_*, TWILIO_* si besoin
php artisan key:generate
php artisan migrate --seed
php artisan storage:link   # requis pour que les pièces jointes soient accessibles
php artisan serve
```

Le seeder crée les comptes de démarrage suivants (mot de passe à changer après la première connexion) :

| Email | Rôle |
|---|---|
| `admin@commune.mr` | admin |
| `acceil@gmail.com` | admin (poste Accueil) |
| `cabinet@gmail.com` | fatou |

## Tests

```bash
php artisan test
```

Les tests tournent sur SQLite en mémoire (`phpunit.xml`) et couvrent le circuit complet de la demande, les permissions par rôle, le dépôt, les affectations, la configuration admin, la gestion des utilisateurs et les notifications de service.

## Notes

- Les tables métier (`tabdepot`, `affectation`, `orientation`, `typedem`) utilisent des noms **singuliers**, contrairement à la convention Laravel — c'est voulu, ne pas renommer.
- Les listes de travail (Dépôt, Affectation) affichent les entrées les plus récentes en premier (tri par id décroissant).
- NNI et adresse ne sont demandés que pour une demande **externe** (citoyen/institution) — sans objet pour une note interne entre agents municipaux, ils sont masqués et jamais enregistrés dans ce cas. Le téléphone n'est pas obligatoire pour un expéditeur institution (courrier officiel scanné).
- "Interne" (Origine) est réservé aux notes d'un service municipal vers un autre. Toute demande venant d'un citoyen — y compris une réclamation liée à un service interne comme les impôts — doit être classée "Externe → Citoyen", jamais "Interne".
