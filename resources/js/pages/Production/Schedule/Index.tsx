import DataTableWrapper, { DataTableWrapperRef } from '@/components/datatables';
import Heading from '@/components/heading';
import PageContainer from '@/components/page-container';
import ScheduleCard from '@/components/schedule-card';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import type { DataTableColumn } from '@/types/DataTables';
import type { Line, Schedule } from '@/types/production';
import { toast } from '@/utils/toast';
import { closestCenter, DndContext, DragEndEvent, DragOverlay, DragStartEvent, PointerSensor, useSensor, useSensors, useDroppable } from '@dnd-kit/core';
import { SortableContext, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { Head, Link, router } from '@inertiajs/react';
import { CalendarDays, Grid3x3, LayoutList, Loader2, Plus, RefreshCw } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

interface ScheduleIndexProps {
    lines: Line[];
    schedules?: Schedule[];
}

type ViewMode = 'list' | 'kanban';
type ScheduleStatus = 'pending' | 'in_progress' | 'completed' | 'delayed';

interface KanbanColumn {
    id: ScheduleStatus;
    title: string;
    description: string;
    color: string;
}

const kanbanColumns: KanbanColumn[] = [
    { id: 'pending', title: 'Pending', description: 'Not started yet', color: 'border-slate-500/50 bg-slate-500/10' },
    { id: 'in_progress', title: 'In Progress', description: 'Currently in production', color: 'border-blue-500/50 bg-blue-500/10' },
    { id: 'delayed', title: 'Delayed', description: 'Behind schedule', color: 'border-red-500/50 bg-red-500/10' },
    { id: 'completed', title: 'Completed', description: 'Finished', color: 'border-green-500/50 bg-green-500/10' },
];

// Droppable Column Component
function KanbanColumnDroppable({
    column,
    schedules,
    onView
}: {
    column: KanbanColumn;
    schedules: Schedule[];
    onView: (schedule: Schedule) => void;
}) {
    const { setNodeRef, isOver } = useDroppable({
        id: column.id,
    });

    const textColorClass = {
        pending: 'text-slate-600 dark:text-slate-400',
        in_progress: 'text-blue-600 dark:text-blue-400',
        delayed: 'text-red-600 dark:text-red-400',
        completed: 'text-green-600 dark:text-green-400',
    }[column.id];

    return (
        <Card className="flex h-[calc(100vh-450px)] min-h-[500px] flex-col">
            <CardHeader className={`border-b ${column.color}`}>
                <CardTitle className={`flex items-center justify-between text-base ${textColorClass}`}>
                    <span>{column.title}</span>
                    <span className="rounded-full bg-background px-2.5 py-0.5 text-sm font-semibold">
                        {schedules.length}
                    </span>
                </CardTitle>
            </CardHeader>

            <CardContent
                ref={setNodeRef}
                className={`flex-1 overflow-y-auto p-3 transition-colors ${
                    isOver ? 'bg-accent/50' : ''
                }`}
            >
                <SortableContext items={schedules.map((s) => s.id)} strategy={verticalListSortingStrategy}>
                    {schedules.length === 0 ? (
                        <div className="flex h-32 items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/25">
                            <p className="text-sm text-muted-foreground">No schedules</p>
                        </div>
                    ) : (
                        schedules.map((schedule) => (
                            <ScheduleCard key={schedule.id} schedule={schedule} onView={onView} />
                        ))
                    )}
                </SortableContext>
            </CardContent>
        </Card>
    );
}

const tableColumns: DataTableColumn<Schedule>[] = [
    { data: 'id', title: 'ID', className: 'desktop', width: '60px' },
    {
        data: 'order',
        title: 'Order',
        className: 'all',
        render: (_data, _type, row: Schedule) => {
            return row.order ? `${row.order.order_number}<br/><small class="text-muted-foreground">${row.order.product_name}</small>` : '-';
        },
    },
    {
        data: 'line',
        title: 'Line',
        className: 'desktop',
        render: (_data, _type, row: Schedule) => {
            return row.line ? `${row.line.code} - ${row.line.name}` : '-';
        },
    },
    { data: 'start_date', title: 'Start', className: 'tablet-l' },
    { data: 'finish_date', title: 'Finish', className: 'desktop' },
    {
        data: 'current_finish_date',
        title: 'Current Finish',
        className: 'desktop',
        render: (_data, _type, row: Schedule) => {
            const isDelayed = row.days_extended > 0;
            return isDelayed
                ? `<span class="text-red-600 font-medium">${row.current_finish_date}</span><br/><small class="text-red-500">+${row.days_extended} days</small>`
                : row.current_finish_date;
        },
    },
    {
        data: 'qty_total_target',
        title: 'Progress',
        className: 'tablet-l',
        render: (_data, _type, row: Schedule) => {
            return `${row.qty_completed} / ${row.qty_total_target}<br/><small class="text-muted-foreground">${row.completion_percentage || 0}%</small>`;
        },
    },
    {
        data: 'status',
        title: 'Status',
        className: 'all',
        render: (_data, _type, row: Schedule) => {
            const statusColors = {
                pending: 'border-slate-500/50 bg-slate-500/10 text-slate-600 dark:text-slate-400',
                in_progress: 'border-blue-500/50 bg-blue-500/10 text-blue-600 dark:text-blue-400',
                completed: 'border-green-500/50 bg-green-500/10 text-green-600 dark:text-green-400',
                delayed: 'border-red-500/50 bg-red-500/10 text-red-600 dark:text-red-400',
            };
            const colorClass = statusColors[row.status] || statusColors.pending;
            return `<span class="inline-flex items-center rounded-md border px-2 py-1 text-xs font-medium ${colorClass}">${row.status.replace('_', ' ').toUpperCase()}</span>`;
        },
    },
    {
        data: null,
        title: 'Actions',
        orderable: false,
        searchable: false,
        className: 'all',
        render: (_data, _type, row: Schedule) => {
            const btn = 'inline-block px-3 py-2 text-sm font-medium rounded text-white transition-colors';
            return `
                <div class="flex flex-wrap gap-2 py-1">
                    <a href="/dashboard/production/schedules/${row.id}" class="${btn} bg-blue-600 hover:bg-blue-700">View</a>
                    <a href="/dashboard/production/schedules/${row.id}/edit" class="${btn} bg-yellow-500 hover:bg-yellow-600">Edit</a>
                    <button class="btn-delete ${btn} bg-red-600 hover:bg-red-700" data-id="${row.id}">Delete</button>
                </div>
            `;
        },
    },
];

export default function ScheduleIndex({ lines, schedules: initialSchedules }: ScheduleIndexProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Production', href: '#' },
        { title: 'Schedules', href: route('production.schedules.index') },
    ];

    const dtRef = useRef<DataTableWrapperRef>(null);
    const [viewMode, setViewMode] = useState<ViewMode>('list');
    const [schedules, setSchedules] = useState<Schedule[]>(initialSchedules || []);
    const [selectedLine, setSelectedLine] = useState<string>('all');
    const [activeId, setActiveId] = useState<number | null>(null);
    const [isLoading, setIsLoading] = useState(false);

    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: {
                distance: 8,
            },
        }),
    );

    // WebSocket real-time updates
    useEffect(() => {
        if (viewMode !== 'kanban') return;

        const channel = window.Echo.channel('schedules');

        channel.listen('.ScheduleUpdated', () => {
            // Refresh schedules when any schedule is updated
            fetchSchedules(selectedLine === 'all' ? undefined : selectedLine);
            toast.success('Schedule updated by another user');
        });

        return () => {
            channel.stopListening('.ScheduleUpdated');
            window.Echo.leaveChannel('schedules');
        };
    }, [viewMode, selectedLine]);

    // Filter schedules by line
    const filteredSchedules = selectedLine === 'all' ? schedules : schedules.filter((s) => s.line?.id === parseInt(selectedLine));

    // Group by status for Kanban
    const schedulesByStatus = kanbanColumns.reduce(
        (acc, column) => {
            acc[column.id] = filteredSchedules.filter((s) => s.status === column.id);
            return acc;
        },
        {} as Record<ScheduleStatus, Schedule[]>,
    );

    const fetchSchedules = async (lineId?: string) => {
        setIsLoading(true);
        try {
            const response = await fetch(route('production.schedules.kanban.data', { line_id: lineId || undefined }));
            const data = await response.json();
            setSchedules(data.schedules);
        } catch (error) {
            toast.error('Failed to fetch schedules');
            console.error('Fetch error:', error);
        } finally {
            setIsLoading(false);
        }
    };

    const handleLineChange = (value: string) => {
        setSelectedLine(value);
        if (viewMode === 'kanban') {
            fetchSchedules(value === 'all' ? undefined : value);
        }
    };

    const handleDragStart = (event: DragStartEvent) => {
        setActiveId(event.active.id as number);
    };

    const handleDragEnd = async (event: DragEndEvent) => {
        const { active, over } = event;
        setActiveId(null);

        if (!over) return;

        const scheduleId = active.id as number;
        const newStatus = over.id as ScheduleStatus;
        const schedule = schedules.find((s) => s.id === scheduleId);

        if (!schedule) {
            toast.error('Schedule not found');
            return;
        }

        if (schedule.status === newStatus) return;

        // Store original schedules for rollback
        const originalSchedules = [...schedules];

        // Optimistic update
        const updatedSchedules = schedules.map((s) => (s.id === scheduleId ? { ...s, status: newStatus } : s));
        setSchedules(updatedSchedules);

        try {
            const response = await fetch(route('production.schedules.update-status', scheduleId), {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status: newStatus }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to update status');
            }

            toast.success(`Schedule moved to ${newStatus.replace('_', ' ')}`);
            // Only reload DataTable if in list view
            if (viewMode === 'list') {
                dtRef.current?.reload();
            }
        } catch (error) {
            // Rollback optimistic update
            setSchedules(originalSchedules);
            toast.error(error instanceof Error ? error.message : 'Failed to update schedule status');
            console.error('Failed to update schedule:', error);
        }
    };

    const handleViewSchedule = (schedule: Schedule) => {
        router.visit(route('production.schedules.show', schedule.id));
    };

    const handleDelete = (id: number) => {
        router.delete(route('production.schedules.destroy', id), {
            onSuccess: () => {
                dtRef.current?.reload();
                if (viewMode === 'kanban') {
                    fetchSchedules(selectedLine === 'all' ? undefined : selectedLine);
                }
            },
        });
    };

    const handleRefresh = () => {
        if (viewMode === 'list') {
            dtRef.current?.reload();
        } else {
            fetchSchedules(selectedLine === 'all' ? undefined : selectedLine);
        }
    };

    const activeSchedule = schedules.find((s) => s.id === activeId);

    const lineOptions = [
        { value: 'all', label: 'All Lines' },
        ...lines.map((line) => ({
            value: line.id.toString(),
            label: `${line.code} - ${line.name}`,
        })),
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Production Schedules" />
            <PageContainer maxWidth="full">
                {/* Header */}
                <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <Heading title="Production Schedules" description="Manage production schedules and track daily output" />

                    <div className="flex flex-wrap items-center gap-3">
                        {/* View Toggle */}
                        <ToggleGroup type="single" value={viewMode} onValueChange={(value) => value && setViewMode(value as ViewMode)}>
                            <ToggleGroupItem value="list" aria-label="List view" className="gap-2">
                                <LayoutList className="h-4 w-4" />
                                <span className="hidden sm:inline">List</span>
                            </ToggleGroupItem>
                            <ToggleGroupItem value="kanban" aria-label="Kanban view" className="gap-2">
                                <Grid3x3 className="h-4 w-4" />
                                <span className="hidden sm:inline">Kanban</span>
                            </ToggleGroupItem>
                        </ToggleGroup>

                        {/* Line Filter (Kanban only) */}
                        {viewMode === 'kanban' && (
                            <div className="flex items-center gap-2">
                                <Label htmlFor="line-filter" className="whitespace-nowrap text-sm">
                                    Line:
                                </Label>
                                <Select value={selectedLine} onValueChange={handleLineChange}>
                                    <SelectTrigger className="w-[180px]">
                                        <SelectValue placeholder="Select line" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {lineOptions.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        )}

                        <Button variant="outline" size="sm" onClick={handleRefresh} disabled={isLoading}>
                            {isLoading ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <RefreshCw className="mr-2 h-4 w-4" />}
                            Refresh
                        </Button>

                        <Button size="sm" asChild>
                            <Link href={route('production.schedules.create')}>
                                <Plus className="mr-2 h-4 w-4" />
                                New Schedule
                            </Link>
                        </Button>
                    </div>
                </div>

                {/* List View */}
                {viewMode === 'list' && (
                    <DataTableWrapper
                        ref={dtRef}
                        ajax={{ url: route('production.schedules.json'), type: 'POST' }}
                        columns={tableColumns}
                        onRowDelete={handleDelete}
                        confirmationConfig={{
                            delete: {
                                title: 'Delete Schedule Confirmation',
                                message: 'Are you sure you want to delete this production schedule?',
                                confirmText: 'Delete',
                                cancelText: 'Cancel',
                                successMessage: 'Schedule deleted successfully',
                            },
                        }}
                    />
                )}

                {/* Kanban View */}
                {viewMode === 'kanban' && (
                    <>
                        {/* Stats Summary */}
                        <div className="mb-6 grid gap-4 md:grid-cols-4">
                            {kanbanColumns.map((column) => {
                                const count = schedulesByStatus[column.id]?.length || 0;
                                const Icon = column.id === 'pending' ? CalendarDays : column.id === 'in_progress' ? Loader2 : column.id === 'delayed' ? RefreshCw : Plus;
                                return (
                                    <Card key={column.id} className={`border-l-4 ${column.color}`}>
                                        <CardContent className="flex items-center justify-between p-4">
                                            <div>
                                                <p className="text-sm font-medium text-muted-foreground">{column.title}</p>
                                                <h3 className="text-2xl font-bold">{count}</h3>
                                                <p className="text-xs text-muted-foreground">{column.description}</p>
                                            </div>
                                            <Icon className="h-8 w-8 text-muted-foreground opacity-50" />
                                        </CardContent>
                                    </Card>
                                );
                            })}
                        </div>

                        {/* Kanban Board */}
                        <DndContext
                            sensors={sensors}
                            collisionDetection={closestCenter}
                            onDragStart={handleDragStart}
                            onDragEnd={handleDragEnd}
                        >
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                                {kanbanColumns.map((column) => (
                                    <KanbanColumnDroppable
                                        key={column.id}
                                        column={column}
                                        schedules={schedulesByStatus[column.id] || []}
                                        onView={handleViewSchedule}
                                    />
                                ))}
                            </div>

                            <DragOverlay>{activeSchedule ? <ScheduleCard schedule={activeSchedule} /> : null}</DragOverlay>
                        </DndContext>
                    </>
                )}
            </PageContainer>
        </AppLayout>
    );
}
