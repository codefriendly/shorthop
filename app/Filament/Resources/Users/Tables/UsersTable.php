<?php

namespace App\Filament\Resources\Users\Tables;

use App\Actions\Users\DisableUser;
use App\Actions\Users\EnableUser;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->badge()
                    ->separator(',')
                    ->placeholder('No roles'),
                TextColumn::make('disabled_at')
                    ->label('Status')
                    ->badge()
                    ->state(fn (User $record): string => $record->isDisabled() ? 'Disabled' : 'Active')
                    ->color(fn (User $record): string => $record->isDisabled() ? 'danger' : 'success'),
                IconColumn::make('two_factor_confirmed_at')
                    ->label('2FA')
                    ->boolean()
                    ->state(fn (User $record): bool => $record->two_factor_confirmed_at !== null),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('disabled_at')
                    ->label('Status')
                    ->placeholder('All users')
                    ->trueLabel('Disabled')
                    ->falseLabel('Active')
                    ->nullable(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('disable')
                    ->label('Disable')
                    ->icon('lucide-user-x')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => ! $record->isDisabled())
                    ->disabled(fn (User $record): bool => $record->is(Filament::auth()->user()))
                    ->action(function (User $record): void {
                        self::disableRecord($record);
                    }),
                Action::make('enable')
                    ->label('Enable')
                    ->icon('lucide-user-check')
                    ->color('success')
                    ->visible(fn (User $record): bool => $record->isDisabled())
                    ->action(function (User $record): void {
                        app(EnableUser::class)->handle($record);
                    }),
            ]);
    }

    private static function disableRecord(User $record): void
    {
        $actor = Filament::auth()->user();

        if (! $actor instanceof User) {
            return;
        }

        app(DisableUser::class)->handle($record, $actor);
    }
}
