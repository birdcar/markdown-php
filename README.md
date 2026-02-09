# birdcar/markdown-php

A [league/commonmark][] extension suite for **Birdcar Flavored Markdown** (BFM) — a superset of CommonMark and GFM that adds directive blocks, extended task lists, task modifiers, and mentions.

See the [BFM spec](https://github.com/birdcar/markdown-spec) for the full syntax definition.

## Requirements

- PHP 8.2+
- league/commonmark ^2.7

## Install

```bash
composer require birdcar/markdown-php
```

## Quick Start

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

## Usage

### The factory

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

### Use individual extensions

Each feature is a self-contained extension. Use only what you need:

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

Available extensions:

| Class | Description |
|---|---|
| `TaskExtension` | `[x]`, `[>]`, `[!]`, etc. in list items |
| `TaskModifierExtension` | `//due:2025-03-01`, `//hard` metadata |
| `MentionExtension` | `@username` inline references |
| `CalloutExtension` | `@callout`/`@endcallout` container blocks |
| `EmbedExtension` | `@embed`/`@endembed` leaf blocks |

### Implement resolvers

Mentions and embeds can be resolved at render time by implementing the contract interfaces.

**MentionResolverInterface:**

```php
use Birdcar\Markdown\Contracts\MentionResolverInterface;

class MyMentionResolver implements MentionResolverInterface
{
    /**
     * @return array{label: string, url: string|null}|null
     */
    public function resolve(string $identifier): ?array
    {
        $user = User::where('username', $identifier)->first();

        if (! $user) {
            return null;
        }

        return [
            'label' => $user->display_name,
            'url' => route('profile.show', $user),
        ];
    }
}
```

Without a resolver, mentions render as `<span class="mention">@identifier</span>`. With a resolver that returns a URL, they render as `<a href="..." class="mention">@label</a>`.

**EmbedResolverInterface:**

```php
use Birdcar\Markdown\Contracts\EmbedResolverInterface;

class OEmbedResolver implements EmbedResolverInterface
{
    /**
     * @return array{type: string, html?: string, title?: string, ...}|null
     */
    public function resolve(string $url): ?array
    {
        $response = Http::get('https://noembed.com/embed', ['url' => $url]);

        if ($response->failed()) {
            return null;
        }

        return $response->json();
    }
}
```

Without a resolver, embeds render as a `<figure>` with a plain link. With a resolver that returns `html`, the resolved HTML is embedded directly.

## Laravel Integration

The package has zero framework dependencies, so it works with any PHP project. Here's how to wire it into Laravel.

### Service provider

```php
// app/Providers/MarkdownServiceProvider.php

namespace App\Providers;

use Birdcar\Markdown\Contracts\EmbedResolverInterface;
use Birdcar\Markdown\Contracts\MentionResolverInterface;
use Birdcar\Markdown\Environment\BfmEnvironmentFactory;
use Birdcar\Markdown\Environment\RenderProfile;
use Illuminate\Support\ServiceProvider;
use League\CommonMark\MarkdownConverter;

class MarkdownServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MarkdownConverter::class, function ($app) {
            $environment = BfmEnvironmentFactory::create(
                profile: RenderProfile::Html,
                embedResolver: $app->make(EmbedResolverInterface::class),
                mentionResolver: $app->make(MentionResolverInterface::class),
            );

            return new MarkdownConverter($environment);
        });

        $this->app->bind(MentionResolverInterface::class, \App\Markdown\UserMentionResolver::class);
        $this->app->bind(EmbedResolverInterface::class, \App\Markdown\CachedOEmbedResolver::class);
    }
}
```

### Blade helper

```php
// app/helpers.php (autoloaded via composer.json)

use League\CommonMark\MarkdownConverter;

function markdown(string $text): string
{
    return (string) app(MarkdownConverter::class)->convert($text);
}
```

```blade
{{-- resources/views/post.blade.php --}}
<article class="prose">
    {!! markdown($post->body) !!}
</article>
```

### Mention resolver with caching

```php
// app/Markdown/UserMentionResolver.php

namespace App\Markdown;

use App\Models\User;
use Birdcar\Markdown\Contracts\MentionResolverInterface;
use Illuminate\Support\Facades\Cache;

class UserMentionResolver implements MentionResolverInterface
{
    public function resolve(string $identifier): ?array
    {
        return Cache::remember("mention:{$identifier}", 3600, function () use ($identifier) {
            $user = User::where('username', $identifier)->first();

            if (! $user) {
                return null;
            }

            return [
                'label' => $user->display_name,
                'url' => route('profile.show', $user),
            ];
        });
    }
}
```

### Email rendering

Use `RenderProfile::Email` when rendering markdown for email notifications:

```php
// app/Mail/CommentNotification.php

public function build()
{
    $environment = BfmEnvironmentFactory::create(
        profile: RenderProfile::Email,
        mentionResolver: app(MentionResolverInterface::class),
    );

    $converter = new MarkdownConverter($environment);

    return $this->view('emails.comment', [
        'bodyHtml' => (string) $converter->convert($this->comment->body),
    ]);
}
```

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
