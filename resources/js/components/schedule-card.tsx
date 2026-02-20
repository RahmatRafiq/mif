import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { cn } from '@/lib/utils';
import type { Schedule } from '@/types/production';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { AlertCircle, Calendar, GripVertical, Package, TrendingUp } from 'lucide-react';
import { CSSProperties } from 'react';

interface ScheduleCardProps {
    schedule: Schedule;
    onView?: (schedule: Schedule) => void;
}

export default function ScheduleCard({ schedule, onView }: ScheduleCardProps) {
    const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
        id: schedule.id,
    });

    const style: CSSProperties = {
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isDragging ? 0.5 : 1,
    };

    const completionPercentage = schedule.completion_percentage || 0;
    const isDelayed = schedule.days_extended > 0;

    const statusColors = {
        pending: 'border-slate-500/50 bg-slate-500/10',
        in_progress: 'border-blue-500/50 bg-blue-500/10',
        completed: 'border-green-500/50 bg-green-500/10',
        delayed: 'border-red-500/50 bg-red-500/10',
    };

    const colorClass = statusColors[schedule.status] || statusColors.pending;

    return (
        <Card
            ref={setNodeRef}
            style={style}
            className={`mb-3 cursor-pointer border-l-4 transition-shadow hover:shadow-md ${colorClass}`}
            onClick={() => onView && onView(schedule)}
        >
            <CardHeader className="pb-3">
                <div className="flex items-start justify-between">
                    <div className="flex-1">
                        <CardTitle className="text-sm font-semibold">
                            <div className="flex items-center gap-2">
                                <Package className="h-4 w-4 text-muted-foreground" />
                                {schedule.order?.order_number || 'N/A'}
                            </div>
                        </CardTitle>
                        <p className="mt-1 text-xs text-muted-foreground">{schedule.order?.product_name || 'No product name'}</p>
                    </div>
                    <div {...attributes} {...listeners} className="cursor-grab active:cursor-grabbing">
                        <GripVertical className="h-5 w-5 text-muted-foreground" />
                    </div>
                </div>
            </CardHeader>

            <CardContent className="space-y-3 pb-4">
                {/* Line Info */}
                <div className="flex items-center gap-2 text-xs">
                    <TrendingUp className="h-3.5 w-3.5 text-muted-foreground" />
                    <span className="font-medium">{schedule.line?.code || 'N/A'}</span>
                    <span className="text-muted-foreground">-</span>
                    <span className="text-muted-foreground">{schedule.line?.name || 'Unknown Line'}</span>
                </div>

                {/* Date Range */}
                <div className="flex items-center gap-2 text-xs">
                    <Calendar className="h-3.5 w-3.5 text-muted-foreground" />
                    <span className="text-muted-foreground">
                        {schedule.start_date} → {schedule.current_finish_date || schedule.finish_date}
                    </span>
                </div>

                {/* Delay Warning */}
                {isDelayed && (
                    <div className="flex items-center gap-2 text-xs text-red-600">
                        <AlertCircle className="h-3.5 w-3.5" />
                        <span className="font-medium">Extended by {schedule.days_extended} days</span>
                    </div>
                )}

                {/* Progress */}
                <div className="space-y-1.5">
                    <div className="flex items-center justify-between text-xs">
                        <span className="text-muted-foreground">Progress</span>
                        <span className="font-medium">
                            {schedule.qty_completed || 0} / {schedule.qty_total_target || 0}
                        </span>
                    </div>
                    <Progress value={completionPercentage} className="h-1.5" />
                    <div className="text-right text-xs text-muted-foreground">{completionPercentage.toFixed(1)}%</div>
                </div>

                {/* Status Badge */}
                <div className="flex items-center gap-2">
                    <Badge
                        variant="outline"
                        className={cn(
                            "text-xs",
                            schedule.status === 'pending' && "border-slate-500/50 bg-slate-500/10 text-slate-600 dark:text-slate-400",
                            schedule.status === 'in_progress' && "border-blue-500/50 bg-blue-500/10 text-blue-600 dark:text-blue-400",
                            schedule.status === 'completed' && "border-green-500/50 bg-green-500/10 text-green-600 dark:text-green-400",
                            schedule.status === 'delayed' && "border-red-500/50 bg-red-500/10 text-red-600 dark:text-red-400"
                        )}
                    >
                        {schedule.status.replace('_', ' ').toUpperCase()}
                    </Badge>
                    {schedule.status === 'completed' && (
                        <span className="text-xs text-green-600 dark:text-green-400">✓ Complete</span>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}
