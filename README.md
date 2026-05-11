# api-skeleton

Skeleton d'API Laravel dockerise avec PHP-FPM, Nginx et MySQL.

## Prerequis

- Docker
- Docker Compose
- Make

Tout le reste tourne dans les conteneurs Docker : PHP, Composer, Nginx et MySQL.

## Installation

1. Cloner le projet, puis entrer dans le dossier :

```bashæ
git clone <url-du-repo>
cd api-skeletonv2
```

2. Creer le fichier d'environnement Laravel :

```bash
cp src/.env.example src/.env
```

3. Verifier la configuration MySQL dans `src/.env`.

Avec le `docker-compose.yml` actuel, le host MySQL doit etre `db` :

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=root
```

4. Construire et demarrer les conteneurs :

```bash
make build
```

Cette commande lance Docker Compose, installe les dependances Composer, genere la cle Laravel et execute les migrations.

5. Ouvrir l'application :

```text
http://localhost:8000
```

## Commandes Make

Les commandes se lancent depuis la racine du projet.

| Commande | Description |
| --- | --- |
| `make build` | Construit les images Docker, demarre les conteneurs en arriere-plan, puis lance `composer setup` dans le conteneur `app`. |
| `make up` | Demarre les conteneurs Docker en arriere-plan sans reconstruire les images. |
| `make down` | Arrete et supprime les conteneurs du projet. Les donnees MySQL sont conservees dans le volume Docker. |
| `make restart` | Redemarre les conteneurs en executant `make down`, puis `make up`. |
| `make shell` | Ouvre un terminal Bash dans le conteneur Laravel `app`. |
| `make composer-install` | Execute `composer install` dans le conteneur `app`. |
| `make composer-setup` | Execute le script Composer `setup` : installation des dependances, creation du `.env` si absent, generation de la cle Laravel et migrations. |
| `make artisan cmd="..."` | Execute une commande Artisan dans le conteneur `app`. Exemple : `make artisan cmd="migrate:fresh --seed"`. |
| `make create-api name=Nom` | Genere les fichiers CRUD API pour un modele. Exemple : `make create-api name=Product`. |
| `make test` | Lance la suite de tests Laravel via `composer test`. |

## Generation d'une API CRUD

Le projet contient une commande Artisan custom :

```bash
make create-api name=Product
```

Elle genere les fichiers principaux pour une ressource API :

- modele
- controller
- query
- resource
- requests `StoreRequest` et `UpdateRequest`
- policy
- migration
- fichier de routes dans `routes/api`
- enregistrement de la route dans `routes/api.php`

Apres generation, adapter la migration et les rules de validation, puis lancer les migrations :

```bash
make artisan cmd="migrate"
```

## Tests

```bash
make test
```

## Arret du projet

```bash
make down
```
