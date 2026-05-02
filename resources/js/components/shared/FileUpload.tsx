import React, { useRef } from 'react';
import { cn } from '@/lib/utils';
import { Upload, X } from 'lucide-react';

interface FileUploadProps {
    accept?: string;
    multiple?: boolean;
    onChange: (files: FileList | null) => void;
    value?: File | null;
    onRemove?: () => void;
    label?: string;
    hint?: string;
    className?: string;
}

export function FileUpload({
    accept,
    multiple = false,
    onChange,
    value,
    onRemove,
    label = 'Click to upload or drag and drop',
    hint,
    className,
}: FileUploadProps) {
    const inputRef = useRef<HTMLInputElement>(null);

    return (
        <div className={cn('space-y-2', className)}>
            <div
                onClick={() => inputRef.current?.click()}
                className="flex cursor-pointer flex-col items-center justify-center rounded-md border-2 border-dashed border-gray-300 p-6 text-center hover:border-primary"
            >
                <Upload className="mb-2 h-8 w-8 text-gray-400" />
                <p className="text-sm text-gray-600">{label}</p>
                {hint && <p className="mt-1 text-xs text-gray-400">{hint}</p>}
                <input
                    ref={inputRef}
                    type="file"
                    className="sr-only"
                    accept={accept}
                    multiple={multiple}
                    onChange={(e) => onChange(e.target.files)}
                />
            </div>

            {value && (
                <div className="flex items-center justify-between rounded-md border bg-gray-50 px-3 py-2 text-sm">
                    <span className="truncate text-gray-700">{value.name}</span>
                    {onRemove && (
                        <button
                            type="button"
                            onClick={onRemove}
                            className="ml-2 text-gray-400 hover:text-red-500"
                        >
                            <X className="h-4 w-4" />
                        </button>
                    )}
                </div>
            )}
        </div>
    );
}
