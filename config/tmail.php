<?php

return [

    /*
    |--------------------------------------------------------------------------
    | TMail Performance Optimization Settings
    |--------------------------------------------------------------------------
    |
    | These settings control various performance optimizations for email
    | fetching in TMail. All features can be enabled/disabled independently
    | for easy rollback if issues occur.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | UNSEEN Filter
    |--------------------------------------------------------------------------
    |
    | When enabled, only fetch emails that are marked as UNSEEN (unread) from
    | the IMAP server. This significantly reduces the number of emails to
    | process on each fetch, especially for inboxes with many old emails.
    |
    | Impact: ~40% faster fetching for inboxes with many read emails
    |
    */
    'enable_unseen_filter' => env('TMAIL_ENABLE_UNSEEN_FILTER', true),

    /*
    |--------------------------------------------------------------------------
    | Lazy Load Attachments
    |--------------------------------------------------------------------------
    |
    | When enabled, attachments are not downloaded during initial email fetch.
    | Instead, only metadata (filename, size, type) is saved. Attachments are
    | downloaded on-demand when user clicks to view/download them.
    |
    | Note: Inline images (cid:) are always downloaded to maintain email display.
    |
    | Impact: ~30% faster initial fetch for emails with attachments
    |
    */
    'enable_lazy_load' => env('TMAIL_ENABLE_LAZY_LOAD', true),

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | When enabled, only load a limited number of emails initially (default 20).
    | Additional emails can be loaded on-demand via "Load More" button or
    | infinite scroll.
    |
    | Impact: ~50% faster initial page load
    |
    */
    'enable_pagination' => env('TMAIL_ENABLE_PAGINATION', true),
    'pagination_limit' => (int) env('TMAIL_PAGINATION_LIMIT', 20),

    /*
    |--------------------------------------------------------------------------
    | Domain Cache
    |--------------------------------------------------------------------------
    |
    | When enabled, blocked and allowed domains are cached in memory (static
    | property) instead of reading from config on every email. Cache is
    | automatically reset per request in PHP-FPM.
    |
    | Impact: ~10% faster email formatting
    |
    */
    'enable_domain_cache' => env('TMAIL_ENABLE_DOMAIN_CACHE', true),

    /*
    |--------------------------------------------------------------------------
    | Memory Bypass
    |--------------------------------------------------------------------------
    |
    | When enabled, emails that are already loaded in the UI are not re-parsed
    | from IMAP. This makes subsequent fetches nearly instant for old emails.
    |
    | Impact: ~90% faster for emails already in memory
    |
    */
    'enable_memory_bypass' => env('TMAIL_ENABLE_MEMORY_BYPASS', true),

    /*
    |--------------------------------------------------------------------------
    | Batch Inline Images
    |--------------------------------------------------------------------------
    |
    | When enabled, inline images (cid:) are processed in a single batch
    | str_replace() operation instead of individual replacements per image.
    |
    | Impact: ~15% faster for emails with multiple inline images
    |
    */
    'enable_batch_inline' => env('TMAIL_ENABLE_BATCH_INLINE', true),

];
