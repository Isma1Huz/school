import React from 'react';
import { cn } from '@/lib/utils';

interface StatusBadgeProps {
  status: string;
}

const statusConfig: Record<string, { bg: string; text: string; label?: string }> = {
  active:    { bg: 'bg-green-100',  text: 'text-green-800' },
  inactive:  { bg: 'bg-gray-100',   text: 'text-gray-600' },
  suspended: { bg: 'bg-red-100',    text: 'text-red-800' },
  trial:     { bg: 'bg-yellow-100', text: 'text-yellow-800' },
  pending:   { bg: 'bg-blue-100',   text: 'text-blue-800' },
  expired:   { bg: 'bg-orange-100', text: 'text-orange-800' },
  blocked:   { bg: 'bg-red-100',    text: 'text-red-800' },
  cancelled: { bg: 'bg-gray-100',   text: 'text-gray-600' },
  paid:      { bg: 'bg-green-100',  text: 'text-green-800' },
  unpaid:    { bg: 'bg-red-100',    text: 'text-red-700' },
  core:      { bg: 'bg-purple-100', text: 'text-purple-800' },
};

export function StatusBadge({ status }: StatusBadgeProps) {
  const config = statusConfig[status?.toLowerCase()] ?? { bg: 'bg-gray-100', text: 'text-gray-700' };
  return (
    <span className={cn('inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize', config.bg, config.text)}>
      {status}
    </span>
  );
}
