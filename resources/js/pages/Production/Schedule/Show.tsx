import Heading from '@/components/heading';
import PageContainer from '@/components/page-container';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Schedule, ScheduleDailyOutput } from '@/types/production';
import { toast } from '@/utils/toast';
import { Head, Link, useForm } from '@inertiajs/react';
import { AlertCircle, Calendar, CheckCircle2, Package, TrendingUp } from 'lucide-react';
import { FormEvent, useState } from 'react';

interface ScheduleShowProps {
    schedule: Schedule;
}

export default function ScheduleShow({ schedule }: ScheduleShowProps) {
    const [editingDailyOutputId, setEditingDailyOutputId] = useState<number | null>(null);
    const { data, setData, post, processing, reset } = useForm({
        daily_output_id: 0,
        actual_output: 0,
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Production', href: '#' },
        { title: 'Schedules', href: route('production.schedules.index') },
        { title: `Schedule #${schedule.id}`, href: '#' },
    ];

    const handleSubmitActualOutput = (e: FormEvent) => {
        e.preventDefault();
        post(route('production.schedules.input-actual'), {
            onSuccess: () => {
                setEditingDailyOutputId(null);
                reset();
                toast.success('Actual output recorded successfully. Balancing applied if needed.');
            },
            onError: () => {
                toast.error('Failed to record actual output');
            },
        });
    };

    const startEditing = (dailyOutput: ScheduleDailyOutput) => {
        setEditingDailyOutputId(dailyOutput.id);
        setData({
            daily_output_id: dailyOutput.id,
            actual_output: dailyOutput.actual_output || 0,
        });
    };

    const statusBadge = (status: string) => {
        const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
            pending: 'secondary',
            in_progress: 'default',
            completed: 'outline',
            delayed: 'destructive',
        };
        return <Badge variant={variants[status] || 'default'}>{status.replace('_', ' ').toUpperCase()}</Badge>;
    };

    const completionPercentage = schedule.completion_percentage || 0;
    const isDelayed = schedule.days_extended > 0;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Schedule #${schedule.id}`} />
            <PageContainer maxWidth="7xl">
                <div className="mb-6 flex items-center justify-between">
                    <Heading title={`Schedule #${schedule.id}`} description="View and input daily production output" />
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={route('production.schedules.edit', schedule.id)}>Edit Schedule</Link>
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href={route('production.schedules.index')}>Back to List</Link>
                        </Button>
                    </div>
                </div>

                {/* Summary Cards */}
                <div className="mb-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Order</CardTitle>
                            <Package className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{schedule.order?.order_number}</div>
                            <p className="text-xs text-muted-foreground">{schedule.order?.product_name}</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Production Line</CardTitle>
                            <TrendingUp className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{schedule.line?.code}</div>
                            <p className="text-xs text-muted-foreground">{schedule.line?.name}</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Schedule Period</CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-sm font-bold">
                                {schedule.start_date} → {schedule.finish_date}
                            </div>
                            {isDelayed && (
                                <p className="text-xs text-red-600">
                                    Extended to: {schedule.current_finish_date} (+{schedule.days_extended} days)
                                </p>
                            )}
                            <p className="text-xs text-muted-foreground">
                                {schedule.total_days || 0} days total ({schedule.current_total_days || schedule.total_days || 0}{' '}
                                current)
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Progress</CardTitle>
                            {schedule.status === 'completed' ? (
                                <CheckCircle2 className="h-4 w-4 text-green-600" />
                            ) : isDelayed ? (
                                <AlertCircle className="h-4 w-4 text-red-600" />
                            ) : (
                                <TrendingUp className="h-4 w-4 text-blue-600" />
                            )}
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {schedule.qty_completed} / {schedule.qty_total_target}
                            </div>
                            <div className="mt-2">
                                <div className="h-2 w-full overflow-hidden rounded-full bg-secondary">
                                    <div
                                        className="h-full bg-primary transition-all"
                                        style={{ width: `${Math.min(completionPercentage, 100)}%` }}
                                    />
                                </div>
                                <p className="mt-1 text-xs text-muted-foreground">{completionPercentage.toFixed(1)}% completed</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Status Badge */}
                <div className="mb-6">
                    <div className="flex items-center gap-2">
                        <span className="text-sm font-medium">Status:</span>
                        {statusBadge(schedule.status)}
                        {isDelayed && <Badge variant="destructive">Delayed by {schedule.days_extended} days</Badge>}
                    </div>
                </div>

                {/* Daily Outputs Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Daily Production Output</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="w-[100px]">Date</TableHead>
                                        <TableHead className="text-right">Target</TableHead>
                                        <TableHead className="text-right">Actual</TableHead>
                                        <TableHead className="text-right">Balance</TableHead>
                                        <TableHead className="text-right">Achievement</TableHead>
                                        <TableHead className="text-center">Status</TableHead>
                                        <TableHead className="text-center">Action</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {schedule.daily_outputs && schedule.daily_outputs.length > 0 ? (
                                        schedule.daily_outputs.map((dailyOutput) => {
                                            const achievement = dailyOutput.achievement_percentage || 0;
                                            const isEditing = editingDailyOutputId === dailyOutput.id;

                                            return (
                                                <TableRow key={dailyOutput.id}>
                                                    <TableCell className="font-medium">{dailyOutput.date}</TableCell>
                                                    <TableCell className="text-right">{dailyOutput.target_output}</TableCell>
                                                    <TableCell className="text-right">
                                                        {isEditing ? (
                                                            <form
                                                                onSubmit={handleSubmitActualOutput}
                                                                className="flex items-center gap-2"
                                                            >
                                                                <Input
                                                                    type="number"
                                                                    min="0"
                                                                    value={data.actual_output}
                                                                    onChange={(e) =>
                                                                        setData('actual_output', parseInt(e.target.value) || 0)
                                                                    }
                                                                    className="w-24"
                                                                    autoFocus
                                                                />
                                                                <Button type="submit" size="sm" disabled={processing}>
                                                                    Save
                                                                </Button>
                                                                <Button
                                                                    type="button"
                                                                    size="sm"
                                                                    variant="outline"
                                                                    onClick={() => {
                                                                        setEditingDailyOutputId(null);
                                                                        reset();
                                                                    }}
                                                                >
                                                                    Cancel
                                                                </Button>
                                                            </form>
                                                        ) : (
                                                            dailyOutput.actual_output || 0
                                                        )}
                                                    </TableCell>
                                                    <TableCell
                                                        className={`text-right ${dailyOutput.balance > 0 ? 'font-semibold text-red-600' : ''}`}
                                                    >
                                                        {dailyOutput.balance}
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        <span
                                                            className={
                                                                achievement >= 100
                                                                    ? 'text-green-600'
                                                                    : achievement >= 80
                                                                      ? 'text-yellow-600'
                                                                      : 'text-red-600'
                                                            }
                                                        >
                                                            {achievement.toFixed(1)}%
                                                        </span>
                                                    </TableCell>
                                                    <TableCell className="text-center">
                                                        {dailyOutput.is_completed ? (
                                                            <Badge variant="outline">Completed</Badge>
                                                        ) : (
                                                            <Badge variant="secondary">Pending</Badge>
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="text-center">
                                                        {!dailyOutput.is_completed && !isEditing && (
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                onClick={() => startEditing(dailyOutput)}
                                                            >
                                                                Input
                                                            </Button>
                                                        )}
                                                    </TableCell>
                                                </TableRow>
                                            );
                                        })
                                    ) : (
                                        <TableRow>
                                            <TableCell colSpan={7} className="text-center text-muted-foreground">
                                                No daily outputs available
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>

                {/* Notes Section */}
                {schedule.notes && (
                    <Card className="mt-6">
                        <CardHeader>
                            <CardTitle>Notes</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm text-muted-foreground">{schedule.notes}</p>
                        </CardContent>
                    </Card>
                )}
            </PageContainer>
        </AppLayout>
    );
}
