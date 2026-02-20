import Heading from '@/components/heading';
import PageContainer from '@/components/page-container';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type AppSetting, type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    Activity,
    AlertTriangle,
    Calendar,
    CheckCircle2,
    Clock,
    Factory,
    Layers,
    Package,
    ShoppingCart,
    TrendingUp,
} from 'lucide-react';

interface DashboardStats {
    orders: {
        total: number;
        pending: number;
        completed: number;
        totalQty: number;
    };
    schedules: {
        total: number;
        active: number;
        completed: number;
        delayed: number;
    };
    lines: {
        total: number;
        active: number;
        capacity: number;
    };
    production: {
        targetQty: number;
        completedQty: number;
        completionPercentage: number;
    };
    today: {
        target: number;
        actual: number;
        achievement: number;
    };
}

interface RecentSchedule {
    id: number;
    order_number: string;
    product_name: string;
    line_name: string;
    start_date: string;
    finish_date: string;
    current_finish_date: string;
    status: string;
    completion_percentage: number;
    days_extended: number;
}

interface LineUtilization {
    name: string;
    code: string;
    capacity: number;
    active_schedules: number;
    utilization: string;
}

interface WeeklyTrendData {
    date: string;
    target: number;
    actual: number;
}

interface OrderStatus {
    status: string;
    count: number;
}

