<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use App\Models\Document;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('previewPdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->visible(fn (Document $record) => (bool) $record->path)
                ->modalHeading('Report Preview')
                ->modalWidth('7xl')
                ->modalSubmitAction(false)
                ->modalContent(fn (Document $record) => view('filament.reports.pdf-preview', [
                    'url' => route('reports.pdf', $record->id),
                ])),
            Actions\Action::make('previewSignedPdf')
                ->label('Preview Signed')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn (Document $record) => (bool) $record->signed_path)
                ->modalHeading('Signed Report Preview')
                ->modalWidth('7xl')
                ->modalSubmitAction(false)
                ->modalContent(fn (Document $record) => view('filament.reports.pdf-preview', [
                    'url' => route('reports.pdf.signed', $record->id),
                ])),
            Actions\DeleteAction::make(),
        ];
    }
}
