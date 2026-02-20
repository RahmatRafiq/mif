import DataTableWrapper, { DataTableWrapperRef } from '@/components/datatables';
import Heading from '@/components/heading';
import PageContainer from '@/components/page-container';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import type { DataTableColumn } from '@/types/DataTables';
import type { Line } from '@/types/production';
import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useRef } from 'react';

const columns: DataTableColumn<Line>[] = [
    { data: 'id', title: 'ID', className: 'all', width: '60px' },
    { data: 'code', title: 'Code', className: 'all' },
    { data: 'name', title: 'Line Name', className: 'all' },
    { data: 'capacity_per_day', title: 'Capacity/Day', className: 'tablet-p' },
    {
        data: 'is_active',
        title: 'Status',
        className: 'tablet-l',
        render: (_data, _type, row: Line) => {
            return row.is_active
                ? '<span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Active</span>'
                : '<span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Inactive</span>';
        },
    },
    {
        data: null,
        title: 'Actions',
        orderable: false,
        searchable: false,
        className: 'all',
        render: (_data, _type, row: Line) => {
            const btn = 'inline-block px-3 py-2 text-sm font-medium rounded text-white transition-colors';
            return `
                <div class="flex flex-wrap gap-2 py-1">
                    <a href="/dashboard/production/lines/${row.id}/edit" class="${btn} bg-yellow-500 hover:bg-yellow-600">Edit</a>
                    <button class="btn-delete ${btn} bg-red-600 hover:bg-red-700" data-id="${row.id}">Delete</button>
                </div>
            `;
        },
    },
];

export default function LineIndex() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Production', href: '#' },
        { title: 'Lines', href: route('production.lines.index') },
    ];
    const dtRef = useRef<DataTableWrapperRef>(null);

    const handleDelete = (id: number) => {
        router.delete(route('production.lines.destroy', id), {
            onSuccess: () => dtRef.current?.reload(),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Sewing Lines" />
            <PageContainer maxWidth="full">
                <div className="flex items-center justify-between">
                    <Heading title="Sewing Lines" description="Manage production sewing lines" />
                    <Link href={route('production.lines.create')}>
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Add Line
                        </Button>
                    </Link>
                </div>

                <div className="mt-6">
                    <DataTableWrapper
                        ref={dtRef}
                        ajax={{ url: route('production.lines.json'), type: 'GET' }}
                        columns={columns}
                        onRowDelete={handleDelete}
                        confirmationConfig={{
                            delete: {
                                title: 'Delete Line Confirmation',
                                message: 'Are you sure you want to delete this production line?',
                                confirmText: 'Delete',
                                cancelText: 'Cancel',
                                successMessage: 'Line deleted successfully',
                            },
                        }}
                    />
                </div>
            </PageContainer>
        </AppLayout>
    );
}