type PageProps = {
    appSettings: AppSetting;
    stats: DashboardStats;
    recentSchedules: RecentSchedule[];
    lineUtilization: LineUtilization[];
    weeklyTrend: WeeklyTrendData[];
    orderStatusDistribution: OrderStatus[];
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard() {
    const { appSettings, stats, recentSchedules, lineUtilization, weeklyTrend, orderStatusDistribution } =
        usePage<PageProps>().props;

    const statusBadge = (status: string) => {
        const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
            pending: 'secondary',
            in_progress: 'default',
            completed: 'outline',
            delayed: 'destructive',
        };
        return (
            <Badge variant={variants[status] || 'default'} className="capitalize">
                {status.replace('_', ' ')}
            </Badge>
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head>
                <title>{appSettings.seo_title || appSettings.app_name || 'Dashboard'}</title>
                {appSettings.seo_description && <meta name="description" content={appSettings.seo_description} />}
                {appSettings.seo_keywords && <meta name="keywords" content={appSettings.seo_keywords} />}
                {appSettings.seo_og_image && <meta property="og:image" content={appSettings.seo_og_image} />}
                {appSettings.app_favicon && <link rel="icon" href={appSettings.app_favicon} />}
            </Head>
            <PageContainer maxWidth="full">
                <Heading title="Production Dashboard" description="Monitor production metrics and performance in real-time" />

                {/* Top Stats Cards */}
                <div className="mb-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    {/* Total Orders */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Orders</CardTitle>
                            <ShoppingCart className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.orders.total}</div>
                            <p className="text-xs text-muted-foreground">
                                {stats.orders.pending} pending, {stats.orders.completed} completed
                            </p>
                            <div className="mt-2 text-xs text-muted-foreground">Total Qty: {stats.orders.totalQty}</div>
                        </CardContent>
                    </Card>

                    {/* Active Schedules */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Active Schedules</CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.schedules.active}</div>
                            <p className="text-xs text-muted-foreground">
                                {stats.schedules.total} total, {stats.schedules.completed} completed
                            </p>
                            {stats.schedules.delayed > 0 && (
                                <div className="mt-2 flex items-center text-xs text-red-600">
                                    <AlertTriangle className="mr-1 h-3 w-3" />
                                    {stats.schedules.delayed} delayed
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Production Lines */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Production Lines</CardTitle>
                            <Factory className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.lines.active}</div>
                            <p className="text-xs text-muted-foreground">of {stats.lines.total} lines active</p>
                            <div className="mt-2 text-xs text-muted-foreground">Capacity: {stats.lines.capacity}/day</div>
                        </CardContent>
                    </Card>

                    {/* Overall Progress */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Overall Progress</CardTitle>
                            <TrendingUp className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.production.completionPercentage}%</div>
                            <p className="text-xs text-muted-foreground">
                                {stats.production.completedQty} / {stats.production.targetQty} completed
                            </p>
                            <Progress value={stats.production.completionPercentage} className="mt-2" />
                        </CardContent>
                    </Card>
                </div>

                {/* Today's Production & Order Status */}
                <div className="mb-6 grid gap-4 md:grid-cols-2">
                    {/* Today's Production */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Clock className="h-5 w-5" />
                                Today's Production
                            </CardTitle>
                            <CardDescription>Real-time production output for today</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm text-muted-foreground">Target</span>
                                    <span className="text-lg font-semibold">{stats.today.target}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm text-muted-foreground">Actual</span>
                                    <span className="text-lg font-semibold text-blue-600">{stats.today.actual}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm text-muted-foreground">Achievement</span>
                                    <span
                                        className={`text-lg font-semibold ${
                                            stats.today.achievement >= 100
                                                ? 'text-green-600'
                                                : stats.today.achievement >= 80
                                                  ? 'text-yellow-600'
                                                  : 'text-red-600'
                                        }`}
                                    >
                                        {stats.today.achievement}%
                                    </span>
                                </div>
                                <Progress value={Math.min(stats.today.achievement, 100)} className="mt-2" />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Order Status Distribution */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Package className="h-5 w-5" />
                                Order Status Distribution
                            </CardTitle>
                            <CardDescription>Breakdown of orders by status</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                {orderStatusDistribution.map((item) => (
                                    <div key={item.status} className="flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            <div
                                                className={`h-3 w-3 rounded-full ${
                                                    item.status === 'Pending'
                                                        ? 'bg-gray-500'
                                                        : item.status === 'Scheduled'
                                                          ? 'bg-blue-500'
                                                          : item.status === 'In Progress'
                                                            ? 'bg-yellow-500'
                                                            : 'bg-green-500'
                                                }`}
                                            />
                                            <span className="text-sm">{item.status}</span>
                                        </div>
                                        <span className="font-semibold">{item.count}</span>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Weekly Production Trend */}
                <Card className="mb-6">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Activity className="h-5 w-5" />
                            Weekly Production Trend
                        </CardTitle>
                        <CardDescription>Target vs Actual output for the last 7 days</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-2">
                            {weeklyTrend.map((day, index) => (
                                <div key={index} className="flex items-center gap-4">
                                    <div className="w-16 text-xs text-muted-foreground">{day.date}</div>
                                    <div className="flex flex-1 gap-2">
                                        <div className="flex-1">
                                            <div className="mb-1 flex justify-between text-xs">
                                                <span>Target</span>
                                                <span>{day.target}</span>
                                            </div>
                                            <div className="h-2 overflow-hidden rounded-full bg-secondary">
                                                <div className="h-full bg-gray-500" style={{ width: '100%' }} />
                                            </div>
                                        </div>
                                        <div className="flex-1">
                                            <div className="mb-1 flex justify-between text-xs">
                                                <span>Actual</span>
                                                <span>{day.actual}</span>
                                            </div>
                                            <div className="h-2 overflow-hidden rounded-full bg-secondary">
                                                <div
                                                    className={`h-full ${day.actual >= day.target ? 'bg-green-500' : 'bg-blue-500'}`}
                                                    style={{ width: `${day.target > 0 ? (day.actual / day.target) * 100 : 0}%` }}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Line Utilization & Recent Schedules */}
                <div className="grid gap-4 md:grid-cols-2">
                    {/* Line Utilization */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Layers className="h-5 w-5" />
                                Line Utilization
                            </CardTitle>
                            <CardDescription>Current status of production lines</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                {lineUtilization.map((line) => (
                                    <div key={line.code} className="flex items-center justify-between rounded-lg border p-3">
                                        <div>
                                            <div className="font-medium">
                                                {line.code} - {line.name}
                                            </div>
                                            <div className="text-xs text-muted-foreground">Capacity: {line.capacity}/day</div>
                                        </div>
                                        <div className="text-right">
                                            <Badge variant={line.active_schedules > 0 ? 'default' : 'secondary'}>
                                                {line.utilization}
                                            </Badge>
                                            {line.active_schedules > 0 && (
                                                <div className="mt-1 text-xs text-muted-foreground">
                                                    {line.active_schedules} schedule{line.active_schedules > 1 ? 's' : ''}
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Recent Schedules */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <CheckCircle2 className="h-5 w-5" />
                                Recent Schedules
                            </CardTitle>
                            <CardDescription>Latest production schedules</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                {recentSchedules.length > 0 ? (
                                    recentSchedules.map((schedule) => (
                                        <Link
                                            key={schedule.id}
                                            href={route('production.schedules.show', schedule.id)}
                                            className="block rounded-lg border p-3 transition-colors hover:bg-muted/50"
                                        >
                                            <div className="mb-1 flex items-start justify-between">
                                                <div className="flex-1">
                                                    <div className="font-medium">{schedule.order_number}</div>
                                                    <div className="text-xs text-muted-foreground">{schedule.product_name}</div>
                                                </div>
                                                {statusBadge(schedule.status)}
                                            </div>
                                            <div className="mt-2 flex items-center justify-between text-xs text-muted-foreground">
                                                <span>{schedule.line_name}</span>
                                                <span>{schedule.completion_percentage}%</span>
                                            </div>
                                            <Progress value={schedule.completion_percentage} className="mt-1 h-1" />
                                        </Link>
                                    ))
                                ) : (
                                    <div className="py-8 text-center text-sm text-muted-foreground">No recent schedules</div>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </PageContainer>
        </AppLayout>
    );
}
