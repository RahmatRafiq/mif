import DataTableWrapper, { DataTableWrapperRef } from '@/components/datatables';
import Heading from '@/components/heading';
import PageContainer from '@/components/page-container';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import type { DataTableColumn } from '@/types/DataTables';
import type { Order } from '@/types/production';
import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useRef } from 'react';

const columns: DataTableColumn<Order>[] = [
    { data: 'id', title: 'ID', className: 'all', width: '60px' },
    { data: 'order_number', title: 'Order Number', className: 'all' },
    { data: 'product_name', title: 'Product', className: 'all' },
    { data: 'product_code', title: 'Code', className: 'tablet-p' },
    { data: 'qty_total', title: 'Qty Total', className: 'tablet-p' },
    { data: 'customer', title: 'Customer', className: 'tablet-l' },
    { data: 'order_date', title: 'Order Date', className: 'desktop' },
    { data: 'due_date', title: 'Due Date', className: 'desktop' },
    {
        data: 'status',
        title: 'Status',
        className: 'tablet-l',
        render: (_data, _type, row: Order) => {
            const statusColors = {
                pending: 'bg-gray-50 text-gray-700 ring-gray-600/20',
                scheduled: 'bg-blue-50 text-blue-700 ring-blue-600/20',
                in_progress: 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
                completed: 'bg-green-50 text-green-700 ring-green-600/20',
                cancelled: 'bg-red-50 text-red-700 ring-red-600/20',
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
        render: (_data, _type, row: Order) => {
            const btn = 'inline-block px-3 py-2 text-sm font-medium rounded text-white transition-colors';
            return `
                <div class="flex flex-wrap gap-2 py-1">
                    <a href="/dashboard/production/orders/${row.id}/edit" class="${btn} bg-yellow-500 hover:bg-yellow-600">Edit</a>
                    <button class="btn-delete ${btn} bg-red-600 hover:bg-red-700" data-id="${row.id}">Delete</button>
                </div>
            `;
        },
    },
];

export default function OrderIndex() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Production' },
        { title: 'Orders', href: route('production.orders.index') },
    ];
    const dtRef = useRef<DataTableWrapperRef>(null);

    const handleDelete = (id: number) => {
        router.delete(route('production.orders.destroy', id), {
            onSuccess: () => dtRef.current?.reload(),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Production Orders" />
            <PageContainer maxWidth="full">
                <div className="flex items-center justify-between">
                    <Heading title="Production Orders" description="Manage production orders" />
                    <Link href={route('production.orders.create')}>
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Add Order
                        </Button>
                    </Link>
                </div>

                <div className="mt-6">
                    <DataTableWrapper
                        ref={dtRef}
                        ajax={{ url: route('production.orders.json'), type: 'POST' }}
                        columns={columns}
                        onRowDelete={handleDelete}
                        confirmationConfig={{
                            delete: {
                                title: 'Delete Order Confirmation',
                                message: 'Are you sure you want to delete this production order?',
                                confirmText: 'Delete',
                                cancelText: 'Cancel',
                                successMessage: 'Order deleted successfully',
                            },
                        }}
                    />
                </div>
            </PageContainer>
        </AppLayout>
    );
}
