# SAB editor guide

Gutenberg is disabled while the SAB theme is active. Posts, pages, milestones and leadership use the native classic editor; widgets use the classic screen.

## Edit milestones

Open **Milestones** and choose a language with Polylang. Each milestone uses the native WordPress title, editor content, featured image and Order field. ACF adds Year, optional video, optional mask, cutout treatment, cinematic layout and full-width finale treatment.

Use Order to arrange records, including records that share a year. Use Polylang's translation controls to link English and Arabic records. Add the translation before publishing the complete pair. Native title and body copy are translated independently. Shared media, year and layout fields can synchronize; editorial text must not be synchronized across languages.

The cinematic homepage and vertical timeline query the same records for the current language. Adding, unpublishing or reordering a milestone changes both views. The milestone count and timeline length derive from published records. Finale treatment is an explicit field, so moving or adding a last milestone does not accidentally turn it into the special anniversary visual.

Use a featured image as the video poster and reduced-motion fallback. If no video is supplied, the image renders. If neither is supplied, the content remains available without a broken media URL.

## Edit leadership

Open **Leadership**. Use the native title for the message heading, editor for the message body, featured image for the portrait and Order for placement. ACF provides a pull quote and optional role. Link translations through Polylang.

The Leadership navigation item uses the `sab-leadership` menu CSS class. Keep that class to retain the overlay interaction. The overlay supports Escape, focus placement, focus return and keyboard focus containment.

## Edit pages

The home page uses the native title for its document metadata and native editor for the introduction. ACF provides the formatted hero heading, hero video/poster, film/poster, introductory heading and closing section.

The timeline page uses the **SAB Timeline** template. Its native editor contains the introduction and its optional formatted hero heading supports emphasis. Keep an English and Arabic translation of each page.

Formatted headings accept `em`, `strong`, `br` and limited span markup. Other frontend copy is escaped. Use the visual content editor for leadership paragraphs.

## Theme Options

**Theme Options** contains shared branding, contact details and social links:

- Primary logo: the same native `custom_logo` setting used by Appearance → Customize → Site Identity. Updating or removing it in either screen updates the other on reload. No duplicate ACF logo value is stored.
- Dark-background logo: used by the header when the cinematic timeline is in dark mode.
- Timeline emblem: the vertical view's introductory artwork.
- Shared contact fields: phone, email, website and map URL.
- Social repeater: platform icon, accessible label, destination and ordering. An unrecognized platform uses a generic link icon.

**Languages → Translations**, group **SAB**, manages the address, contact accessibility label, establishment line, copyright, regulatory text, website label, interface labels and social accessibility labels. Existing English and Arabic values have been migrated.

The single **Theme Options** page includes a **Language logo variants** repeater for language-specific primary, dark-background and footer logos. An empty primary override inherits the native Site Logo.

The **Global source text and interface labels** panel defines editable source strings. Translate these in Polylang. Editing a source string creates a new string to translate; it does not automatically rewrite the previous translation. Native page/post content and navigation menus keep their linked Polylang translations.

No email or street address was invented during migration. Enter those fields if a separate contact display is required. Existing regulatory text was migrated as supplied and remains editable.

## Menus and media

Use Appearance → Menus to edit primary and footer menus for each language. Preserve the `sab-view-link` CSS class on the timeline item: the theme automatically turns it into the cinematic return link on the timeline page. Edit that return label under Languages → Translations, group SAB, for the **Nav Cinematic** source string.

Media is stored as attachment IDs. Replace media through the Media Library/ACF selectors; no source path editing is needed. Site title and favicon use WordPress Site Identity. Supply alt text for meaningful images; milestone artwork is decorative beside the same visible milestone copy.
