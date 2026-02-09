# PRD: Filament v4 Migration - Phase 1

**Contract**: ./contract.md
**Phase**: 1 of 1
**Focus**: Migrate birdcar/markdown-filament from Filament v3 to v4

## Phase Overview

This is a single-phase migration. The scope is well-bounded: update version constraints, move the server-side preview method from a user-facing trait to the `BfmEditor` component itself (leveraging v4's `callSchemaComponentMethod`), update the Blade view to use v4 patterns, and ensure all tests pass.

The biggest win is eliminating `HasBfmEditor`. In v3, users had to add this trait to every Livewire page using the editor. In v4, `callSchemaComponentMethod` lets form components expose Livewire-callable methods directly, so preview "just works" without any user-side boilerplate.

## User Stories

1. As a Filament v4 developer, I want to `composer require birdcar/markdown-filament` and have it work with Filament v4 so that I can use BFM in my admin panels.
2. As a Filament v4 developer, I want `BfmEditor::make('content')` to provide server-side BFM preview without adding any trait to my page class, so that integration is zero-boilerplate.
3. As a Filament v4 developer, I want `BfmTextColumn` and `BfmTextEntry` to render BFM content in tables and infolists, consistent with v3 behavior.

## Functional Requirements

### Composer & Dependencies

- **FR-1.1**: `composer.json` must require `filament/filament: ^4.0`
- **FR-1.2**: `composer.json` must require `illuminate/support: ^11.28|^12.0` (Filament v4 minimum)
- **FR-1.3**: `orchestra/testbench` constraint must be compatible with Laravel 11/12

### BfmEditor Component

- **FR-1.4**: `BfmEditor` must have a `renderBfmPreview()` method annotated with `#[ExposedLivewireMethod]` and `#[Renderless]`
- **FR-1.5**: `renderBfmPreview()` must accept no arguments — it reads the component's state directly
- **FR-1.6**: `renderBfmPreview()` must return rendered HTML string via `Str::bfm()`
- **FR-1.7**: `renderBfmPreview()` must return empty string for null/empty state

### HasBfmEditor Trait Removal

- **FR-1.8**: Delete `src/Concerns/HasBfmEditor.php`
- **FR-1.9**: Delete `tests/HasBfmEditorTest.php`
- **FR-1.10**: Update PHPStan ignore list (remove unused trait ignore, may need new ignores)

### Blade View Updates

- **FR-1.11**: Add `$key = $getKey()` to the `@php` block
- **FR-1.12**: Preview toggle must call `$wire.callSchemaComponentMethod(@js($key), 'renderBfmPreview')` instead of `$wire.renderBfmPreview('{{ $statePath }}')`
- **FR-1.13**: File upload must use `callSchemaComponentMethod(@js($key), 'saveUploadedFileAttachment', ...)` instead of `getFormComponentFileAttachmentUrl`
- **FR-1.14**: `canAttachFiles` must use `$hasFileAttachments()` instead of `$hasToolbarButton('attachFiles')`

### BfmTextColumn & BfmTextEntry

- **FR-1.15**: No changes required — verify they still work with v4's `TextColumn` and `TextEntry`

## Non-Functional Requirements

- **NFR-1.1**: All 16 existing test assertions must still pass (adapted for API changes)
- **NFR-1.2**: PHPStan level 8 must pass cleanly
- **NFR-1.3**: No backward compatibility with Filament v3

## Dependencies

### Prerequisites

- Phase 1 and Phase 2 of bfm-laravel-integration complete (they are)
- Understanding of Filament v4 `callSchemaComponentMethod` API

### Outputs for Next Phase

- Updated package ready for Phase 3 (CSS/styling) work

## Acceptance Criteria

- [ ] `composer.json` requires `filament/filament: ^4.0` and `illuminate/*: ^11.28|^12.0`
- [ ] `HasBfmEditor` trait and its test file are deleted
- [ ] `BfmEditor` has `renderBfmPreview()` with `#[ExposedLivewireMethod]` + `#[Renderless]`
- [ ] Blade view uses `$getKey()`, `callSchemaComponentMethod`, v4 file upload pattern
- [ ] `BfmTextColumn` and `BfmTextEntry` pass existing tests
- [ ] `cd packages/filament && composer test` — all tests pass
- [ ] `cd packages/filament && composer analyse` — PHPStan clean

---

*Review this PRD and provide feedback before spec generation.*
