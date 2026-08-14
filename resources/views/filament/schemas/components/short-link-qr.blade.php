<div
    class="grid justify-items-center gap-3"
    x-data="{
        copied: false,
        copyUnavailable: false,
        failed: false,
        loaded: false,
        transparent: false,
        async copyPng() {
            this.copied = false;
            this.copyUnavailable = false;

            if (! window.ClipboardItem || ! window.navigator.clipboard) {
                this.copyUnavailable = true;

                return;
            }

            try {
                const response = await fetch(this.transparent ? @js($transparentCopyPngUrl) : @js($copyPngUrl), { credentials: 'same-origin' });

                if (! response.ok) {
                    throw new Error('Unable to load QR PNG.');
                }

                const blob = await response.blob();

                await window.navigator.clipboard.write([
                    new ClipboardItem({ [blob.type]: blob }),
                ]);

                this.copied = true;
                setTimeout(() => this.copied = false, 1500);
            } catch (error) {
                this.copyUnavailable = true;
            }
        },
        toggleTransparent() {
            this.transparent = ! this.transparent;
            this.loaded = false;
            this.failed = false;
            this.copied = false;
            this.copyUnavailable = false;
        },
    }"
>
    <div
        class="relative flex aspect-square w-60 max-w-full items-center justify-center rounded-lg border border-gray-200 p-4 dark:border-gray-700"
        x-bind:class="transparent ? 'qr-transparent-checker' : 'bg-white dark:bg-gray-950'"
    >
        <div
            class="absolute inset-4 rounded-md bg-gray-100 dark:bg-gray-800"
            x-show="! loaded && ! failed"
            aria-hidden="true"
        >
            <div class="grid h-full grid-cols-5 grid-rows-5 gap-1 p-6 opacity-70">
                @foreach (range(1, 25) as $cell)
                    <span class="rounded-sm bg-gray-300 dark:bg-gray-700"></span>
                @endforeach
            </div>
        </div>

        <img
            src="{{ $svgUrl }}"
            x-bind:src="transparent ? @js($transparentSvgUrl) : @js($svgUrl)"
            alt="QR code for {{ $shortUrl }}"
            class="relative size-full transition-opacity duration-200"
            x-bind:class="loaded ? 'opacity-100' : 'opacity-0'"
            x-on:load="loaded = true"
            x-on:error="failed = true"
        >

        <p
            class="absolute inset-4 flex items-center justify-center rounded-md bg-gray-50 px-4 text-center text-sm text-gray-600 dark:bg-gray-900 dark:text-gray-300"
            x-cloak
            x-show="failed"
        >
            QR code could not be generated.
        </p>
    </div>

    <button
        type="button"
        class="inline-flex items-center gap-2 rounded-lg text-sm text-gray-700 transition hover:text-gray-950 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 dark:text-gray-300 dark:hover:text-white"
        x-on:click="toggleTransparent()"
        x-bind:aria-pressed="transparent.toString()"
    >
        <span
            class="flex h-5 w-9 items-center rounded-full border border-gray-300 bg-gray-100 p-0.5 transition dark:border-gray-600 dark:bg-gray-800"
            x-bind:class="transparent ? 'bg-primary-600 border-primary-600 dark:bg-primary-500 dark:border-primary-500' : ''"
            aria-hidden="true"
        >
            <span
                class="size-4 rounded-full bg-white shadow-sm transition"
                x-bind:class="transparent ? 'translate-x-4' : 'translate-x-0'"
            ></span>
        </span>
        Transparent background
    </button>

    <div class="flex flex-wrap justify-center gap-2">
        <span x-show="! transparent">
            <x-filament::button
                tag="a"
                :href="$downloadPngUrl"
                color="gray"
                :download="$downloadPngFilename"
                icon="lucide-download"
                size="sm"
            >
                PNG
            </x-filament::button>
        </span>

        <span x-cloak x-show="transparent">
            <x-filament::button
                tag="a"
                :href="$transparentDownloadPngUrl"
                color="gray"
                :download="$transparentDownloadPngFilename"
                icon="lucide-download"
                size="sm"
            >
                PNG
            </x-filament::button>
        </span>

        <span x-show="! transparent">
            <x-filament::button
                tag="a"
                :href="$downloadSvgUrl"
                color="gray"
                :download="$downloadSvgFilename"
                icon="lucide-download"
                size="sm"
            >
                SVG
            </x-filament::button>
        </span>

        <span x-cloak x-show="transparent">
            <x-filament::button
                tag="a"
                :href="$transparentDownloadSvgUrl"
                color="gray"
                :download="$transparentDownloadSvgFilename"
                icon="lucide-download"
                size="sm"
            >
                SVG
            </x-filament::button>
        </span>

        <x-filament::button
            color="gray"
            icon="lucide-copy"
            size="sm"
            x-on:click="copyPng()"
            x-bind:disabled="copyUnavailable"
        >
            Copy PNG
        </x-filament::button>
    </div>

    <p class="text-xs text-success-600 dark:text-success-400" x-cloak x-show="copied">
        PNG copied to clipboard.
    </p>

    <p class="text-xs text-gray-500 dark:text-gray-400" x-cloak x-show="copyUnavailable">
        PNG clipboard copy is not available in this browser or connection.
    </p>
</div>
