<div
    class="grid gap-5"
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
    <div class="grid gap-3">
        <div class="grid gap-1.5">
            <p class="m-0 text-xs font-medium text-gray-600 dark:text-gray-400">
                Short URL
            </p>
            <p class="m-0 inline-flex items-center gap-1.5 break-all font-mono text-sm text-gray-950 dark:text-white">
                <button
                    type="button"
                    class="inline-flex size-5 shrink-0 cursor-pointer items-center justify-center rounded-md border-0 bg-transparent text-gray-400 transition hover:bg-gray-50 hover:text-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 dark:text-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-200"
                    x-on:click.stop.prevent="copy()"
                    aria-label="Copy short URL"
                    title="Copy short URL"
                >
                    <x-lucide-copy x-show="! copied" width="14" height="14" aria-hidden="true" />
                    <x-lucide-check x-cloak x-show="copied" width="14" height="14" class="text-success-600 dark:text-success-400" aria-hidden="true" />
                </button>
                <span>{{ $shortUrl }}</span>
            </p>
        </div>

        <div class="grid gap-1.5">
            <p class="m-0 text-xs font-medium text-gray-600 dark:text-gray-400">
                Destination URL
            </p>
            <p class="m-0 inline-flex items-center gap-1.5 break-all text-sm text-gray-950 dark:text-white">
                <a
                    href="{{ $destinationUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex size-5 shrink-0 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-50 hover:text-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 dark:text-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-200"
                    aria-label="Open destination URL"
                    title="Open destination URL"
                >
                    <x-lucide-external-link width="14" height="14" aria-hidden="true" />
                </a>
                <span>{{ $destinationUrl }}</span>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-x-20 gap-y-2 border-t border-gray-200 pt-4 sm:grid-cols-2 dark:border-gray-800">
        <div class="grid gap-1.5">
            <p class="m-0 inline-flex items-center gap-1.5 whitespace-nowrap text-xs font-medium text-gray-600 dark:text-gray-400">
                Redirect:
                <span class="fi-badge fi-size-sm fi-color-warning">{{ $redirectLabel }}</span>
            </p>
        </div>

        <div class="grid gap-1.5">
            <p class="m-0 inline-flex items-center gap-1.5 whitespace-nowrap text-xs font-medium text-gray-600 dark:text-gray-400">
                Visits:
                <span class="text-sm font-normal text-gray-950 dark:text-white">{{ $visitsCount }}</span>
            </p>
        </div>

        <div class="grid gap-1.5">
            <p class="m-0 inline-flex items-center gap-1.5 whitespace-nowrap text-xs font-medium text-gray-600 dark:text-gray-400">
                Track visits:
                @if ($trackVisits)
                    <x-lucide-circle-check width="16" height="16" class="text-success-600 dark:text-success-400" aria-label="Yes" />
                @else
                    <x-lucide-circle-x width="16" height="16" class="text-danger-600 dark:text-danger-400" aria-label="No" />
                @endif
            </p>
        </div>

        <div class="grid gap-1.5">
            <p class="m-0 inline-flex items-center gap-1.5 whitespace-nowrap text-xs font-medium text-gray-600 dark:text-gray-400">
                Forward query parameters:
                @if ($forwardQueryParams)
                    <x-lucide-circle-check width="16" height="16" class="text-success-600 dark:text-success-400" aria-label="Yes" />
                @else
                    <x-lucide-circle-x width="16" height="16" class="text-danger-600 dark:text-danger-400" aria-label="No" />
                @endif
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-x-20 gap-y-2 sm:grid-cols-2">
        <div class="grid gap-1.5">
            <p class="m-0 inline-flex items-center gap-1.5 whitespace-nowrap text-xs font-medium text-gray-600 dark:text-gray-400">
                Created:
                <span class="text-sm font-normal text-gray-950 dark:text-white">{{ $createdAt }}</span>
            </p>
        </div>

        <div class="grid gap-1.5">
            <p class="m-0 inline-flex items-center gap-1.5 whitespace-nowrap text-xs font-medium text-gray-600 dark:text-gray-400">
                Updated:
                <span class="text-sm font-normal text-gray-950 dark:text-white">{{ $updatedAt }}</span>
            </p>
        </div>
    </div>
</div>
