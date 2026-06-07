# Security Rules

## General

Security has priority over convenience.

## Permissions

Never recommend:

chmod -R 777

Prefer minimum required permissions.

## Laravel

Protect:

- .env
- storage
- bootstrap/cache

## Production

Before destructive actions:

- Explain risk
- Suggest backup
- Suggest rollback

## Malware Prevention

Check:

- Unexpected PHP files
- Modified index files
- Suspicious cron jobs
- Writable public directories

## Deployment

Prefer:

- Git
- SSH keys
- Controlled releases

Avoid:

- Direct FTP modifications

## Secrets

Never expose:

- API keys
- Passwords
- Tokens
- Credentials