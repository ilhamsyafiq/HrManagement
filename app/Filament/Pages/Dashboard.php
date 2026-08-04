<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Str;

/**
 * Admin dashboard with per-user customization.
 *
 * Each admin can choose which widgets appear and drag to reorder them; the choice
 * is persisted on the user (`users.dashboard_preferences`). When no preference is
 * saved the panel's default widget order (from AdminPanelProvider) is used.
 *
 * Ordering works because the Filament widgets Blade component renders in array
 * order — overriding getWidgets() to return a reordered/filtered array is enough.
 */
class Dashboard extends BaseDashboard
{
    /**
     * Friendly labels for the customization list, keyed by widget class.
     * Any widget missing here falls back to a humanized class name.
     */
    protected static function widgetLabels(): array
    {
        return [
            \App\Filament\Widgets\StatsOverview::class => 'Key metrics (KPI cards)',
            \App\Filament\Widgets\TodayAttendanceChart::class => 'Workforce status today',
            \App\Filament\Widgets\LeavesByTypeChart::class => 'Leaves by type (this year)',
            \App\Filament\Widgets\AttendanceRateChart::class => 'Daily attendance rate (30 days)',
            \App\Filament\Widgets\AttendanceTrendChart::class => 'Attendance & late arrivals (6 months)',
            \App\Filament\Widgets\LeaveStatusChart::class => 'Leave requests by status',
            \App\Filament\Widgets\HeadcountByRoleChart::class => 'Headcount by role',
            \App\Filament\Widgets\DepartmentHeadcountChart::class => 'Headcount by department',
            \App\Filament\Widgets\PendingApprovalsOverview::class => 'Pending approvals',
            \App\Filament\Widgets\UpcomingLeavesTable::class => 'Upcoming approved leaves',
        ];
    }

    public static function widgetLabel(string $class): string
    {
        return static::widgetLabels()[$class] ?? Str::headline(class_basename($class));
    }

    /**
     * The saved preference rows for the current user, or null when unset.
     *
     * @return array<int, array{key: string, visible: bool}>|null
     */
    protected function widgetPreferences(): ?array
    {
        $prefs = auth()->user()?->dashboard_preferences;

        return is_array($prefs) && ! empty($prefs['widgets']) ? $prefs['widgets'] : null;
    }

    /**
     * Panel-registered widget classes in their default (panel) order.
     *
     * @return array<int, string>
     */
    protected function registeredWidgetKeys(): array
    {
        return array_map(
            fn ($widget) => is_string($widget) ? $widget : $widget->widget,
            parent::getWidgets(),
        );
    }

    /**
     * Apply the user's visibility + ordering preferences over the panel widgets.
     */
    public function getWidgets(): array
    {
        $registered = parent::getWidgets();
        $prefs = $this->widgetPreferences();

        if (! $prefs) {
            return $registered;
        }

        // Index by class string so we can pull in preferred order and detect
        // any widgets added to the panel since the preference was saved.
        $remaining = [];
        foreach ($registered as $widget) {
            $remaining[is_string($widget) ? $widget : $widget->widget] = $widget;
        }

        $ordered = [];
        foreach ($prefs as $row) {
            $key = $row['key'] ?? null;
            if ($key === null || ! array_key_exists($key, $remaining)) {
                continue;
            }
            if ($row['visible'] ?? true) {
                $ordered[] = $remaining[$key];
            }
            unset($remaining[$key]);
        }

        // Newly-added widgets not covered by saved prefs stay visible, appended last.
        foreach ($remaining as $widget) {
            $ordered[] = $widget;
        }

        return $ordered;
    }

    /**
     * Rows to seed the customization form: saved prefs merged with the current
     * widget set (drops removed widgets, appends new ones as visible).
     *
     * @return array<int, array{key: string, visible: bool}>
     */
    protected function customizationRows(): array
    {
        $registered = $this->registeredWidgetKeys();
        $prefs = $this->widgetPreferences();

        $rows = [];
        $seen = [];

        if ($prefs) {
            foreach ($prefs as $row) {
                $key = $row['key'] ?? null;
                if ($key !== null && in_array($key, $registered, true)) {
                    $rows[] = ['key' => $key, 'visible' => (bool) ($row['visible'] ?? true)];
                    $seen[$key] = true;
                }
            }
        }

        foreach ($registered as $key) {
            if (! isset($seen[$key])) {
                $rows[] = ['key' => $key, 'visible' => true];
            }
        }

        return $rows;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('customizeDashboard')
                ->label('Customize')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('gray')
                ->modalHeading('Customize dashboard')
                ->modalDescription('Switch widgets on or off. Drag a row to change the order.')
                ->modalSubmitActionLabel('Save')
                ->modalWidth('md')
                ->fillForm(fn (): array => ['widgets' => $this->customizationRows()])
                ->form([
                    Forms\Components\Repeater::make('widgets')
                        ->hiddenLabel()
                        ->reorderable()
                        ->addable(false)
                        ->deletable(false)
                        ->collapsible(false)
                        ->itemLabel(fn (array $state): ?string => isset($state['key'])
                            ? static::widgetLabel($state['key'])
                            : null)
                        ->schema([
                            Forms\Components\Hidden::make('key'),
                            Forms\Components\Toggle::make('visible')
                                ->hiddenLabel()
                                ->inline()
                                ->default(true),
                        ]),
                ])
                ->action(function (array $data): void {
                    $rows = collect($data['widgets'] ?? [])
                        ->map(fn ($row) => [
                            'key' => $row['key'] ?? null,
                            'visible' => (bool) ($row['visible'] ?? true),
                        ])
                        ->filter(fn ($row) => $row['key'] !== null)
                        ->values()
                        ->all();

                    $user = auth()->user();
                    $prefs = $user->dashboard_preferences ?? [];
                    $prefs['widgets'] = $rows;
                    $user->dashboard_preferences = $prefs;
                    $user->save();

                    Notification::make()
                        ->title('Dashboard layout saved')
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl());
                }),

            Action::make('resetDashboard')
                ->label('Reset')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->link()
                ->visible(fn (): bool => $this->widgetPreferences() !== null)
                ->requiresConfirmation()
                ->modalHeading('Reset dashboard layout')
                ->modalDescription('This restores the default widgets and order.')
                ->action(function (): void {
                    $user = auth()->user();
                    $prefs = $user->dashboard_preferences ?? [];
                    unset($prefs['widgets']);
                    $user->dashboard_preferences = $prefs ?: null;
                    $user->save();

                    Notification::make()
                        ->title('Dashboard reset to default')
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl());
                }),
        ];
    }
}
