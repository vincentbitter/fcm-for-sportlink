# Changelog

All notable changes to this project will be documented in this file.

## [0.11.0] - 2026-05-07

### 🐛 Bug Fixes

- Import game facilitators

## [0.10.0] - 2026-05-07

### 🚀 Features

- Import referees

### 🐛 Bug Fixes

- Move Sportlink down in the admin menu.
- Update matches with 0 if no goals were made.
- Update matches with 0 if no goals were made.

### ⚙️  Miscellaneous Tasks

- WordPress stubs to improve development experience.

## [0.9.0] - 2026-02-08

### 🚀 Features

- Import birthdays

## [0.8.0] - 2025-10-19

### 🚀 Features

- Load match results from last year instead of week.

### 🐛 Bug Fixes

- Update 0-values in match results.

## [0.7.0] - 2025-10-03

### 🚀 Features

- Import scheduled matches manually and automatically.
- Import match results manually and automatically.

### 🐛 Bug Fixes

- Prevent missing references.
- Timeout for Sportlink API increased from 5 to 120 seconds.
- Typo in NL translation.

### ⚡ Performance

- Only store teams that are imported from Sportlink in cache.

## [0.6.0] - 2025-09-19

### 📚 Documentation

- WordPress repository assets.

### ⚙️  Miscellaneous Tasks

- Release to WordPress Plugin Repository.

## [0.5.0] - 2025-08-28

### 📚 Documentation

- Disclaimer and FAQ about relation with Sportlink.
- Correct navigation instruction for configuration.

### ⚙️  Miscellaneous Tasks

- Project renamed to "Football Club Manager for Sportlink" to avoid confusion.
- Slug renamed to match plugin name.

## [0.4.0] - 2025-08-26

### 📚 Documentation

- Add privacy section.

## [0.3.0] - 2025-08-20

### 🚀 Features

- Support Football Club Manager 0.2.0

### 🐛 Bug Fixes

- Require WordPress 6.8 (major) instead of 6.8.1
- Refresh CSS on new version.
- Exception in exception handling due to renaming SportlinkException to FCMSL_Sportlink_Exception..
- Disable meta box save to avoid exceptions on nonce validation.
- Plugin Check findings.

### 📚 Documentation

- Improved readme for Github and Wordpress Plugin Repository.

### ⚙️  Miscellaneous Tasks

- *(security)* Improve nonce validation.
- Lower minimum PHP version requirement to 7.4.

## [0.2.0] - 2025-07-04

### 🚀 Features

- Error handling if Sportlink API returns errors. Doing a manual import, a detailed error message is shown.
- Import player photos from Sportlink.

### 🐛 Bug Fixes

- Zip-file renamed to fcm-sportlink.zip (without version number), so plugin will end in the correct folder.

### 🚜 Refactor

- Solved finding of Plugin Check to improve security.

## [0.1.0] - 2025-07-03

### 📚 Documentation

- Create changelog

### ⚙️  Miscellaneous Tasks

- Create GitHub release


