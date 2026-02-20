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
import { Line } from '@/types/production';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

export default function LineForm({ line }: { line?: Line }) {
    const isEdit = !!line;
    const { data, setData, post, put, processing, errors } = useForm({
        name: line?.name || '',
        code: line?.code || '',
        description: line?.description || '',
        capacity_per_day: line?.capacity_per_day || 0,
        is_active: line?.is_active ?? true,
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Production', href: '#' },
        { title: 'Lines', href: route('production.lines.index') },
        { title: isEdit ? 'Edit Line' : 'Create Line', href: '#' },
    ];

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEdit) {
            put(route('production.lines.update', line!.id));
        } else {
            post(route('production.lines.store'));
        }
    };

    const statusOptions = [
        { value: true, label: 'Active' },
        { value: false, label: 'Inactive' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isEdit ? 'Edit Line' : 'Create Line'} />
            <PageContainer maxWidth="2xl">
                <HeadingSmall
                    title={isEdit ? 'Edit Sewing Line' : 'Create Sewing Line'}
                    description="Fill in the details below"
                />

                <form onSubmit={handleSubmit} className="mt-6 space-y-6">
                    <div className="grid gap-6 md:grid-cols-2">
                        <div>
                            <Label htmlFor="name">Line Name *</Label>
                            <Input
                                id="name"
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                placeholder="e.g., Line A"
                                required
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div>
                            <Label htmlFor="code">Line Code *</Label>
                            <Input
                                id="code"
                                type="text"
                                value={data.code}
                                onChange={(e) => setData('code', e.target.value)}
                                placeholder="e.g., L001"
                                required
                            />
                            <InputError message={errors.code} />
                        </div>
                    </div>

                    <div className="grid gap-6 md:grid-cols-2">
                        <div>
                            <Label htmlFor="capacity">Capacity Per Day</Label>
                            <Input
                                id="capacity"
                                type="number"
                                min="0"
                                value={data.capacity_per_day}
                                onChange={(e) => setData('capacity_per_day', parseInt(e.target.value) || 0)}
                                placeholder="e.g., 500"
                            />
                            <InputError message={errors.capacity_per_day} />
                        </div>

                        <div>
                            <Label htmlFor="status">Status *</Label>
                            <CustomSelect
                                id="status"
                                options={statusOptions}
                                value={statusOptions.find((opt) => opt.value === data.is_active)}
                                onChange={(selected) => setData('is_active', (selected as { value: boolean }).value)}
                            />
                            <InputError message={errors.is_active} />
                        </div>
                    </div>

                    <div>
                        <Label htmlFor="description">Description</Label>
                        <Textarea
                            id="description"
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            placeholder="Optional description"
                            rows={3}
                        />
                        <InputError message={errors.description} />
                    </div>

                    <div className="flex items-center gap-4">
                        <Button type="submit" disabled={processing}>
                            {isEdit ? 'Update Line' : 'Create Line'}
                        </Button>
                        <Button type="button" variant="outline" asChild>
                            <Link href={route('production.lines.index')}>Cancel</Link>
                        </Button>
                    </div>
                </form>
            </PageContainer>
        </AppLayout>
    );
}
