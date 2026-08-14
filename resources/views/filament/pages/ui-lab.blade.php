<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Lucide icon samples
            </x-slot>

            <x-slot name="description">
                A scratchpad for checking visual choices before using them in the app.
            </x-slot>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-3">
                        <x-lucide-link width="32" height="32" class="text-primary-600 dark:text-primary-400" />
                        <div>
                            <p class="font-medium text-gray-950 dark:text-white">Link</p>
                            <code class="text-sm text-gray-500 dark:text-gray-400">&lt;x-lucide-link /&gt;</code>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-3">
                        <x-lucide-chart-column width="32" height="32" class="text-primary-600 dark:text-primary-400" />
                        <div>
                            <p class="font-medium text-gray-950 dark:text-white">Analytics</p>
                            <code class="text-sm text-gray-500 dark:text-gray-400">&lt;x-lucide-chart-column /&gt;</code>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-3">
                        <x-lucide-copy width="32" height="32" class="text-primary-600 dark:text-primary-400" />
                        <div>
                            <p class="font-medium text-gray-950 dark:text-white">Copy</p>
                            <code class="text-sm text-gray-500 dark:text-gray-400">&lt;x-lucide-copy /&gt;</code>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Filament component samples
            </x-slot>

            <x-slot name="description">
                Examples using the same Blade Icons names that Filament accepts in PHP and Blade components.
            </x-slot>

            <div class="space-y-6">
                <div class="flex flex-wrap items-center gap-3">
                    <x-filament::button icon="lucide-copy">
                        Copy short URL
                    </x-filament::button>

                    <x-filament::button color="gray" icon="lucide-external-link" outlined>
                        Open destination
                    </x-filament::button>

                    <x-filament::badge color="success" icon="lucide-circle-check">
                        Tracking enabled
                    </x-filament::badge>

                    <x-filament::badge color="warning" icon="lucide-triangle-alert">
                        Expiring soon
                    </x-filament::badge>
                </div>

                <pre class="overflow-x-auto rounded-lg bg-gray-950 p-4 text-sm text-gray-100"><code>&lt;x-filament::button icon=&quot;lucide-copy&quot;&gt;
    Copy short URL
&lt;/x-filament::button&gt;

&lt;x-filament::button color=&quot;gray&quot; icon=&quot;lucide-external-link&quot; outlined&gt;
    Open destination
&lt;/x-filament::button&gt;

&lt;x-filament::badge color=&quot;success&quot; icon=&quot;lucide-circle-check&quot;&gt;
    Tracking enabled
&lt;/x-filament::badge&gt;</code></pre>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Filament PHP usage
            </x-slot>

            <div class="space-y-3">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Filament PHP components can use the same icon names:
                </p>

                <pre class="overflow-x-auto rounded-lg bg-gray-950 p-4 text-sm text-gray-100"><code>use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

Action::make('copy')
    -&gt;icon('lucide-copy');

TextInput::make('destination_url')
    -&gt;prefixIcon('lucide-link');

Toggle::make('track_visits')
    -&gt;onIcon('lucide-circle-check')
    -&gt;offIcon('lucide-circle-x');

protected static string | BackedEnum | null $navigationIcon = 'lucide-flask-conical';</code></pre>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
