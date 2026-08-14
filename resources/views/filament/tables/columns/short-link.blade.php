@php
    $record = $getRecord();
    $shortPath = '/'.$record->url_key;
    $shortUrl = url($record->url_key);
@endphp

<div
    class="inline-flex max-w-full items-center gap-1.5 whitespace-nowrap align-middle leading-none"
    x-data="{
        copied: false,
        copy() {
            const text = @js($shortUrl);
            const markCopied = () => {
                this.copied = true;
                setTimeout(() => this.copied = false, 1500);
            };
            const fallbackCopy = () => {
                const textarea = document.createElement('textarea');

                textarea.value = text;
                textarea.setAttribute('readonly', '');
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                const copied = document.execCommand('copy');
                textarea.remove();

                if (copied) {
                    markCopied();
                }
            };

            if (window.navigator.clipboard && window.isSecureContext) {
                window.navigator.clipboard.writeText(text).then(markCopied).catch(fallbackCopy);

                return;
            }

            fallbackCopy();
        },
    }"
>
    <span class="font-mono text-sm leading-none text-gray-950 dark:text-white">
        {{ $shortPath }}
    </span>

    <button
        type="button"
        class="inline-flex size-5 shrink-0 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-50 hover:text-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 dark:text-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-200"
        x-on:click.stop.prevent="copy()"
        aria-label="Copy link"
        title="Copy link"
    >
        <x-lucide-copy x-show="! copied" width="14" height="14" aria-hidden="true" />
        <x-lucide-check x-cloak x-show="copied" width="14" height="14" class="text-success-600 dark:text-success-400" aria-hidden="true" />
    </button>
</div>
