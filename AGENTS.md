# AGENTS.md — Moloni for Magento 2

Guidance for AI coding agents (Claude Code, Cursor, Copilot, …) and new developers working in this module. This is the canonical context file; `CLAUDE.md` imports it.

## What this plugin is

**Moloni for Magento 2** (Composer package `moloni/magento2`, Magento module `Invoicing_Moloni`, PHP namespace `Invoicing\Moloni\`) is a Magento 2 module that connects a Magento store to Moloni (moloni.pt) invoicing. It turns Magento orders into Moloni fiscal documents (invoices, invoice-receipts, simplified invoices, estimates, …, optionally preceded by a bill of lading), creates the customers, products, categories, payment methods and delivery methods those documents need, pushes single products to Moloni, and pulls price/stock changes back from Moloni on a cron. It talks to Moloni **only over the public API**, at **`https://api.moloni.pt/v1/`** (the base URL hard-coded in `Moloni::API_URL` and `ApiSession::API_URL`). It does not run alongside any Moloni platform or call internal services. The README still labels it **BETA**.

**Supported versions:** Magento **2.4.x** on PHP **7.3 – 8.x**. `composer.json` declares **no** `magento/framework` or `php` constraint (it only pins `config.platform.php` to `8.0`, which affects dependency resolution only), and `etc/module.xml` declares no `<sequence>`. The code uses `JsonException` (PHP ≥ 7.3) and no 7.4+/8.x-only syntax. Keep it that way.

## Responsibilities & boundaries

**Owns** (reacts to Magento events/observers, cron and admin actions, never webhooks):
- **Automatic document generation:** the `sales_order_save_after` event (`etc/events.xml`) → `Observer\SalesOrderAfterSave`. It only acts when there is a valid Moloni session, the `document_auto` setting is on, and the order's **state** is in the `orders_statuses` setting. It skips orders that already have a row in `moloni_documents`, then calls `Libraries\MoloniLibrary\Controllers\Documents::createDocumentFromOrderId()`.
- **Manual document generation:** admin **Sales › Moloni › Encomendas** (`Controller\Adminhtml\Home\Index`, a pending-orders grid: `view/adminhtml/ui_component/moloni_orders_grid.xml`, filtered by `orders_since` + `orders_statuses`), the "Moloni" column on the core sales order grid (`Ui\Component\Listing\Column\Moloni`) and the button on the order view (`Block\Adminhtml\Buttons\Sales\Document`, an `adminhtml` plugin on `Magento\Sales\Block\Adminhtml\Order\View`). All of these route to `Controller\Adminhtml\Documents\Create` / `MassCreate`. A second issue for the same order needs an explicit `force=1` link (`Controller\Adminhtml\Documents::documentExists()`).
- **"Don't generate" (discard):** `Controller\Adminhtml\Documents\Remove` / `MassRemove` insert a placeholder `moloni_documents` row (`invoice_id = 0`, `invoice_status = -1`, `invoice_type = 'Anulada'`), so the order drops out of the pending list and the observer skips it. Nothing is sent to Moloni.
- **Product push (manual):** the "sync" button on the product edit form (`view/adminhtml/ui_component/product_form.xml` → `Block\Adminhtml\Buttons\Products\SyncButton`) → `Controller\Adminhtml\Sync\Product` → `Controllers\Products::syncProductFromId()`. It inserts the product in Moloni if its SKU (first 30 chars) isn't found. If the SKU exists it does **not** push: it pulls price/stock from Moloni into Magento (`syncProductFromMoloni`) per the sync settings.
- **Product pull (cron):** job `products_sync` in the custom cron group `moloni` (`etc/crontab.xml`, `etc/cron_groups.xml`, every minute) → `Cron\ProductsSync`. It calls `products/getModifiedSince` from the stored `cron_date`, updates Magento price and/or stock when `products_sync_price` / `products_sync_stock` is on, then stamps `cron_date`.
- **Products created on the fly:** while building a document, every order line whose SKU isn't in Moloni is inserted (with its category tree, tax and, for bundles/composites, child products), and so is the `Portes` shipping product.
- **Admin UI:** the login → company-select → settings flow (`Controller\Adminhtml\Home\Welcome`, `Home\Company`, `Settings\Edit`), the order-view "Documents" tab (`Block\Adminhtml\Order\View\Tab\Documents`, `sales_order_view_document_grid`), and a request-log console (`Block\Adminhtml\Console\Log`).
- **Storefront:** a "Documents" link on the customer's order page (`view/frontend/layout/sales_order_info_links.xml` → `moloni/documents/view`, `Controller\Documents\View` + `Block\Documents\DocumentsList`). It lists the **closed** Moloni documents whose `your_reference` equals the order increment id, with PDF links.

**Must not:**
- **double-issue a document.** The `moloni_documents` row per order (`DocumentsRepository::getByOrderId`) is the only idempotency guard, for both the observer and the admin actions. Never bypass it, and keep the `force=1` re-issue path an explicit, merchant-confirmed action.
- invent tax/exemption/document-type mappings. Those are fiscal-legal.
- hard-code credentials, a company or an endpoint. The OAuth app credentials (developer id / client secret / redirect URI) are entered by the merchant and stored in `moloni_tokens`.
- issue (close) a document whose totals don't match the order. `validateDocumentTotals()` (< €0.02 difference) keeps it a draft, and that behaviour is load-bearing.
- **Not supported, by design: refunds / credit notes.** There is no creditmemo observer and refunds are not synced to Moloni (`creditNotes` exists only as a document-type mapping). Don't add refund sync as a side effect of another change.

**Moloni API surface used** (all `POST <API_URL><resource>/<method>/?human_errors=true&access_token=…`, via `Moloni::execute()`): `grant` (authorization_code + refresh_token, GET, in `ApiSession`), `authorize` (login redirect URL), `companies/getAll|getOne`, `customers/getByVat|getByEmail|getNextNumber|insert|update`, `products/getAll|getByReference|getModifiedSince|insert|update`, `productCategories/getAll|insert`, `taxes/getAll`, `taxExemptions/getAll`, `measurementUnits/getAll`, `documentSets/getAll`, `countries/getAll`, `paymentMethods/getAll|insert`, `deliveryMethods/getAll|insert`, `documents/getOne|getPdfLink` plus the per-type `<documentType>/getAll|insert|update` (type from `Classes\Documents::setDocumentType()`: `invoices`, `invoiceReceipts`, `simplifiedInvoices`, `billsOfLading`, `deliveryNotes`, `estimates`, `creditNotes`, `receitps` [sic], and `documents` for the generic list).

## Architecture map

| Path | What it is |
| --- | --- |
| `registration.php` | Registers module `Invoicing_Moloni` |
| `composer.json` | Package `moloni/magento2` (type `magento2-module`, `version` field = release version), PSR-4 `Invoicing\Moloni\` → root. No runtime dependencies |
| `etc/module.xml` | Module declaration (`setup_version="1.1.01"`) |
| `etc/events.xml` | `sales_order_save_after` → `Observer\SalesOrderAfterSave` |
| `etc/crontab.xml`, `etc/cron_groups.xml` | `products_sync` job, every minute, in its own `moloni` cron group (separate process) |
| `etc/di.xml` | Interface preferences, the admin grid data providers/collections, and the two Monolog loggers |
| `etc/adminhtml/` | Admin route `moloni` (`routes.xml`), menu under **Sales › Moloni** (`menu.xml`), order-view button plugin + custom message renderers (`di.xml`) |
| `etc/acl.xml` | ACL `Invoicing_Moloni::home|documents|settings` |
| `etc/frontend/routes.xml` | Storefront route `moloni` |
| `Observer/SalesOrderAfterSave.php` | Automatic document creation |
| `Cron/ProductsSync.php` | Moloni → Magento price/stock pull |
| `Controller/Adminhtml/` | Admin actions: `Home/` (welcome/login, company select, pending orders), `Documents/` (create, mass create, remove, mass remove), `Settings/` (edit, save), `Sync/Product`, `Ajax/Documents` (stub that returns `[]`). Base classes `Home.php`, `Documents.php`, `Settings.php` |
| `Controller/Documents/View.php` | Storefront "Documents" page for a customer order |
| `Libraries/MoloniLibrary/Moloni.php` | **The Moloni API client** (`execute($url, $body)`), session check, default + per-company settings, request log; lazy `__get` accessors for the `Classes\*` resource wrappers (`$moloni->products`, `->documents`, …) |
| `Libraries/MoloniLibrary/Dependencies/ApiSession.php` | OAuth `grant` (authorization code + refresh), token expiry handling |
| `Libraries/MoloniLibrary/Dependencies/ApiErrors.php` | Collected API/validation errors (`throwError`, `getErrors('first'|'last')`) |
| `Libraries/MoloniLibrary/Classes/` | Thin per-resource API wrappers (one class per Moloni resource) |
| `Libraries/MoloniLibrary/Controllers/` | Mapping/use-case layer: `Documents` (order → document, shipping document, payments, delivery, totals check), `Customers` (order billing → Moloni customer), `Products` (order line / product → Moloni product, shipping line, categories, tax lookup, Moloni → Magento sync), `Tools` (countries, PT VAT and zip validation) |
| `Model/`, `Api/` | Repositories + data interfaces for the module tables (`Tokens`, `Settings`, `Documents`), plus admin grid collections (`Model/ResourceModel/Orders`, `OrdersDocuments`, `Documents/Grid`) |
| `Setup/` | `InstallSchema` / `UpgradeSchema` create `moloni_tokens`, `moloni_settings`, `moloni_documents`. `InstallData` / `UpgradeData` are no-ops |
| `Ui/` | Settings form data provider + option sources (`Ui/Settings/`), grid action columns (`Ui/Component/Listing/Column/`), order-documents provider |
| `Block/` | Admin blocks (home, company, documents, console log, buttons, order tab) and the storefront documents list |
| `Logger/` | `moloni.sync` and `moloni.documents` loggers → `var/log/moloni.sync.log`, `var/log/moloni.documents.log` |
| `view/adminhtml/`, `view/frontend/` | Layouts, UI components, templates, LESS (`web/css/source/_module.less`), JS, icon font |
| `i18n/` | `pt_PT.csv`, `en_US.csv` |

No git submodules. `vendor/` and `composer.lock` are **gitignored**. There are no runtime Composer dependencies (only `ext-json`); inside a store the Magento autoloader loads the module.

## Running locally

No local host stack is committed (no `docker-compose`, no dev guide). Use any local Magento 2.4 store:

1. **Magento host:** a local Magento 2.4.x store (e.g. Docker) on a supported PHP version.
2. **Install the module:** check this repo out at `app/code/Invoicing/Moloni/` in the store and run `composer install` for its dependencies (merchants install it with `composer require moloni/magento2`, per `README.md`).
3. `bin/magento module:enable Invoicing_Moloni --clear-static-content`, then `bin/magento setup:upgrade` and `bin/magento cache:flush` (add `bin/magento setup:di:compile` in production mode or after constructor changes).
4. **Cron** (only for the product pull): run `bin/magento cron:run` (or `cron:run --group=moloni`).
5. In admin open **Sales › Moloni**, enter the Moloni developer id, client secret and redirect URI of a Moloni API app, authorize, and pick the company. Then fill **Configurações**: document set, maturity date and measurement unit are required. Credentials, tokens and settings are entered in the module's admin and live in its DB tables. They are never committed.

## Commands

| Command | Where | What it does |
| --- | --- | --- |
| `composer install` | module root | Install the module's Composer deps / autoloader (none at runtime beyond `ext-json`) |
| `bin/magento setup:upgrade` | Magento root | Register the module / run its schema setup |
| `bin/magento setup:di:compile` | Magento root | Regenerate DI (needed after constructor changes in production mode) |
| `bin/magento cache:flush` | Magento root | Flush config/layout caches after XML changes |
| `bin/magento cron:run --group=moloni` | Magento root | Run the products sync cron once |
| `php -l <file>` | module root | Syntax check (the only static check available) |

## Verification

**There is no automated test or lint gate.** The repo has no `phpunit`, `phpcs` or `phpstan` config and no CI workflow. Verification is `php -l` plus exercising the flow in a local store:

1. `php -l` every touched PHP file, and keep to syntax that runs on PHP 7.3 (the code currently avoids 7.4+ features: no typed properties, arrow functions, `match`, nullsafe `?->`, enums or `readonly`).
2. After XML/DI/constructor changes: `setup:upgrade` + `setup:di:compile` + `cache:clean` in the local store. A DI error only shows up there.
3. **Exercise the behaviour in the local store**: place an order → automatic document (with `document_auto` on and the order in a configured status), manual create from the pending grid / order view, discard, the shipping-document option, product sync button, the cron pull, settings save, and the storefront "Documents" page. Check `var/log/moloni.documents.log` / `moloni.sync.log`. Use a Moloni test company and keep documents as **drafts** (`document_status = 0`) unless closing them is the point of the test.

## Conventions

- **Magento idioms:** constructor DI only (factories for the per-call `Controllers\Documents` / `Controllers\Products`), events/observers in `etc/events.xml`, admin plugins in `etc/adminhtml/di.xml`, UI components for forms/grids, `__()` for strings, `messageManager` for admin feedback.
- **All API calls go through `Moloni::execute('<resource>/<method>', $values)`**, normally via a `Classes\*` wrapper reached as `$this->moloni-><resource>`. `company_id` is added by the wrapper. Errors are collected in `ApiErrors` (read `getErrors('first')`), and methods return `false`/`0` rather than throwing.
- **Settings** are per-company rows in `moloni_settings`, loaded by `Moloni::checkActiveSession()` into `$moloni->settings` (defaults in `Moloni::$settings`). Always call `checkActiveSession()` before reading settings or calling the API. `orders_statuses` is stored as JSON.
- **New setting:** add its default to `Moloni::$settings`, the form field to `view/adminhtml/ui_component/moloni_settings_edit.xml`, and the value to `Ui/Settings/DataProvider.php`. `Settings\Save` persists the `general` / `products` / `orders` / `sync` form groups as they are.
- **Merchant-facing UI uses Magento admin conventions** (UI components, admin grid, message manager). Match what `view/adminhtml/` already renders.
- **User-facing strings are pt-PT** in the code, wrapped in `__()`, with `i18n/*.csv` translations.
- **Indentation is 4 spaces** (PSR-2 style) in all PHP (`.editorconfig` has a `[*.php]` override for this).
- **Release bump:** the release version is the `version` in `composer.json` (one-line "Release x.y.z" commits, `vX.Y.Z` tags). `etc/module.xml` `setup_version` is not the release version. It only matters for schema migrations (see Gotchas).
- **Branch flow:** cut feature branches from `devel` and open PRs against `devel`. A release merges `devel` into `master` and is tagged `vX.Y.Z`, with the `composer.json` `version` bumped.
- **Idempotency + fiscal correctness are load-bearing.** Document creation must not double-issue, and tax/document-type/exemption mappings are fiscal-legal.

## Gotchas

Curated, verified conventions and traps. This is the default-trusted source — every agent reads it.

- **The API base is v1, not v2.** `Moloni::API_URL` and `ApiSession::API_URL` are both `https://api.moloni.pt/v1/`. Auth is the OAuth **authorization_code** flow (`authorize` → `grant`), not password grant. Access tokens are treated as valid for 3000 s, refresh tokens for 5 days past that; after that the tokens are deleted and the merchant must log in again.
- **The order-status filter compares the order *state* to a list of *statuses*.** `SalesOrderAfterSave` checks `in_array($order->getState(), settings['orders_statuses'])`, but the setting's options are order **statuses** (`Ui/Settings/Options/OrdersStatuses`), and the admin grids filter on `grid.status`. Custom statuses whose code differs from their state never auto-generate.
- **The observer fires on every order save.** `sales_order_save_after` runs for every save (status changes, comments, invoicing in Magento…). The `moloni_documents` row is the only thing preventing a second document, and it is written **after** the API insert/close (`setDocumentHasCreated`), so a failure in between can leave a Moloni document with no local row.
- **The shipping-document option is not atomic.** With `shipping_document = 1`, a bill of lading (`billsOfLading`) is inserted and **closed** first, then associated to the main document. If the main insert fails, the closed bill of lading stays in Moloni and the order is still "pending", so a retry issues another one.
- **The shipping line is the `Portes` product** (`Controllers\Products::setShippingFromOrder()`, added when `getShippingAmount() > 0`). The document line's `name` is the order's `getShippingDescription()` (e.g. "Flat Rate - Fixed") and its `reference` is `Portes`. The line points to the Moloni product found by `products/getByReference` (`reference = 'Portes'`, `exact`). Only if none exists does `createShippingFromOrder()` insert one: name = that order's shipping description, reference `Portes`, `type = 2` (service), `has_stock = 0`, category `Portes` (created if missing), price = base shipping amount, the default measurement unit, and the shipping tax (from the order's `shipping` tax item, `shipping_tax_exemption` when 0%, overridden by `shipping_tax`). The Moloni product's name is therefore the first order's carrier description, while each line carries its own order's description.
- **The shipping line's price/discount use mismatched sources.** `price` = `getBaseShippingAmount()` (base currency) but `price_with_taxes` = `getShippingInclTax()` (order currency). `discount` is set from `getShippingDiscountAmount()`, an **amount**, used as a **percentage** (flagged as unfinished in a code comment).
- **`getTaxIdFromRate()` never creates taxes.** It looks for a Moloni tax with that value in the fiscal zone (from Magento's `tax/calculation/based_on`: shipping, billing or origin = company country) and prefers "IVA Normal". Failing that it silently falls back to the company's `active_by_default` tax, then retries with 23%. An unmatched rate therefore lands on a **different** tax rather than erroring. The totals check is what catches it (the document stays a draft with a warning).
- **Product identity is the SKU truncated to 30 chars** (`mb_substr($sku, 0, 30)`, Moloni's reference limit). Two Magento SKUs sharing a 30-char prefix map to the same Moloni product.
- **Customers:** an empty VAT, or (for PT) an invalid one, becomes `999999990` (final consumer), matched by **email**. Otherwise customers are matched by VAT. An existing customer is **updated** with the order's billing data on every document.
- **Missing payment and delivery methods are created in the merchant's Moloni company**, matched by name (case-insensitive) against Magento's payment title and shipping description.
- **Non-EUR orders:** for a Portuguese company (`country_id = 1`) the document gets `exchange_currency_id = 1` and the Magento store rate to EUR. Non-PT companies are unhandled (flagged as undecided in a code comment).
- **The request log contains the access token.** `Moloni::execute()` stores each full request URL (with `access_token`) plus the payloads in the `moloni_logs` data persistor, shown by the admin console block. OAuth `grant` URLs carry the client secret in the query string. Never log or surface these anywhere new.
- **`etc/di.xml` preferences for the API interfaces point at a non-existent namespace** (`Magento\Framework\Libraries\MoloniLibrary\…`). Nothing injects those interfaces; everything type-hints the concrete `Invoicing\Moloni\Libraries\MoloniLibrary\Moloni`. Don't start injecting `MoloniApiRepositoryInterface` without fixing them.
- **Schema setup is imperative and version-gated.** `InstallSchema` creates the three tables on a fresh install; `UpgradeSchema` only runs its body when the stored version is `< 1.3.1` (it creates any missing table), while `module.xml` says `setup_version="1.1.01"`. A new table or column needs a `setup_version` bump **and** a new `version_compare` branch in `UpgradeSchema` **and** the same change in `InstallSchema`. Otherwise existing stores or fresh installs miss it.
- **`view/frontend/layout/sales_order_view_xml` has no `.xml` extension**, so Magento ignores it. It is dead, not a live layout.
- **No submodules** (`.gitmodules` absent).

> Recent, **unverified** findings live in `.claude/journal/` (one dated file per finding). They are promoted into this section by manual curation once re-verified against the code — treat them as leads, not yet rules.
