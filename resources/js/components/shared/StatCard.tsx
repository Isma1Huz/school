import React from 'react';
import { Link } from '@inertiajs/react';
import { TrendingUp, TrendingDown } from 'lucide-react';
import { cn } from '@/lib/utils';

interface StatCardProps {
  label: string;
  value: number | string;
  icon: React.ElementType;
  color?: string;
  trend?: { value: number; up: boolean };
  href?: string;
}

function StatCardInner({ label, value, icon: Icon, color = 'text-blue-600', trend }: StatCardProps) {
  return (
    <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm hover:shadow-md transition-shadow">
      <div className="flex items-start justify-between">
        <div className="flex-1">
          <p className="text-sm font-medium text-gray-500">{label}</p>
          <p className="mt-2 text-3xl font-bold text-gray-900">{value}</p>
          {trend && (
            <div className={cn('mt-2 flex items-center gap-1 text-sm font-medium', trend.up ? 'text-green-600' : 'text-red-500')}>
              {trend.up ? <TrendingUp className="h-4 w-4" /> : <TrendingDown className="h-4 w-4" />}
              <span>{trend.value}% vs last month</span>
            </div>
          )}
        </div>
        <div className={cn('rounded-lg bg-gray-50 p-3', color)}>
          <Icon className="h-6 w-6" />
        </div>
      </div>
    </div>
  );
}

export function StatCard(props: StatCardProps) {
  if (props.href) {
    return (
      <Link href={props.href} className="block">
        <StatCardInner {...props} />
      </Link>
    );
  }
  return <StatCardInner {...props} />;
}
