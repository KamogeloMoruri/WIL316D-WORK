# WIL316D-WORK
# AI Donation Tracker

A small app for recording and tracking charity donations, with an AI
assistant that can answer questions about the data and a Cardano
transaction ID checker.

This copy has been made **portable**: it needs almost no setup on a new
machine, and in particular it does **not** require MySQL or any manual
database setup. If Docker is available, it doesn't even require PHP to be
installed at all.

## What was fixed / added from the original

- `save_donation.php`, `get_donations.php`, `ai_assistant.php` and
  `verify_transaction.php` — the PHP backend was missing entirely; the
  frontend was calling `save_donation.php` but it didn't exist.
- `app.js` referenced a `purpose` field that didn't exist in the HTML — a
  Purpose dropdown was added to the form, and the JS is now wired to
  `getElementById("donationForm")` (it was silently failing on `querySelector("form")`
  if more than one form existed).
- The donations table and total are now loaded dynamically from the
  database instead of being hard-coded.
- The AI Assistant and "Verify Transaction" buttons had no JS behaviour at
  all — both are now functional.
- Uses **SQLite** (a single file, `data/donations.db`) instead of MySQL, so
  there's no database server to install or configure — the file and table
  are created automatically the first time the app runs.

## Option 1: Run with Docker (recommended — no PHP install needed)

This is the easiest path if PHP has been giving you trouble locally —
Docker runs PHP inside a container, so your system doesn't need PHP at all.

1. Install [Docker Desktop](https://www.docker.com/products/docker-desktop/) if you don't have it.
2. In this folder, run:
   ```
   docker compose up --build
   ```
3. Open **http://localhost:8000/website.html** in your browser.

Or just double-click `start.sh` (Mac/Linux) or `start.bat` (Windows) — they
detect Docker automatically and fall back to a local PHP install if Docker
isn't present.

To stop it, press `Ctrl+C`, or run `docker compose down`.

## Option 2: Run with a local PHP install

1. Install PHP 8+ if it isn't already installed:
   - **Windows**: install [XAMPP](https://www.apachefriends.org/) (includes PHP), or PHP directly from https://windows.php.net/download/
   - **Mac**: `brew install php`
   - **Linux**: `sudo apt install php php-sqlite3` (Debian/Ubuntu) or your distro's equivalent
2. Check the `pdo_sqlite` extension is enabled (it's on by default in most
   installs): `php -m | grep sqlite`
3. From this project folder, run PHP's built-in server:
   ```
   php -S localhost:8000
   ```
4. Open **http://localhost:8000/website.html** in your browser.

No database setup step is needed — `data/donations.db` is created
automatically on first use.

## Optional: connecting a real AI model / real Cardano lookups

By default, **no API keys are required** — the AI Assistant answers
questions using the donation data directly (totals, counts, averages, top
donor, breakdown by purpose), and the Cardano checker validates that an ID
is correctly formatted.

To upgrade either feature:

- **AI Assistant**: set an `ANTHROPIC_API_KEY` environment variable to have
  it call the Claude API for richer answers.
- **Cardano verification**: set a `BLOCKFROST_API_KEY` (free from
  [blockfrost.io](https://blockfrost.io)) to have it check the transaction
  ID against the real Cardano blockchain.

With Docker, add these to a `.env` file next to `docker-compose.yml`:
```
ANTHROPIC_API_KEY=sk-ant-...
BLOCKFROST_API_KEY=mainnet...
```
Without Docker, set them as normal environment variables before running
`php -S localhost:8000`.

## Project structure

```
website.html          the page
style.css              styling
app.js                 frontend logic (fetches, form handling)
index.html             redirects to website.html
config.php              shared DB connection (creates the SQLite file/table)
save_donation.php       POST endpoint: add a donation
get_donations.php       GET endpoint: list donations + total
ai_assistant.php        POST endpoint: answer a question about the data
verify_transaction.php  POST endpoint: check a Cardano transaction ID
data/                  the SQLite database lives here (auto-created)
docker-compose.yml      one-command Docker setup
Dockerfile              PHP + required extensions
start.sh / start.bat    convenience launch scripts
```
