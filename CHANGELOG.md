# Release Notes for Element Meta

## 5.1.4 - 2025-01-03
### Fixed
- Use `SIGNED` cast for MySQL

## 5.1.3 - 2025-01-03
### Fixed
- Use simple concatenation to avoid added slashes (backported from 5.1.2)

## 5.1.2 - 2025-01-03
### Fixed
- Fixed special JSON keys issue with MySQL [issue #7](https://github.com/matfish2/craft-entry-meta/issues/7)

## 5.1.1 - 2024-11-05
### Fixed
- Fixed element query filtering elements that don't have element meta

## 5.1.0 - 2024-11-04
### Added
- Craft 5: Add support for element queries including twig. [discussion](https://github.com/matfish2/craft-entry-meta/discussions/6#discussioncomment-11141312)

## 5.0.0 - 2024-04-13
- Craft 5: Initial Release

## 4.1.0 - 2024-01-26
### Fixed
- Install migration: use `dateTime()->notNull()` instead of `timestamp()`

## 4.0.0 - 2023-07-30
> {note} Breaking Change. See [README](https://github.com/matfish2/craft-entry-meta#migrating-to-version-4) for further information
### Improved
- Use a dedicated polymorphic table

## 3.1.0 - 2022-11-21
### Added
- Added existence filters

## 3.0.2 - 2022-08-31
### Fixed
- Fixed validation message

## 3.0.1 - 2022-08-31
### Added
- Cache enabled elements

## 3.0.0 - 2022-08-31
> {note} Breaking Change. See README for quick migration
### Added
- Craft 4: Extend functionality to allow for all element types [#3](https://github.com/matfish2/craft-entry-meta/issues/3)

## 2.0.1 - 2022-06-08
- Craft 4: Resolve removed CP meta hook [#10172](https://github.com/craftcms/cms/issues/10172#issuecomment-1149443831)

## 2.0.0 - 2022-06-06
- Craft 4: Initial Release

## 1.0.1 - 2021-12-07
### Fixed
- Ensure install migration runs on new environment [#2](https://github.com/matfish2/craft-entry-meta/issues/2)

## 1.0.0 - 2021-12-03
### Added
- Publish stable release [#1](https://github.com/matfish2/craft-entry-meta/issues/1)

## 1.0.0-rc.4 - 2021-12-01
### Added
- Convert keys and values displayed on sidebar to human-readable format.

### Changed
- Refactored plugin class

## 1.0.0-rc.3 - 2021-11-29
### Changed
- Breaking Change: Remove query service. Use ActiveQuery instead for more flexible queries.

## 1.0.0-rc.2 - 2021-11-29
### Fixed
- Fixed Postgres query 

## 1.0.0-rc.1 - 2021-11-28
### Added
- Initial release