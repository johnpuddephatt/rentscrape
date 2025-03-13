<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Concerns\HasPreview;
use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\EditRecord;
use Pboivin\FilamentPeek\Pages\Actions\PreviewAction;
use Pboivin\FilamentPeek\Pages\Concerns\HasPreviewModal;
use Filament\Support\Enums\MaxWidth;

class EditPost extends EditRecord
{
    use HasPreview, HasPreviewModal;

    protected static string $view = 'filament.pages.edit';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
    /**
     * The resource model.
     */
    protected static string $resource = PostResource::class;

    /**
     * The preview modal URL.
     */
    protected function getPreviewModalUrl(): ?string
    {
        $this->generatePreviewSession();

        return route('post.show', [
            'post' => $this->record->slug,
            'previewToken' => $this->previewToken,
        ]);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction(),
            $this->getSaveFormAction(),
        ];
    }

    /**
     * The header actions.
     */
    protected function getHeaderActions(): array
    {
        return [

            ActionGroup::make([
                PreviewAction::make(),

                Actions\Action::make('view')
                    ->label('Visit post')
                    ->url(fn($record) => $record->url)
                    ->extraAttributes(['target' => '_blank']),

                Actions\DeleteAction::make(),
            ]),
        ];
    }
}
