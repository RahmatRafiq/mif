// Production Schedule Management Types

export interface Line {
    id: number;
    name: string;
    code: string;
    description?: string;
    capacity_per_day: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    deleted_at?: string;
    schedules_count?: number;
}

export interface Order {
    id: number;
    order_number: string;
    product_name: string;
    product_code?: string;
    qty_total: number;
    customer?: string;
    order_date: string;
    due_date: string;
    status: 'pending' | 'scheduled' | 'in_progress' | 'completed' | 'cancelled';
    notes?: string;
    created_at: string;
    updated_at: string;
    deleted_at?: string;
    total_scheduled_qty?: number;
    total_completed_qty?: number;
    remaining_qty?: number;
    schedules_count?: number;
}

export interface Schedule {
    id: number;
    order_id: number;
    line_id: number;
    start_date: string;
    finish_date: string;
    current_finish_date: string;
    qty_total_target: number;
    qty_completed: number;
    days_extended: number;
    status: 'pending' | 'in_progress' | 'completed' | 'delayed';
    notes?: string;
    created_at: string;
    updated_at: string;
    deleted_at?: string;

    // Relationships
    order?: Order;
    line?: Line;
    daily_outputs?: ScheduleDailyOutput[];

    // Computed attributes
    remaining_qty?: number;
    completion_percentage?: number;
    total_days?: number;
    current_total_days?: number;
    base_target_per_day?: number;
    remainder?: number;
    is_delayed?: boolean;
}

export interface ScheduleDailyOutput {
    id: number;
    schedule_id: number;
    date: string;
    target_output: number;
    actual_output: number;
    balance: number;
    is_completed: boolean;
    notes?: string;
    created_at: string;
    updated_at: string;

    // Relationship
    schedule?: Schedule;

    // Computed attributes
    achievement_percentage?: number;
}

export interface ScheduleFormData {
    order_id: number | null;
    line_id: number | null;
    start_date: string;
    finish_date: string;
    qty_total_target: number;
    notes?: string;
}

export interface LineFormData {
    name: string;
    code: string;
    description?: string;
    capacity_per_day: number;
    is_active: boolean;
}

export interface OrderFormData {
    order_number: string;
    product_name: string;
    product_code?: string;
    qty_total: number;
    customer?: string;
    order_date: string;
    due_date: string;
    status?: 'pending' | 'scheduled' | 'in_progress' | 'completed' | 'cancelled';
    notes?: string;
}

export interface ActualOutputInput {
    daily_output_id: number;
    actual_output: number;
}
