import React, { useState } from 'react';
import { cn } from '@/lib/utils';
import { ChevronDown, ChevronRight } from 'lucide-react';

interface PermissionNode {
    key: string;
    label: string;
    children?: PermissionNode[];
}

interface PermissionTreeProps {
    nodes: PermissionNode[];
    selected: string[];
    onChange: (selected: string[]) => void;
    className?: string;
}

function getAllKeys(nodes: PermissionNode[]): string[] {
    return nodes.flatMap((n) => [n.key, ...(n.children ? getAllKeys(n.children) : [])]);
}

function TreeNode({
    node,
    selected,
    onChange,
}: {
    node: PermissionNode;
    selected: string[];
    onChange: (selected: string[]) => void;
}) {
    const [expanded, setExpanded] = useState(true);
    const hasChildren = !!node.children?.length;
    const isChecked = selected.includes(node.key);
    const childKeys = node.children ? getAllKeys(node.children) : [];
    const allChildrenChecked = childKeys.every((k) => selected.includes(k));
    const someChildrenChecked = childKeys.some((k) => selected.includes(k));

    function toggle() {
        if (hasChildren) {
            // Toggle all children
            if (allChildrenChecked) {
                onChange(selected.filter((k) => !childKeys.includes(k)));
            } else {
                onChange([...new Set([...selected, ...childKeys])]);
            }
        } else {
            if (isChecked) {
                onChange(selected.filter((k) => k !== node.key));
            } else {
                onChange([...selected, node.key]);
            }
        }
    }

    return (
        <div>
            <div className="flex items-center gap-2 py-1">
                {hasChildren && (
                    <button
                        type="button"
                        onClick={() => setExpanded((v) => !v)}
                        className="text-gray-400 hover:text-gray-600"
                    >
                        {expanded ? <ChevronDown className="h-4 w-4" /> : <ChevronRight className="h-4 w-4" />}
                    </button>
                )}
                {!hasChildren && <span className="w-4" />}
                <input
                    type="checkbox"
                    id={node.key}
                    checked={hasChildren ? allChildrenChecked : isChecked}
                    ref={(el) => {
                        if (el) el.indeterminate = hasChildren && someChildrenChecked && !allChildrenChecked;
                    }}
                    onChange={toggle}
                    className="h-4 w-4 rounded border-gray-300 text-primary"
                />
                <label htmlFor={node.key} className="cursor-pointer select-none text-sm text-gray-700">
                    {node.label}
                </label>
            </div>
            {hasChildren && expanded && (
                <div className="ml-6">
                    {node.children!.map((child) => (
                        <TreeNode key={child.key} node={child} selected={selected} onChange={onChange} />
                    ))}
                </div>
            )}
        </div>
    );
}

export function PermissionTree({ nodes, selected, onChange, className }: PermissionTreeProps) {
    return (
        <div className={cn('rounded-md border p-4', className)}>
            {nodes.map((node) => (
                <TreeNode key={node.key} node={node} selected={selected} onChange={onChange} />
            ))}
        </div>
    );
}
