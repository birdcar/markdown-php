# birdcar/markdown-php

A [league/commonmark][] extension suite for **Birdcar Flavored Markdown** (BFM) — a superset of CommonMark and GFM that adds directive blocks, extended task lists, task modifiers, and mentions.

See the [BFM spec](https://github.com/birdcar/markdown-spec) for the full syntax definition.

## Packages

This is a monorepo containing the core library and framework integration packages:

| Package | Description | Install |
|---------|-------------|---------|
| `birdcar/markdown-php` | Core library (this repo root) | `composer require birdcar/markdown-php` |
| [`birdcar/markdown-laravel`](packages/laravel/) | Laravel service provider, `Str::bfm()` macro, `@bfmStyles` directive | `composer require birdcar/markdown-laravel` |
| [`birdcar/markdown-filament`](packages/filament/) | Filament v4 `BfmEditor`, `BfmTextColumn`, `BfmTextEntry` | `composer require birdcar/markdown-filament` |

## Requirements

- PHP 8.2+
- league/commonmark ^2.7

## Quick Start

### Core library (any PHP project)

```php
use Birdcar\Markdown\Environment\BfmEnvironmentFactory;
use League\CommonMark\MarkdownConverter;

$environment = BfmEnvironmentFactory::create();
$converter = new MarkdownConverter($environment);

echo $converter->convert(<<<'MD'
- [>] Call the dentist //due:2025-03-01
- [!] File taxes //due:2025-04-15 //hard
- [x] Buy groceries

@callout type=warning title="Heads Up"
Don't forget to bring your **insurance card**.
@endcallout

Hey @sarah, can you review this?
MD);
```

### Laravel

```bash
composer require birdcar/markdown-laravel
```

Zero config — the service provider auto-discovers and registers everything:

```php
use Illuminate\Support\Str;

// Render BFM to HTML
$html = Str::bfm('- [>] Call dentist //due:2025-03-01');

// Inline rendering (no wrapping <p> tags)
$html = Str::inlineBfm('Hey @sarah');

// Access the configured converter directly
$converter = app(\League\CommonMark\MarkdownConverter::class);
```

In Blade templates:

```blade
{{-- Include BFM default styles --}}
@bfmStyles

{{-- Render content --}}
<article class="prose">
    {!! Str::bfm($post->body) !!}
</article>
```

Publish the config to customize the render profile or bind resolver classes:

```bash
php artisan vendor:publish --tag=bfm-config
```

```php
// config/bfm.php
return [
    'profile' => 'html',  // 'html', 'email', or 'plain'
    'resolvers' => [
        'mention' => \App\Markdown\UserMentionResolver::class,
        'embed'   => \App\Markdown\OEmbedResolver::class,
    ],
];
```

### Filament v4

```bash
composer require birdcar/markdown-filament
```

Drop-in replacement for Filament's `MarkdownEditor` with server-side BFM preview:

```php
use Birdcar\Markdown\Filament\Forms\Components\BfmEditor;
use Birdcar\Markdown\Filament\Tables\Columns\BfmTextColumn;
use Birdcar\Markdown\Filament\Infolists\Components\BfmTextEntry;

// Form field with live BFM preview
BfmEditor::make('content')
    ->previewDebounce(300);

// Table column that renders BFM
BfmTextColumn::make('content');

// Infolist entry that renders BFM
BfmTextEntry::make('content');
```

The `BfmEditor` provides a preview toggle button that renders BFM syntax server-side via Filament v4's `callSchemaComponentMethod` — no traits or page-level configuration needed.

## The Factory

`BfmEnvironmentFactory::create()` returns a fully configured `Environment` with CommonMark, GFM (minus task lists, which BFM replaces), and all five BFM extensions:

```php
use Birdcar\Markdown\Environment\BfmEnvironmentFactory;
use Birdcar\Markdown\Environment\RenderProfile;

$environment = BfmEnvironmentFactory::create(
    profile: RenderProfile::Html,           // Html (default), Email, or Plain
    embedResolver: $myEmbedResolver,        // optional — implements EmbedResolverInterface
    mentionResolver: $myMentionResolver,    // optional — implements MentionResolverInterface
    config: [],                             // additional league/commonmark config
);
```

### Individual extensions

Each feature is a self-contained extension:

```php
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Birdcar\Markdown\Inline\Task\TaskExtension;
use Birdcar\Markdown\Inline\TaskModifier\TaskModifierExtension;

$environment = new Environment();
$environment->addExtension(new CommonMarkCoreExtension());
$environment->addExtension(new TaskExtension());
$environment->addExtension(new TaskModifierExtension());

$converter = new MarkdownConverter($environment);
echo $converter->convert('- [>] Call dentist //due:2025-03-01');
```

| Class | Description |
|---|---|
| `TaskExtension` | `[x]`, `[>]`, `[!]`, etc. in list items |
| `TaskModifierExtension` | `//due:2025-03-01`, `//hard` metadata |
| `MentionExtension` | `@username` inline references |
| `CalloutExtension` | `@callout`/`@endcallout` container blocks |
| `EmbedExtension` | `@embed`/`@endembed` leaf blocks |

## Resolvers

Mentions and embeds can be resolved at render time by implementing the contract interfaces.

**MentionResolverInterface:**

```php
use Birdcar\Markdown\Contracts\MentionResolverInterface;

class UserMentionResolver implements MentionResolverInterface
{
    public function resolve(string $identifier): ?array
    {
        $user = User::where('username', $identifier)->first();

        return $user ? [
            'label' => $user->display_name,
            'url' => route('profile.show', $user),
        ] : null;
    }
}
```

Without a resolver, mentions render as `<span class="mention">@identifier</span>`. With a resolver that returns a URL, they render as `<a href="..." class="mention">@label</a>`.

**EmbedResolverInterface:**

```php
use Birdcar\Markdown\Contracts\EmbedResolverInterface;

class OEmbedResolver implements EmbedResolverInterface
{
    public function resolve(string $url): ?array
    {
        $response = Http::get('https://noembed.com/embed', ['url' => $url]);

        return $response->successful() ? $response->json() : null;
    }
}
```

Without a resolver, embeds render as a `<figure>` with a plain link. With a resolver that returns `html`, the resolved HTML is embedded directly.

## Styling

The `@bfmStyles` Blade directive (from `birdcar/markdown-laravel`) outputs a default stylesheet covering all BFM output elements. The stylesheet uses CSS custom properties for theming and supports both `prefers-color-scheme: dark` and class-based dark mode (`.dark`).

To publish the stylesheet for customization:

```bash
php artisan vendor:publish --tag=bfm-assets
```

Override any variable in your own CSS:

```css
:root {
  --bfm-task-priority: #b91c1c;
  --bfm-mention-bg: #fce7f3;
  --bfm-mention-text: #9d174d;
}
```

The Filament package automatically loads BFM styles into the admin panel.

## Syntax Reference

### Extended Task Lists

Seven states, inspired by Bullet Journal:

```markdown
- [ ] Open task
- [x] Completed
- [>] Scheduled for later
- [<] Migrated elsewhere
- [-] No longer relevant
- [o] Calendar event
- [!] High priority
```

### Task Modifiers

Inline metadata on task items using `//key:value` syntax:

```markdown
- [>] Call dentist //due:2025-03-01
- [ ] Weekly review //every:weekly
- [o] Team retro //due:2025-02-07 //every:2-weeks
- [ ] Run backups //cron:0 9 * * 1
- [!] File taxes //due:2025-04-15 //hard
- [>] Wait for response //wait
```

### Mentions

```markdown
Hey @sarah, can you review this? Also cc @john.doe and @dev-team.
```

### Directive Blocks

**Callouts** (container — body is parsed as markdown):

```markdown
@callout type=warning title="Watch Out"
This is a warning with **bold** text and [links](https://example.com).
@endcallout
```

**Embeds** (leaf — body is treated as caption text):

```markdown
@embed https://www.youtube.com/watch?v=dQw4w9WgXcQ
A classic internet moment.
@endembed
```

## HTML Output

The extensions produce semantic, BEM-style HTML:

```html
<!-- Task list item -->
<li data-task="scheduled" class="task-item task-item--scheduled">
  <p>
    <span class="task-marker task-marker--scheduled" title="Scheduled" data-state="scheduled">
      <span class="task-marker__icon">▷</span>
    </span>
    Call dentist
    <span class="task-mod task-mod--due" data-key="due" data-value="2025-03-01">//due:2025-03-01</span>
  </p>
</li>

<!-- Mention -->
<a href="/users/sarah" class="mention">@Sarah Chen</a>

<!-- Callout -->
<aside class="callout callout--warning">
  <div class="callout__header">Watch Out</div>
  <div class="callout__body"><p>Content with <strong>markdown</strong>.</p></div>
</aside>

<!-- Embed (fallback) -->
<figure class="embed">
  <a href="https://example.com/video" class="embed__link">https://example.com/video</a>
  <figcaption class="embed__caption">A great video.</figcaption>
</figure>
```

## License

MIT

[league/commonmark]: https://commonmark.thephpleague.com
