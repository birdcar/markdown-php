@php
    use Filament\Support\Facades\FilamentAsset;

    $id = $getId();
    $fieldWrapperView = $getFieldWrapperView();
    $extraAttributeBag = $getExtraAttributeBag();
    $key = $getKey();
    $statePath = $getStatePath();
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    @if ($isDisabled())
        <div
            @class([
                'fi-fo-markdown-editor fi-disabled',
                'prose max-w-none dark:prose-invert',
                'min-h-[theme(spacing.48)]' => ! $isInline(),
            ])
        >
            {!! str($getState())->markdown()->sanitizeHtml() !!}
        </div>
    @else
        <div
            x-data="{
                showPreview: false,
                previewHtml: '',
                previewLoading: false,
            }"
            class="fi-fo-markdown-editor"
        >
            {{-- Preview toggle button --}}
            <div class="flex items-center justify-end border-b border-gray-200 px-2 py-1 dark:border-white/10">
                <button
                    type="button"
                    x-on:click="
                        showPreview = !showPreview;
                        if (showPreview) {
                            previewLoading = true;
                            $wire.callSchemaComponentMethod(@js($key), 'renderBfmPreview')
                                .then(html => {
                                    previewHtml = html;
                                    previewLoading = false;
                                })
                                .catch(() => {
                                    previewLoading = false;
                                });
                        }
                    "
                    class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-gray-600 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5"
                    :class="showPreview ? 'bg-gray-100 dark:bg-white/5' : ''"
                >
                    <x-filament::icon
                        icon="heroicon-m-eye"
                        x-show="!showPreview"
                        class="h-4 w-4"
                    />
                    <x-filament::icon
                        icon="heroicon-m-pencil-square"
                        x-show="showPreview"
                        class="h-4 w-4"
                    />
                    <span x-text="showPreview ? 'Edit' : 'Preview'"></span>
                </button>
            </div>

            {{-- EasyMDE editor (hidden when preview is active) --}}
            <div x-show="!showPreview">
                <x-filament::input.wrapper
                    :valid="! $errors->has($statePath)"
                    :attributes="
                        \Filament\Support\prepare_inherited_attributes($extraAttributeBag)
                            ->class(['overflow-hidden'])
                    "
                >
                    <div
                        x-load
                        x-load-src="{{ FilamentAsset::getAlpineComponentSrc('markdown-editor', 'filament/forms') }}"
                        x-data="markdownEditorFormComponent({
                            canAttachFiles: @js($hasFileAttachments()),
                            isLiveDebounced: @js($isLiveDebounced()),
                            isLiveOnBlur: @js($isLiveOnBlur()),
                            liveDebounce: @js($getNormalizedLiveDebounce()),
                            maxHeight: @js($getMaxHeight()),
                            minHeight: @js($getMinHeight()),
                            placeholder: @js($getPlaceholder()),
                            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')", isOptimisticallyLive: false) }},
                            toolbarButtons: @js($getToolbarButtons()),
                            translations: @js(__('filament-forms::components.markdown_editor')),
                            uploadFileAttachmentUsing: async (file, onSuccess, onError) => {
                                $wire.upload(`componentFileAttachments.{{ $statePath }}`, file, () => {
                                    $wire
                                        .callSchemaComponentMethod(
                                            '{{ $key }}',
                                            'saveUploadedFileAttachmentAndGetUrl',
                                        )
                                        .then((url) => {
                                            if (! url) {
                                                return onError()
                                            }

                                            onSuccess(url)
                                        })
                                })
                            },
                        })"
                        wire:ignore
                        {!! $isLiveOnBlur() ? 'x-on:blur="$wire.$refresh()"' : '' !!}
                        @class([
                            '[&_.CodeMirror]:min-h-[theme(spacing.48)]' => ! $isInline(),
                        ])
                    >
                        <textarea x-ref="editor" x-cloak></textarea>
                    </div>
                </x-filament::input.wrapper>
            </div>

            {{-- Server-rendered BFM preview --}}
            <div
                x-show="showPreview"
                x-cloak
                class="rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-white/5"
                style="min-height: {{ $getMinHeight() ?? '12rem' }}"
            >
                <template x-if="previewLoading">
                    <div class="flex items-center justify-center py-8 text-sm text-gray-400">
                        <x-filament::loading-indicator class="mr-2 h-4 w-4" />
                        Rendering preview...
                    </div>
                </template>

                <template x-if="!previewLoading && previewHtml === ''">
                    <div class="py-8 text-center text-sm text-gray-400">
                        Nothing to preview
                    </div>
                </template>

                <template x-if="!previewLoading && previewHtml !== ''">
                    <div class="prose max-w-none dark:prose-invert" x-html="previewHtml"></div>
                </template>
            </div>
        </div>
    @endif
</x-dynamic-component>
