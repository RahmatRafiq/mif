import DataTableWrapper, { DataTableWrapperRef } from '@/components/datatables';
import Heading from '@/components/heading';
import PageContainer from '@/components/page-container';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import type { DataTableColumn } from '@/types/DataTables';
import type { Schedule } from '@/types/production';
import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useRef } from 'react';

const columns: DataTableColumn<Schedule>[] = [
    { data: 'id', title: 'ID', className: 'all', width: '60px' },
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
        className: 'tablet-p',
        render: (_data, _type, row: Schedule) => {
            return row.line ? `${row.line.code} - ${row.line.name}` : '-';
        },
    },
    { data: 'start_date', title: 'Start Date', className: 'tablet-l' },
    { data: 'finish_date', title: 'Finish Date', className: 'tablet-l' },
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
        title: 'Target/Completed',
        className: 'tablet-p',
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
                pending: 'bg-gray-50 text-gray-700 ring-gray-600/20',
                in_progress: 'bg-blue-50 text-blue-700 ring-blue-600/20',
                completed: 'bg-green-50 text-green-700 ring-green-600/20',
                delayed: 'bg-red-50 text-red-700 ring-red-600/20',
            };
            const colorClass = statusColors[row.status] || statusColors.pending;
            return `<span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset ${colorClass}">${row.status.replace('_', ' ').toUpperCase()}</span>`;
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

export default function ScheduleIndex() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Production' },
        { title: 'Schedules', href: route('production.schedules.index') },
    ];
    const dtRef = useRef<DataTableWrapperRef>(null);

    const handleDelete = (id: number) => {
        router.delete(route('production.schedules.destroy', id), {
            onSuccess: () => dtRef.current?.reload(),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Production Schedules" />
            <PageContainer maxWidth="full">
                <div className="flex items-center justify-between">
                    <Heading title="Production Schedules" description="Manage production schedules and track daily output" />
                    <Link href={route('production.schedules.create')}>
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Add Schedule
                        </Button>
                    </Link>
                </div>

                <div className="mt-6">
                    <DataTableWrapper
                        ref={dtRef}
                        ajax={{ url: route('production.schedules.json'), type: 'GET' }}
                        columns={columns}
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
                </div>
            </PageContainer>
        </AppLayout>
    );
}
