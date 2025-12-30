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

**Build Reactions. Simply and Powerfully.**

Laravel Reaction simplifies reaction management in Laravel applications. Stop creating separate tables for each reaction type and start building social interaction features with confidence. It provides a modern, flexible package that allows your Eloquent models to handle reaction functionality (like, dislike, love, etc.)—perfect for building social features, engagement systems, and user interaction tracking. This is where powerful reaction management meets developer-friendly simplicity—giving you complete control over user reactions without the complexity.

## Why Laravel Reaction?

### Simple API

Laravel Reaction provides a clean, intuitive API for managing reactions. Add, remove, toggle, and query reactions with simple method calls—no complex queries or manual relationship management.

### Flexible Reaction Types

Support any reaction type you need: like, dislike, love, heart, thumbs up, and more. The package doesn't limit you to predefined reaction types—use whatever makes sense for your application.

### Anonymous Reactions

Support both authenticated user reactions and anonymous device-based reactions. Perfect for applications where users can react without logging in, or where you need to track reactions by device.

### Polymorphic Relationships

Use reactions on any Eloquent model through polymorphic relationships. Articles, posts, comments, products—anything can be reactable, and any model can be a reactor.

## What is Reaction Management?

Reaction management is the process of allowing users or devices to express their feelings or opinions about content through reactions. Traditional approaches often involve:

- Creating separate tables for each reaction type (likes, dislikes, etc.)
- Writing complex queries to check reaction status
- Managing reaction state manually
- Duplicating code across different models

Laravel Reaction solves these challenges by providing:

- **Unified System**: Single table for all reaction types
- **Polymorphic Design**: Works with any model
- **Simple API**: Clean methods for all operations
- **Event Integration**: Built-in events for extensibility
- **Query Helpers**: Easy methods for common queries

Consider a social media platform where users can react to posts with multiple reaction types. With Laravel Reaction, you can add reactions programmatically, track reactions by user or device, get reaction summaries and counts, toggle reactions easily, and integrate with notification systems through events. The power of reaction management lies not only in flexible reaction types but also in making it easy to query, track, and manage throughout your application.

## What Awaits You?

By adopting Laravel Reaction, you will:

- **Build social features** - Add like, dislike, and custom reactions to any content
- **Simplify reaction management** - Single API for all reaction operations
- **Support anonymous users** - Track reactions by device without authentication
- **Improve user engagement** - Easy-to-use reaction system increases interaction
- **Enable flexible reactions** - Use any reaction type that fits your needs
- **Maintain clean code** - Simple, intuitive API that follows Laravel conventions

## Quick Start

Install Laravel Reaction via Composer:

```bash
composer require jobmetric/laravel-reaction
```

## Documentation

Ready to transform your Laravel applications? Our comprehensive documentation is your gateway to mastering Laravel Reaction:

**[📚 Read Full Documentation →](https://jobmetric.github.io/packages/laravel-reaction/)**

The documentation includes:

- **Getting Started** - Quick introduction and installation guide
- **HasReaction** - Trait for models that can receive reactions
- **CanReact** - Trait for models that can give reactions
- **Reaction Model** - Eloquent model for storing reactions
- **Events** - Hook into reaction lifecycle
- **Querying** - Methods for counting, summarizing, and filtering reactions
- **Real-World Examples** - See how it works in practice

## Contributing

Thank you for participating in `laravel-reaction`. A contribution guide can be found [here](CONTRIBUTING.md).

## License

The `laravel-reaction` is open-sourced software licensed under the MIT license. See [License File](LICENCE.md) for more information.
