import React, { useState } from 'react';
import { Search, X } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface FilterField {
  key: string;
  label: string;
  type: 'text' | 'select';
  options?: Array<{ value: string; label: string }>;
}

interface FilterBarProps {
  filters: Record<string, string>;
  onApply: (filters: Record<string, string>) => void;
  fields: FilterField[];
}

export function FilterBar({ filters, onApply, fields }: FilterBarProps) {
  const [local, setLocal] = useState<Record<string, string>>(filters);

  function handleChange(key: string, value: string) {
    setLocal((prev) => ({ ...prev, [key]: value }));
  }

  function handleApply() {
    const cleaned = Object.fromEntries(Object.entries(local).filter(([, v]) => v !== ''));
    onApply(cleaned);
  }

  function handleReset() {
    const empty = Object.fromEntries(fields.map((f) => [f.key, '']));
    setLocal(empty);
    onApply({});
  }

  const hasFilters = Object.values(local).some((v) => v !== '');

  return (
    <div className="mb-4 rounded-lg border border-gray-200 bg-white p-4">
      <div className="flex flex-wrap items-end gap-3">
        {fields.map((field) => (
          <div key={field.key} className="flex flex-col gap-1 min-w-[160px]">
            <label className="text-xs font-medium text-gray-600">{field.label}</label>
            {field.type === 'select' ? (
              <select
                value={local[field.key] ?? ''}
                onChange={(e) => handleChange(field.key, e.target.value)}
                className="h-9 rounded-md border border-gray-300 bg-white px-2 text-sm focus:border-[#800020] focus:outline-none focus:ring-1 focus:ring-[#800020]"
              >
                <option value="">All</option>
                {field.options?.map((opt) => (
                  <option key={opt.value} value={opt.value}>{opt.label}</option>
                ))}
              </select>
            ) : (
              <input
                type="text"
                value={local[field.key] ?? ''}
                onChange={(e) => handleChange(field.key, e.target.value)}
                placeholder={`Filter by ${field.label.toLowerCase()}…`}
                className="h-9 rounded-md border border-gray-300 px-2 text-sm focus:border-[#800020] focus:outline-none focus:ring-1 focus:ring-[#800020]"
                onKeyDown={(e) => e.key === 'Enter' && handleApply()}
              />
            )}
          </div>
        ))}
        <div className="flex items-end gap-2">
          <Button onClick={handleApply} size="sm" className="h-9 bg-[#800020] hover:bg-[#6b001b]">
            <Search className="mr-1.5 h-3.5 w-3.5" /> Apply
          </Button>
          {hasFilters && (
            <Button onClick={handleReset} size="sm" variant="outline" className="h-9">
              <X className="mr-1.5 h-3.5 w-3.5" /> Clear
            </Button>
          )}
        </div>
      </div>
    </div>
  );
}
