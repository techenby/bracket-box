# Bracket Box

Community brackets decided one matchup at a time.

**PHP:** 8.4
**Laravel:** 13
**Node:** 22
**Asset Compiler:** Vite
**Database:** SQLite
**Frontend:** [Livewire v4](https://livewire.laravel.com/docs/quickstart)
**Testing:** [Pest v4](https://pestphp.com/docs/installation)

**Notable Composer Packages:**
- [larastan/larastan](https://github.com/larastan/larastan)
- [laravel/boost](https://github.com/laravel/boost)
- [laravel/chisel](https://github.com/laravel/chisel)
- [laravel/fortify](https://github.com/laravel/fortify)
- [laravel/pail](https://github.com/laravel/pail)
- [laravel/pao](https://github.com/laravel/pao)
- [league/flysystem-aws-s3-v3](https://github.com/thephpleague/flysystem-aws-s3-v3)
- [livewire/blaze](https://github.com/livewire/blaze)
- [livewire/flux](https://fluxui.dev/)
- [pestphp/pest-plugin-browser](https://pestphp.com/docs/browser-testing)

**Notable NPM Packages:**
- [@laravel/passkeys](https://www.npmjs.com/package/@laravel/passkeys)
- [playwright](https://github.com/microsoft/playwright)
- [tailwindcss](https://tailwindcss.com/)

### Helpful Commands

- `composer run setup`
    Sets up the repo for development by installing PHP dependencies, creating the `.env` file if missing, generating the app key, running database migrations, and installing and building the frontend assets with npm.

- `composer run dev`
    Runs multiple development tasks in parallel, including serving the site, running the queues, running `pail`, and compiling frontend assets.

- `composer run lint`
    Runs Pint to standardize the codebase.

- `composer run lint:check`
    Runs Pint in check-only mode, without applying fixes.

- `composer run ci:check`
    Runs all the commands that GitHub Actions will run, including Pint, PHPStan, and the test suite.

- `composer run types:check`
    Runs PHPStan to check the codebase for type safety.

- `composer run test`
    Runs Pint, PHPStan, and the test suite.

## ERD

| Color | Meaning |
| --- | --- |
| Blue | Application tables |
| Red Orange | Laravel default tables |

```mermaid
---
config:
  theme: default
---
erDiagram
	direction TB
	users {
		integer id PK ""
		varchar name  ""
		varchar email UK ""
		datetime email_verified_at  ""
		varchar password  ""
		text two_factor_secret  ""
		text two_factor_recovery_codes  ""
		datetime two_factor_confirmed_at  ""
		varchar remember_token  ""
		datetime created_at  ""
		datetime updated_at  ""
	}

	brackets {
		integer id PK ""
		integer user_id FK ""
		varchar name  ""
		varchar slug UK ""
		text description  ""
		integer size  ""
		varchar status  ""
		integer round_duration_hours  ""
		boolean is_unlisted  ""
		integer current_round  ""
		datetime completed_at  ""
		datetime created_at  ""
		datetime updated_at  ""
	}

	contestants {
		integer id PK ""
		integer bracket_id FK ""
		varchar name  ""
		varchar image_path  ""
		integer seed  ""
		datetime created_at  ""
		datetime updated_at  ""
	}

	matchups {
		integer id PK ""
		integer bracket_id FK ""
		integer round  ""
		integer position  ""
		integer contestant_one_id FK ""
		integer contestant_two_id FK ""
		integer winner_id FK ""
		boolean decided_by_coin_flip  ""
		datetime opens_at  ""
		datetime closes_at  ""
		datetime created_at  ""
		datetime updated_at  ""
	}

	votes {
		integer id PK ""
		integer matchup_id FK ""
		integer contestant_id FK ""
		varchar voter_hash  ""
		integer user_id FK ""
		datetime created_at  ""
		datetime updated_at  ""
	}

	passkeys {
		integer id PK ""
		integer user_id FK ""
		varchar name  ""
		varchar credential_id UK ""
		json credential  ""
		datetime last_used_at  ""
		datetime created_at  ""
		datetime updated_at  ""
	}

	sessions {
		varchar id PK ""
		integer user_id FK ""
		varchar ip_address  ""
		text user_agent  ""
		text payload  ""
		integer last_activity  ""
	}

	password_reset_tokens {
		varchar email PK ""
		varchar token  ""
		datetime created_at  ""
	}

	users||--o{brackets:"creates"
	users||--o{votes:"casts"
	users||--o{passkeys:"has"
	users||--o{sessions:"has"
	brackets||--o{contestants:"has"
	brackets||--o{matchups:"has"
	contestants||--o{matchups:"competes in"
	contestants||--o{matchups:"wins"
	contestants||--o{votes:"receives"
	matchups||--o{votes:"has"

	sessions:::Laravel
	password_reset_tokens:::Laravel

	classDef Laravel stroke:#FF2D20, fill:#FFD6D4, color:#BF2118
```
