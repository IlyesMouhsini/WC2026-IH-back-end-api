# WC2026 Hub - API

API REST développée avec **Symfony 7**, back-end du projet [WC2026 Hub](https://github.com/IlyesMouhsini/WC2026-Intelligence-Hub) : elle centralise les données du tournoi (équipes, rencontres, scores) synchronisées depuis une API externe, et gère la couche communautaire de l'application (comptes utilisateurs, pronostics, classement).

Ce projet est le pendant back-end de [WC2026 Hub](https://github.com/IlyesMouhsini/WC2026-Intelligence-Hub), une plateforme de data intelligence sur la Coupe du Monde 2026 construite en SvelteKit. Les deux projets forment un écosystème connecté : WC2026 Hub consomme cette API pour afficher en direct les rencontres, leurs scores, et les fonctionnalités communautaires du tournoi.

## Stack technique

- **Symfony 7** (LTS) — framework back-end PHP
- **Doctrine ORM** — mapping objet-relationnel et migrations de base de données
- **API Platform** — génération automatique de l'API REST (CRUD, documentation OpenAPI/Swagger)
- **MySQL** — base de données relationnelle
- **HttpClient (Symfony)** — intégration d'une API REST externe ([football-data.org](https://www.football-data.org/)) pour synchroniser équipes et rencontres réelles
- **Nelmio CORS Bundle** — autorise les appels du front SvelteKit en développement

## Fonctionnalités

- Gestion des équipes et des rencontres du tournoi (scores, statut, phase de compétition), synchronisées automatiquement depuis une API externe
- Comptes utilisateurs et système de pronostics : chaque utilisateur pronostique un score pour chaque rencontre
- Calcul automatique des points selon la précision du pronostic (score exact, bonne issue, ou aucun point) une fois le résultat réel connu
- API REST documentée, consommée en direct par le front [WC2026 Hub](https://github.com/IlyesMouhsini/WC2026-Intelligence-Hub)

## Modèle de données

```
Utilisateur 1 ──── N Pronostic N ──── 1 Rencontre
                                          │
                                N ─────────┴───── 1  (x2 : domicile + extérieur)
                                       Equipe
```

| Entité | Rôle |
|---|---|
| `Equipe` | Une sélection nationale participant au tournoi |
| `Rencontre` | Un match entre deux équipes (date, scores, phase) |
| `Utilisateur` | Un participant de la plateforme |
| `Pronostic` | Le score deviné par un utilisateur pour une rencontre donnée, et les points obtenus |

## Commandes disponibles

En plus des commandes standard de Symfony/Doctrine, ce projet ajoute :

| Commande | Rôle |
|---|---|
| `php bin/console app:sync-equipes` | Importe les équipes qualifiées depuis football-data.org |
| `php bin/console app:sync-rencontres` | Importe/met à jour toutes les rencontres et leurs scores depuis football-data.org |
| `php bin/console app:seed-demo-data` | Crée une rencontre et un pronostic de démonstration à partir des équipes déjà synchronisées |
| `php bin/console app:calculer-points` | Calcule les points de tous les pronostics dont la rencontre est terminée |

## Installation locale

### Prérequis

- PHP 8.2+
- Composer
- [Symfony CLI](https://symfony.com/download)
- MySQL (ex. via XAMPP)
- Une clé API gratuite sur [football-data.org](https://www.football-data.org/client/register)

### Étapes

```bash
git clone https://github.com/TonPseudo/wc2026-hub-api.git
cd wc2026-hub-api
composer install
```

Configurer la connexion à la base de données dans `.env` :

```
DATABASE_URL="mysql://root:@127.0.0.1:3306/wc2026_hub?serverVersion=8.0&charset=utf8mb4"
```

Créer un fichier `.env.local` (non versionné) avec votre clé API football-data.org :

```
FOOTBALL_API_KEY=votre_cle_api
FOOTBALL_API_URL=https://api.football-data.org/v4
```

Créer la base et appliquer les migrations :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

Charger les données (fixtures + synchronisation depuis l'API externe) :

```bash
php bin/console doctrine:fixtures:load
php bin/console app:sync-equipes
php bin/console app:sync-rencontres
php bin/console app:seed-demo-data
php bin/console app:calculer-points
```

Lancer le serveur local :

```bash
symfony server:start
```

La documentation de l'API est alors accessible sur `http://127.0.0.1:8000/api`.

## Roadmap

- [x] Modélisation des entités (Equipe, Utilisateur, Rencontre, Pronostic)
- [x] Migrations et base de données
- [x] Fixtures (utilisateur de test)
- [x] Intégration d'une API REST externe (football-data.org) : équipes et rencontres réelles
- [x] Service de calcul de points, testable indépendamment
- [x] Connexion avec le front WC2026 Hub (SvelteKit)
- [ ] Authentification JWT
- [ ] Groupes de sérialisation API Platform (masquer les données sensibles, ex. mot de passe, des réponses imbriquées)
- [ ] Tests PHPUnit sur la logique métier
- [ ] Classement des utilisateurs par total de points

## Auteur

Ilyès Mouhsini - étudiant BUT Informatique, IUT de Vélizy-Rambouillet (UVSQ)
