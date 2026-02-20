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
import { Line, Order, Schedule } from '@/types/production';
import { toast } from '@/utils/toast';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent, useEffect, useState } from 'react';

interface ScheduleFormProps {
    schedule?: Schedule;
    orders?: Order[];
    lines?: Line[];
}

export default function ScheduleForm({ schedule, orders = [], lines = [] }: ScheduleFormProps) {
    const isEdit = !!schedule;
    const { data, setData, post, put, processing, errors } = useForm({
        order_id: schedule?.order_id || null,
        line_id: schedule?.line_id || null,
        start_date: schedule?.start_date || '',
        finish_date: schedule?.finish_date || '',
        qty_total_target: schedule?.qty_total_target || 0,
        notes: schedule?.notes || '',
    });

    const [selectedOrder, setSelectedOrder] = useState<Order | null>(
        schedule?.order || (orders.length > 0 ? orders[0] : null)
    );

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Production', href: '#' },
        { title: 'Schedules', href: route('production.schedules.index') },
        { title: isEdit ? 'Edit Schedule' : 'Create Schedule', href: '#' },
    ];

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEdit) {
            put(route('production.schedules.update', schedule!.id), {
                onSuccess: () => toast.success('Schedule updated successfully'),
                onError: () => toast.error('Failed to update schedule'),
            });
        } else {
            post(route('production.schedules.store'), {
                onSuccess: () => toast.success('Schedule created successfully with daily targets'),
                onError: () => toast.error('Failed to create schedule'),
            });
        }
    };

    // Order options
    const orderOptions = orders
        .filter((order) => order.status !== 'cancelled' && order.status !== 'completed')
        .map((order) => ({
            value: order.id,
            label: `${order.order_number} - ${order.product_name} (${order.remaining_qty || order.qty_total} remaining)`,
        }));

    // Line options (only active lines)
    const lineOptions = lines
        .filter((line) => line.is_active)
        .map((line) => ({
            value: line.id,
            label: `${line.code} - ${line.name} (Capacity: ${line.capacity_per_day}/day)`,
        }));

    // Auto-fill qty_total_target when order is selected
    useEffect(() => {
        if (selectedOrder && !isEdit) {
            const remainingQty = selectedOrder.remaining_qty || selectedOrder.qty_total;
            setData('qty_total_target', remainingQty);
        }
    }, [selectedOrder]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isEdit ? 'Edit Schedule' : 'Create Schedule'} />
            <PageContainer maxWidth="4xl">
                <HeadingSmall
                    title={isEdit ? 'Edit Production Schedule' : 'Create Production Schedule'}
                    description="Fill in the details below"
                />

                <form onSubmit={handleSubmit} className="mt-6 space-y-6">
                    <div className="grid gap-6 md:grid-cols-2">
                        <div>
                            <Label htmlFor="order">Order *</Label>
                            <CustomSelect
                                id="order"
                                options={orderOptions}
                                value={orderOptions.find((opt) => opt.value === data.order_id)}
                                onChange={(selected) => {
                                    const orderId = (selected as { value: number }).value;
                                    setData('order_id', orderId);
                                    const order = orders.find((o) => o.id === orderId);
                                    if (order) setSelectedOrder(order);
                                }}
                                placeholder="Select an order..."
                            />
                            <InputError message={errors.order_id} />
                        </div>

                        <div>
                            <Label htmlFor="line">Production Line *</Label>
                            <CustomSelect
                                id="line"
                                options={lineOptions}
                                value={lineOptions.find((opt) => opt.value === data.line_id)}
                                onChange={(selected) => setData('line_id', (selected as { value: number }).value)}
                                placeholder="Select a line..."
                            />
                            <InputError message={errors.line_id} />
                        </div>
                    </div>

                    <div className="grid gap-6 md:grid-cols-3">
                        <div>
                            <Label htmlFor="start_date">Start Date *</Label>
                            <Input
                                id="start_date"
                                type="date"
                                value={data.start_date}
                                onChange={(e) => setData('start_date', e.target.value)}
                                required
                            />
                            <InputError message={errors.start_date} />
                        </div>

                        <div>
                            <Label htmlFor="finish_date">Finish Date *</Label>
                            <Input
                                id="finish_date"
                                type="date"
                                value={data.finish_date}
                                onChange={(e) => setData('finish_date', e.target.value)}
                                required
                            />
                            <InputError message={errors.finish_date} />
                        </div>

                        <div>
                            <Label htmlFor="qty_total_target">Target Quantity *</Label>
                            <Input
                                id="qty_total_target"
                                type="number"
                                min="1"
                                value={data.qty_total_target}
                                onChange={(e) => setData('qty_total_target', parseInt(e.target.value) || 0)}
                                placeholder="e.g., 1000"
                                required
                            />
                            <InputError message={errors.qty_total_target} />
                        </div>
                    </div>

                    {selectedOrder && (
                        <div className="rounded-lg border border-border bg-muted/50 p-4">
                            <h4 className="mb-2 text-sm font-semibold">Order Details</h4>
                            <div className="grid gap-2 text-sm">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Product:</span>
                                    <span className="font-medium">{selectedOrder.product_name}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Total Qty:</span>
                                    <span className="font-medium">{selectedOrder.qty_total}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Remaining:</span>
                                    <span className="font-medium text-blue-600">
                                        {selectedOrder.remaining_qty || selectedOrder.qty_total}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Due Date:</span>
                                    <span className="font-medium">{selectedOrder.due_date}</span>
                                </div>
                            </div>
                        </div>
                    )}

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
                            {isEdit ? 'Update Schedule' : 'Create Schedule'}
                        </Button>
                        <Button type="button" variant="outline" asChild>
                            <Link href={route('production.schedules.index')}>Cancel</Link>
                        </Button>
                    </div>
                </form>
            </PageContainer>
        </AppLayout>
    );
}
