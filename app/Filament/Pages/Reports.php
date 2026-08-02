<?php

namespace App\Filament\Pages;

use App\Models\Department;
use App\Models\User;
use App\Services\ReportDataService;
use App\Services\ReportPdfService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';

    protected static ?string $title = 'Reports (PDF)';

    protected static ?int $navigationSort = 99;

    protected static string $view = 'filament.pages.reports';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->endOfMonth()->format('Y-m-d'),
            'department_id' => null,
            'user_id' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->native(false)
                    ->displayFormat('Y-m-d'),
                DatePicker::make('end_date')
                    ->label('End Date')
                    ->native(false)
                    ->displayFormat('Y-m-d'),
                Select::make('department_id')
                    ->label('Department')
                    ->placeholder('All Departments')
                    ->options(fn () => Department::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
                Select::make('user_id')
                    ->label('Employee')
                    ->placeholder('All Employees')
                    ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->columns(2)
            ->statePath('data');
    }

    /**
     * Filters as expected by ReportPdfService (dates as Y-m-d strings).
     *
     * @return array<string, mixed>
     */
    protected function filters(): array
    {
        $state = $this->form->getState();

        return [
            'start_date' => $state['start_date'] ?? null,
            'end_date' => $state['end_date'] ?? null,
            'department_id' => $state['department_id'] ?? null,
            'user_id' => $state['user_id'] ?? null,
        ];
    }

    protected function download(string $pdf, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf;
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    /**
     * Inline preview URL for a report type, carrying the current form filters as
     * query params. The preview modal (filament.reports.pdf-preview) reuses this
     * URL for its iframe, Print, and Download (adds ?download=1) buttons.
     */
    protected function reportUrl(string $type): string
    {
        $query = http_build_query(array_filter([
            'start_date' => $this->data['start_date'] ?? null,
            'end_date' => $this->data['end_date'] ?? null,
            'department_id' => $this->data['department_id'] ?? null,
            'user_id' => $this->data['user_id'] ?? null,
        ], fn ($v) => $v !== null && $v !== ''));

        return route('admin.reports.pdf', ['type' => $type]) . ($query ? '?' . $query : '');
    }

    /**
     * A header action that opens the PDF in a preview modal (view first), with
     * Print / Download available inside the modal instead of an instant download.
     */
    protected function previewAction(string $type, string $label, string $icon, string $color): Action
    {
        return Action::make($type)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->modalHeading($label)
            ->modalWidth('7xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->modalContent(fn () => view('filament.reports.pdf-preview', [
                'url' => $this->reportUrl($type),
            ]));
    }

    /**
     * A header action that opens the report's DATA as an on-screen table (built by
     * ReportDataService using the same queries/filters as the PDF) so the admin can
     * review the records before printing or downloading. Reads the current form
     * filters at the moment the modal opens.
     */
    protected function viewDataAction(string $type, string $label, string $icon, string $color): Action
    {
        return Action::make($type . 'Data')
            ->label('View Data')
            ->icon($icon)
            ->color($color)
            ->modalHeading($label . ' — Data')
            ->modalWidth('7xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->modalContent(fn () => view('filament.reports.data-table', [
                'data' => app(ReportDataService::class)->{$type}($this->filters()),
                'title' => $label,
            ]));
    }

    /**
     * Groups a single report's two actions ("View Data" + "Preview PDF") under one
     * tidy dropdown button in the header.
     */
    protected function reportGroup(string $type, string $label, string $icon, string $color): ActionGroup
    {
        return ActionGroup::make([
            $this->viewDataAction($type, $label, $icon, $color),
            $this->previewAction($type, $label, $icon, $color),
        ])
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->button();
    }

    /**
     * @return array<ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            $this->reportGroup('attendance', 'Attendance Report', 'heroicon-o-clipboard-document-list', 'primary'),
            $this->reportGroup('leave', 'Leave Report', 'heroicon-o-calendar-days', 'success'),
            $this->reportGroup('employee', 'Employee Report', 'heroicon-o-users', 'warning'),
            $this->reportGroup('department', 'Department Report', 'heroicon-o-building-office-2', 'gray'),
            $this->reportGroup('monthlySummary', 'Monthly Summary', 'heroicon-o-chart-bar', 'info'),
            $this->reportGroup('audit', 'Audit Report', 'heroicon-o-shield-check', 'danger'),
        ];
    }
}
