# Task API — API REST en Symfony

Petite **API REST de gestion de tâches**, construite avec **Symfony 7**, pour démontrer la conception d'une API back-end propre : entité Doctrine, validation, sérialisation JSON, gestion d'erreurs et **tests automatisés**.

> Projet de démonstration réalisé par **Boubacar Konaté**, développeur web full stack.

## Fonctionnalités

- API REST complète (CRUD) sur une ressource `Task`.
- **Validation** des données entrantes (DTO + contraintes Symfony) avec erreurs `422` détaillées.
- **Sérialisation JSON** via le composant Serializer (groupes de sérialisation).
- Gestion d'erreurs cohérente (`400`, `404`, `422`) au format JSON.
- Base **SQLite** (zéro configuration) et **migration** Doctrine versionnée.
- **Tests PHPUnit** : unitaires (entité) + fonctionnels (appels HTTP réels sur une base de test isolée).

## Stack

- PHP 8.2, Symfony 7
- Doctrine ORM + Migrations
- Serializer, Validator
- PHPUnit
- SQLite

## Endpoints

| Méthode | URL | Description |
|--------|-----|-------------|
| `GET` | `/api/tasks` | Lister les tâches (filtre optionnel `?done=0` ou `?done=1`) |
| `GET` | `/api/tasks/{id}` | Voir une tâche |
| `POST` | `/api/tasks` | Créer une tâche |
| `PUT` | `/api/tasks/{id}` | Modifier une tâche |
| `PATCH` | `/api/tasks/{id}/toggle` | Basculer l'état fait / à faire |
| `DELETE` | `/api/tasks/{id}` | Supprimer une tâche |

### Exemple de ressource

```json
{
  "id": 1,
  "title": "Préparer l'entretien",
  "description": "Relire le projet",
  "done": false,
  "priority": 2,
  "createdAt": "2026-10-05T07:00:00+00:00"
}
```

`priority` : `1` basse, `2` normale, `3` haute.

## Exemples (curl)

```bash
# Créer une tâche
curl -X POST http://localhost:8000/api/tasks \
  -H "Content-Type: application/json" \
  -d '{"title":"Préparer l'\''entretien","priority":3}'

# Lister les tâches non terminées
curl "http://localhost:8000/api/tasks?done=0"

# Basculer l'état d'une tâche
curl -X PATCH http://localhost:8000/api/tasks/1/toggle

# Supprimer une tâche
curl -X DELETE http://localhost:8000/api/tasks/1
```

## Lancer en local

Prérequis : **PHP 8.2+**, **Composer**.

```bash
composer install
php bin/console doctrine:migrations:migrate --no-interaction   # crée la base SQLite
php -S localhost:8000 -t public public/index.php               # ou : symfony serve
```

L'API est alors disponible sur http://localhost:8000.

## Tests

```bash
php bin/phpunit
```

Les tests fonctionnels recréent un schéma SQLite propre à chaque test — aucune configuration de base n'est nécessaire.

## Déploiement

Un `Dockerfile` est fourni : l'API se déploie gratuitement sur Render, Fly.io ou Koyeb (image Docker, base SQLite embarquée).

> Remarque : sur un hébergement gratuit, le système de fichiers est éphémère — la base SQLite peut être réinitialisée après une mise en veille. C'est volontaire pour une simple démo.

## Pistes d'évolution

- Authentification **JWT** (endpoints protégés).
- **Pagination** et tri sur la liste.
- Documentation **OpenAPI / Swagger**.
