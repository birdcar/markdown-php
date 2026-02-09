# Filament v4 Migration Contract

**Created**: 2026-02-09
**Confidence Score**: 98/100
**Status**: Draft

## Problem Statement

The `birdcar/markdown-filament` package was built targeting Filament v3. Filament v4 is now the stable release, and v3 is no longer actively maintained. The package must be updated to target Filament v4 to remain usable.

Filament v4 introduces `callSchemaComponentMethod` with `#[ExposedLivewireMethod]`, which enables form components to expose Livewire methods directly — eliminating the need for the `HasBfmEditor` trait that users currently must add to their Livewire pages. This is a significant DX improvement: users no longer need to know about or use any trait for server-side preview to work.

## Goals

1. **Target Filament v4**: Update all Composer constraints and service provider registrations to work with Filament v4 (and drop v3 support).
2. **Eliminate the HasBfmEditor trait**: Move the `renderBfmPreview` method directly onto `BfmEditor` using `#[ExposedLivewireMethod]`, so users get server-side preview without adding any trait to their pages.
3. **Update Blade view to v4 patterns**: Use `$key = $getKey()`, `callSchemaComponentMethod`, and the v4 file upload callback pattern.
4. **Maintain full test coverage**: All existing tests pass against Filament v4 with updated assertions where APIs changed.

## Success Criteria

- [ ] `composer.json` requires `filament/filament: ^4.0` and `illuminate/*: ^11.28|^12.0`
- [ ] `BfmEditor::renderBfmPreview()` is a method on the component class with `#[ExposedLivewireMethod]` and `#[Renderless]`
- [ ] `HasBfmEditor` trait is deleted
- [ ] Blade view uses `$wire.callSchemaComponentMethod(@js($key), 'renderBfmPreview')` instead of `$wire.renderBfmPreview()`
- [ ] Blade view uses `$key = $getKey()` and v4 file upload pattern
- [ ] `BfmTextColumn` and `BfmTextEntry` work unchanged
- [ ] All tests pass: `cd packages/filament && composer test`
- [ ] PHPStan level 8 clean: `cd packages/filament && composer analyse`
- [ ] Test for `HasBfmEditor` is removed or replaced with a test for `BfmEditor::renderBfmPreview()`

## Scope Boundaries

### In Scope

- Update `packages/filament/composer.json` version constraints
- Move preview method from trait to `BfmEditor` class
- Delete `HasBfmEditor` trait and its test file
- Update Blade view to v4 APIs (`$getKey()`, `callSchemaComponentMethod`, file upload)
- Update `TestCase.php` service provider list for v4 changes (if needed)
- Update `BfmEditorTest.php` to remove trait-dependent tests, add preview method tests
- Update Filament test `orchestra/testbench` constraint for Laravel 11/12

### Out of Scope

- **Filament v3 backward compatibility** — Clean break to v4 only, no dual-version support
- **New features** — This is a migration, not a feature addition
- **Phase 3 CSS/styling work** — Remains a separate task
- **Laravel package changes** — `birdcar/markdown-laravel` is unaffected

### Future Considerations

- Filament v4's new Tiptap editor option (if they add one)
- Schema component lifecycle hooks that could enhance preview behavior

---

*This contract was generated from brain dump input. Review and approve before proceeding to PRD generation.*
