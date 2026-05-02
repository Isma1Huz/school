import React from 'react';

interface CurriculumNode {
    id: number;
    name: string;
    children?: CurriculumNode[];
}

interface CurriculumTreeProps {
    nodes: CurriculumNode[];
    onSelect?: (node: CurriculumNode) => void;
}

function TreeNode({ node, onSelect }: { node: CurriculumNode; onSelect?: (n: CurriculumNode) => void }) {
    return (
        <div className="ml-4 border-l border-gray-200 pl-3">
            <button
                className="my-1 text-sm text-gray-700 hover:text-primary"
                onClick={() => onSelect?.(node)}
            >
                {node.name}
            </button>
            {node.children?.map((child) => (
                <TreeNode key={child.id} node={child} onSelect={onSelect} />
            ))}
        </div>
    );
}

export function CurriculumTree({ nodes, onSelect }: CurriculumTreeProps) {
    return (
        <div className="rounded-md border p-3">
            {nodes.map((node) => (
                <TreeNode key={node.id} node={node} onSelect={onSelect} />
            ))}
        </div>
    );
}
