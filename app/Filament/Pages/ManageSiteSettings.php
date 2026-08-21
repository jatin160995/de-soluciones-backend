<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;
use Filament\Forms\Components\FileUpload;

/**
 * Website-level (platform-wide) header & footer editor.
 *
 * Reads/writes the site_settings key/value store via the SiteSetting model, so
 * every value here is shared across all stores in the future multi-vendor phase
 * (announcement bar, header/footer menus, social links, contact info). The
 * storefront layout renders straight from these settings via StorefrontComposer.
 */
class ManageSiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.manage-site-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Configuración del Sitio';

    protected static ?string $title = 'Configuración del Sitio';

    protected static ?int $navigationSort = 90;

    /** Form state container. */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        $map = SiteSetting::map();

        $this->form->fill([
            'site_name'          => $map['site_name'] ?? 'DE Soluciones',
            'site_logo'          => $map['site_logo'] ?? null,
            'announcement_items' => $map['announcement_items'] ?? [],
            'header_menu'        => $map['header_menu'] ?? [],
            'footer_menu_tienda' => $map['footer_menu_tienda'] ?? ['heading' => 'Tienda', 'items' => []],
            'footer_menu_ayuda'  => $map['footer_menu_ayuda'] ?? ['heading' => 'Ayuda', 'items' => []],
            'social_links'       => $map['social_links'] ?? ['instagram' => '', 'facebook' => '', 'whatsapp' => '', 'tiktok' => ''],
            'footer_about'       => $map['footer_about'] ?? '',
            'contact_address'    => $map['contact_address'] ?? '',
            'contact_phone'      => $map['contact_phone'] ?? '',
            'contact_email'      => $map['contact_email'] ?? '',
            'support_phone'      => $map['support_phone'] ?? '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General')
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Nombre del sitio')
                            ->required()
                            ->maxLength(100),
                        FileUpload::make('site_logo')
                            ->label('Logo del sitio')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('site')
                            ->visibility('public')
                            ->helperText('Si lo dejas vacío, se muestra el logotipo por defecto.'),

                    ]),

                Section::make('Barra de anuncios')
                    ->description('Mensajes que se desplazan en la parte superior del sitio.')
                    ->schema([
                        Repeater::make('announcement_items')
                            ->label('Anuncios')
                            ->schema([
                                TextInput::make('icon')
                                    ->label('Icono')
                                    ->helperText('Clase de Bootstrap Icons, ej: bi-truck')
                                    ->required(),
                                TextInput::make('text')
                                    ->label('Texto')
                                    ->required(),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['text'] ?? null)
                            ->addActionLabel('Agregar anuncio'),
                    ]),

                Section::make('Menú del encabezado')
                    ->description('Enlaces de la barra de navegación principal.')
                    ->schema([
                        Repeater::make('header_menu')
                            ->label('Enlaces')
                            ->schema([
                                TextInput::make('label')->label('Texto')->required(),
                                TextInput::make('url')->label('URL')->required(),
                                Toggle::make('hot')
                                    ->label('Destacar (punto rojo)')
                                    ->default(false)
                                    ->inline(false),
                            ])
                            ->columns(3)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['label'] ?? null)
                            ->addActionLabel('Agregar enlace'),
                    ]),

                Section::make('Pie de página — Columna "Tienda"')
                    ->schema([
                        TextInput::make('footer_menu_tienda.heading')->label('Título')->required(),
                        Repeater::make('footer_menu_tienda.items')
                            ->label('Enlaces')
                            ->schema([
                                TextInput::make('label')->label('Texto')->required(),
                                TextInput::make('url')->label('URL')->required(),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['label'] ?? null)
                            ->addActionLabel('Agregar enlace'),
                    ]),

                Section::make('Pie de página — Columna "Ayuda"')
                    ->schema([
                        TextInput::make('footer_menu_ayuda.heading')->label('Título')->required(),
                        Repeater::make('footer_menu_ayuda.items')
                            ->label('Enlaces')
                            ->schema([
                                TextInput::make('label')->label('Texto')->required(),
                                TextInput::make('url')->label('URL')->required(),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['label'] ?? null)
                            ->addActionLabel('Agregar enlace'),
                    ]),

                Section::make('Redes sociales')
                    ->description('Pega el enlace completo de cada red. Deja el campo vacío para ocultar ese icono.')
                    ->schema([
                        TextInput::make('social_links.instagram')->label('Instagram'),
                        TextInput::make('social_links.facebook')->label('Facebook'),
                        TextInput::make('social_links.whatsapp')->label('WhatsApp'),
                        TextInput::make('social_links.tiktok')->label('TikTok'),
                    ])
                    ->columns(2),

                Section::make('Información de contacto')
                    ->schema([
                        Textarea::make('footer_about')
                            ->label('Descripción del pie de página')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('contact_address')->label('Dirección'),
                        TextInput::make('contact_phone')->label('Teléfono'),
                        TextInput::make('contact_email')->label('Correo electrónico')->email(),
                        TextInput::make('support_phone')->label('Teléfono de soporte (barra de navegación)'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar cambios')
                ->action(fn() => $this->save())
                ->keyBindings(['mod+s']),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        SiteSetting::put('site_name', $state['site_name'] ?? 'DE Soluciones');
        SiteSetting::put('site_logo', $state['site_logo'] ?? null);
        SiteSetting::put('announcement_items', array_values($state['announcement_items'] ?? []));
        SiteSetting::put('header_menu', array_values($state['header_menu'] ?? []));
        SiteSetting::put('footer_menu_tienda', [
            'heading' => $state['footer_menu_tienda']['heading'] ?? 'Tienda',
            'items'   => array_values($state['footer_menu_tienda']['items'] ?? []),
        ]);
        SiteSetting::put('footer_menu_ayuda', [
            'heading' => $state['footer_menu_ayuda']['heading'] ?? 'Ayuda',
            'items'   => array_values($state['footer_menu_ayuda']['items'] ?? []),
        ]);
        SiteSetting::put('social_links', $state['social_links'] ?? []);
        SiteSetting::put('footer_about', $state['footer_about'] ?? '');
        SiteSetting::put('contact_address', $state['contact_address'] ?? '');
        SiteSetting::put('contact_phone', $state['contact_phone'] ?? '');
        SiteSetting::put('contact_email', $state['contact_email'] ?? '');
        SiteSetting::put('support_phone', $state['support_phone'] ?? '');

        Notification::make()
            ->title('Configuración guardada')
            ->success()
            ->send();
    }
}
