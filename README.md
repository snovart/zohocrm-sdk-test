# Zoho CRM Sync Service

This project provides a Laravel-based synchronization service that imports and updates records from Zoho CRM into local database tables.

## Overview

The synchronization system uses the Crmoz Zoho SDK to fetch data from Zoho CRM modules (`Contacts`, `Accounts`, `Leads`, and `Deals`) and upsert them into corresponding local tables.  
Synchronization is incremental — only new or modified records are imported, and existing records are updated if the remote version is newer.

## Architecture

### Main Components

- **ZohoSyncService**  
  Handles module synchronization.  
  - Iterates through paginated or cursor-based API responses.  
  - Calls the proper upsert method per module.  
  - Saves synchronization checkpoints.  

- **ZohoSdkClient**  
  Adapter for the Zoho SDK, responsible for fetching records and handling paging or cursors.  

- **Mappers**  
  Each module (Contacts, Accounts, Leads, Deals) has a dedicated Mapper class that converts Zoho API payloads into local model arrays.

- **Models**  
  Each CRM table (`crm_contacts`, `crm_accounts`, `crm_leads`, `crm_deals`) has a corresponding Eloquent model (e.g., `CrmContact`).

- **SyncState**  
  A table tracking synchronization checkpoints, including the last cursor, success timestamp, and error timestamp.

## Data Flow

1. `php artisan zoho:sync:pull <Module> --since="ISO_DATE"` command triggers synchronization.  
2. The service fetches pages of records from Zoho CRM using the SDK.  
3. Each record is mapped through its corresponding Mapper.  
4. Records are upserted into the database (`updateOrCreate` based on `zoho_id`).  
5. The `SyncState` record for the module is updated with the latest cursor and timestamp.

## Commands

### Pull records for one module

```bash
php artisan zoho:sync:pull Contacts --since="2025-01-01T00:00:00Z"
php artisan zoho:sync:pull Accounts --since="2025-01-01T00:00:00Z"
php artisan zoho:sync:pull Leads --since="2025-01-01T00:00:00Z"
php artisan zoho:sync:pull Deals --since="2025-01-01T00:00:00Z"
```

### Pull all modules at once

A helper command can be created to sync all modules at once:

```bash
php artisan zoho:sync:all --since="2025-01-01T00:00:00Z"
```

### Scheduled Sync Example

You can configure periodic synchronization via the Laravel scheduler:

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('zoho:sync:all')->everyTenMinutes();
}
```

## Upsert Logic

Synchronization does **not** truncate or overwrite tables.  
Instead, it performs **incremental upsert** logic:

- If a record does **not exist**, it is inserted.
- If a record exists and the Zoho version is **newer**, it is updated.
- If a record exists and the local version is **newer or same**, it is skipped.

Deleted Zoho records are currently ignored (soft-deletion not implemented).

## Tables

| Table | Description |
|--------|--------------|
| `crm_contacts` | Contacts from Zoho CRM |
| `crm_accounts` | Accounts from Zoho CRM |
| `crm_leads` | Leads from Zoho CRM |
| `crm_deals` | Deals from Zoho CRM |
| `sync_states` | Checkpoints for each module |

## Environment Variables

Example `.env` configuration for Zoho SDK:

```env
ZOHO_SDK_REDIS_PREFIX=z9nfosh1Wa6K70WL
ZOHO_CRM_VERIFY_TOKEN=oMfYVcohuW1pTtNHEHul5fr4srOAvWSL
ZOHO_SDK_DATACENTER=EU

ZOHO_CRM_CLIENT_ID=1000.OBA1SP6XVS1TRPLIG0YAG0OTAHMW7S
ZOHO_CRM_CLIENT_SECRET=93a9aa5de38672fc3348ae49c939f5a8112353a3f7

ZOHO_CRM_ACCOUNTS_URL=https://accounts.zoho.eu
ZOHO_CRM_API_BASE_URL=https://www.zohoapis.eu
ZOHO_CRM_DC=eu
ZOHO_REDIRECT_URI=http://127.0.0.1:8000/zoho/oauth/callback
ZOHO_REFRESH_TOKEN=1000.505d14d38e0cef7f38a19834ad79cfc8.b0b8134026bdd48970738564aa3137c8
```

## Error Handling

- Transactions are wrapped with `DB::beginTransaction()` and `DB::rollBack()` on errors.  
- Errors are logged and timestamped in `sync_states.last_error_at`.  
- Failed synchronization throws an exception.

## Logging

When `APP_DEBUG=true`, the service logs detailed events:
- Start and end of synchronization.
- Raw SDK responses and record counts.
- Exceptions with stack traces.

## License

This project is proprietary to the current integration setup.  
Use within your own Zoho CRM integration environment.
