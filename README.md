# SAB Centenary WordPress

Native WordPress theme with ACF Pro, Polylang, cinematic/vertical milestone views, leadership messages and editable global options.

## Repository layout

- `theme/sab`: complete installable theme, content types, ACF fields/options, shared slugs, importer and updater.
- `plugins/sab-content`: empty retired-plugin bridge for upgrades from older releases; not required.
- `docs`: setup, editing and verification notes.
- `tests`: WordPress integration checks.
- `tools/package.py`: builds the theme ZIP, legacy upgrade bridge and delivery bundle.

Install ACF Pro and Polylang separately. Activate the SAB Centenary theme. SAB Content is no longer needed. On a fresh installation run `wp sab setup`, then `wp sab import` in a separate request. See [setup](docs/SETUP.md) and [editor guide](docs/EDITOR-GUIDE.md).

## Translations

Use linked Polylang pages/posts for content and **Languages → Translations → SAB** for global text and interface labels. There is one shared Theme Options page for branding, contacts, social links, source strings and language logo variants. Legacy EN/AR options migrate automatically once when an administrator opens WordPress.

## GitHub → WordPress updates

Every push to `main` runs `.github/workflows/release.yml`, checks syntax, stamps the theme and retired compatibility stub with the same increasing version, and publishes their ZIPs in a GitHub Release. Code is served from `https://github.com/pmunankarmi/sab`.

Keep the **SAB Centenary theme** active. WordPress uses its native theme update API hooks to read the latest release. Available versions appear in **Dashboard → Updates**, **Appearance → Themes**, and a SAB admin notice. **Tools → SAB GitHub Updates** shows the installed/latest versions with automatic checks.

An administrator visit triggers a check at most hourly; WordPress's scheduled update checks also run normally. Detection is not instantaneous, and WordPress must be able to reach GitHub. A push creates an available update; installation still requires the administrator's normal Update action unless they separately enable WordPress auto-updates.

Each workflow run uses `major.minor.run_number`; edit `release-version.json` when intentionally changing major/minor versions. Release packages have the required `sab/` and `sab-content/` roots. Do not install GitHub's automatic repository source ZIP as a theme.

No GitHub token is required while this repository remains public. A private-repository change requires a separate authenticated update design; do not paste credentials into theme files.

Local-only credentials, WordPress installations, original reference archive and generated ZIPs are excluded from Git. ACF Pro and Polylang themselves are not redistributed here.

## Theme-only upgrade

Update the SAB theme first. It automatically deactivates the old SAB Content plugin on the next administrator request. If the old plugin already loaded in that request, its existing hooks finish normally; the theme takes over on subsequent requests. You can then delete SAB Content. Existing posts, attachments, ACF field keys, shared options and Polylang translations remain in the database unchanged. ACF Pro and Polylang remain required. Switching themes hides SAB-specific editing screens until SAB is reactivated.

The tiny `sab-content.zip` release asset is retained solely because previous updater versions expect it; it contains no content features. New installations only install `sab-theme.zip`.
