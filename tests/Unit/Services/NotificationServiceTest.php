<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Modules\Core\Models\Notification;
use App\Modules\Core\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\WithTenant;
use Tests\TestCase;

/**
 * Unit tests for NotificationService.
 * Covers: send, markAsRead, markAllAsRead, getUnread, getAll.
 */
class NotificationServiceTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    private NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake(); // Don't actually dispatch jobs
        $this->service = app(NotificationService::class);
    }

    // ---------------------------------------------------------------
    // send
    // ---------------------------------------------------------------

    public function test_send_creates_notification_record(): void
    {
        [$tenant, $admin] = $this->createTenant();

        $this->service->send($admin, 'welcome', [
            'title' => 'Welcome!',
            'body'  => 'Thanks for joining.',
        ], ['in_app']);

        $this->assertDatabaseHas('notifications', [
            'tenant_id'       => $tenant->id,
            'notifiable_type' => User::class,
            'notifiable_id'   => $admin->id,
            'type'            => 'welcome',
            'channel'         => 'in_app',
            'status'          => 'pending',
        ]);
    }

    public function test_send_creates_one_record_per_channel(): void
    {
        [$tenant, $admin] = $this->createTenant();

        $this->service->send($admin, 'announcement', [
            'title' => 'Announcement',
            'body'  => 'Important update.',
        ], ['in_app', 'email']);

        $this->assertDatabaseCount('notifications', 2);
    }

    public function test_send_dispatches_a_job_per_channel(): void
    {
        [$tenant, $admin] = $this->createTenant();

        $this->service->send($admin, 'test-event', [
            'title' => 'Test',
            'body'  => 'Test body',
        ], ['in_app', 'sms']);

        Queue::assertCount(2);
    }

    // ---------------------------------------------------------------
    // markAsRead
    // ---------------------------------------------------------------

    public function test_mark_as_read_sets_read_at_and_status(): void
    {
        [$tenant, $admin] = $this->createTenant();

        $notification = Notification::create([
            'tenant_id'       => $tenant->id,
            'notifiable_type' => User::class,
            'notifiable_id'   => $admin->id,
            'type'            => 'info',
            'data'            => ['title' => 'Test', 'body' => 'Body'],
            'channel'         => 'in_app',
            'status'          => 'sent',
        ]);

        $this->service->markAsRead($notification->id);

        $this->assertDatabaseHas('notifications', [
            'id'     => $notification->id,
            'status' => 'read',
        ]);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_mark_as_read_rejects_notification_from_another_tenant(): void
    {
        [$tenantA, $adminA] = $this->createTenant();
        [$tenantB]          = $this->createTenant([], ['email' => 'admin2@ns.test']);

        $notificationB = Notification::withoutGlobalScopes()->create([
            'tenant_id'       => $tenantB->id,
            'notifiable_type' => User::class,
            'notifiable_id'   => $adminA->id,
            'type'            => 'info',
            'data'            => ['title' => 'Other'],
            'channel'         => 'in_app',
            'status'          => 'sent',
        ]);

        $this->bindTenant($tenantA); // bind tenant A

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        try {
            $this->service->markAsRead($notificationB->id);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
            throw new \Illuminate\Auth\Access\AuthorizationException();
        }
    }

    // ---------------------------------------------------------------
    // markAllAsRead
    // ---------------------------------------------------------------

    public function test_mark_all_as_read_marks_all_unread_for_user(): void
    {
        [$tenant, $admin] = $this->createTenant();

        foreach (range(1, 3) as $i) {
            Notification::create([
                'tenant_id'       => $tenant->id,
                'notifiable_type' => User::class,
                'notifiable_id'   => $admin->id,
                'type'            => "event-{$i}",
                'data'            => ['title' => "Notification {$i}"],
                'channel'         => 'in_app',
                'status'          => 'sent',
            ]);
        }

        $this->service->markAllAsRead($admin);

        $unread = Notification::withoutGlobalScopes()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $admin->id)
            ->whereNull('read_at')
            ->count();

        $this->assertEquals(0, $unread);
    }

    // ---------------------------------------------------------------
    // getUnread
    // ---------------------------------------------------------------

    public function test_get_unread_returns_only_unread_notifications(): void
    {
        [$tenant, $admin] = $this->createTenant();

        // One unread, one read
        Notification::create([
            'tenant_id'       => $tenant->id,
            'notifiable_type' => User::class,
            'notifiable_id'   => $admin->id,
            'type'            => 'unread-event',
            'data'            => ['title' => 'Unread'],
            'channel'         => 'in_app',
            'status'          => 'sent',
        ]);

        Notification::create([
            'tenant_id'       => $tenant->id,
            'notifiable_type' => User::class,
            'notifiable_id'   => $admin->id,
            'type'            => 'read-event',
            'data'            => ['title' => 'Already Read'],
            'channel'         => 'in_app',
            'status'          => 'read',
            'read_at'         => now(),
        ]);

        $unread = $this->service->getUnread($admin);

        $this->assertCount(1, $unread);
        $this->assertEquals('unread-event', $unread->first()->type);
    }

    public function test_get_unread_respects_limit(): void
    {
        [$tenant, $admin] = $this->createTenant();

        foreach (range(1, 25) as $i) {
            Notification::create([
                'tenant_id'       => $tenant->id,
                'notifiable_type' => User::class,
                'notifiable_id'   => $admin->id,
                'type'            => "event-{$i}",
                'data'            => ['title' => "Notification {$i}"],
                'channel'         => 'in_app',
                'status'          => 'sent',
            ]);
        }

        $unread = $this->service->getUnread($admin, 10);

        $this->assertCount(10, $unread);
    }

    // ---------------------------------------------------------------
    // getAll (paginated)
    // ---------------------------------------------------------------

    public function test_get_all_returns_paginated_notifications(): void
    {
        [$tenant, $admin] = $this->createTenant();

        foreach (range(1, 5) as $i) {
            Notification::create([
                'tenant_id'       => $tenant->id,
                'notifiable_type' => User::class,
                'notifiable_id'   => $admin->id,
                'type'            => "page-event-{$i}",
                'data'            => ['title' => "Page Notif {$i}"],
                'channel'         => 'in_app',
                'status'          => 'sent',
            ]);
        }

        $paginated = $this->service->getAll($admin, 3);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $paginated);
        $this->assertEquals(5, $paginated->total());
        $this->assertCount(3, $paginated->items());
    }
}
