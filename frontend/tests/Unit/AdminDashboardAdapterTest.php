<?php

namespace Tests\Unit;

use App\Frontend\Admin\AdminDashboardAdapter;
use App\Models\AdminAPI;
use Mockery;
use Tests\TestCase;

class AdminDashboardAdapterTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_load_transforms_backend_kpis_successfully(): void
    {
        $mockApi = Mockery::mock(AdminAPI::class);

        $mockApi->shouldReceive('getDashboard')->once()->andReturn([
            'statusCode' => 200,
            'message'    => 'OK',
            'data'       => [
                'users' => [
                    'total'    => 150,
                    'new_30d'  => 25,
                    'prev_30d' => 20,
                    'inactive' => 3,
                    'by_type'  => [
                        'candidate' => 100,
                        'company'   => 45,
                        'admin'     => 5,
                    ],
                ],
                'companies' => [
                    'total'     => 45,
                    'new_30d'   => 8,
                    'by_status' => [
                        'pending'   => 4,
                        'approved'  => 38,
                        'rejected'  => 2,
                        'suspended' => 1,
                    ],
                ],
                'jobs' => [
                    'total'     => 80,
                    'published' => 65,
                ],
                'applications' => [
                    'total'    => 320,
                    'last_30d' => 90,
                    'prev_30d' => 70,
                ],
                'interviews' => [
                    'scheduled' => 15,
                ],
                'trend' => [
                    ['month' => '2026-05', 'label' => 'May', 'users' => 10, 'applications' => 25],
                    ['month' => '2026-06', 'label' => 'Jun', 'users' => 15, 'applications' => 40],
                ],
                'pending_companies' => [
                    ['id' => 1, 'name' => 'Acme Inc', 'industry' => 'Tech', 'contact_email' => 'a@acme.com', 'created_at' => '2026-10-01T00:00:00Z'],
                ],
                'recent_activity' => [
                    ['id' => 10, 'action' => 'company.approved', 'auditable_type' => 'Company', 'user' => 'Super Admin', 'created_at' => '2026-10-28T10:00:00Z'],
                ],
                'generated_at' => '2026-10-28T12:00:00Z',
            ],
        ]);

        $adapter = new AdminDashboardAdapter($mockApi);
        $result = $adapter->load();

        $this->assertNull($result['loadError']);
        $stats = $result['stats'];

        // Users KPIs
        $this->assertSame(150, $stats['users']['total']);
        $this->assertTrue($stats['users']['growth_available']);
        $this->assertSame(25, $stats['users']['new_30d']);
        $this->assertSame(20, $stats['users']['prev_30d']);
        $this->assertTrue($stats['users']['inactive_available']);
        $this->assertSame(3, $stats['users']['inactive']);
        $this->assertSame(100, $stats['users']['by_type']['candidate']);

        // Companies KPIs
        $this->assertSame(45, $stats['companies']['total']);
        $this->assertSame(4, $stats['companies']['by_status']['pending']);

        // Jobs KPIs
        $this->assertTrue($stats['jobs']['available']);
        $this->assertSame(80, $stats['jobs']['total']);
        $this->assertSame(65, $stats['jobs']['published']);

        // Applications KPIs
        $this->assertTrue($stats['applications']['available']);
        $this->assertSame(320, $stats['applications']['total']);
        $this->assertSame(90, $stats['applications']['last_30d']);
        $this->assertSame(70, $stats['applications']['prev_30d']);

        // Interviews KPIs
        $this->assertTrue($stats['interviews']['available']);
        $this->assertSame(15, $stats['interviews']['scheduled']);

        // Trends & Activity
        $this->assertTrue($stats['trend_available']);
        $this->assertCount(2, $stats['trend']);
        $this->assertCount(1, $stats['pending_companies']);
        $this->assertCount(1, $stats['recent_activity']);
        $this->assertSame('Super Admin', $stats['recent_activity'][0]['user']['name']);
        $this->assertSame('2026-10-28T12:00:00Z', $stats['generated_at']);
    }

    public function test_load_falls_back_to_legacy_when_dashboard_endpoint_fails(): void
    {
        $mockApi = Mockery::mock(AdminAPI::class);

        // Dashboard endpoint fails
        $mockApi->shouldReceive('getDashboard')->once()->andReturn([
            'statusCode' => 500,
            'message'    => 'Server error',
            'data'       => null,
        ]);

        // Legacy fallback calls
        $mockApi->shouldReceive('getUsers')->andReturn([
            'statusCode' => 200,
            'data'       => [],
            'meta'       => ['total' => 50],
        ]);

        $mockApi->shouldReceive('getCompanies')->andReturn([
            'statusCode' => 200,
            'data'       => [],
            'meta'       => ['total' => 12],
        ]);

        $mockApi->shouldReceive('getAuditLogs')->andReturn([
            'statusCode' => 200,
            'data'       => [],
            'meta'       => ['total' => 0],
        ]);

        $adapter = new AdminDashboardAdapter($mockApi);
        $result = $adapter->load();

        $this->assertNotNull($result['stats']);
        $this->assertSame(50, $result['stats']['users']['total']);
        $this->assertSame(12, $result['stats']['companies']['total']);
        $this->assertFalse($result['stats']['jobs']['available']);
        $this->assertFalse($result['stats']['applications']['available']);
    }
}
