import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
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
            {...attributes}
            {...listeners}
            className={`mb-1.5 cursor-move border-l-4 transition-shadow hover:shadow-md ${colorClass}`}
        >
            <CardHeader className="px-2.5 py-2 pb-1">
                <div className="flex items-start justify-between">
                    <div
                        className="flex-1"
                        onClick={(e) => {
                            e.stopPropagation();
                            if (onView) {
                                onView(schedule);
                            }
                        }}
                    >
                        <CardTitle className="text-xs font-semibold">
                            <div className="flex items-center gap-1.5">
                                <Package className="h-3 w-3 text-muted-foreground" />
                                {schedule.order?.order_number || 'N/A'}
                            </div>
                        </CardTitle>
                        <p className="mt-0.5 text-[10px] text-muted-foreground line-clamp-1">{schedule.order?.product_name || 'No product name'}</p>
                    </div>
                    <div className="cursor-grab active:cursor-grabbing">
                        <GripVertical className="h-4 w-4 text-muted-foreground" />
                    </div>
                </div>
            </CardHeader>

            <CardContent className="space-y-1 px-2.5 py-1.5 pt-0">
                {/* Line Info */}
                <div className="flex items-center gap-1.5 text-[10px]">
                    <TrendingUp className="h-3 w-3 text-muted-foreground" />
                    <span className="font-medium">{schedule.line?.code || 'N/A'}</span>
                    <span className="text-muted-foreground">-</span>
                    <span className="text-muted-foreground truncate">{schedule.line?.name || 'Unknown Line'}</span>
                </div>

                {/* Date Range */}
                <div className="flex items-center gap-1.5 text-[10px]">
                    <Calendar className="h-3 w-3 text-muted-foreground" />
                    <span className="text-muted-foreground">
                        {schedule.start_date} → {schedule.current_finish_date || schedule.finish_date}
                    </span>
                </div>

                {/* Delay Warning */}
                {isDelayed && (
                    <div className="flex items-center gap-1.5 text-[10px] text-red-600">
                        <AlertCircle className="h-3 w-3" />
                        <span className="font-medium">+{schedule.days_extended}d</span>
                    </div>
                )}

                {/* Progress */}
                <div className="space-y-0.5">
                    <div className="flex items-center justify-between text-[10px]">
                        <span className="text-muted-foreground">Progress</span>
                        <span className="font-medium">
                            {schedule.qty_completed || 0} / {schedule.qty_total_target || 0}
                        </span>
                    </div>
                    <Progress value={completionPercentage} className="h-1" />
                    <div className="text-right text-[9px] text-muted-foreground">{completionPercentage.toFixed(1)}%</div>
                </div>
            </CardContent>
        </Card>
    );
}
