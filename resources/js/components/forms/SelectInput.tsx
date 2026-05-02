import React from 'react';
import * as SelectPrimitive from '@radix-ui/react-select';
import { ChevronDown, Check } from 'lucide-react';
import { cn } from '@/lib/utils';
import { FormField, Label } from '@/components/ui/form';
import type { SelectOption } from '@/types';

interface SelectInputProps {
    label?: string;
    error?: string;
    hint?: string;
    required?: boolean;
    placeholder?: string;
    options: SelectOption[];
    value?: string;
    onChange?: (value: string) => void;
    disabled?: boolean;
    wrapperClassName?: string;
}

export function SelectInput({
    label,
    error,
    hint,
    required,
    placeholder = 'Select…',
    options,
    value,
    onChange,
    disabled,
    wrapperClassName,
}: SelectInputProps) {
    return (
        <FormField label={label} error={error} hint={hint} required={required} className={wrapperClassName}>
            <SelectPrimitive.Root value={value} onValueChange={onChange} disabled={disabled}>
                <SelectPrimitive.Trigger
                    className={cn(
                        'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                        error && 'border-destructive',
                    )}
                >
                    <SelectPrimitive.Value placeholder={placeholder} />
                    <SelectPrimitive.Icon>
                        <ChevronDown className="h-4 w-4 opacity-50" />
                    </SelectPrimitive.Icon>
                </SelectPrimitive.Trigger>
                <SelectPrimitive.Portal>
                    <SelectPrimitive.Content className="z-50 overflow-hidden rounded-md border bg-white shadow-md">
                        <SelectPrimitive.Viewport className="p-1">
                            {options.map((opt) => (
                                <SelectPrimitive.Item
                                    key={opt.value}
                                    value={opt.value}
                                    disabled={opt.disabled}
                                    className="relative flex cursor-pointer select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm text-gray-700 hover:bg-gray-100 focus:outline-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
                                >
                                    <span className="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
                                        <SelectPrimitive.ItemIndicator>
                                            <Check className="h-4 w-4" />
                                        </SelectPrimitive.ItemIndicator>
                                    </span>
                                    <SelectPrimitive.ItemText>{opt.label}</SelectPrimitive.ItemText>
                                </SelectPrimitive.Item>
                            ))}
                        </SelectPrimitive.Viewport>
                    </SelectPrimitive.Content>
                </SelectPrimitive.Portal>
            </SelectPrimitive.Root>
        </FormField>
    );
}
