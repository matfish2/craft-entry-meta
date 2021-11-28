# Entry Meta

This package adds the ability to save schemaless metadata to entries.

## Why?

Sometimes you'll need to attach additional information to your entry without creating a corresponding field in Craft. A
Common examples are saving an identifier of the entry in another system, keeping track of post views, or flagging an entry as seeded for later removal. 

One option would be to create a read-only field on Craft using a plugin that allows for hidden/read-only field types.
However, there are multiple cons to this approach:

1. You now have a field in your section layout that does not semantically belong there.
2. It can be accessed, modified and deleted from the control panel (granted only on dev environment).
3. It is loaded when creating or editing a post, which can be easily tampered with from the developer console of the
   browser.
4. It does not allow for ad-hoc data that could pertain to a single post or just some posts, without creating yet
   another field.
5. It is saved to the `content` table, rather than the `entries` table, where it naturally belongs.

Entry metadata offers a more flexible, schemaless alternative, which by-passes the Craft data structure and allows you
to save metadata in JSON format directly to the `entries` table.

## Installation

1. Include the package:

```
composer require matfish/craft-entry-meta
```

2. Install the plugin:

```
php craft plugin/install craft-entry-meta
```

## Usage

`$entry` below refers to an entry element (`craft\elements\Entry`)
Note that element must be already saved in order to use metadata methods.

Set metadata (will replace existing metadata):

```php
$entry->setEntryMetadata([
    'foo'=>'bar'
]);
```

Add to existing metadata, or replace an existing value:

```php
$entry->addEntryMetadata([
    'a'=>1
]);
```

Get all metadata of an entry:

```php
$entry->getEntryMetadata();   
```

Get a specific key value:

```php
$entry->getEntryMetadata('foo');   
```

Or using Twig:

```twig
{{entry.entryMetadata('foo')}}
```

### Query by metadata

Get the plugin instance:
```php
$instance = matfish\EntryMeta\EntryMeta::getInstance();
```
Run query:
```php
$instance->query
->findEntriesByMetadata(['foo'=>'bar'])
->all();
```

You can also query nested structures using the `.` syntax:

```php
$instance->query
->findEntriesByMetadata(['foo.baz'=>'bar'])
->all();
```

Query by multiple nodes (AND relations):
```php
$instance->query
->findEntriesByMetadata([
    'foo.baz'=>'bar',
    'foobar'=> true
    ])
->all();
```
### Metadata on Entry edit
By default metadata is rendered on the sidebar along with Craft's metadata (status, created at, updated at).
You can disable this behaviour via the plugin settings.

Crate a `config\entry-meta.php` file:
```php
return [
   'displayMetadataInCp'=>false
];
```

## License

You can try Entry Meta in a development environment for as long as you like. Once your site goes live, you are required
to purchase a license for the plugin. License is purchasable through
the [Craft Plugin Store](https://plugins.craftcms.com/entry-meta).

For more information, see
Craft's [Commercial Plugin Licensing](https://craftcms.com/docs/3.x/plugins.html#commercial-plugin-licensing).

## Requirements

This plugin requires Craft CMS 3.7.0 or later.

## Contribution Guidelines

Community is at the heart of open-source projects. We are always happy to receive constructive feedback from users to
incrementally improve the product and/or the documentation.

Below are a few simple rules that are designed to facilitate interactions and prevent misunderstandings:

Please only open a new issue for bug reports. For feature requests and questions open a
new [Discussion](https://github.com/matfish2/craft-entry-meta/discussions) instead, and precede [FR] to the title.

If you wish to endorse an existing FR please just vote the OP up, while refraining from +1 replies.