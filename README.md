# SAB Centenary WordPress

Native WordPress theme with ACF Pro, Polylang, cinematic/vertical milestone views, leadership messages and editable global options.

## Repository layout

- `theme/sab`: installable theme source.
- `plugins/sab-content`: content types, ACF fields/options, shared slugs, migration inputs and the GitHub updater.
- `docs`: setup, editing and verification notes.
- `tests`: WordPress integration checks.
- `tools/package.py`: builds the two installable ZIPs and delivery bundle.

Install ACF Pro and Polylang separately. Activate SAB Content and the SAB Centenary theme. On a fresh installation run `wp sab setup`, then `wp sab import` in a separate request. See [setup](docs/SETUP.md) and [editor guide](docs/EDITOR-GUIDE.md).

## Translations

Use linked Polylang pages/posts for content and **Languages → Translations → SAB** for global text and interface labels. There is one shared Theme Options page for branding, contacts, social links, source strings and language logo variants. Legacy EN/AR options migrate automatically once when an administrator opens WordPress.

## GitHub → WordPress updates

Every push to `main` runs `.github/workflows/release.yml`, checks syntax, stamps the theme/plugin with the same increasing version, and publishes their ZIPs in a GitHub Release. Code is served from `https://github.com/pmunankarmi/sab`.

Keep **SAB Content** active. WordPress uses its native theme/plugin update API hooks to read the latest release. Available versions appear in **Dashboard → Updates**, **Appearance → Themes**, **Plugins**, and a SAB admin notice. **Tools → SAB GitHub Updates** shows the installed/latest versions with automatic checks.

An administrator visit triggers a check at most hourly; WordPress's scheduled update checks also run normally. Detection is not instantaneous, and WordPress must be able to reach GitHub. A push creates an available update; installation still requires the administrator's normal Update action unless they separately enable WordPress auto-updates.

Each workflow run uses `major.minor.run_number`; edit `release-version.json` when intentionally changing major/minor versions. Release packages have the required `sab/` and `sab-content/` roots. Do not install GitHub's automatic repository source ZIP as a theme.

No GitHub token is required while this repository remains public. A private-repository change requires a separate authenticated update design; do not paste credentials into theme files.

Local-only credentials, WordPress installations, original reference archive and generated ZIPs are excluded from Git. ACF Pro and Polylang themselves are not redistributed here.
