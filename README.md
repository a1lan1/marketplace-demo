# Laravel Vuetify Starter

This project is a comprehensive starter template for developing web applications with a Laravel backend and a Vuetify 3 frontend.

It comes packed with a suite of pre-configured services for rapid development, including Filament for the admin panel, Horizon for queues, Telescope for debugging, Meilisearch for search, Mailpit for local email testing, and a full monitoring stack with Prometheus and Grafana. It also includes an analytics pipeline powered by Kafka and ClickHouse for real-time event tracking.

#### The development environment is fully containerized using [Docker](https://www.docker.com/products/docker-desktop/).




## Getting Started

1. **Install dependencies and start the application containers.**

```bash
  make install
```
or
```bash
  make build
  make up
```

2. **Run migrations and seed data.**

```bash
  make migrate
```
or
```bash
  make dbs
```

3. **Start the frontend development server.**

```bash
  yarn dev
```

### CI/CD

This project includes a pre-configured CI/CD pipeline using GitHub Actions.

- **Continuous Integration (CI)**: On every `push` or `pull_request` to `main` and `develop`, a workflow runs linting (`make lint`) and tests (`make test`) inside a Docker environment. This ensures code quality and that all tests pass before merging. See `.github/workflows/ci.yml`.

- **Continuous Deployment (CD)**: A template for deploying to production is available at `.github/workflows/deploy.yml`. It is disabled by default.

  **To activate deployment:**
  1.  Go to your repository's **Settings > Secrets and variables > Actions**.
  2.  Add the following repository secrets:
      - `SSH_HOST`: Your server's IP address or domain.
      - `SSH_USER`: The username for SSH login.
      - `SSH_PRIVATE_KEY`: The private SSH key for authentication.
  3.  In `.github/workflows/deploy.yml`, uncomment the "Real Deployment" step and remove the "Demonstration" steps.
  4.  Update the `cd /path/to/your/project` line with the actual project path on your server.

### Code Quality & Linting

```bash
  make lint
```

### Shell Access

```bash
  make shell
```

### Testing

```bash
  make test
```

## Available Services

- **Application**: [http://localhost:8585](http://localhost:8585)
- **Filament Admin Panel**: [http://localhost:8585/admin](http://localhost:8585/admin)
- **Horizon Dashboard**: [http://localhost:8585/horizon](http://localhost:8585/horizon)
- **Log Viewer**: [http://localhost:8585/log-viewer](http://localhost:8585/log-viewer)
- **Laravel Telescope**: [http://localhost:8585/telescope](http://localhost:8585/telescope)
- **Meilisearch Dashboard**: [http://localhost:7700](http://localhost:7700)
- **Mailpit (Email Client)**: [http://localhost:8025](http://localhost:8025)
- **Grafana Dashboards**: [http://localhost:3000](http://localhost:3000) (user: `test@example.com`, pass: `password`)
- **Prometheus Targets**: [http://localhost:9090](http://localhost:9090)

## Links

- **[mateusjunges/laravel-kafka](https://laravelkafka.com/docs/v2.9/introduction)**: This package provides a nice way of producing and consuming kafka messages in Laravel projects.
- **[Visualising Laravel and Horizon metrics using Prometheus and Grafana](https://freek.dev/2507-visualising-laravel-and-horizon-metrics-using-prometheus-and-grafana)**: A step-by-step guide to visualising Laravel and Horizon metrics using Prometheus and Grafana.
- **[Setting up Prometheus and Grafana](https://spatie.be/docs/laravel-prometheus/v1/setting-up-prometheus-and-grafana/self-hostedfana)**: Setting up Prometheus and Grafana.
- **[Filament 4](https://filamentphp.com/docs/4.x/getting-started)**: UI framework for admin panels & apps with Livewire.
- **[Pest 4](https://pestphp.com)**: The elegant PHP testing framework.

---

## Kafka + ClickHouse + Grafana

- Topics:
    - `user_activity` - user activities.

- Producers:
    - `App\Kafka\Producers\UserActivityProducer` - publishes to `user_activity` when user activity is created.

- Consumers:
    - The Laravel application publishes events to Apache Kafka, which are then automatically consumed by ClickHouse
      using its built-in Kafka engine and materialized views for analytical processing.
    - Frontend → POST `/api/user-activities` → Kafka `user_activity` → ClickHouse `events_raw` (Kafka) → `events_mv` →
      `events` (MergeTree) → Grafana.

### ClickHouse

- Uses scripts from ./clickhouse/initdb and can be safely re-run.
- Apply manually idempotent DDL to create tables and views in ClickHouse.

```bash
  make clickhouse-apply-ddl
```

- Check data in ClickHouse.

```bash
  docker compose exec clickhouse clickhouse-client -q "SELECT ts, event_type, page FROM events ORDER BY ts DESC LIMIT 20"
```

### Frontend auto-tracking

- Enabled in `resources/js/app.ts` via `initActivityAutoTrack()`.
- Events:
    - `page_view` — on initial load and Inertia navigations.
    - `click` — automatically for elements with `data-track="click"`.

Manual events (example):

```ts
import {trackClick, trackSignIn, trackSignUp, trackError} from '@/composables/useActivity'

trackClick(undefined, {button: 'buy', productId: 123})
trackSignIn(undefined, {method: 'password'})
trackSignUp(undefined, {method: 'google'})
trackError('Checkout failed', {code: 'E_CHECKOUT'})
```
