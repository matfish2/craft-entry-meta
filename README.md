# Element Meta

This package adds the ability to save schemaless metadata to all element types, including custom elements.

## Why?

Sometimes you'll need to attach additional information to your element (e.g Entry, Product or Category) without creating a corresponding field in Craft. 
Common examples include saving an identifier of the element in another system, keeping track of post views, or flagging an element as seeded for later removal. 

One option would be to create a read-only field on Craft using a plugin that allows for hidden/read-only field types.
However, there are multiple cons to this approach:

1. You now have a field in your section layout that does not semantically belong there.
2. It can be accessed, modified and deleted from the control panel (granted only on dev environment).
3. It is loaded when creating or editing a post, which can be easily tampered with from the developer console of the
   browser.
4. It does not allow for ad-hoc data that could pertain to a single post or just some posts, without creating yet
   another field.
5. It is saved to the `content` table, which is not its natural habitat.

Element Meta offers a more flexible, schemaless alternative, which by-passes the Craft data structure and allows you
to save metadata in JSON format to a designated polymorphic table.

## Installation

1. Include the package:

```
composer require matfish/craft-entry-meta
```

2. Install the plugin:

```
php craft plugin/install entry-meta
```

## Initial Setup

Once installed go to the plugin's settings page (Under `Settings` in the control panel).

![Settings Page](https://user-images.githubusercontent.com/1510460/187615962-066465b2-4318-4d81-8de7-f54c1bf0d262.png)

Select the elements you'd like to add metadata to.
Note that for custom elements (as opposed to Craft's native elements) you would need to provide both the Element class and the Active Record class.
E.g if you would like to enable metadata on Craft Commerce's products:

Element Class = `craft\commerce\elements\Product`

Active Record Class = `craft\commerce\records\Product`

The package will validate both classes to ensure they exist and are children of Element and ActiveRecord respectively.

Once you save the settings the plugin will add the metadata functionality to the relevant elements.

## Usage

`$entry` below refers to an entry element (`craft\elements\Entry`).

Note that the element must be already saved in order to use metadata methods.

Set metadata (will replace existing metadata):

```php
$entry->setElementMetadata([
    'foo'=>'bar'
]);
```

Add to existing metadata, or replace an existing value:

```php
$entry->addElementMetadata([
    'a'=>1
]);
```

Delete metadata:
```php
$entry->deleteElementMetadata();
```

Get all metadata of an entry:

```php
$entry->getElementMetadata();   
```

Get a specific key value:

```php
$entry->getElementMetadata('foo');   
```

Get nested values:
```php
$entry->getElementMetadata('foo.bar');   
```   

Or using Twig:

```twig
{{entry.getElementMetadata('foo')}}
```

### Query by metadata

You can query by metadata on the active record class or the element class (e.g `craft\records\Entry`, `craft\elements\Entry`) using the methods detailed below.

Note you should first join the metadata table using the `joinMetadata` method. This is a left join, so it doesn't affect query results, just adds the metadata column. 

E.g 
```twig
{{ craft.entries().joinMetadata().whereMetadata('foo','bar') }}
```
or:
```php
craft\elements\Entry::find()->joinMetadata()->whereMetadata('foo','bar')
```
or:
```php
craft\records\Entry::find()->joinMetadata()->whereMetadata('foo','bar')
```

#### Filter by metadata
##### Value
```php
Entry::find()->whereMetadata('foo','bar');
```
For more complex queries you can also chain `orWhereMetadata` and `andWhereMetadata`.

The method defaults to the `=` operand, which you can override on the third argument. E.g:
```php
Entry::find()->whereMetadata('views',0,'>');
```
##### Existence
You can also check for existence of metadata **keys**:
```php
Entry::find()->hasMetadata('foo');
```
Or, conversely:
```php
Entry::find()->doesntHaveMetadata('foo');
```
To filter down by the existence of metadata itself, simply omit the key argument:
```php
Entry::find()->hasMetadata(); 
```
or
```php
Entry::find()->doesntHaveMetadata(); 
```
#### Sort by metadata
```php
Entry::find()->whereMetadata('views',0,'>')->orderByMetadata('views',false, true);
```
The second boolean argument is whether to sort in ascending order. Defaults to `true`

The third boolean argument is whether the value is an integer. Defaults to `false`

#### Nested data
You can search and sort by nested data using the `.` syntax:
```php
Entry::find()->whereMetadata('foo.bar','baz')->orderByMetadata('foobar.baz');
```
### Metadata on element editor page
By default metadata is rendered on the sidebar along with Craft's metadata (status, created at, updated at).
You can disable this behaviour via the plugin settings page.

## License

You can try Element Meta in a development environment for as long as you like. Once your site goes live, you are required
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