<?php

namespace App\Filament\Widgets;

use App\Actions\ShortLinks\ForgetShortLinkQrCodeAssets;
use App\Actions\ShortLinks\GenerateShortLinkQrCodeAsset;
use App\Filament\Tables\Columns\VisitsSparklineColumn;
use AshAllenDesign\ShortURL\Facades\ShortURL as ShortURLBuilder;
use AshAllenDesign\ShortURL\Models\ShortURL;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Livewire\Component as LivewireComponent;

class LinksTable extends TableWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Links')
            ->query(fn (): Builder => ShortURL::query()->withCount('visits'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->placeholder('Untitled')
                    ->searchable()
                    ->sortable(),
                ViewColumn::make('url_key')
                    ->label('Link')
                    ->view('filament.tables.columns.short-link')
                    ->extraCellAttributes(['class' => 'whitespace-nowrap'])
                    ->searchable()
                    ->sortable(),
                TextColumn::make('destination_url')
                    ->label('Destination')
                    ->limit(60)
                    ->tooltip(fn (ShortURL $record): string => $record->destination_url)
                    ->searchable(),
                VisitsSparklineColumn::make('visits_count')
                    ->label('Visits')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->model(ShortURL::class)
                    ->modelLabel('link')
                    ->modalHeading('Create link')
                    ->icon('lucide-plus')
                    ->schema(self::linkFormSchema(requireKey: false))
                    ->using(fn (array $data): Model => self::createShortUrl($data)),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modelLabel('link')
                    ->modalHeading(fn (ShortURL $record): string => filled($record->title) ? (string) $record->title : 'Untitled')
                    ->modalWidth('6xl')
                    ->schema(self::linkViewSchema())
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false),
                EditAction::make()
                    ->modelLabel('link')
                    ->modalHeading('Edit link')
                    ->schema(self::linkFormSchema(requireKey: true))
                    ->using(fn (ShortURL $record, array $data): Model => self::updateShortUrl($record, $data)),
                Action::make('open')
                    ->label('Open')
                    ->icon('lucide-external-link')
                    ->url(fn (ShortURL $record): string => url($record->url_key))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateIcon('lucide-link')
            ->emptyStateHeading('No links yet')
            ->emptyStateDescription('Create your first short link to see it here.');
    }

    /**
     * @return array<int, mixed>
     */
    private static function linkFormSchema(bool $requireKey): array
    {
        return [
            TextInput::make('title')
                ->live(onBlur: true)
                ->afterStateUpdated(self::validateFieldOnBlur(...))
                ->maxLength(255),
            TextInput::make('destination_url')
                ->label('Destination URL')
                ->prefixIcon('lucide-link')
                ->live(onBlur: true)
                ->afterStateUpdated(self::validateFieldOnBlur(...))
                ->url()
                ->required()
                ->maxLength(2048),
            TextInput::make('url_key')
                ->label('Short key')
                ->prefix(url('/').'/')
                ->helperText($requireKey ? null : 'Leave blank to generate one automatically.')
                ->live(onBlur: true)
                ->afterStateUpdated(self::validateFieldOnBlur(...))
                ->required($requireKey)
                ->nullable(! $requireKey)
                ->maxLength(255)
                ->rules([
                    'regex:/^[A-Za-z0-9_-]+$/',
                    Rule::notIn(self::reservedKeys()),
                ])
                ->unique(table: 'short_urls', column: 'url_key', ignoreRecord: true),
            Select::make('redirect_status_code')
                ->label('Redirect type')
                ->options([
                    301 => '301 - Permanent',
                    302 => '302 - Temporary',
                ])
                ->default(302)
                ->required(),
            Toggle::make('track_visits')
                ->label('Collect visitor details')
                ->helperText('Every redirect records a timestamp for visit counts. Enable this to collect the selected metadata below.')
                ->default(true)
                ->live()
                ->onIcon('lucide-circle-check')
                ->offIcon('lucide-circle-x'),
            Fieldset::make('Visitor details')
                ->schema([
                    Toggle::make('track_referer_url')
                        ->label('Referrer site')
                        ->helperText('Stores only the HTTP or HTTPS origin, without credentials, paths, queries, or fragments.')
                        ->default(true),
                    Toggle::make('track_device_type')
                        ->label('Device type')
                        ->default(true),
                    Toggle::make('track_browser')
                        ->label('Browser')
                        ->default(true),
                    Toggle::make('track_browser_version')
                        ->label('Browser version')
                        ->default(true),
                    Toggle::make('track_operating_system')
                        ->label('Operating system')
                        ->default(true),
                    Toggle::make('track_operating_system_version')
                        ->label('Operating system version')
                        ->default(true),
                    Toggle::make('track_ip_address')
                        ->label('IP address')
                        ->helperText('Stores the visitor\'s full IP address.')
                        ->default(false),
                ])
                ->columns([
                    'default' => 1,
                    'sm' => 2,
                ])
                ->visible(fn (Get $get): bool => (bool) $get('track_visits')),
            Toggle::make('forward_query_params')
                ->label('Forward query parameters')
                ->default(false)
                ->onIcon('lucide-circle-check')
                ->offIcon('lucide-circle-x'),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private static function linkViewSchema(): array
    {
        return [
            Grid::make([
                'default' => 1,
                'lg' => 2,
            ])
                ->schema([
                    Section::make('Link')
                        ->icon('lucide-link')
                        ->schema([
                            SchemaView::make('filament.schemas.components.short-link-details')
                                ->viewData(fn (ShortURL $record): array => [
                                    'createdAt' => $record->created_at->diffForHumans(),
                                    'destinationUrl' => $record->destination_url,
                                    'forwardQueryParams' => $record->forward_query_params,
                                    'redirectLabel' => match ((int) $record->redirect_status_code) {
                                        301 => '301 Permanent',
                                        302 => '302 Temporary',
                                        default => (string) $record->redirect_status_code,
                                    },
                                    'shortUrl' => url($record->url_key),
                                    'trackVisits' => $record->track_visits,
                                    'updatedAt' => $record->updated_at->diffForHumans(),
                                    'visitsCount' => $record->visits_count ?? $record->visits()->count(),
                                ]),
                        ]),
                    Section::make('QR code')
                        ->icon('lucide-qr-code')
                        ->schema([
                            SchemaView::make('filament.schemas.components.short-link-qr')
                                ->viewData(fn (ShortURL $record): array => [
                                    'copyPngUrl' => route('short-links.qr.show', ['shortURL' => $record, 'format' => GenerateShortLinkQrCodeAsset::FORMAT_PNG]),
                                    'downloadPngFilename' => GenerateShortLinkQrCodeAsset::filename($record, GenerateShortLinkQrCodeAsset::FORMAT_PNG),
                                    'downloadPngUrl' => route('short-links.qr.download', ['shortURL' => $record, 'format' => GenerateShortLinkQrCodeAsset::FORMAT_PNG]),
                                    'downloadSvgFilename' => GenerateShortLinkQrCodeAsset::filename($record, GenerateShortLinkQrCodeAsset::FORMAT_SVG),
                                    'downloadSvgUrl' => route('short-links.qr.download', ['shortURL' => $record, 'format' => GenerateShortLinkQrCodeAsset::FORMAT_SVG]),
                                    'shortUrl' => url($record->url_key),
                                    'svgUrl' => route('short-links.qr.show', ['shortURL' => $record, 'format' => GenerateShortLinkQrCodeAsset::FORMAT_SVG]),
                                    'transparentCopyPngUrl' => route('short-links.qr.show', ['shortURL' => $record, 'format' => GenerateShortLinkQrCodeAsset::FORMAT_PNG, 'background' => GenerateShortLinkQrCodeAsset::BACKGROUND_TRANSPARENT]),
                                    'transparentDownloadPngFilename' => GenerateShortLinkQrCodeAsset::filename($record, GenerateShortLinkQrCodeAsset::FORMAT_PNG, GenerateShortLinkQrCodeAsset::BACKGROUND_TRANSPARENT),
                                    'transparentDownloadPngUrl' => route('short-links.qr.download', ['shortURL' => $record, 'format' => GenerateShortLinkQrCodeAsset::FORMAT_PNG, 'background' => GenerateShortLinkQrCodeAsset::BACKGROUND_TRANSPARENT]),
                                    'transparentDownloadSvgFilename' => GenerateShortLinkQrCodeAsset::filename($record, GenerateShortLinkQrCodeAsset::FORMAT_SVG, GenerateShortLinkQrCodeAsset::BACKGROUND_TRANSPARENT),
                                    'transparentDownloadSvgUrl' => route('short-links.qr.download', ['shortURL' => $record, 'format' => GenerateShortLinkQrCodeAsset::FORMAT_SVG, 'background' => GenerateShortLinkQrCodeAsset::BACKGROUND_TRANSPARENT]),
                                    'transparentSvgUrl' => route('short-links.qr.show', ['shortURL' => $record, 'format' => GenerateShortLinkQrCodeAsset::FORMAT_SVG, 'background' => GenerateShortLinkQrCodeAsset::BACKGROUND_TRANSPARENT]),
                                ]),
                        ]),
                ]),
        ];
    }

    private static function validateFieldOnBlur(SchemaComponent $component, LivewireComponent $livewire): void
    {
        $livewire->validateOnly($component->getStatePath());
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function createShortUrl(array $data): ShortURL
    {
        $builder = ShortURLBuilder::destinationUrl($data['destination_url'])
            ->trackVisits((bool) $data['track_visits'])
            ->trackIPAddress((bool) $data['track_ip_address'])
            ->trackOperatingSystem((bool) $data['track_operating_system'])
            ->trackOperatingSystemVersion((bool) $data['track_operating_system_version'])
            ->trackBrowser((bool) $data['track_browser'])
            ->trackBrowserVersion((bool) $data['track_browser_version'])
            ->trackRefererURL((bool) $data['track_referer_url'])
            ->trackDeviceType((bool) $data['track_device_type'])
            ->forwardQueryParams((bool) $data['forward_query_params'])
            ->redirectStatusCode((int) $data['redirect_status_code']);

        if (filled($data['url_key'] ?? null)) {
            $builder->urlKey($data['url_key']);
        }

        $record = $builder->make();

        $record->forceFill([
            'title' => $data['title'] ?? null,
        ])->save();

        return $record;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function updateShortUrl(ShortURL $record, array $data): ShortURL
    {
        $record->forceFill([
            ...$data,
            'destination_url' => self::normalizeDestinationUrl((string) $data['destination_url']),
            'default_short_url' => url($data['url_key']),
        ])->save();

        app(ForgetShortLinkQrCodeAssets::class)->handle($record);

        return $record;
    }

    private static function normalizeDestinationUrl(string $destinationUrl): string
    {
        if (config('short-url.enforce_https')) {
            return str_replace('http://', 'https://', $destinationUrl);
        }

        return $destinationUrl;
    }

    /**
     * @return list<string>
     */
    private static function reservedKeys(): array
    {
        return [
            'app',
            'login',
            'logout',
            'register',
            'forgot-password',
            'reset-password',
            'email',
            'api',
            'livewire',
            'storage',
            'build',
            'assets',
            'settings',
            'dashboard',
            'up',
            'flux',
            'filament',
            'user',
            'passkeys',
            'two-factor-challenge',
        ];
    }
}
