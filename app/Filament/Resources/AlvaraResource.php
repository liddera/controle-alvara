<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlvaraResource\Pages;
use App\Filament\Resources\AlvaraResource\RelationManagers;
use App\Models\Alvara;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AlvaraResource extends Resource
{
    protected static ?string $model = Alvara::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Alvarás';

    protected static ?string $modelLabel = 'Alvará';

    protected static ?string $pluralModelLabel = 'Alvarás';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dados do Alvará')
                    ->schema([
                        Forms\Components\Select::make('empresa_id')
                            ->relationship('empresa', 'nome')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Empresa'),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(auth()->id())
                            ->label('Proprietário'),
                        Forms\Components\TextInput::make('tipo')
                            ->required()
                            ->placeholder('Ex: Alvará de Funcionamento')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('numero')
                            ->label('Número / Protocolo')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('data_emissao')
                            ->label('Data de Emissão'),
                        Forms\Components\DatePicker::make('data_vencimento')
                            ->required()
                            ->label('Data de Vencimento'),
                    ])->columns(2),

                Forms\Components\Section::make('Informações Adicionais')
                    ->schema([
                        Forms\Components\Textarea::make('observacoes')
                            ->label('Observações')
                            ->rows(3),
                        Forms\Components\Select::make('status')
                            ->options([
                                'vigente' => 'Vigente',
                                'proximo' => 'Próximo ao Vencimento',
                                'vencido' => 'Vencido',
                            ])
                            ->required()
                            ->default('vigente'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tipo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('empresa.nome')
                    ->searchable()
                    ->sortable()
                    ->label('Empresa'),
                Tables\Columns\TextColumn::make('data_vencimento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('Vencimento'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'vigente' => 'success',
                        'proximo' => 'warning',
                        'vencido' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('numero')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'vigente' => 'Vigente',
                        'proximo' => 'Próximo',
                        'vencido' => 'Vencido',
                    ]),
                Tables\Filters\SelectFilter::make('empresa')
                    ->relationship('empresa', 'nome'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlvaras::route('/'),
            'create' => Pages\CreateAlvara::route('/create'),
            'edit' => Pages\EditAlvara::route('/{record}/edit'),
        ];
    }
}
