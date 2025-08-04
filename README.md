[contributors-shield]: https://img.shields.io/github/contributors/jobmetric/laravel-reaction.svg?style=for-the-badge
[contributors-url]: https://github.com/jobmetric/laravel-reaction/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/jobmetric/laravel-reaction.svg?style=for-the-badge&label=Fork
[forks-url]: https://github.com/jobmetric/laravel-reaction/network/members
[stars-shield]: https://img.shields.io/github/stars/jobmetric/laravel-reaction.svg?style=for-the-badge
[stars-url]: https://github.com/jobmetric/laravel-reaction/stargazers
[license-shield]: https://img.shields.io/github/license/jobmetric/laravel-reaction.svg?style=for-the-badge
[license-url]: https://github.com/jobmetric/laravel-reaction/blob/master/LICENCE.md
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-blue.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/majidmohammadian

[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![MIT License][license-shield]][license-url]
[![LinkedIn][linkedin-shield]][linkedin-url]

# Laravel Reaction

A modern, flexible, and test-covered Laravel package that allows your models to handle **reaction functionality** (like, dislike, love, etc.). This package provides a clean API for both **reactable** (e.g., articles, posts) and **reactor** (e.g., users, devices) models.

## 💾 Installation

Install via composer:

```bash
composer require jobmetric/laravel-reaction
```

Then publish and run the migration:

```bash
php artisan migrate
```

## ✨ Usage

### Step 1: Add `HasReaction` to your model (e.g., `Article`)

```php
use JobMetric\Reaction\HasReaction;

class Article extends Model
{
    use HasReaction;
}
```

### Step 2: Add `CanReact` to your reactor model (e.g., `User`)

```php
use JobMetric\Reaction\CanReact;

class User extends Model
{
    use CanReact;
}
```

## ✅ Main Features

### ➕ Add Reaction

```php
$article->addReaction('like', $user); // with user
$article->addReaction('like', null, ['device_id' => 'abc123']); // anonymous
```

### 🔁 Toggle Reaction

```php
$article->toggleReaction('like', $user); // Adds if not exists, removes if exists
```

### ❌ Remove Reaction

```php
$article->removeReaction('like', $user);
```

### ❌❌ Remove All Reactions (by user or device)

```php
$article->removeAllReactions($user); // or pass device_id
```

### ♻️ Update Reaction

```php
$article->updateReaction('like', 'dislike', $user);
```

### ♻️ Restore Deleted Reaction

```php
$article->restoreReaction('like', $user);
```

## 📊 Counting and Summary

### Total Reactions

```php
$article->totalReactions();
```

### Count by Type

```php
$article->countReactions('like');
```

### Reaction Summary

```php
$article->reactionSummary();
// returns: ['like' => 3, 'dislike' => 1]
```

## 🔍 Querying

### Has Reaction?

```php
$article->hasReaction('like', $user);
```

### Get Latest Reactions

```php
$article->latestReactions(5);
```

### Get Specific Reaction

```php
$article->reactionTo($user);
```

## 🧠 Reactor Functions (for User or any model using CanReact)

### Check if Reacted

```php
$user->hasReactedTo($article);
```

### Check Specific Reaction

```php
$user->reactedWithTo('like', $article);
```

### Get Summary of User Reactions

```php
$user->reactionSummary(); // ['like' => 3, 'dislike' => 2]
```

### Get All Reacted Items

```php
$user->reactedItems(); // Returns models
$user->reactedItems('like', Article::class);
```

### Latest Reactions Given

```php
$user->latestReactionsGiven(10);
```

## 🧱 Reaction Model Columns

| Field           | Description                                 |
|-----------------|---------------------------------------------|
| reactor_type    | Polymorphic class of reactor (e.g., User)   |
| reactor_id      | ID of the reactor                           |
| reactable_type  | Polymorphic class of reactable (e.g., Post) |
| reactable_id    | ID of the reactable                         |
| reaction        | Reaction type (e.g., like, love, etc.)      |
| ip              | IP address of reaction                      |
| device_id       | Optional device identifier                  |
| source          | Source (e.g., web, app, api)                |

## 🧪 Events

| Event                 | Triggered When               |
|-----------------------|------------------------------|
| ReactionAddEvent      | A new reaction is added      |
| ReactionRemovingEvent | Before a reaction is removed |
| ReactionRemovedEvent  | After a reaction is removed  |

## 🧼 Pruning Reactions

This package uses SoftDeletes and supports automatic pruning:

```bash
php artisan model:prune
```

You can configure the number of days in your config:

```php
'reaction' => [
    'prune_days' => 30,
],
```

## 🤝 Contributing

Thank you for considering contributing to the Laravel Reaction! The contribution guide can be found in the [CONTRIBUTING.md](https://github.com/jobmetric/laravel-reaction/blob/master/CONTRIBUTING.md).

## 📄 License

This package is open-sourced under the [MIT license](https://github.com/jobmetric/laravel-reaction/blob/master/LICENCE.md).
