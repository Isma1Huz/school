import React from 'react';
import { Head, Link } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';

export default function TenantSettings() {
    return (
        <TenantLayout title="Settings" breadcrumbs={[{ label: 'Settings' }]}>
            <Head title="Settings" />
            <Tabs defaultValue="general">
                <TabsList>
                    <TabsTrigger value="general">General</TabsTrigger>
                    <TabsTrigger value="modules">Modules</TabsTrigger>
                    <TabsTrigger value="permissions">Permissions</TabsTrigger>
                    <TabsTrigger value="subscription">Subscription</TabsTrigger>
                </TabsList>
                <TabsContent value="general">
                    <Link href="/settings/general" className="text-primary text-sm hover:underline">Open General Settings →</Link>
                </TabsContent>
                <TabsContent value="modules">
                    <Link href="/settings/modules" className="text-primary text-sm hover:underline">Manage Modules →</Link>
                </TabsContent>
                <TabsContent value="permissions">
                    <Link href="/settings/permissions" className="text-primary text-sm hover:underline">Manage Permissions →</Link>
                </TabsContent>
                <TabsContent value="subscription">
                    <Link href="/settings/subscription" className="text-primary text-sm hover:underline">Subscription Details →</Link>
                </TabsContent>
            </Tabs>
        </TenantLayout>
    );
}
