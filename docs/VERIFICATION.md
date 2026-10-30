# Verification

Validated in the local SAB installation:

- Theme PHP files and browser scripts pass syntax checks.
- 36 English and 36 Arabic milestones; two leadership records per language.
- Translation pairs share slugs and retain linked language records.
- Both homepages and timeline pages return HTTP 200, with dynamic data and Arabic RTL markup.
- Native featured images and imported media are attached to records.
- Theme Options logo changes/removal and native logo changes/removal resolve to the same setting; no duplicate logo option is written.
- Reimport creates no duplicate records or attachments.
- Desktop browser: English/Arabic navigation, leadership overlay opening/closing and focus placement, and both timeline layouts.
- Browser console checked for JavaScript errors during the initial desktop overlay test.
- Admin dashboard and Theme Options branding screen render correctly.

The in-app browser's mobile viewport override did not change its actual 1280px viewport. Mobile CSS and the menu implementation are included, but a real mobile browser visual pass remains to be performed. Screenshots in this directory document desktop views.

The local PHP worker's external HTTP crash was isolated with a local-only MU plugin; it is not included in the theme or content-plugin ZIPs. Production hosting, HTTPS, cache behavior, plugin updates and production ACF license activation have not been tested or deployed.

GitHub updater checks cover native theme/plugin responses, version metadata, package URL validation, unrelated plugin/theme isolation, and prerelease rejection.

The first GitHub Actions release completed successfully. The real GitHub release was detected by local WordPress, with both native theme and content-plugin update responses present. No available update was installed during verification.

A native AJAX plugin update was tested successfully after correcting Local’s macOS NAT64 DNS crash with local-only IPv4 resolution. TLS verification and macOS fork protections remain enabled. The stale maintenance flag left by the failed update was cleared.

Global text refactor: 70 existing English/Arabic values were verified after migration into Polylang; both language logo variants were preserved. A temporary Polylang catalogue edit reached both Arabic views and was restored. Re-running migration did not overwrite translations. Old theme field reads were verified against the new catalogues.
