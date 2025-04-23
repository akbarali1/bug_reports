<?php
declare(strict_types=1);


namespace form;

use App\Filament\Admin\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SettingResource extends Resource
{
	protected static ?string $model          = Setting::class;
	protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
	
	public static function form(Form $form): Form
	{
		return $form->schema([
			TextInput::make('key')
				->label('Kalit (key)')
				->required()
				->disabledOn('edit')
				->unique(ignoreRecord: true)
				->maxLength(255),
			
			Select::make('group')
				->label('Guruh')
				->options([
					'register'  => 'Ro‘yxatdan o‘tish',
					'birthday'  => 'Tug‘ilgan kun',
					'rules'     => 'Aksiya qoidalari',
					'promocode' => 'Promokodlar',
					'bonus'     => 'Ballar',
					'general'   => 'Umumiy',
				])
				->required()
				->searchable(),
			
			Select::make('type')
				->default('text')
				->disabledOn('edit')
				->native(false)
				->label('Turi')
				->required()
				->reactive()
				->options([
					'text'   => 'Matn',
					'date'   => 'Sana',
					'file'   => 'Fayl (rasm/video)',
					'number' => 'Raqam',
				]),
			
			TextInput::make('value')
				->dehydrated(fn(Get $get) => $get('type') === 'text')
				->default(fn($record) => $record?->type === 'text' ? $record->value : null)
				->label('Qiymat (matn)')
				->visible(fn($get) => $get('type') === 'text'),
			
			// NUMBER
			TextInput::make('value')
				->label('Qiymat (raqam)')
				->numeric()
				->visible(fn($get) => $get('type') === 'number')
				->default(fn($record) => $record?->type === 'number' ? $record->value : null)
				->dehydrateStateUsing(fn($state, $get) => $get('type') === 'number' ? $state : null)
				->dehydrated(fn($get) => $get('type') === 'number'),
			
			DatePicker::make('value')
				->label('Qiymat (sana)')
				->visible(fn($get) => $get('type') === 'date'),
			
			FileUpload::make('value')
				->label('Fayl(lar)')
				->disk('public')
				->directory('settings')
				->multiple()
				->maxFiles(3)
				->visible(fn($get) => $get('type') === 'file')
				->preserveFilenames()
				->acceptedFileTypes(['image/*', 'video/*'])
				->formatStateUsing(function ($state) {
					if (is_null($state)) {
						return null;
					}
					
					return is_array($state) ? $state : json_decode($state, true);
				})
				->dehydrateStateUsing(fn($state) => json_encode($state)),
			
			Textarea::make('description')
				->label('Izoh')
				->rows(5),
		]);
	}
	
	/**
	 * @throws \Exception
	 */
	public static function table(Table $table): Table
	{
		return $table->columns([
			TextColumn::make('key')
				->toggleable(isToggledHiddenByDefault: true)
				->searchable()
				->label('Kalit')
				->searchable()
				->sortable(),
			TextColumn::make('value')
				->searchable()
				->label('Qiymat')
				->limit(50)
				->description(fn(Setting $setting): string => $setting->description)
				->toggleable(),
			TextColumn::make('group')
				->searchable()
				->label('Guruh')
				->sortable(),
			TextColumn::make('updated_at')
				->date("d.m.Y H:i")
				->sortable(),
		])
			->actions([
				ViewAction::make(),
				EditAction::make(),
				DeleteAction::make(),
			])
			->filters([
				SelectFilter::make('group')
					->label('Guruh bo‘yicha')
					->options([
						'register'  => 'Ro‘yxatdan o‘tish',
						'birthday'  => 'Tug‘ilgan kun',
						'rules'     => 'Aksiya qoidalari',
						'promocode' => 'Promokodlar',
						'bonus'     => 'Ballar',
						'general'   => 'Umumiy',
					]),
			])
			->bulkActions([
				BulkActionGroup::make([
					DeleteBulkAction::make(),
				]),
			])
			->defaultSort('updated_at', 'desc');
	}
	
	public static function getPages(): array
	{
		return [
			'index'  => Pages\ListSettings::route('/'),
			'create' => Pages\CreateSetting::route('/create'),
			'edit'   => Pages\EditSetting::route('/{record}/edit'),
		];
	}
}
