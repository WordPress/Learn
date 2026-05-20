# ACF Integration

Integration with Advanced Custom Fields Pro, bootstrapped in `acf/load.php` with `Main` main object.
All names take place in `WP_Syntex\Polylang_Pro\Integrations\ACF` namespace.

## How field translation works

### For standard custom fields

`Dispatcher` applies strategies to custom fields of `Entity\Abstract_Object` objects (posts or terms) as well as ACF blocks.
These strategies follow the Strategy Pattern to make fields benefit from Polylang Pro core features such as:

| Feature | Strategy |
| --- | --- |
| New post translation creation | `Strategy\Copy` |
| Synchronization on post/term update | `Strategy\Synchronize` |
| Bulk translate | `Strategy\Copy` or `Strategy\Copy_All` |
| Export, import and machine translation | `Strategy\Export`, `Strategy\Import` and `Strategy\Abstract_Collect_Ids` |

### For block fields

Import, export, machine translation and smart synchronization for fields containing IDs are supported thanks to `Entity\Blocks` class. This class is able to apply any strategy to a list of blocks thanks to `Entity\Blocks::apply_on_blocks()`.

### Note on language location

To provide end users a way to have field groups displayed only for specific language, a custom location is provided with `Location\Language`.

## Translated labels

Now that field groups are not translatable anymore, the only way to have them displayed in several languages is using strings translations.
This new feature lays in `Labels\Field_Groups`.
Note that the integration offers the same feature for ACF post types and taxonomies (`Labels\Abstract_Object_Type`).

## Editor

Integration with editors UI can be found in `Post` and `Term` (see `admin_enqueue_scripts()` and `on_ajax_post/term_lang_choice()`), as well as in `Dispatcher` (see `add_language_to_query()`) and `acf/js/index.js`.

## Migration from Polylang Pro prior to 3.7

Field groups are not translated at all now. For sites having translated field groups with Polylang Pro < 3.7, a migration will occur on update.
This migration will update each field groups with a location corresponding to the formerly assigned language.
The related code can be found in `WP_Syntex\Polylang_Pro\Upgrade::upgrade_3_7()`.
