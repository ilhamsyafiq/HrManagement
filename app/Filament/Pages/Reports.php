<?php

namespace App\Filament\Pages;

use App\Models\Department;
use App\Models\User;
use App\Services\ReportDataService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';

    protected static ?string $title = 'Reports';

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
                    ->label('Start date')
                    ->native(false)
                    ->displayFormat('d M Y'),
                DatePicker::make('end_date')
                    ->label('End date')
                    ->native(false)
                    ->displayFormat('d M Y'),
                Select::make('department_id')
                    ->label('Department')
                    ->placeholder('All departments')
                    ->options(fn () => Department::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
                Select::make('user_id')
                    ->label('Employee')
                    ->placeholder('All employees')
                    ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->columns(2)
            ->statePath('data');
    }

    /**
     * Report definitions for the card grid.
     *
     * @return array<int, array<string, string>>
     */
    public function reportCards(): array
    {
        return [
            ['action' => 'attendanceReportAction', 'label' => 'Attendance', 'icon' => 'heroicon-o-clipboard-document-list', 'desc' => 'Clock in/out, late arrivals and overtime per employee.'],
            ['action' => 'leaveReportAction', 'label' => 'Leave', 'icon' => 'heroicon-o-calendar-days', 'desc' => 'Annual, medical and emergency leave taken in the range.'],
            ['action' => 'employeeReportAction', 'label' => 'Employee', 'icon' => 'heroicon-o-users', 'desc' => 'Directory with role, department and employment status.'],
            ['action' => 'departmentReportAction', 'label' => 'Department', 'icon' => 'heroicon-o-building-office-2', 'desc' => 'Headcount and totals grouped by department.'],
            ['action' => 'monthlySummaryReportAction', 'label' => 'Monthly summary', 'icon' => 'heroicon-o-chart-bar', 'desc' => 'Attendance and leave totals for the selected month.'],
            ['action' => 'auditReportAction', 'label' => 'Audit', 'icon' => 'heroicon-o-shield-check', 'desc' => 'Record of changes with old → new values.'],
        ];
    }

    /**
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
     * One action per report — opens a tabbed modal: on-screen Data + PDF preview
     * (with print/download inside). Same filters/queries as the PDF.
     */
    protected function buildReportAction(string $name, string $type, string $label): Action
    {
        return Action::make($name)
            ->label('Open report')
            ->icon('heroicon-m-arrow-up-right')
            ->color('primary')
            ->modalHeading($label . ' report')
            ->modalWidth('7xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->modalContent(fn () => view('filament.reports.report-modal', [
                'title' => $label,
                'data' => app(ReportDataService::class)->{$type}($this->filters()),
                'url' => $this->reportUrl($type),
            ]));
    }

    public function attendanceReportAction(): Action
    {
        return $this->buildReportAction('attendanceReport', 'attendance', 'Attendance');
    }

    public function leaveReportAction(): Action
    {
        return $this->buildReportAction('leaveReport', 'leave', 'Leave');
    }

    public function employeeReportAction(): Action
    {
        return $this->buildReportAction('employeeReport', 'employee', 'Employee');
    }

    public function departmentReportAction(): Action
    {
        return $this->buildReportAction('departmentReport', 'department', 'Department');
    }

    public function monthlySummaryReportAction(): Action
    {
        return $this->buildReportAction('monthlySummaryReport', 'monthlySummary', 'Monthly summary');
    }

    public function auditReportAction(): Action
    {
        return $this->buildReportAction('auditReport', 'audit', 'Audit');
    }
}
