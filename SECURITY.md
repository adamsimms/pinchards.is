# Security Policy

## Supported versions

| Version | Supported |
|---------|-----------|
| `main` branch (live at [www.pinchards.is](https://www.pinchards.is)) | Yes |
| Older tags / forks | No |

## Reporting a vulnerability

**Please do not open a public GitHub issue** for security problems.

Email **adam@adamsimms.xyz** with:

- A description of the issue and impact
- Steps to reproduce (or proof of concept)
- Affected URLs or files, if known

We aim to acknowledge reports within a few days and will coordinate on disclosure timing.

## Secrets and credentials

- **Never commit** AWS keys, Mapbox/Google tokens, SSH private keys, or `secrets.local.php`.
- Production secrets live in **GitHub Actions repository secrets** and are deployed to `~/.config/pinchards.is/secrets.local.php` on the server (outside the web document root).
- Local dev uses `secrets.local.php` in the repo root (gitignored).

If you accidentally commit a secret:

1. **Rotate the credential immediately** — assume it is compromised once pushed.
2. Notify the maintainers so history can be reviewed.

## Scope notes

This site is a small PHP application on shared hosting. In scope: authentication bypass, secret exposure, server-side injection, and misconfigurations that expose private files. Out of scope: social engineering, denial-of-service, and issues in third-party services (AWS, Cloudflare, Mapbox, Vimeo, Google Maps) unless introduced by this repo’s integration.

## Safe harbor

We appreciate responsible disclosure and will not pursue action against researchers who act in good faith and follow this policy.
