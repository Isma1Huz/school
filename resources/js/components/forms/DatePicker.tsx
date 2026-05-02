import React from 'react';
import { format } from 'date-fns';
import { Calendar as CalendarIcon } from 'lucide-react';
import { DayPicker } from 'react-day-picker';
import * as Popover from '@radix-ui/react-popover';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form';

interface DatePickerProps {
    label?: string;
    error?: string;
    hint?: string;
    required?: boolean;
    value?: Date | null;
    onChange?: (date: Date | undefined) => void;
    placeholder?: string;
    disabled?: boolean;
    wrapperClassName?: string;
}

export function DatePicker({
    label,
    error,
    hint,
    required,
    value,
    onChange,
    placeholder = 'Pick a date',
    disabled,
    wrapperClassName,
}: DatePickerProps) {
    return (
        <FormField label={label} error={error} hint={hint} required={required} className={wrapperClassName}>
            <Popover.Root>
                <Popover.Trigger asChild>
                    <Button
                        variant="outline"
                        disabled={disabled}
                        className={cn(
                            'w-full justify-start text-left font-normal',
                            !value && 'text-muted-foreground',
                            error && 'border-destructive',
                        )}
                    >
                        <CalendarIcon className="mr-2 h-4 w-4" />
                        {value ? format(value, 'PPP') : placeholder}
                    </Button>
                </Popover.Trigger>
                <Popover.Portal>
                    <Popover.Content align="start" className="z-50 rounded-md border bg-white p-0 shadow-lg">
                        <DayPicker
                            mode="single"
                            selected={value ?? undefined}
                            onSelect={onChange}
                            initialFocus
                        />
                    </Popover.Content>
                </Popover.Portal>
            </Popover.Root>
        </FormField>
    );
}
