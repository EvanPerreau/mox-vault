# Architecture modulaire de l'application Laravel

## Objectif

Cette architecture a été conçue pour construire une application Laravel **hautement modulaire**, **domain-driven**, **scalable** et **lisible**.

Chaque module est autonome, responsable de sa propre logique, et respectueux des principes SOLID.


---

# 1. Organisation des Modules

## a. Types de Modules

| Type de module | Rôle | Contient des routes ? |
|:---------------|:------|:----------------------|
| Module Database | Contient uniquement les migrations pour la DB | ❌ Non |
| Module Entities | Contient les entités Eloquent | ❌ Non |
| Module Webservice | Exposition de routes HTTP (controllers, routes) | ✅ Oui |
| Module Business | Logique métier pure | ❌ Non |
| Module Tech | Logiciel technique : services transverses, outils | ❌ Non |
| Module External | Intégrations avec services externes (API tierces) | ❌ Non |
| Module Common | Outils, Helpers, DTOs, Contracts partagés | ❌ Non |


## b. Dossier par module

Chaque module est situé dans le dossier : `modules/<NomDuModule>`


# 2. Structure interne d'un module

**Exemple pour un module Database (`CardDatabase`)** :

```
modules/
  CardDatabase/
    migrations/
    CardDatabaseServiceProvider.php
```

**Exemple pour un module Entities (`Entities`)** :

```
modules/
  Entities/
    entities/
    EntitiesServiceProvider.php
```

**Exemple pour un module Webservice (`PublicApi`)** :

```
modules/
  PublicApi/
    controllers/
    routes/
    middleware/
    PublicApiServiceProvider.php
```

**Exemple pour un module Business (`CardManager`)** :

```
modules/
  CardManager/
    services/
    CardManagerServiceProvider.php
```

**Exemple pour un module Tech (`StorageService`)** :

```
modules/
  StorageService/
    services/
    StorageServiceProvider.php
```

**Exemple pour un module External (`ScryfallApiClient`)** :

```
modules/
  ScryfallApiClient/
    services/
    ScryfallApiClientServiceProvider.php
```

**Exemple pour un module Common (`Shared`)** :

```
modules/
  Shared/
    helpers/
    dtos/
    contracts/
    SharedServiceProvider.php
```


# 3. Nommage

- **ServiceProvider** : `<NomDuModule>ServiceProvider`
- **Service** : `<Nom>Service` (ex: `CardImportService`)
- **Commande artisan** : `<Nom>Command` (ex: `ImportCardsCommand`)
- **Repository** : `<Nom>Repository` (ex: `CardRepository`)
- **Modèle Eloquent** : `Nom.php` (ex: `Card.php`)


# 4. Fonctionnement des modules

Chaque module doit :

- Définir son propre ServiceProvider.
- Il sera automatiquement enregistré.

- Dans son ServiceProvider, chaque module peut :
  - **Publier ses routes** (seulement pour module Webservice)
  - **Publier ses migrations** (seulement pour module Database)
  - **Enregistrer ses services** dans le conteneur DI (`$this->app->bind(...)`)


# 5. Conventions spécifiques

- **Pas d'import direct entre modules.**
  - Communication via **Services** ou **Contracts**.

- **Pas de routes** sauf dans un module Webservice.

- **Shared** contient :
  - Helpers globaux
  - DTOs communs
  - Contracts communs


# 6. Avantages

- **Découplage total**
- **Maintenance facilitée**
- **Testabilité accrue**
- **Évolution simple** (ajout d'un nouveau module = ajout d'un dossier et d'un ServiceProvider)


# 7. Exemple d'ajout de module

Pour ajouter un module `SocialGraph` :

1. Créer :
   ```
   modules/SocialGraph/
       services/
       models/
       SocialGraphServiceProvider.php
   ```

2. Implémenter la logique interne dans `services/`

3. Ne pas créer de routes sauf si nécessaire et fait dans un module Webservice à part.

# Fin de documentation 📖

Projet pensé pour être **propre**, **scalable**, **moderne** et **robuste**.

