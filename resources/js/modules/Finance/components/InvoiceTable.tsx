import React from 'react';
import { formatDate, formatCurrency } from '@/lib/formatters';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';

interface Invoice { id: number; number: string; amount: number; status: string; due_date: string; }

interface InvoiceTableProps {
    invoices: Invoice[];
}

export function InvoiceTable({ invoices }: InvoiceTableProps) {
    return (
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Invoice #</TableHead>
                    <TableHead>Amount</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Due Date</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {invoices.map((inv) => (
                    <TableRow key={inv.id}>
                        <TableCell>{inv.number}</TableCell>
                        <TableCell>{formatCurrency(inv.amount)}</TableCell>
                        <TableCell>
                            <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${
                                inv.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                            }`}>
                                {inv.status}
                            </span>
                        </TableCell>
                        <TableCell>{formatDate(inv.due_date)}</TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
