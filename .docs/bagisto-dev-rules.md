# Bagisto Development Rules

## Platform

This project is built on:

- Laravel
- Bagisto
- PHP 8.3
- MySQL
- Blade
- Tailwind CSS
- Vite


Prefer:

- Configuration
- Service Providers
- Events
- Repositories
- Package extensions
- Overrides

Avoid:

- Vendor modifications
- Core hacks
- Temporary fixes

## Bagisto Rules

Before creating custom functionality:

1. Check existing package capability.
2. Check existing event system.
3. Check repository layer.
4. Check configuration options.

Always preserve upgrade compatibility.

## Database

Prefer migrations.

Avoid direct production schema modifications.

## Performance

Avoid:

- N+1 queries
- Excessive observers
- Heavy view logic

Prefer:

- Eager loading
- Caching
- Service classes


## Webkul Core Protection Rule

Never modify anything inside:

packages/Webkul/*

This includes:

- Controllers
- Models
- Repositories
- DataGrids
- Views
- Routes
- Service Providers
- Assets
- Config files

Treat all Webkul packages as vendor code.

All customizations must be implemented through:

- Custom packages
- Custom modules
- Theme overrides
- Events
- Listeners
- Service container bindings
- Configuration
- View overrides
- Middleware
- Plugins/extensions

The objective is to maintain full upgrade compatibility with future Bagisto releases.

Direct modification of Webkul packages is prohibited unless explicitly approved.