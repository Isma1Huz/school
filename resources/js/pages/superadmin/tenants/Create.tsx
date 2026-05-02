import React from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { TextInput } from '@/components/forms/TextInput';
import { SelectInput } from '@/components/forms/SelectInput';
import type { PageProps, SubscriptionPlan, SelectOption } from '@/types';

interface CreateTenantProps extends PageProps {
  plans: SubscriptionPlan[];
}

const tenantTypes: SelectOption[] = [
  { value: 'school', label: 'School' },
  { value: 'business', label: 'Business' },
  { value: 'ngo', label: 'NGO' },
  { value: 'church', label: 'Church' },
];

const subscriptionStatuses: SelectOption[] = [
  { value: 'trial', label: 'Trial' },
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
  { value: 'cancelled', label: 'Cancelled' },
];

export default function CreateTenant() {
  const { plans } = usePage<CreateTenantProps>().props;

  const { data, setData, post, processing, errors } = useForm({
    name: '', type: 'school', email: '', status: 'active',
    subscription_plan_id: '', subscription_status: 'trial',
  });

  const planOptions: SelectOption[] = (plans ?? []).map((p) => ({ value: String(p.id), label: p.name }));

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    post('/super-admin/tenants');
  }

  return (
    <SuperAdminLayout>
      <Head title="New Tenant" />
      <PageHeader
        title="New Tenant"
        breadcrumbs={[{ label: 'Tenants', href: '/super-admin/tenants' }, { label: 'Create' }]}
      />
      <Card className="max-w-2xl">
        <CardContent className="pt-6">
          <form onSubmit={handleSubmit} className="space-y-4">
            <TextInput label="Tenant Name" value={data.name} onChange={(e) => setData('name', e.target.value)} error={errors.name} required />
            <div className="grid grid-cols-2 gap-4">
              <SelectInput label="Type" options={tenantTypes} value={data.type} onChange={(v) => setData('type', v)} error={errors.type} required />
              <SelectInput label="Status" options={[{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }]} value={data.status} onChange={(v) => setData('status', v)} error={errors.status} />
            </div>
            <TextInput label="Email" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} error={errors.email} />
            <div className="grid grid-cols-2 gap-4">
              <SelectInput label="Subscription Plan" options={planOptions} value={data.subscription_plan_id} onChange={(v) => setData('subscription_plan_id', v)} error={errors.subscription_plan_id} placeholder="Select a plan" />
              <SelectInput label="Subscription Status" options={subscriptionStatuses} value={data.subscription_status} onChange={(v) => setData('subscription_status', v)} error={errors.subscription_status} />
            </div>
            <div className="flex gap-3 pt-2">
              <Button type="submit" disabled={processing} className="bg-[#800020] hover:bg-[#6b001b]">{processing ? 'Creating…' : 'Create Tenant'}</Button>
              <Button type="button" variant="outline" onClick={() => history.back()}>Cancel</Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </SuperAdminLayout>
  );
}
