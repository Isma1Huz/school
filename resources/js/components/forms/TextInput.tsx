import React from 'react';
import { cn } from '@/lib/utils';
import { FormField, Input } from '@/components/ui/form';

interface TextInputProps extends React.InputHTMLAttributes<HTMLInputElement> {
    label?: string;
    error?: string;
    hint?: string;
    wrapperClassName?: string;
}

export function TextInput({ label, error, hint, wrapperClassName, className, ...props }: TextInputProps) {
    return (
        <FormField
            label={label}
            error={error}
            hint={hint}
            required={props.required}
            className={wrapperClassName}
        >
            <Input className={cn(error && 'border-destructive focus-visible:ring-destructive', className)} {...props} />
        </FormField>
    );
}
