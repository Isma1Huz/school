import React, { useState } from 'react';
import { FlashMessage } from '@/components/shared/FlashMessage';
import { SuperAdminSidebar } from '@/components/superadmin/SuperAdminSidebar';
import { SuperAdminHeader } from '@/components/superadmin/SuperAdminHeader';

interface SuperAdminLayoutProps {
  children: React.ReactNode;
  title?: string;
  actions?: React.ReactNode;
  contextPanel?: React.ReactNode;
}

export default function SuperAdminLayout({
  children,
  title,
  actions,
  contextPanel,
}: SuperAdminLayoutProps) {
  const [collapsed, setCollapsed] = useState(false);

  return (
    <div className="flex h-screen overflow-hidden bg-gray-50">
      {/* Left Sidebar */}
      <SuperAdminSidebar collapsed={collapsed} onToggle={() => setCollapsed((c) => !c)} />

      {/* Main column */}
      <div className="flex flex-1 flex-col overflow-hidden">
        <SuperAdminHeader title={title} actions={actions} />

        {/* Content row */}
        <div className="flex flex-1 overflow-hidden">
          <main className="flex-1 overflow-y-auto p-6">
            <FlashMessage />
            {children}
          </main>

          {/* Right context panel */}
          {contextPanel && (
            <aside className="w-[280px] shrink-0 overflow-y-auto border-l border-gray-200 bg-white p-4">
              {contextPanel}
            </aside>
          )}
        </div>
      </div>
    </div>
  );
}
