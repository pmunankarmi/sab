# SAB WordPress setup

The implementation uses a native PHP theme, ACF Pro and Polylang. No separate frontend application or build service is required.

## Local review

- Local application site: **SAB**
- WordPress directory: `/Users/puskar/Documents/Sites/SAB/app/public`
- English homepage: http://sab.local/en/home/
- Arabic homepage: http://sab.local/ar/home/
- English timeline: http://sab.local/en/timeline/
- Arabic timeline: http://sab.local/ar/timeline/
- Dashboard: http://sab.local/wp-admin/
- Local credentials: `.local-access.txt` in the SAB source workspace, excluded from Git and delivery ZIPs.

Start **SAB** in Local before visiting these URLs. Source is maintained in `/Users/puskar/Documents/ChatGPT/SAB`. `python3 tools/sync-local.py` copies the theme and content plugin to the local installation. This requires filesystem access to the installation directory.

Local uses PHP 8.2.29, WordPress 7.1.4, ACF Pro 6.8.6 and Polylang 3.6.2. ACF Pro was copied from your existing local test installation; no license or license database was copied. A local-only MU plugin prevents external HTTP requests because the macOS Local PHP worker aborted during dashboard requests to external services. It allows local, GitHub and WordPress update-service requests; external requests that are allowed use the streams transport. This helper is excluded from the delivery packages. Do not deploy it to staging or production.

## Install on a fresh WordPress site

1. Install and activate your licensed ACF Pro and Polylang.
2. Install `sab-content.zip` under Plugins and activate it.
3. Install `sab-theme.zip` under Appearance → Themes and activate SAB Centenary.
4. With WP-CLI, run these as separate commands from the WordPress directory:

   ```sh
   wp sab setup
   wp sab import
   ```

`setup` creates English and Arabic and configures pretty permalinks with language directories. `import` creates pages, translation pairs, milestones, leadership records, menus, options and Media Library attachments from the supplied source. It sets the English homepage and links its Arabic translation.

The importer is intended for a fresh SAB installation. Back up an existing site before importing. Re-running the importer skips existing imported records and initialized options, preserving editorial changes and preventing duplicate media. It does not reset the site. Do not use the generic WordPress importer to recreate shared-slug translation pairs.

To migrate the already reviewed Local site instead, move its database and uploads together with the theme and plugin, then perform a serialization-aware URL replacement. Do not copy `wp-config.php`, Local-specific MU plugins, local credentials or server configuration to production.

## Content and configuration ownership

- Theme: templates, CSS, fonts, decorative SVGs and browser interactions.
- SAB Content plugin: custom post types, ACF schemas, option pages, shared-slug compatibility and migration commands.
- Database and Media Library: all visitor-facing copy, media, logos, navigation, links and translations.
- `seed/content.json` and `seed/media`: migration inputs only. Frontend templates never read seed content or use it as a fallback.

ACF JSON schemas are in `plugins/sab-content/acf-json`. Language-specific option schemas are generated from `seed/option-fields.json` for each configured language. Preserve field keys if extending the schemas.

## Shared slugs

`includes/polylang-slug.php` reuses the GPL helper from your Ghalya project. Its duplicate-load guard now lives in the plugin loader so the file actually registers its filters. SAB skips this helper if Polylang Pro is active or a standalone copy already defines its function. The helper retains the reserved-slug and SQL-match guards.

English and Arabic translations have matching slugs, such as `/en/timeline/` and `/ar/timeline/`. Language directories remain necessary to distinguish translations. Re-test this compatibility helper when upgrading WordPress or Polylang.

## Validation commands

On this computer, from the source workspace:

```sh
tools/local-wp eval-file tests/integration.php
tools/local-wp eval-file tests/dynamic-content.php
python3 tools/package.py
```

The integration suite checks translation links, shared slugs, images, four frontend URLs, RTL output, repeat imports and the native logo adapter. The dynamic-content test temporarily edits a milestone title and creates a temporary milestone, verifies both views, and restores its changes.

## Future GitHub updates

The source repository is https://github.com/pmunankarmi/sab. Every push to `main` automatically builds and publishes a versioned GitHub Release containing the correctly rooted `sab-theme.zip` and `sab-content.zip` packages. Keep SAB Content active for both update integrations.

WordPress checks the public release API during its normal update checks and at most hourly on an administrator visit. Available updates appear in Dashboard → Updates and the native theme/plugin screens. Tools → SAB GitHub Updates shows the automatic check and release status. This is polling, not an instantaneous webhook. Website files are installed only by the administrator's Update action unless they separately enable WordPress auto-updates.

On this Local installation, the local-only HTTP helper allows GitHub API and release downloads through WordPress's streams transport, avoiding the Local cURL worker crash while keeping TLS certificate verification enabled. Other external services remain isolated locally. None of this Local-only configuration is shipped in the installable packages.
