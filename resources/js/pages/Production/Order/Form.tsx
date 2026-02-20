import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import PageContainer from '@/components/page-container';
import CustomSelect from '@/components/select';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Order } from '@/types/production';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

export default function OrderForm({ order }: { order?: Order }) {
    const isEdit = !!order;
    const { data, setData, post, put, processing, errors } = useForm({
        order_number: order?.order_number || '',
        product_name: order?.product_name || '',
        product_code: order?.product_code || '',
        qty_total: order?.qty_total || 0,
        customer: order?.customer || '',
        order_date: order?.order_date || new Date().toISOString().split('T')[0],
        due_date: order?.due_date || '',
        status: order?.status || 'pending',
        notes: order?.notes || '',
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Production', href: '#' },
        { title: 'Orders', href: route('production.orders.index') },
        { title: isEdit ? 'Edit Order' : 'Create Order', href: '#' },
    ];

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEdit) {
            put(route('production.orders.update', order!.id));
        } else {
            post(route('production.orders.store'));
        }
    };

    const statusOptions = [
        { value: 'pending', label: 'Pending' },
        { value: 'scheduled', label: 'Scheduled' },
        { value: 'in_progress', label: 'In Progress' },
        { value: 'completed', label: 'Completed' },
        { value: 'cancelled', label: 'Cancelled' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isEdit ? 'Edit Order' : 'Create Order'} />
            <PageContainer maxWidth="4xl">
                <HeadingSmall
                    title={isEdit ? 'Edit Production Order' : 'Create Production Order'}
                    description="Fill in the details below"
                />

                <form onSubmit={handleSubmit} className="mt-6 space-y-6">
                    <div className="grid gap-6 md:grid-cols-2">
                        <div>
                            <Label htmlFor="order_number">Order Number *</Label>
                            <Input
                                id="order_number"
                                type="text"
                                value={data.order_number}
                                onChange={(e) => setData('order_number', e.target.value)}
                                placeholder="e.g., PO-2026-001"
                                required
                            />
                            <InputError message={errors.order_number} />
                        </div>

                        <div>
                            <Label htmlFor="customer">Customer</Label>
                            <Input
                                id="customer"
                                type="text"
                                value={data.customer}
                                onChange={(e) => setData('customer', e.target.value)}
                                placeholder="e.g., PT ABC"
                            />
                            <InputError message={errors.customer} />
                        </div>
                    </div>

                    <div className="grid gap-6 md:grid-cols-2">
                        <div>
                            <Label htmlFor="product_name">Product Name *</Label>
                            <Input
                                id="product_name"
                                type="text"
                                value={data.product_name}
                                onChange={(e) => setData('product_name', e.target.value)}
                                placeholder="e.g., T-Shirt Basic"
                                required
                            />
                            <InputError message={errors.product_name} />
                        </div>

                        <div>
                            <Label htmlFor="product_code">Product Code</Label>
                            <Input
                                id="product_code"
                                type="text"
                                value={data.product_code}
                                onChange={(e) => setData('product_code', e.target.value)}
                                placeholder="e.g., TS-001"
                            />
                            <InputError message={errors.product_code} />
                        </div>
                    </div>

                    <div className="grid gap-6 md:grid-cols-2">
                        <div>
                            <Label htmlFor="qty_total">Total Quantity *</Label>
                            <Input
                                id="qty_total"
                                type="number"
                                min="1"
                                value={data.qty_total}
                                onChange={(e) => setData('qty_total', parseInt(e.target.value) || 0)}
                                placeholder="e.g., 1000"
                                required
                            />
                            <InputError message={errors.qty_total} />
                        </div>

                        <div>
                            <Label htmlFor="status">Status *</Label>
                            <CustomSelect
                                id="status"
                                options={statusOptions}
                                value={statusOptions.find((opt) => opt.value === data.status)}
                                onChange={(selected) => setData('status', (selected as { value: string }).value as Order['status'])}
                            />
                            <InputError message={errors.status} />
                        </div>
                    </div>

                    <div className="grid gap-6 md:grid-cols-2">
                        <div>
                            <Label htmlFor="order_date">Order Date *</Label>
                            <Input
                                id="order_date"
                                type="date"
                                value={data.order_date}
                                onChange={(e) => setData('order_date', e.target.value)}
                                required
                            />
                            <InputError message={errors.order_date} />
                        </div>

                        <div>
                            <Label htmlFor="due_date">Due Date *</Label>
                            <Input
                                id="due_date"
                                type="date"
                                value={data.due_date}
                                onChange={(e) => setData('due_date', e.target.value)}
                                required
                            />
                            <InputError message={errors.due_date} />
                        </div>
                    </div>

                    <div>
                        <Label htmlFor="notes">Notes</Label>
                        <Textarea
                            id="notes"
                            value={data.notes}
                            onChange={(e) => setData('notes', e.target.value)}
                            placeholder="Optional notes"
                            rows={3}
                        />
                        <InputError message={errors.notes} />
                    </div>

                    <div className="flex items-center gap-4">
                        <Button type="submit" disabled={processing}>
                            {isEdit ? 'Update Order' : 'Create Order'}
                        </Button>
                        <Button type="button" variant="outline" asChild>
                            <Link href={route('production.orders.index')}>Cancel</Link>
                        </Button>
                    </div>
                </form>
            </PageContainer>
        </AppLayout>
    );
}
